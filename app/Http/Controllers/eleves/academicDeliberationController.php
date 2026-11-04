<?php namespace gesecole\Http\Controllers\eleves;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use gesecole\Services\AcademicDeliberationService;

class academicDeliberationController extends academicDocumentController {
    private function manager(){
        if(!Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU) || Auth::user()->isTeacher())abort(403,'La délibération et ses rapports sont réservés à l’administration autorisée.');
        if(!Schema::hasTable('academic_deliberations'))abort(503,'L’administrateur doit initialiser le module de délibération.');
    }
    private function signingKey(){ $key=config('app.key');if(!$key)abort(503,'La clé de signature de l’application doit être configurée.');return $key; }
    private function contextFields($context){$fields=array();foreach(array('id_campus','id_tutelle','id_classe','id_cycle','id_nniveau','modele','year','periode')as $key)$fields[$key]=$context[$key];return $fields;}
    public function index(Request $request){
        $this->manager();foreach(array('id_tutelle','id_classe','id_cycle','id_nniveau')as $key)if(!$request->input($key))return redirect()->to(url('/eleves/documents-tutelles').'?'.http_build_query($request->only('modele','id_tutelle','id_classe','id_cycle','id_nniveau')))->with('deliberation_selection',true);
        $context=$this->context($request);$context['session_mode']='final';$service=new AcademicDeliberationService;$data=$service->data($context);$criteria=$service->criteria($request->all());$plan=null;$payload=null;$signature=null;
        if($request->input('operation')==='previsualiser'){
            $plan=$service->preview($data,$criteria);
            $payload=base64_encode(json_encode(array('context'=>$this->contextFields($context),'criteria'=>$criteria,'fingerprint'=>$plan['fingerprint'],'id_user'=>(int)Auth::id(),'created'=>time(),'nonce'=>\Illuminate\Support\Str::random(40))));
            $signature=hash_hmac('sha256',$payload,$this->signingKey());
        }
        return view('eleves.academic_documents.deliberations',array('titre'=>'Délibérations par tutelle','context'=>$context,'data'=>$data,'criteria'=>$criteria,'types'=>AcademicDeliberationService::types(),'plan'=>$plan,'payload'=>$payload,'signature'=>$signature));
    }
    public function apply(Request $request){
        $this->manager();$this->validate($request,array('payload'=>'required|string|max:65536','signature'=>'required|string|size:64','propositions'=>'required|array','motif'=>'required|string|min:3|max:1000'));
        $payload=$request->input('payload');if(!hash_equals(hash_hmac('sha256',$payload,$this->signingKey()),$request->input('signature')))abort(403,'L’aperçu de délibération a été altéré.');
        $decoded=base64_decode($payload,true);$snapshot=$decoded===false?null:json_decode($decoded,true);
        if(!$snapshot || !isset($snapshot['context'],$snapshot['criteria'],$snapshot['fingerprint'],$snapshot['created'],$snapshot['id_user'],$snapshot['nonce']))abort(422,'Aperçu invalide.');
        if((int)$snapshot['id_user']!==(int)Auth::id() || time()-(int)$snapshot['created']>3600 || (int)$snapshot['created']>time()+60)abort(409,'Cet aperçu est expiré ou appartient à un autre utilisateur.');
        $context=$this->context($request);$context['session_mode']='final';if($snapshot['context']!==$this->contextFields($context))abort(403,'L’aperçu appartient à un autre campus, une autre année ou une autre tutelle.');
        $service=new AcademicDeliberationService;$criteria=$service->criteria($snapshot['criteria']);$keys=$request->input('propositions');foreach($keys as $key)if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/',$key))abort(422,'Proposition invalide.');
        $keys=array_values(array_unique($keys));sort($keys);$motif=trim($request->input('motif'));if(mb_strlen($motif)<3)abort(422,'Indiquez le motif de la décision du jury.');
        $selectionHash=hash('sha256',json_encode(array($keys,$motif)));$requestKey=hash('sha256',$snapshot['nonce']);
        $id=$service->apply($context,$criteria,$snapshot['fingerprint'],$keys,$requestKey,$selectionHash,$motif,function($c,$s,$seq){return $this->authorizeNote($c,$s,$seq);});
        $query=$this->contextFields($context);unset($query['id_campus'],$query['year']);$query['id_lot']=$id;
        return redirect()->to(url('/eleves/notes/deliberations/rapport').'?'.http_build_query($query))->with('success','Délibération appliquée. Les valeurs avant/après sont conservées dans le rapport.');
    }
    private function reportContext(Request $request){
        $this->manager();$this->renewSchoolYear();
        $this->validate($request,array('modele'=>'required|in:'.implode(',',array_keys(config('academic_documents.profiles'))),'id_tutelle'=>'required|integer|min:1',
            'id_classe'=>'integer|min:0','id_cycle'=>'integer|min:0','id_nniveau'=>'integer|min:0','id_lot'=>'integer|min:0',
            'rapport_semestre'=>'integer|min:0|max:10','rapport_examen'=>'in:tous,normal,rattrapage','rapport_type'=>'in:'.implode(',',array_merge(array('tous'),array_keys(AcademicDeliberationService::types()))),
            'rapport_format'=>'in:synthetique,detaille','rapport_portee'=>'in:globale,selection','page_size'=>'in:a4,a3'));
        $campus=$this->currentCampus();if(!$campus)abort(422,'Choisissez le campus.');$tutelle=\gesecole\TutelleAcademique::findOrFail($request->input('id_tutelle'));
        if($tutelle->modele_documents!==$request->input('modele'))abort(422,'Le modèle ne correspond pas à la tutelle.');
        $global=$request->input('rapport_portee')==='globale';$context=array('id_campus'=>(int)$campus->id,'id_tutelle'=>(int)$tutelle->id,'campus'=>$campus,'tutelle'=>$tutelle,'modele'=>$request->input('modele'),
            'year'=>session('year'),'profile'=>config('academic_documents.profiles.'.$request->input('modele')),'periode'=>'annuel','session_mode'=>'final');
        foreach(array('id_classe'=>'classe','id_cycle'=>'cycle','id_nniveau'=>'nniveau')as $key=>$label){$context[$key]=$global?0:(int)$request->input($key,0);
            $table=$key==='id_nniveau'?'n_niveaus':$label;$context[$label]=$context[$key]?DB::table($table)->where('id',$context[$key])->first():(object)array('nom'=>'Tous / toutes');
            if(!$context[$label])abort(422,'Filtre de rapport invalide.');}
        return $context;
    }
    private function reportData(Request $request,$paginate){
        $context=$this->reportContext($request);$items=DB::table('academic_deliberation_items as i')->join('academic_deliberations as b','b.id','=','i.id_deliberation')
            ->where('b.id_campus',$context['id_campus'])->where('b.id_tutelle',$context['id_tutelle'])->where('b.annee_scolaire',$context['year']);
        foreach(array('id_classe','id_cycle','id_nniveau')as $key)if($context[$key])$items->where('b.'.$key,$context[$key]);
        if($request->input('id_lot'))$items->where('b.id',(int)$request->input('id_lot'));
        if($request->input('rapport_type','tous')!=='tous')$items->where('b.type',$request->input('rapport_type'));
        if($request->input('rapport_semestre'))$items->where('i.semestre',(int)$request->input('rapport_semestre'));
        $exam=$request->input('rapport_examen','tous');if($exam!=='tous')$items->whereIn('i.sequence',$exam==='normal'?array(3,4):array(5,6));
        $copy=clone $items;$batchIds=array();foreach($copy->select('b.id as batch_id')->distinct()->get()as $row)$batchIds[]=$row->batch_id;$batches=DB::table('academic_deliberations')->whereIn('id',$batchIds)->orderBy('id','desc');
        $copy=clone $items;$metrics=$copy->select(DB::raw('COUNT(*) AS lignes, COUNT(DISTINCT i.id_eleve) AS etudiants, COUNT(DISTINCT (i.id_classe_matiere,i.semestre)) AS matieres'))->first();
        $metrics->lots=count($batchIds);$batches=$paginate?$batches->paginate(20):$batches->get();$visible=array();foreach($batches as $batch)$visible[]=$batch->id;
        $copy=clone $items;$rows=$copy->whereIn('b.id',$visible)->select('i.*','b.type','b.motif','b.id_creator','b.created_at as date_decision')->orderBy('b.id','desc')->orderBy('i.semestre')->orderBy('i.id')->get();$groups=array();
        foreach($rows as $row){$row->snapshot=json_decode($row->details,true);$groups[$row->id_deliberation][]=$row;}
        return array('context'=>$context,'report'=>array('batches'=>$batches,'groups'=>$groups,'metrics'=>$metrics),'filters'=>$request->only('modele','id_tutelle','id_classe','id_cycle','id_nniveau','rapport_type','rapport_semestre','rapport_examen','rapport_format','rapport_portee','id_lot'),
            'format'=>$request->input('rapport_format','detaille'),'types'=>AcademicDeliberationService::types(),'titre'=>'Rapport des délibérations');
    }
    public function report(Request $request){
        $this->manager();if(!$request->input('id_tutelle') || !$request->input('modele'))return redirect()->to(url('/eleves/documents-tutelles'))->with('deliberation_selection',true);
        return view('eleves.academic_documents.deliberation_report',$this->reportData($request,true));
    }
    public function reportDocumentData(Request $request){
        $report=$this->reportData($request,false);$request->merge(array('document'=>'deliberation_rapport'));
        $data=array('context'=>$report['context'],'results'=>array(),'sessions'=>array(),'metadata'=>array(),'deliberation_report'=>$report['report'],'report_format'=>$report['format'],'deliberation_types'=>$report['types']);
        return $this->stationery($data,$request);
    }
    public function printReport(Request $request){return view('eleves.academic_documents.print',$this->reportDocumentData($request));}
    public function exportReport(Request $request){
        $data=$this->reportData($request,false);$stream=fopen('php://temp','w+');
        fputcsv($stream,array('Lot','Date','Tutelle','Specialite','Cycle','Niveau','Type','Matricule','Etudiant','Module','Matiere','Semestre','Sequence','Note finale avant /20','Note finale apres /20','Contribution avant /100','Contribution apres /100','Utilisateur','Motif'),';');
        foreach($data['report']['groups']as $rows)foreach($rows as $r){$d=$r->snapshot;$row=array($r->id_deliberation,$r->date_decision,$d['tutelle'],$d['classe'],$d['cycle'],$d['nniveau'],$data['types'][$r->type],$d['matricule'],$d['etudiant'],$d['module'],$d['matiere'],$r->semestre,$r->sequence,$d['final_before'],$d['final_after'],$d['note_before'],$d['note_after'],$r->id_creator,$r->motif);
            foreach($row as &$cell)if(is_string($cell) && preg_match('/^[=+@\-\t\r]/',$cell))$cell="'".$cell;unset($cell);fputcsv($stream,$row,';');}
        rewind($stream);$csv="\xEF\xBB\xBF".stream_get_contents($stream);fclose($stream);
        return response($csv)->header('Content-Type','text/csv; charset=UTF-8')->header('Content-Disposition','attachment; filename="rapport_deliberations.csv"')->header('Cache-Control','private, no-store');
    }
}