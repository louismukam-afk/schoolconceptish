<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;

/** Contrôle toutes les notes de la reprise avant validation de la transaction. */
class TestokImportVerifier {
    public function verify($source,$mapping)
    {
        $years=array();foreach($source['tables']['annee'] as $a)$years[$a['IDANNEE']]=TestokImportService::schoolYear($a['ANNEES']);
        $programmes=array();foreach($source['tables']['programme_specialite'] as $p)$programmes[$p['IDPROGRAMMESP']]=$p;
        $profiles=DB::table('tutelles_academiques')->whereNotNull('modele_documents')->lists('id','modele_documents');
        $expected=array();$byProfile=array();$sessionIds=array();
        foreach(DB::table('testok_import_links')->where('source',$mapping['source'])->where('entite','like','session_examen_%')->get() as $link)$sessionIds[$link->legacy_id.':'.substr($link->entite,15)]=$link->target_id;
        foreach($source['tables']['notes'] as $n){
            foreach(TestokTutellePolicy::models($programmes[$n['IDPROGRAMMESP']]['IDFILIERE']) as $model){
                if(!isset($profiles[$model]))throw new \RuntimeException('Tutelle absente pendant le contrôle : '.$model);
                $key=$n['IDNOTE'].':'.$profiles[$model];$expected[$key]=$n;
                if(!isset($byProfile[$model]))$byProfile[$model]=array('attendues'=>0,'verifiees'=>0);
                $byProfile[$model]['attendues']++;
            }
        }
        $count=count($expected);$checked=0;$errors=array();
        DB::table('testok_import_links as l')->leftJoin('note as n','n.id','=','l.target_id')
            ->leftJoin('session_exams as s','s.id','=','n.id_session')
            ->leftJoin('tutelles_academiques as t','t.id','=','s.id_tutelle')
            ->where('l.source',$mapping['source'])->where('l.entite','like','notes_%')
            ->select('l.id','l.legacy_id','l.entite','l.empreinte','n.id_eleve','n.id_campus','n.annee_scolaire','n.id_matiere','n.id_classe','n.id_classe_matiere','n.id_session','n.sequence','n.note','n.anonymat','n.mois_examen','s.id_tutelle','s.annee_academique','s.id_classe_matiere as session_cm','s.id_matiere as session_matiere','t.modele_documents')
            ->orderBy('l.id')->chunk(500,function($rows)use(&$expected,&$checked,&$errors,&$byProfile,$mapping,$years,$sessionIds){
                foreach($rows as $row){
                    $tutor=substr($row->entite,6);$key=$row->legacy_id.':'.$tutor;
                    if(!isset($expected[$key]))continue;
                    $n=$expected[$key];unset($expected[$key]);$checked++;
                    $values=array('id_eleve'=>$mapping['etudiants'][$n['IDETUDIANT']],'id_campus'=>$mapping['campus_id'],'annee_scolaire'=>$years[$n['IDANNEE']],'id_matiere'=>$mapping['matieres'][$n['IDMATIERE']],'id_classe'=>$mapping['specialites'][$n['IDSPECIALITE']],'id_classe_matiere'=>$mapping['programmes'][$n['IDPROGRAMMESP']],'sequence'=>TestokImportService::sequence($n['TYPE_EXAMEN']),'anonymat'=>$n['ANONYMATS'],'mois_examen'=>$n['MOIS'],'id_tutelle'=>$tutor,'annee_academique'=>$years[$n['IDANNEE']],'session_cm'=>$mapping['programmes'][$n['IDPROGRAMMESP']],'session_matiere'=>$mapping['matieres'][$n['IDMATIERE']]);
                    $bad=array();foreach($values as $field=>$value)if((string)$row->$field!==(string)$value)$bad[]=$field;
                    $sessionKey=$n['IDSESSION'].':'.$tutor;
                    if(!isset($sessionIds[$sessionKey]) || (int)$row->id_session!==(int)$sessionIds[$sessionKey])$bad[]='id_session';
                    if((float)$row->note!==(float)$n['NOTE'])$bad[]='note';
                    if(!hash_equals($row->empreinte,hash('sha256',json_encode($n))))$bad[]='empreinte';
                    if(!isset($byProfile[$row->modele_documents]))$bad[]='modele_documents';
                    else $byProfile[$row->modele_documents]['verifiees']++;
                    if($bad)$errors[]=array('note_source'=>$n['IDNOTE'],'tutelle'=>$tutor,'champs'=>$bad);
                }
            });
        return array('ok'=>!count($expected) && !count($errors),'attendues'=>$count,'verifiees'=>$checked,'manquantes'=>count($expected),'erreurs'=>$errors,'par_tutelle'=>$byProfile);
    }
}
