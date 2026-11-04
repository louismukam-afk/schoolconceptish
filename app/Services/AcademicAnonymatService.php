<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

/** Un code appartient à un campus, une tutelle et une évaluation précis. */
class AcademicAnonymatService {
    public function selection($context,$session,$sequence,$mode)
    {
        $sequence=(int)$sequence;$parity=(int)$session->semestre%2?1:2;
        if(!in_array($mode,array('matiere','examen'),true))abort(422,'Mode d’anonymat invalide.');
        if(!in_array($sequence,array($parity+2,$parity+4),true))abort(422,'Choisissez la session normale ou le rattrapage du semestre de cette matière.');
        $column=$sequence<=4?'sn'.$parity:'ratrappage'.$parity;
        if((float)$session->$column<=0)abort(422,'Cette évaluation n’est pas configurée.');
        $selection=array();
        foreach(array('id_campus','id_tutelle','id_classe','id_cycle','id_nniveau')as $key)$selection[$key]=$context[$key];
        $selection['annee_scolaire']=$context['year'];$selection['semestre']=(int)$session->semestre;
        $selection['sequence']=$sequence;$selection['mode_generation']=$mode;
        $selection['id_session']=$mode==='matiere'?(int)$session->id:0;
        $selection['scope_key']=hash('sha256',implode('|',array_values($selection)));
        return $selection;
    }

    public function lock($context,$session,$sequence)
    {
        $selection=$this->selection($context,$session,$sequence,'examen');
        DB::select('SELECT pg_advisory_xact_lock(hashtext(?))',array('academic-anonymat:'.$selection['scope_key']));
    }
    public function rows($context,$session,$sequence,$mode)
    {
        $selection=$this->selection($context,$session,$sequence,$mode);
        $students=(new AcademicDocumentService)->students($context);$allowed=array();
        foreach($students as $student)$allowed[$student->id]=$student;
        $rows=DB::table('academic_exam_anonymats')->where('scope_key',$selection['scope_key'])->orderBy('code')->get();
        $filtered=array();
        foreach($rows as $row)if(isset($allowed[$row->id_eleve])){$row->version=hash('sha256',json_encode($row));$row->student=$allowed[$row->id_eleve];$filtered[]=$row;}
        return $filtered;
    }

    public function noteQuery($context,$session,$sequence)
    {
        return \gesecole\note::where('id_campus',$context['id_campus'])->where('annee_scolaire',$context['year'])
            ->where('id_classe',$context['id_classe'])->where('id_classe_matiere',$session->programme_id)
            ->where('id_session',$session->id)->where('sequence',$sequence);
    }

    public function notes($context,$session,$sequence)
    {
        $notes=array();foreach($this->noteQuery($context,$session,$sequence)->get()as $note){
            if(isset($notes[$note->id_eleve]))abort(409,'Notes en double : corrigez les doublons avant la saisie.');
            $notes[$note->id_eleve]=$note;
        }
        return $notes;
    }

