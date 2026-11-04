<?php namespace gesecole\Http\Controllers\eleves;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use gesecole\Services\AcademicDocumentService;
use gesecole\Services\AcademicAnonymatService;

class academicAnonymatController extends academicDocumentController {
    private function data(Request $request,$write=false)
    {
        if(!Schema::hasTable('academic_exam_anonymats'))abort(503,'Le module d’anonymats doit être initialisé par l’administrateur.');
        $context=$this->context($request);$service=new AcademicDocumentService;$sessions=$service->sessions($context);$selected=null;
        foreach($sessions as $session)if((int)$session->id===(int)$request->input('id_session'))$selected=$session;
        if($request->input('id_session') && !$selected)abort(404,'Session absente de ce contexte.');
        $parity=$selected?(int)$selected->semestre%2:1;
        $sequence=(int)$request->input('sequence',$parity?3:4);$mode=$request->input('mode_generation','matiere');
        if(!in_array($mode,array('matiere','examen'),true))abort(422,'Mode d’anonymat invalide.');
        $rows=array();$notes=array();$selection=null;
        if($selected){
            if(Auth::user()->isTeacher() && !DB::table('dispense')->where('annee_scolaire',$context['year'])->where('id_personnel',Auth::user()->id_personnel)
                ->where('id_classe',$context['id_classe'])->where('id_nniveau',$context['id_nniveau'])->where('id_matiere',$selected->matiere_id)->exists())abort(403,'Cette matière ne vous est pas affectée.');
            $anonymats=new AcademicAnonymatService;$selection=$anonymats->selection($context,$selected,$sequence,$mode);
            if($write)$this->authorizeNote($context,$selected,$sequence);
            $rows=$anonymats->rows($context,$selected,$sequence,$mode);$notes=$anonymats->notes($context,$selected,$sequence);
        }
        return array('titre'=>'Anonymats par tutelle','context'=>$context,'sessions'=>$sessions,'selected'=>$selected,'sequence'=>$sequence,
            'mode'=>$mode,'rows'=>$rows,'notes'=>$notes,'selection'=>$selection,'canManage'=>!Auth::user()->isTeacher());
    }
    private function selected($data){if(!$data['selected'])abort(422,'Choisissez une matière et son évaluation.');}
    private function manager(){if(Auth::user()->isTeacher())abort(403,'La gestion des correspondances est réservée à l’administration.');}
    private function target($data)
    {
        $query=array();foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode')as $key)$query[$key]=$data['context'][$key];
        $query['id_session']=$data['selected']?$data['selected']->id:null;$query['sequence']=$data['sequence'];$query['mode_generation']=$data['mode'];
        return url('/eleves/notes/anonymats').'?'.http_build_query($query);
    }
    public function index(Request $request)
    {
        foreach(array('id_tutelle','id_classe','id_cycle','id_nniveau')as $key)if(!$request->input($key))return redirect()->to(url('/eleves/documents-tutelles').'?'.http_build_query($request->only('modele','id_tutelle','id_classe','id_cycle','id_nniveau')))->with('anonymat_selection',true);
        $data=$this->data($request);
        if(!$data['canManage']){unset($data['notes']);$data['rows']=array_map(function($row){return (object)array('code'=>$row->code);},$data['rows']);}
        return view('eleves.academic_documents.anonymats',$data);
    }
    public function generateCodes(Request $request)
    {
        $this->manager();$data=$this->data($request,true);$this->selected($data);$created=0;$anonymats=new AcademicAnonymatService;
        DB::transaction(function()use($data,$request,$anonymats,&$created){
            $sessions=array($data['selected']);
            if($data['mode']==='matiere' && $request->input('toutes_matieres')){
                $sessions=array();foreach($data['sessions']as $s)if((int)$s->semestre===(int)$data['selected']->semestre){
                    $parity=(int)$s->semestre%2?1:2;$column=$data['sequence']<=4?'sn'.$parity:'ratrappage'.$parity;
                    if((float)$s->$column>0)$sessions[]=$s;
                }
                usort($sessions,function($a,$b){return $a->id===$b->id?0:($a->id<$b->id?-1:1);});
            }
            foreach($sessions as $s)$created+=$anonymats->generate($data['context'],$s,$data['sequence'],$data['mode']);
        });
        return redirect()->to($this->target($data))->with('success',$created.' code(s) créé(s). Les codes déjà attribués sont conservés.');
    }
    public function removeCode(Request $request)
    {
        $this->manager();$this->validate($request,array('id_anonymat'=>'required|integer','version'=>'required|string'));
        $data=$this->data($request,true);$this->selected($data);
        (new AcademicAnonymatService)->remove($data['context'],$data['selected'],$data['sequence'],$data['mode'],$request->input('id_anonymat'),$request->input('version'));
        return redirect()->to($this->target($data))->with('success','Code retiré. Aucune note n’a été modifiée.');
    }
    public function entry(Request $request)
    {
        $data=$this->data($request,true);$this->selected($data);$rows=array();$parity=(int)$data['selected']->semestre%2?1:2;
        $column=$data['sequence']<=4?'sn'.$parity:'ratrappage'.$parity;$weight=(float)$data['selected']->$column;
        foreach($data['rows']as $row){$n=isset($data['notes'][$row->id_eleve])?$data['notes'][$row->id_eleve]:null;
            $rows[]=array('id'=>$row->id,'code'=>$row->code,'note'=>$n?round($n->note*20/$weight,2):'', 'version'=>$n?hash('sha256',$n->toJson()):'new');
        }
        unset($data['notes']);$data['rows']=$rows;$data['weight']=$weight;
        return view('eleves.academic_documents.anonymat_entry',$data);
    }
    public function storeAnonymousNotes(Request $request)
    {
        $this->validate($request,array('notes_anonymes'=>'required|array','versions'=>'required|array','supprimer'=>'array','session_fingerprint'=>'required|string'));
        $data=$this->data($request,true);$this->selected($data);
        return DB::transaction(function()use($request,$data){
            (new AcademicAnonymatService)->lock($data['context'],$data['selected'],$data['sequence']);
            $data=$this->data($request,true);$map=array();foreach($data['rows']as $row)$map[$row->id]=$row;
            $notes=array();$versions=array();$codes=array();$deletes=array();$inputVersions=$request->input('versions',array());$deleteIds=$request->input('supprimer',array());
            foreach($deleteIds as $id)if(!array_key_exists($id,$request->input('notes_anonymes')))abort(422,'Suppression sans anonymat soumis.');
            foreach($request->input('notes_anonymes')as $id=>$raw){
                if(!isset($map[$id]))abort(403,'Anonymat absent de cette évaluation et de ce contexte.');
                $row=$map[$id];$notes[$row->id_eleve]=$raw;$versions[$row->id_eleve]=isset($inputVersions[$id])?$inputVersions[$id]:null;$codes[$row->id_eleve]=$row->code;
                if(in_array((string)$id,array_map('strval',$deleteIds),true))$deletes[]=$row->id_eleve;
            }
            $request->merge(array('notes'=>$notes,'versions'=>$versions,'anonymats'=>$codes,'supprimer'=>$deletes,'anonymat_mode'=>$data['mode']));
            $request->attributes->set('academic_anonymat_entry',true);
            parent::saveNotes($request);
            return redirect()->to(str_replace('/notes/anonymats?','/notes/anonymats/saisie?',$this->target($data)))->with('success','Notes enregistrées par anonymat.');
        });
    }
}