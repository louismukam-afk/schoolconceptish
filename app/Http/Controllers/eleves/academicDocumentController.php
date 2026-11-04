<?php namespace gesecole\Http\Controllers\eleves;

use gesecole\Http\Controllers\Controller;
use gesecole\AcademicDocumentGeneration;
use gesecole\TutelleAcademique;
use gesecole\NNiveau;
use gesecole\cycle;
use gesecole\classe;
use gesecole\note;
use gesecole\Services\AcademicDocumentService;
use gesecole\Services\AcademicResultCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class academicDocumentController extends Controller {
    public function index(Request $request)
    {
        $this->renewSchoolYear();
        $campusId = $this->currentCampusId();
        $classIds = DB::table('campus_specialite')->where('id_campus', $campusId)->lists('id_classe');
        $affectations = array();
        foreach(DB::table('specialite_tutelle')->whereIn('id_classe',$classIds)->get() as $a) $affectations[$a->id_classe][]=(string)$a->id_tutelle;
        $choices=DB::table('session_exams as se')->join('classe_matiere as cm','cm.id','=','se.id_classe_matiere')
            ->join('matiere as m','m.id','=','cm.id_matiere')->whereIn('cm.id_classe',$classIds)->where('se.annee_academique',session('year'))
            ->select('se.id','se.id_tutelle','cm.id_classe','cm.id_cycle','cm.id_nniveau','cm.semestre','m.nom')->get();
        return view('eleves.academic_documents.index', array(
            'titre' => 'Documents et notes par tutelle', 'profiles' => config('academic_documents.profiles'),
            'documents' => config('academic_documents.documents'),
            'tutelles' => TutelleAcademique::whereNotNull('modele_documents')->orderBy('nom')->get(),
            'classes' => classe::whereIn('classe.id', $classIds)->leftJoin('niveau','niveau.id','=','classe.id_niveau')->select('classe.*','niveau.nom as filiere_nom')->orderBy('classe.nom')->get(),
            'cycles' => cycle::orderBy('nom')->get(), 'nniveaux' => NNiveau::orderBy('ordre')->get(),
            'selection' => $request->all(), 'campus' => $this->currentCampus(), 'affectations'=>$affectations, 'contextChoices'=>$choices,
        ));
    }

    protected function context(Request $request)
    {
        $this->renewSchoolYear();
        $service = new AcademicDocumentService;
        return $service->context($request->all(), $this->currentCampusId(), session('year'));
    }

    private function documentData(Request $request)
    {
        $this->validate($request, array('document' => 'required|in:'.implode(',', array_keys(config('academic_documents.documents'))),
            'page_size' => 'in:a4,a3', 'id_eleve' => 'integer', 'id_session' => 'integer', 'date_soutenance' => 'date', 'attestation_jury_date'=>'date_format:Y-m-d', 'attestation_issue_date'=>'date_format:Y-m-d', 'attestation_reference'=>'string|max:80'));
        $context = $this->context($request);
        if($request->input('document')==='anonymat' && Auth::user()->isTeacher())abort(403,'Les correspondances sont réservées à l’administration.');
        if($context['periode']!=='annuel' && in_array($request->input('document'),array('admis','merite','non_admis','a_deliberer','attestation'),true))abort(422,'Les listes d’admission et attestations utilisent l’année complète.');
        $summaryDocuments = array('pv','pv_groupes','pv_modules','admis','merite','non_admis','a_deliberer','releve','releve_modules','attestation','statistiques');
        $sessionId = in_array($request->input('document'), $summaryDocuments, true) ? null : $request->input('id_session');
        $includeUngraded = in_array($request->input('document'), array('pv_modules','releve_modules','anonymat_codes','anonymat_report','anonymat_presence','anonymat_fiches','programme','presence','anonymat','report','matiere_cc','matiere_sn','matiere_tpe','matiere_rat'), true);
        $data = (new AcademicDocumentService)->build($context, $request->input('id_eleve'), $sessionId, !$includeUngraded);
        if(in_array($request->input('document'),array('pv_modules','releve_modules'),true))$data=(new \gesecole\Services\AcademicModuleService)->build($data);
        $anonymatDocuments=array('anonymat_codes','anonymat_report','anonymat_presence','anonymat_fiches');
        if(in_array($request->input('document'),$anonymatDocuments,true)){
            if(!Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU))abort(403,'Accès réservé aux utilisateurs autorisés à gérer les évaluations.');
            if($request->input('document')!=='anonymat_report' && Auth::user()->isTeacher())abort(403,'Les correspondances sont réservées à l’administration.');
            if(!\Illuminate\Support\Facades\Schema::hasTable('academic_exam_anonymats'))abort(503,'Initialisez le module d’anonymats.');
            $session=null;foreach($data['sessions']as $s)if((int)$request->input('id_session')===(int)$s->id)$session=$s;
            if(!$session)abort(422,'Choisissez une matière pour éditer les fiches d’anonymat.');
            if(Auth::user()->isTeacher())$this->authorizeNote($context,$session,(int)$request->input('sequence',((int)$session->semestre%2?3:4)));
            $sequence=(int)$request->input('sequence',((int)$session->semestre%2?3:4));
            $data['anonymat_rows']=(new \gesecole\Services\AcademicAnonymatService)->rows($context,$session,$sequence,$request->input('mode_generation','matiere'));
            if($request->input('id_eleve'))$data['anonymat_rows']=array_values(array_filter($data['anonymat_rows'],function($row)use($request){return (int)$row->id_eleve===(int)$request->input('id_eleve');}));
            $data['anonymat_session']=$session;$data['anonymat_sequence']=$sequence;
            $data['anonymat_mode']=$request->input('mode_generation','matiere');
            $data['context']['session_mode']=$sequence<=4?'normal':'final';
            if($request->input('document')==='anonymat_report'){
                $data['results']=array();$data['metadata']=array();
                $data['anonymat_rows']=array_map(function($row){return (object)array('code'=>$row->code);},$data['anonymat_rows']);
            }
        }
        return $this->stationery($data,$request);
    }

    protected function stationery($data, Request $request)
    {
        $context=$data['context'];
        $data['document'] = $request->input('document');
        $data['label'] = config('academic_documents.documents.'.$data['document']);
        $data['page_size'] = $request->input('page_size') ?: 'a4';
        $large=$data['page_size']==='a3';
        $data['certificate']=$data['document']==='attestation' && config('academic_attestations.'.$context['modele'])!==null;
        $data['certificate_authority']=config('academic_attestations.'.$context['modele']);
        $data['attestation_reference']=$request->input('attestation_reference');
        $data['attestation_jury_date']=$request->input('attestation_jury_date')?date('d/m/Y',strtotime($request->input('attestation_jury_date'))):'……………………';
        $data['attestation_issue_date']=$request->input('attestation_issue_date')?date('d/m/Y',strtotime($request->input('attestation_issue_date'))):date('d/m/Y');
        $landscape=in_array($data['document'],array('pv','pv_groupes','pv_modules','deliberation_rapport','rattrapage_global','report','programme'),true);
        $landscape=$landscape || $data['certificate'];
        $data['paper_orientation']=$landscape?'landscape':'portrait';
        $data['paper_margin']=$large?12:10;
        $data['paper_width']=$landscape?($large?420:297):($large?297:210);
        $data['paper_font']=$large?13:11;
        $columns=count($data['sessions'])+6;
        if(isset($data['modules']))foreach($data['modules'] as $module)if($module['is_grouped'])$columns++;
        $data['matrix_font']=$large?($columns>35?9:11):($columns>35?6:($columns>23?7:8));
        $paperQuery=$request->only('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode','session_mode','document','id_eleve','id_session','date_soutenance','attestation_reference','attestation_jury_date','attestation_issue_date','mode_generation','sequence','rapport_type','rapport_semestre','rapport_examen','rapport_format','rapport_portee','id_lot');
        $data['paper_links']=array();
        foreach(array('a4','a3') as $format)$data['paper_links'][$format]=url('/eleves/documents-tutelles/apercu').'?'.http_build_query(array_merge($paperQuery,array('page_size'=>$format)));
        $data['date_soutenance'] = $request->input('date_soutenance');
        $data['generated_at'] = date('d/m/Y H:i');
        $data['archived'] = false;
        $campus=$context['campus'];
        $data['footer_lines']=array(
            $campus->localisation ?: config('academic_stationery.adresse'),
            'B.P. : '.($campus->boite_postale ?: config('academic_stationery.boite_postale')).' · Tél. : '.($campus->telephone ?: config('academic_stationery.telephone')).' · Fax : '.config('academic_stationery.fax'),
            'E-mail : '.config('academic_stationery.email').' · Site web : '.($campus->site_web ?: config('academic_stationery.site_web')),
        );
        if(in_array($data['document'],array('releve','releve_modules'),true))$data['footer_lines'][]=config('academic_stationery.note_releve');
        // Incorporer le logo dans le HTML : les archives restent autonomes.
        $campusLogo=public_path('img/logo.jpg');
        $data['logo_campus']=is_file($campusLogo)?'data:image/jpeg;base64,'.base64_encode(file_get_contents($campusLogo)):'';
        if($context['modele']==='polytechnique'){
            $polytechniqueLogo=public_path('images/tutelles/testok/polytechnique.png');
            $data['logo_polytechnique']=is_file($polytechniqueLogo)?'data:image/png;base64,'.base64_encode(file_get_contents($polytechniqueLogo)):'';
        }
        foreach (array('logo'=>'logo_tutelle','logo_faculte'=>'logo_faculte') as $field=>$key) {
            $path = $context['tutelle']->$field ? realpath(public_path($context['tutelle']->$field)) : false;
            $public = realpath(public_path()).DIRECTORY_SEPARATOR;
            $data[$key] = '';
            if ($path && strpos(strtolower($path), strtolower($public)) === 0 && is_file($path)) {
                $info = getimagesize($path);
                if ($info) $data[$key] = 'data:'.$info['mime'].';base64,'.base64_encode(file_get_contents($path));
            }
        }
        if($data['certificate'] && $context['modele']==='ngaoundere' && !$data['logo_faculte']){
            $iutLogo=public_path('img/iut.jpg');
            if(is_file($iutLogo))$data['logo_faculte']='data:image/jpeg;base64,'.base64_encode(file_get_contents($iutLogo));
        }
        $filtered = array();
        foreach ($data['results'] as $r) {
            $decision = $r['summary']['decision']; $doc = $data['document'];
            if (in_array($doc, array('admis', 'merite', 'attestation')) && $decision !== 'ADMIS') continue;
            if ($doc === 'non_admis' && $decision !== 'NON ADMIS') continue;
            if ($doc === 'a_deliberer' && $decision !== 'A DELIBERER') continue;
            $filtered[] = $r;
        }
        if ($data['document'] !== 'merite') usort($filtered, function ($a, $b) { return strcasecmp($a['student']->nom.' '.$a['student']->prenom, $b['student']->nom.' '.$b['student']->prenom); });
        $data['results'] = $filtered;
        return $data;
    }

    public function preview(Request $request)
    {
        if($request->input('document')==='deliberation_rapport')return (new academicDeliberationController)->printReport($request);
        return view('eleves.academic_documents.print', $this->documentData($request));
    }

    public function students(Request $request)
    {
        $context=$this->context($request);$rows=array();
        foreach((new AcademicDocumentService)->students($context)as $s)$rows[]=array('id'=>$s->id,'label'=>trim($s->nom.' '.$s->prenom).' · '.$s->matricule);
        return response()->json($rows)->header('Cache-Control','private, no-store');
    }

    public function exportCsv(Request $request)
    {
        if($request->input('document')==='deliberation_rapport')return (new academicDeliberationController)->exportReport($request);
        $data=$this->documentData($request);
        if(isset($data['anonymat_rows']))return $this->anonymatCsv($data);
        $stream=fopen('php://temp','w+');
        $moduleReports=isset($data['modules']);
        $moduleKeys=array();
        if($moduleReports)foreach($data['modules']as $key=>$module)foreach($module['subjects']as $uv)$moduleKeys[$uv['key']]=$key;
        $columns=array('Tutelle','Annee','Periode','Matricule','Etudiant','Code','Matiere','UE','Semestre','Credits','CC /20','TPE /20','SN /20','Rattrapage /20','Final /20','Session','Moyenne /20','Credits capitalises','Decision','Grade','MGP lineaire /4','Mention');
        if($moduleReports)$columns=array_merge($columns,array('Identifiant du groupe','Moyenne du module /20','Credits du module'));
        fputcsv($stream,$columns,';');
        foreach($data['results']as $r)foreach($r['subjects']as $subject){
            $res=$subject['result'];
            $module=$moduleReports?$r['modules'][$moduleKeys[$subject['programme_id'].':'.$subject['semestre']]]:null;
            if($data['document']==='rattrapage' && $res['normal']!==null && $res['normal']>=10)continue;
            if($data['document']==='rattrapage_effectue' && $res['components']['rat']===null)continue;
            $row=array($data['context']['tutelle']->nom,$data['context']['year'],$data['context']['periode'],$r['student']->matricule,$r['student']->nom.' '.$r['student']->prenom,$subject['code'],$subject['nom'],$moduleReports?$module['nom']:$subject['module'],$subject['semestre'],$subject['credit']);
            foreach(array('cc','tpe','sn','rat')as $part)$row[]=$res['components'][$part]!==null && $subject['weights'][$part]>0?round($res['components'][$part]*20/$subject['weights'][$part],4):'';
            $row[]=$res['final'];$row[]=$res['session'];$row[]=$r['summary']['incomplete']?'Incomplet':$r['summary']['average'];$row[]=$r['summary']['credits'];$row[]=$r['summary']['decision'];$row[]=$res['grade'];$row[]=$r['summary']['incomplete']?'':$r['summary']['mgp'];$row[]=$r['summary']['incomplete']?'':$r['summary']['mention'];
            if($moduleReports)$row=array_merge($row,array($module['id_matiere_mere'],$module['is_grouped']?($module['average']===null?($module['complete']?'Non calculable':'Incomplet'):$module['average']):'',$module['is_grouped']?$module['credits']:''));
            foreach($row as &$cell)if(is_string($cell) && preg_match('/^[=+@\-\t\r]/',$cell))$cell="'".$cell;
            unset($cell);fputcsv($stream,$row,';');
        }
        rewind($stream);$csv="\xEF\xBB\xBF".stream_get_contents($stream);fclose($stream);
        return response($csv)->header('Content-Type','text/csv; charset=UTF-8')->header('Content-Disposition','attachment; filename="'.$data['context']['modele'].'_'.$data['document'].'.csv"')->header('Cache-Control','private, no-store');
    }

    private function anonymatCsv($data)
    {
        $stream=fopen('php://temp','w+');$anonymous=$data['document']==='anonymat_report';
        $header=$anonymous?array('Code anonymat','Matiere','Semestre','Evaluation','Note /20','Observations'):array('Code anonymat','Matricule','Etudiant','Matiere','Semestre','Evaluation');
        fputcsv($stream,$header,';');
        foreach($data['anonymat_rows']as $record){
            $evaluation=$data['anonymat_sequence']<=4?'Session normale':'Rattrapage';
            $row=$anonymous?array($record->code,$data['anonymat_session']->nom,$data['anonymat_session']->semestre,$evaluation,'',''):
                array($record->code,$record->student->matricule,$record->student->nom.' '.$record->student->prenom,$data['anonymat_session']->nom,$data['anonymat_session']->semestre,$evaluation);
            foreach($row as &$cell)if(is_string($cell) && preg_match('/^[=+@\-\t\r]/',$cell))$cell="'".$cell;unset($cell);
            fputcsv($stream,$row,';');
        }
        rewind($stream);$csv="\xEF\xBB\xBF".stream_get_contents($stream);fclose($stream);
        return response($csv)->header('Content-Type','text/csv; charset=UTF-8')->header('Content-Disposition','attachment; filename="'.$data['document'].'.csv"')->header('Cache-Control','private, no-store');
    }

    private function authorizeAnonymatArchive($document)
    {
        if($document==='deliberation_rapport' && (Auth::user()->isTeacher() || !Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU)))abort(403,'Rapport réservé à l’administration des délibérations.');
        if($document==='anonymat' && Auth::user()->isTeacher())abort(403,'Les correspondances sont réservées à l’administration.');
        if(!in_array($document,array('anonymat_codes','anonymat_report','anonymat_presence','anonymat_fiches'),true))return;
        if(!Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU) || ($document!=='anonymat_report' && Auth::user()->isTeacher()))abort(403,'Accès aux documents d’anonymat non autorisé.');
    }
    public function generate(Request $request)
    {
        $data = $request->input('document')==='deliberation_rapport'?(new academicDeliberationController)->reportDocumentData($request):$this->documentData($request);
        $data['archived'] = true;
        $html = view('eleves.academic_documents.print', $data)->render();
        $generation = new AcademicDocumentGeneration;
        foreach (array('id_campus', 'id_tutelle', 'id_classe', 'id_cycle', 'id_nniveau', 'modele') as $key) $generation->$key = $data['context'][$key];
        $generation->annee_academique = session('year');
        $generation->document = $data['document']; $generation->periode = $data['context']['periode'];
        $generation->effectif = isset($data['deliberation_report'])?$data['deliberation_report']['metrics']->etudiants:(isset($data['anonymat_rows'])?count($data['anonymat_rows']):count($data['results']));
        $snapshot=$data['context'];
        $snapshot['document_selection']=$request->only('id_eleve','id_session','date_soutenance','attestation_reference','attestation_jury_date','attestation_issue_date','page_size','mode_generation','sequence','rapport_type','rapport_semestre','rapport_examen','rapport_format','rapport_portee','id_lot');
        $generation->contexte = json_encode($snapshot);
        $generation->contenu = $html; $generation->empreinte = hash('sha256', $html);
        $generation->id_creator = Auth::id(); $generation->save();
        return redirect()->to('/eleves/documents-tutelles/generations/'.$generation->id);
    }

    public function history(Request $request)
    {
        $query = AcademicDocumentGeneration::forCampus($this->currentCampusId())->where('annee_academique', session('year'));
        if(!Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU))$query->whereNotIn('document',array('deliberation_rapport','anonymat_codes','anonymat_report','anonymat_presence','anonymat_fiches'));
        elseif(Auth::user()->isTeacher())$query->whereNotIn('document',array('deliberation_rapport','anonymat','anonymat_codes','anonymat_presence','anonymat_fiches'));
        foreach (array('id_tutelle', 'modele', 'id_classe', 'document') as $key) if ($request->input($key)) $query->where($key, $request->input($key));
        return view('eleves.academic_documents.history', array('titre' => 'Historique des documents par tutelle', 'generations' => $query->orderBy('id', 'desc')->paginate(30), 'filters' => $request->all()));
    }

    public function printSaved($id)
    {
        $generation = AcademicDocumentGeneration::forCampus($this->currentCampusId())->findOrFail($id);
        $this->authorizeAnonymatArchive($generation->document);
        return response($generation->verifiedContent())->header('Content-Type', 'text/html; charset=UTF-8')->header('Cache-Control', 'private, no-store');
    }

    public function destroy($id)
    {
        $generation = AcademicDocumentGeneration::forCampus($this->currentCampusId())->findOrFail($id);
        $this->authorizeAnonymatArchive($generation->document);
        $generation->delete();
        return redirect()->to('/eleves/documents-tutelles/historique');
    }

    public function notes(Request $request)
    {
        foreach(array('id_tutelle','id_classe','id_cycle','id_nniveau') as $field){
            if(!ctype_digit((string)$request->input($field)) || (int)$request->input($field)<=0){
                $selection=$request->only('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode','session_mode','document','page_size');
                return redirect()->to(url('/eleves/documents-tutelles').'?'.http_build_query($selection))
                    ->withErrors(array('contexte'=>'Choisissez la tutelle, la spécialité, le cycle et le niveau avant d’ouvrir la saisie des notes. Si aucun cycle ou niveau n’est proposé, configurez les sessions d’examen de cette formation pour l’année sélectionnée.'));
            }
        }
        $context = $this->context($request);
        $service = new AcademicDocumentService;
        $sessions = $service->sessions($context); $selected = null;
        foreach ($sessions as $s) if ((int) $request->input('id_session') === (int) $s->id) $selected = $s;
        $students = $service->students($context); $notes = array();
        $defaultSequence=1;
        if($selected){$parity=(int)$selected->semestre%2?1:2;$columns=array('cc'.$parity=>$parity,'sn'.$parity=>$parity+2,'tpe'.$parity=>$parity+6,'ratrappage'.$parity=>$parity+4);foreach($columns as $column=>$seq)if((float)$selected->$column>0){$defaultSequence=$seq;break;}}
        $sequence = (int) $request->input('sequence', $defaultSequence);
        if (!in_array($sequence, array(1,2,3,4,5,6,7,8))) abort(422, 'Évaluation invalide.');
        if ($selected) {
            $this->authorizeNote($context, $selected, $sequence);
            if(Auth::user()->isTeacher() && in_array($sequence,array(3,4,5,6),true) && \Illuminate\Support\Facades\Schema::hasTable('academic_exam_anonymats')){
                $anonymats=new \gesecole\Services\AcademicAnonymatService;
                foreach(array('matiere','examen')as $mode)if(count($anonymats->rows($context,$selected,$sequence,$mode))){
                    $query=$request->only('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode');
                    $query['id_session']=$selected->id;$query['sequence']=$sequence;$query['mode_generation']=$mode;
                    return redirect()->to(url('/eleves/notes/anonymats/saisie').'?'.http_build_query($query));
                }
            }
            $ids = array(); foreach ($students as $s) $ids[] = $s->id;
            $query = note::where('annee_scolaire', $context['year'])->where('id_campus', $context['id_campus'])
                ->where('id_session', $selected->id)->where('id_classe_matiere', $selected->programme_id)->where('sequence', $sequence)->whereIn('id_eleve', $ids);
            foreach ($query->get() as $n) {
                if (isset($notes[$n->id_eleve])) abort(422, 'Notes en double : corrigez ces doublons avant la saisie.');
                $notes[$n->id_eleve] = $n;
            }
        }
        return view('eleves.academic_documents.notes', array('titre' => 'Saisie des notes par tutelle', 'context' => $context, 'sessions' => $sessions, 'selected' => $selected, 'students' => $students, 'notes' => $notes, 'sequence' => $sequence, 'input' => $request->all()));
    }

    protected function authorizeNote($context, $session, $sequence)
    {
        $parity=(int)$session->semestre%2?1:2;
        if(!in_array((int)$sequence,array($parity,$parity+2,$parity+4,$parity+6),true))abort(422,'Cette évaluation ne correspond pas au semestre de la matière.');
        $user = Auth::user();
        if ($user->isTeacher() && !DB::table('dispense')->where('annee_scolaire', $context['year'])
            ->where('id_personnel', $user->id_personnel)->where('id_matiere', $session->matiere_id)
            ->where('id_classe', $context['id_classe'])->where('id_nniveau', $context['id_nniveau'])->exists()) abort(403, 'Cette matière ne vous est pas affectée.');
        $lock = DB::table('verrou_note')->where('annee_scolaire', $context['year'])->where('sequence', $sequence)
            ->where('target', 'note_sequence')->where('is_active', true)
            ->where(function ($q) use ($context) { $q->where('id_campus', $context['id_campus'])->orWhereNull('id_campus'); })
            ->where(function ($q) { $q->whereNull('starts_at')->orWhere('starts_at', '<=', date('Y-m-d H:i:s')); })
            ->where(function ($q) { $q->whereNull('ends_at')->orWhere('ends_at', '>=', date('Y-m-d H:i:s')); })->exists();
        if ($lock) abort(423, 'Cette évaluation est verrouillée.');
        $columns = array(1 => 'cc1', 2 => 'cc2', 3 => 'sn1', 4 => 'sn2', 5 => 'ratrappage1', 6 => 'ratrappage2', 7 => 'tpe1', 8 => 'tpe2');
        $weight = (float) $session->{$columns[$sequence]};
        if ($weight <= 0) abort(422, 'Cette évaluation n’est pas configurée dans la session.');
        return $weight;
    }

    public function saveNotes(Request $request)
    {
        $this->validate($request, array('id_session' => 'required|integer', 'sequence' => 'required|in:1,2,3,4,5,6,7,8', 'notes' => 'array', 'supprimer' => 'array', 'anonymat_mode'=>'in:matiere,examen'));
        $context = $this->context($request); $service = new AcademicDocumentService; $selected = null;
        foreach ($service->sessions($context) as $s) if ((int) $s->id === (int) $request->input('id_session')) $selected = $s;
        if (!$selected) abort(404, 'Session absente de ce contexte.');
        if($request->input('session_fingerprint') && !hash_equals(hash('sha256',json_encode($selected)),$request->input('session_fingerprint')))abort(409,'La configuration de la session a changé. Rechargez la liste.');
        $sequence = (int) $request->input('sequence'); $weight = $this->authorizeNote($context, $selected, $sequence);
        $allowed = array(); foreach ($service->students($context) as $student) $allowed[$student->id] = true;
        $submitted = (array) $request->input('notes', array()); $deletes = (array) $request->input('supprimer', array());
        foreach ($deletes as $id) if (!array_key_exists($id, $submitted)) $submitted[$id] = '';
        $versions = (array) $request->input('versions', array());
        $anonymatService=\Illuminate\Support\Facades\Schema::hasTable('academic_exam_anonymats')?new \gesecole\Services\AcademicAnonymatService:null;
        DB::transaction(function () use ($submitted, $allowed, $deletes, $versions, $context, $selected, $sequence, $weight, $request, $anonymatService) {
            if($anonymatService && in_array($sequence,array(3,4,5,6),true))$anonymatService->lock($context,$selected,$sequence);
            foreach ($submitted as $studentId => $raw) {
                if (!isset($allowed[$studentId])) abort(403, 'Étudiant hors du contexte autorisé.');
                $delete = in_array((string) $studentId, array_map('strval', $deletes), true);
                if ($raw === '' && !$delete) continue;
                if (!$delete && (!is_numeric($raw) || (float) $raw < 0 || (float) $raw > 20)) abort(422, 'Les notes doivent être comprises entre 0 et 20.');
                DB::select('SELECT pg_advisory_xact_lock(hashtext(?))', array('academic-note:'.$context['id_campus'].':'.$context['year'].':'.$selected->id.':'.$sequence.':'.$studentId));
                $rows = note::where('annee_scolaire', $context['year'])->where('id_campus', $context['id_campus'])
                    ->where('id_classe', $context['id_classe'])->where('id_session', $selected->id)
                    ->where('id_classe_matiere', $selected->programme_id)->where('id_eleve', $studentId)->where('sequence', $sequence)->lockForUpdate()->get();
                if (count($rows) > 1) abort(409, 'Plusieurs notes existent pour cette évaluation.');
                $n = count($rows) ? $rows->first() : null;
                $version = $n ? hash('sha256', $n->toJson()) : 'new';
                if (!isset($versions[$studentId]) || !hash_equals($version, $versions[$studentId])) abort(409, 'Une note a changé depuis l’ouverture de la page. Rechargez la liste.');
                $before = $n ? $n->toJson() : null;
                if ($delete && !$n) continue;
                $operation = $delete ? 'suppression' : ($n ? 'modification' : 'creation');
                $assignedCode=$anonymatService?$anonymatService->codeForNote($context,$selected,$sequence,$studentId,$request->input('anonymat_mode')):null;
                if($assignedCode!==null && Auth::user()->isTeacher() && !$request->attributes->get('academic_anonymat_entry'))abort(403,'Utilisez la saisie par anonymat pour cette évaluation.');
                if (!$delete) {
                    $anonymats = (array) $request->input('anonymats', array()); $months = (array) $request->input('mois', array());
                    $anonymat = isset($anonymats[$studentId]) ? mb_substr($anonymats[$studentId], 0, 100) : ($n?$n->anonymat:null);
                    if($assignedCode!==null){
                        if(trim((string)$anonymat)!=='' && trim((string)$anonymat)!==$assignedCode)abort(422,'Ce code ne correspond pas à l’anonymat attribué. Utilisez la saisie par anonymat ou le code de la liste.');
                        $anonymat=$assignedCode;
                    }
                    $month = isset($months[$studentId]) ? mb_substr($months[$studentId], 0, 100) : ($n?$n->mois_examen:null);
                    if ($n && abs(round($n->note*20/$weight,2)-(float)$raw)<0.000001 && (string)$n->anonymat===(string)$anonymat && (string)$n->mois_examen===(string)$month) continue;
                    if (!$n) { $n = new note; $n->id_creator = Auth::id(); }
                    $n->id_eleve = $studentId; $n->id_matiere = $selected->matiere_id; $n->sequence = $sequence;
                    $n->annee_scolaire = $context['year']; $n->id_campus = $context['id_campus']; $n->id_classe = $context['id_classe'];
                    $n->id_classe_matiere = $selected->programme_id; $n->id_session = $selected->id;
                    $n->note = round((float) $raw * $weight / 20, 4); $n->id_last_editor = Auth::id();
                    $n->anonymat = $anonymat;
                    $n->mois_examen = $month;
                    $n->edition_details = 'Tutelle '.$context['id_tutelle'].' : '.$operation.' '.$raw.'/20';
                    $n->save();
                }
                DB::table('academic_note_audits')->insert(array('id_note' => $n->id, 'id_campus' => $context['id_campus'], 'id_tutelle' => $context['id_tutelle'], 'id_user' => Auth::id(), 'operation' => $operation, 'avant' => $before, 'apres' => $delete ? null : $n->toJson(), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')));
                if ($delete) $n->delete();
            }
        });
        $query = array('id_session' => $selected->id, 'sequence' => $sequence);
        foreach (array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode') as $key) $query[$key]=$context[$key];
        return redirect()->to('/eleves/documents-tutelles/notes?'.http_build_query($query, '', '&'));
    }
}