    private function audit($row,$operation,$before=null)
    {
        DB::table('academic_anonymat_audits')->insert(array('id_anonymat'=>$row->id,'id_campus'=>$row->id_campus,
            'id_tutelle'=>$row->id_tutelle,'annee_scolaire'=>$row->annee_scolaire,'id_user'=>Auth::id(),'operation'=>$operation,
            'avant'=>$before,'apres'=>$operation==='suppression'?null:json_encode($row),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
    }

    public function generate($context,$session,$sequence,$mode)
    {
        $selection=$this->selection($context,$session,$sequence,$mode);
        $students=(new AcademicDocumentService)->students($context);
        if(!count($students))abort(422,'Aucun étudiant inscrit dans ce contexte.');
        $created=0;
        DB::transaction(function()use($selection,$students,$context,$session,$sequence,$mode,&$created){
            $this->lock($context,$session,$sequence);
            // Séries distinctes entre tutelles ; les codes historiques restent inchangés.
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))',array('academic-anonymat-series:'.$context['year']));
            $seriesUsed=array_fill_keys(DB::table('academic_exam_anonymats')->where('annee_scolaire',$context['year'])->lists('code'),true);
            $retiredSeries=DB::table('academic_anonymat_audits')->where('annee_scolaire',$context['year'])->where('operation','suppression')->lists('avant');
            foreach($retiredSeries as $json){$old=json_decode($json,true);if($old)$seriesUsed[$old['code']]=true;}
            $existing=DB::table('academic_exam_anonymats')->where('scope_key',$selection['scope_key'])->get();
            $assigned=array();$used=array();foreach($existing as $row){$assigned[$row->id_eleve]=true;$used[$row->code]=true;}
            $retired=DB::table('academic_anonymat_audits')->where('id_campus',$context['id_campus'])->where('id_tutelle',$context['id_tutelle'])->where('annee_scolaire',$context['year'])->where('operation','suppression')->get();
            foreach($retired as $audit){$old=json_decode($audit->avant,true);if($old && $old['scope_key']===$selection['scope_key'])$used[$old['code']]=true;}
            // Reprendre les codes historiques de la même UV, sans toucher aux notes.
            $notes=$mode==='matiere'?$this->notes($context,$session,$sequence):array();
            $legacy=array();foreach($students as $student){
                if(isset($assigned[$student->id]) || !isset($notes[$student->id]))continue;
                $code=trim((string)$notes[$student->id]->anonymat);if($code==='')continue;
                if(preg_match('/[\x00-\x1f\x7f]/',$code) || mb_strlen($code)>100)abort(422,'Un code historique est invalide. Corrigez-le avant de générer les anonymats.');
                if(isset($used[$code]))abort(409,'Un code historique est partagé par plusieurs étudiants de cette évaluation. Corrigez le doublon avant de générer.');
                $used[$code]=true;$legacy[$student->id]=$code;
            }
            // Les noms ne déterminent pas l’ordre d’attribution des nouveaux codes.
            shuffle($students);$rank=0;$prefix='AN'.($sequence<=4?'SN':'R').$selection['semestre'].'-';
            foreach($students as $student){
                if(isset($assigned[$student->id]))continue;
                if(isset($legacy[$student->id]))$code=$legacy[$student->id];
                else{do{$code=$prefix.str_pad(++$rank,5,'0',STR_PAD_LEFT);}while(isset($used[$code]) || isset($seriesUsed[$code]));$used[$code]=true;$seriesUsed[$code]=true;}
                $row=$selection;$row['code']=$code;$row['id_eleve']=$student->id;$row['id_creator']=Auth::id();
                $row['created_at']=date('Y-m-d H:i:s');$row['updated_at']=$row['created_at'];
                $row['id']=DB::table('academic_exam_anonymats')->insertGetId($row);$this->audit((object)$row,'creation');$created++;
            }
        });
        return $created;
    }

    public function remove($context,$session,$sequence,$mode,$id,$version)
    {
        $selection=$this->selection($context,$session,$sequence,$mode);
        DB::transaction(function()use($selection,$context,$session,$sequence,$id,$version){
            $this->lock($context,$session,$sequence);
            $row=DB::table('academic_exam_anonymats')->where('scope_key',$selection['scope_key'])->where('id',$id)->lockForUpdate()->first();
            if(!$row)abort(404,'Anonymat absent de ce contexte.');
            if(!is_string($version) || !hash_equals(hash('sha256',json_encode($row)),$version))abort(409,'Cet anonymat a changé. Rechargez la liste.');
            $notes=\gesecole\note::where('id_eleve',$row->id_eleve)->where('id_campus',$context['id_campus'])->where('annee_scolaire',$context['year'])->where('sequence',$sequence);
            if($row->id_session)$notes->where('id_session',$row->id_session);
            else{
                $ids=array();foreach((new AcademicDocumentService)->sessions($context)as $s)if((int)$s->semestre===(int)$row->semestre)$ids[]=$s->id;
                $notes->whereIn('id_session',$ids);
            }
            if($notes->exists())abort(409,'Une note existe déjà pour cet étudiant et cette évaluation. Le code doit être conservé.');
            $this->audit($row,'suppression',json_encode($row));DB::table('academic_exam_anonymats')->where('id',$row->id)->delete();
        });
    }

    /** Le mode explicite évite de confondre un code par UV et un code commun. */
    public function codeForNote($context,$session,$sequence,$studentId,$mode=null)
    {
        if(!in_array((int)$sequence,array(3,4,5,6),true))return null;
        foreach($mode?array($mode):array('matiere','examen')as $candidate){
            $selection=$this->selection($context,$session,$sequence,$candidate);
            $row=DB::table('academic_exam_anonymats')->where('scope_key',$selection['scope_key'])->where('id_eleve',$studentId)->first();
            if($row)return $row->code;
        }
        return null;
    }
}