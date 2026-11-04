<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;

/** Regroupements réels de groupe_matieres, séparés par semestre et identité. */
class AcademicModuleService {
    public function build($data)
    {
        $context=$data['context'];$ids=array();
        foreach($data['sessions'] as $s)$ids[]=$s->programme_id;
        $rows=DB::table('groupe_matieres')->where('id_classe',$context['id_classe'])->where('id_nniveau',$context['id_nniveau'])->whereIn('id_classe_matiere',$ids)->get();
        $names=DB::table('matiere')->lists('nom','id');$memberships=array();
        foreach($rows as $row)$memberships[$row->id_classe_matiere][]=$row;
        $definitions=array();$keys=array();
        foreach($data['sessions'] as $s){
            $parents=array();
            if(isset($memberships[$s->programme_id]))foreach($memberships[$s->programme_id] as $g){
                // L'écran historique stocke aussi 1/2 pour les semestres impair/pair.
                $semester=(int)$s->semestre;$groupSemester=(int)$g->semestre;
                $legacyParity=$groupSemester>=1 && $groupSemester<=2 && $groupSemester===($semester%2?1:2);
                if($groupSemester!==0 && $groupSemester!==$semester && !$legacyParity)continue;
                $parents[(int)$g->id_matiere_mere]=true;
            }
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
}
