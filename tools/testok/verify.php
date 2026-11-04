<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require __DIR__.'/../../bootstrap/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;
use gesecole\Services\AcademicDocumentService;
use gesecole\Http\Controllers\eleves\academicDocumentController;
$options=getopt('',array('output:'));
$out=isset($options['output'])?$options['output']:storage_path('app/import-testok/verification.json');
$db=DB::connection();$db->disableQueryLog();$db->beginTransaction();$db->statement('SET TRANSACTION READ ONLY');
$report=array('imported_links'=>DB::table('testok_import_links')->select('entite',DB::raw('COUNT(*) as nombre'))->groupBy('entite')->get(),'samples'=>array());
$checked=0;$mismatches=0;
DB::table('testok_import_links as l')->join('note as n','n.id','=','l.target_id')->join('session_exams as s','s.id','=','n.id_session')
    ->where('l.source','testok-hintel')->where('l.entite','like','notes_%')->select('l.entite','l.donnees_source','l.empreinte','n.note','n.id_campus','s.id_tutelle','n.anonymat','n.mois_examen')->orderBy('l.id')
    ->chunk(500,function($rows)use(&$checked,&$mismatches){foreach($rows as $row){$checked++;$raw=json_decode($row->donnees_source,true);if(!$raw || !hash_equals($row->empreinte,hash('sha256',$row->donnees_source)) || (float)$row->note!==(float)$raw['NOTE'] || (int)$row->id_campus!==1 || $row->entite!=='notes_'.$row->id_tutelle || (string)$row->anonymat!==(string)$raw['ANONYMATS'] || (string)$row->mois_examen!==(string)$raw['MOIS'])$mismatches++;}});
$report['grade_integrity']=array('checked'=>$checked,'mismatches'=>$mismatches);
$rows=DB::select("SELECT se.id_tutelle,t.modele_documents AS modele,cm.id_classe,cm.id_cycle,cm.id_nniveau,se.annee_academique AS year,COUNT(n.id) AS notes,COUNT(DISTINCT n.id_eleve) AS students FROM note n JOIN session_exams se ON se.id=n.id_session JOIN classe_matiere cm ON cm.id=se.id_classe_matiere JOIN tutelles_academiques t ON t.id=se.id_tutelle WHERE n.id_campus=1 AND n.edition_details LIKE 'Import Test OK%' GROUP BY se.id_tutelle,t.modele_documents,cm.id_classe,cm.id_cycle,cm.id_nniveau,se.annee_academique ORDER BY COUNT(n.id) DESC");
$seen=array();
$report['browser_contexts']=array();$browserSeen=array();
foreach($rows as $row)if($row->year==='2025-2026' && !isset($browserSeen[$row->modele])){$report['browser_contexts'][]=$row;$browserSeen[$row->modele]=true;}
foreach($rows as $row){
    if(isset($seen[$row->modele]))continue;$seen[$row->modele]=true;
    session(array('campus_id'=>1,'year'=>$row->year));
    $input=array('modele'=>$row->modele,'id_tutelle'=>$row->id_tutelle,'id_classe'=>$row->id_classe,'id_cycle'=>$row->id_cycle,'id_nniveau'=>$row->id_nniveau,'periode'=>'annuel','document'=>'pv');
    $sample=array('context'=>$input,'year'=>$row->year,'notes'=>(int)$row->notes,'students'=>(int)$row->students);
    try{
        $service=new AcademicDocumentService;$context=$service->context($input,1,$row->year);$data=$service->build($context);
        $stats=array();foreach($data['results']as $r){$d=$r['summary']['decision'];$stats[$d]=isset($stats[$d])?$stats[$d]+1:1;}
        $sample['sessions']=count($data['sessions']);$sample['decisions']=$stats;
        $controller=new academicDocumentController;
        foreach(array('pv','pv_groupes','releve','admis','attestation','report','presence','anonymat','rattrapage','statistiques')as $doc){
            $input['document']=$doc;$html=$controller->preview(Illuminate\Http\Request::create('/','GET',$input))->render();
            if(strpos($html,'<!DOCTYPE html>')===false)throw new RuntimeException('Sortie HTML invalide : '.$doc);
            $sample['documents'][$doc]=strlen($html);
        }
        $sample['ok']=true;
    }catch(Exception $e){$sample['ok']=false;$sample['error']=$e->getMessage();}
    $report['samples'][]=$sample;
}
$db->rollBack();
$report['ok']=count($seen)===5 && $checked>0 && $mismatches===0;
foreach($report['samples']as $s)if(!$s['ok'])$report['ok']=false;
file_put_contents($out,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
exit($report['ok']?0:1);
