<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;

/** Regroupements réels de groupe_matieres, séparés par semestre et identité. */
class AcademicModuleService {
    public function build($data)
    {
        $context=$data['context'];$ids=array();
        foreach($data['sessions'] as $s)$ids[]=$s->programme_id;
        // Les lignes du programme courant peuvent avoir de nouveaux identifiants
        // classe_matiere alors que groupe_matieres référence encore les anciens.
        // On conserve la correspondance exacte et on ajoute un repli par nom d'UV.
        $rows=DB::table('groupe_matieres')->where('id_classe',$context['id_classe'])->where('id_nniveau',$context['id_nniveau'])->get();
        $names=DB::table('matiere')->lists('nom','id');$memberships=array();$membershipsByName=array();$groupProgrammeIds=array();
        foreach($rows as $row)$groupProgrammeIds[]=$row->id_classe_matiere;
        $groupProgrammes=$groupProgrammeIds?DB::table('classe_matiere')->where('id_classe',$context['id_classe'])
            ->where('id_nniveau',$context['id_nniveau'])->where('id_cycle',$context['id_cycle'])
            ->whereIn('id',array_unique($groupProgrammeIds))->get(array('id','id_matiere')):array();
        $programmeMatiere=array();
        foreach($groupProgrammes as $programme)$programmeMatiere[$programme->id]=$programme->id_matiere;
        foreach($rows as $row){
            if(!isset($programmeMatiere[$row->id_classe_matiere]))continue;
            $memberships[$row->id_classe_matiere][]=$row;
            $matiereId=$programmeMatiere[$row->id_classe_matiere];
            if(isset($names[$matiereId]))$membershipsByName[$this->normalizeSubjectName($names[$matiereId])][]=$row;
        }
        $definitions=array();$keys=array();
        foreach($data['sessions'] as $s){
            $parents=array();$allParents=array();
            $subjectNameKey=$this->normalizeSubjectName($s->nom);
            $subjectMemberships=isset($memberships[$s->programme_id])?$memberships[$s->programme_id]
                :(isset($membershipsByName[$subjectNameKey])?$membershipsByName[$subjectNameKey]:array());
            foreach($subjectMemberships as $g){
                $allParents[(int)$g->id_matiere_mere]=true;
                // L'écran historique stocke aussi 1/2 pour les semestres impair/pair.
                $semester=(int)$s->semestre;$groupSemester=(int)$g->semestre;
                $legacyParity=$groupSemester>=1 && $groupSemester<=2 && $groupSemester===($semester%2?1:2);
                if($groupSemester!==0 && $groupSemester!==$semester && !$legacyParity)continue;
                $parents[(int)$g->id_matiere_mere]=true;
            }
            // Older group assignments sometimes store a parity that differs
            // from the real semester. Reuse a sole unambiguous parent group.
            if(!count($parents) && count($allParents)===1)$parents=$allParents;
            if(!count($parents) && count($allParents)>1)abort(422,'Semestre de groupe ambigu pour le programme '.$s->programme_id.'. Corrigez ses affectations avant de generer le PV.');
            if(count($parents)>1)abort(422,'Plusieurs groupes de matières pour la même UV et le même semestre : programme '.$s->programme_id.'. Corrigez les affectations avant de produire le document.');
            $parent=count($parents)?key($parents):null;
            if($parent && !isset($names[$parent]))abort(422,'Le groupe de matières référence un module absent : '.$parent);
            $key='s'.$s->semestre.($parent?':module'.$parent:':uv'.$s->programme_id);
            $subjectKey=$s->programme_id.':'.$s->semestre;$keys[$subjectKey]=$key;
            if(!isset($definitions[$key]))$definitions[$key]=array('key'=>$key,'id_matiere_mere'=>$parent,'nom'=>$parent?$names[$parent]:$s->nom,'semestre'=>(int)$s->semestre,'is_grouped'=>(bool)$parent,'subjects'=>array());
            $definitions[$key]['subjects'][]=array('key'=>$subjectKey,'code'=>$s->code,'nom'=>$s->nom,'credit'=>(float)$s->credit);
        }
        uasort($definitions,function($a,$b){if($a['semestre']!==$b['semestre'])return $a['semestre']<$b['semestre']?-1:1;$name=strcasecmp($a['nom'],$b['nom']);return $name?:strcmp($a['key'],$b['key']);});
        foreach($data['results'] as &$result){
            $modules=$definitions;
            foreach($modules as &$module){$module['subjects']=array();$module['credits']=0.0;$module['sum']=0.0;$module['complete']=true;}unset($module);
            foreach($result['subjects'] as $subject){
                $key=$keys[$subject['programme_id'].':'.$subject['semestre']];
                $credit=(float)$subject['credit'];if($credit<0)abort(422,'Crédits négatifs dans le programme '.$subject['programme_id']);
                $modules[$key]['subjects'][]=$subject;$modules[$key]['credits']+=$credit;
                if(!$subject['result']['complete'] || $subject['result']['final']===null)$modules[$key]['complete']=false;
                else $modules[$key]['sum']+=$subject['result']['final']*$credit;
            }
            foreach($modules as &$module)$module['average']=$module['complete'] && $module['credits']>0?$module['sum']/$module['credits']:null;unset($module);
            $result['modules']=$modules;
        }unset($result);
        $data['modules']=$definitions;
        return $data;
    }

    private function normalizeSubjectName($name)
    {
        $ascii=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',(string)$name);
        if($ascii!==false)$name=$ascii;
        return preg_replace('/[^a-z0-9]+/','',strtolower((string)$name));
    }
}
