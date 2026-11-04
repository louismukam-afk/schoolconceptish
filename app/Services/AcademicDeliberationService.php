<?php namespace gesecole\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use gesecole\note;

class AcademicDeliberationService {
    public static function types(){return array('matiere_note'=>'Par matière','ue_note'=>'Par module / groupe de matières','credit'=>'Par crédits','matiere_credit'=>'Par matières et crédits','ue_credit'=>'Par modules et crédits','individuelle'=>'Par étudiant');}
    private function number($value,$label,$optional=false){
        if($optional && ($value===null || $value===''))return null;
        if(!is_scalar($value) || !is_numeric(str_replace(',','.',(string)$value)))abort(422,$label.' invalide.');
        $n=(float)str_replace(',','.',(string)$value);if(!is_finite($n))abort(422,$label.' invalide.');return $n;
    }
    public function criteria($input){
        $type=isset($input['type_deliberation'])?$input['type_deliberation']:'matiere_note';if(!is_string($type) || !isset(self::types()[$type]))abort(422,'Type de délibération invalide.');
        $base=isset($input['base'])?$input['base']:'note20';if(!in_array($base,array('note20','note100','credit'),true))abort(422,'Base invalide.');
        if(in_array($type,array('credit','matiere_credit','ue_credit'),true))$base='credit';elseif($base==='credit')abort(422,'Choisissez une base de notes pour ce type.');
        $evaluation=isset($input['evaluation'])?$input['evaluation']:'retenue';if(!in_array($evaluation,array('retenue','normal','rattrapage'),true))abort(422,'Évaluation invalide.');
        $semester=isset($input['semestre'])?(string)$input['semestre']:'0';if(!ctype_digit($semester) || (int)$semester>10)abort(422,'Semestre invalide.');
        $scope=isset($input['portee'])?$input['portee']:'specialite';if(!in_array($scope,array('specialite','selection'),true))abort(422,'Portée invalide.');
        $target=$this->number(isset($input['valeur_cible'])?$input['valeur_cible']:10,'Objectif');
        if($target<0 || ($base!=='credit' && $target>($base==='note100'?100:20)))abort(422,'Objectif hors limites.');
        $min=$this->number(isset($input['interval_min'])?$input['interval_min']:null,'Borne minimale',true);
        $max=$this->number(isset($input['interval_max'])?$input['interval_max']:null,'Borne maximale',true);
        if(($min!==null && $min<0) || ($max!==null && $max<0) || ($min!==null && $max!==null && $min>$max))abort(422,'Intervalle incohérent.');
        $lists=array();foreach(array('matieres','modules')as $key){$values=isset($input[$key])?$input[$key]:array();if(!is_array($values) || count($values)>1000)abort(422,'Sélection invalide.');foreach($values as $v)if(!is_string($v) && !is_int($v))abort(422,'Sélection invalide.');$lists[$key]=array_values(array_unique(array_map('strval',$values)));sort($lists[$key]);}
        $student=isset($input['id_eleve'])?(string)$input['id_eleve']:'';if($student!=='' && (!ctype_digit($student) || (int)$student<=0))abort(422,'Étudiant invalide.');
        if($type==='individuelle' && $student==='')abort(422,'Choisissez l’étudiant à délibérer.');
        return array('type_deliberation'=>$type,'base'=>$base,'evaluation'=>$evaluation,'semestre'=>(int)$semester,'portee'=>$scope,'valeur_cible'=>$target,
            'interval_min'=>$min,'interval_max'=>$max,'min_inclus'=>!empty($input['min_inclus']),'max_inclus'=>!empty($input['max_inclus']),
            'matieres'=>$lists['matieres'],'modules'=>$lists['modules'],'id_eleve'=>$student);
    }
    private function inInterval($value,$c){return ($c['interval_min']===null || ($c['min_inclus']?$value>=$c['interval_min']:$value>$c['interval_min'])) && ($c['interval_max']===null || ($c['max_inclus']?$value<=$c['interval_max']:$value<$c['interval_max']));}
    private function subjectKey($s){return $s['programme_id'].':'.$s['semestre'];}
    public function data($context){
        $context['session_mode']='final';$data=(new AcademicModuleService)->build((new AcademicDocumentService)->build($context,null,null,false));$names=array();
        foreach($data['modules']as $module)foreach($module['subjects']as $subject)$names[$subject['key']]=$module['nom'];
        foreach($data['sessions']as $session)$session->module=$names[$session->programme_id.':'.$session->semestre];
        foreach($data['results']as &$r){
            foreach($r['subjects']as &$subject)$subject['module']=$names[$this->subjectKey($subject)];unset($subject);
            foreach($r['modules']as &$module){foreach($module['subjects']as &$subject)$subject['module']=$module['nom'];unset($subject);}unset($module);
        }unset($r);return $data;
    }
    public function fingerprint($data){return hash('sha256',json_encode(array($data['context'],$data['sessions'],$data['modules'],$data['metadata'],$data['results'])));}
    private function change($data,$r,$s,$target,$evaluation){
        if(!$s['result']['complete'] || $s['result']['final']===null || $target<=$s['result']['final']+0.000001)return null;
        $seq=AcademicResultCalculator::sequences($s['semestre']);$exam=$evaluation==='retenue'?($s['result']['session']==='R'?'rat':'sn'):($evaluation==='rattrapage'?'rat':'sn');
        $weight=(float)$s['weights'][$exam];if($weight<=0)return null;
        $sid=$r['student']->id;$session=$s['session_id'];$sequence=$seq[$exam];
        if(!isset($data['metadata'][$sid][$session][$sequence]))return null;
        $row=$data['metadata'][$sid][$session][$sequence];
        if((int)$row->id_campus!==$data['context']['id_campus'] || (int)$row->id_classe_matiere!==(int)$s['programme_id'] || (int)$row->id_matiere!==(int)$s['matiere_id'])return null;
        $base=0;$notes=array();foreach($seq as $name=>$index){$value=$s['result']['components'][$name];if($value!==null)$notes[$index]=$value;
            if(in_array($name,array('cc','tpe'),true) && $s['weights'][$name]>0){if($value===null || $value<0 || $value>$s['weights'][$name]+0.0001)return null;$base+=$value;}}
        $value=round($target*5-$base,4);if($value<0 || $value>$weight+0.00001)return null;
        $notes[$sequence]=$value;$result=(new AcademicResultCalculator)->subject($notes,$s['weights'],$s['semestre']);
        if(!$result['complete'] || abs($result['final']-$target)>0.00001)return null;
        return array('id_note'=>(int)$row->id,'id_eleve'=>(int)$sid,'id_session'=>(int)$session,'id_classe_matiere'=>(int)$s['programme_id'],'id_matiere'=>(int)$s['matiere_id'],
            'semestre'=>(int)$s['semestre'],'sequence'=>$sequence,'key'=>$this->subjectKey($s),'matiere'=>$s['nom'],'module'=>$s['module'],
            'credit'=>(float)$s['credit'],'note_before'=>(float)$row->note,'note_after'=>$value,'final_before'=>$s['result']['final'],'final_after'=>$result['final'],'result_after'=>$result);
    }
    private function capacity($data,$r,$s,$evaluation){
        $seq=AcademicResultCalculator::sequences($s['semestre']);$exam=$evaluation==='retenue'?($s['result']['session']==='R'?'rat':'sn'):($evaluation==='rattrapage'?'rat':'sn');
        $base=0;foreach(array('cc','tpe')as $name)if($s['weights'][$name]>0){if($s['result']['components'][$name]===null)return null;$base+=$s['result']['components'][$name];}
        $max=floor((($base+$s['weights'][$exam])/5)*100+0.000001)/100;
        return $max<=20 && $this->change($data,$r,$s,$max,$evaluation)?$max:null;
    }
    private function closestFirst($a,$b){if($a['result']['final']==$b['result']['final'])return strcmp($this->subjectKey($a),$this->subjectKey($b));return $a['result']['final']>$b['result']['final']?-1:1;}
    private function moduleChanges($data,$r,$module,$target,$evaluation){
        if(!$module['complete'] || $module['average']===null || $target<=$module['average']+0.000001)return array();
        $remaining=$target*$module['credits']-$module['sum'];$uvs=$module['subjects'];usort($uvs,array($this,'closestFirst'));$changes=array();
        foreach($uvs as $s){if(!$s['result']['complete'] || $s['result']['final']>=10 || $s['credit']<=0)continue;
            $max=$this->capacity($data,$r,$s,$evaluation);if($max===null)continue;
            $required=min($max,ceil(($s['result']['final']+$remaining/$s['credit'])*100-0.000001)/100);
            $change=$this->change($data,$r,$s,$required,$evaluation);if(!$change)continue;
            $changes[]=$change;$remaining-=($change['final_after']-$change['final_before'])*$s['credit'];if($remaining<=0.000001)break;
        }
        return $remaining>0.000001?array():$changes;
    }
    private function creditChanges($data,$r,$subjects,$target,$evaluation){
        $threshold=$data['context']['profile']['credit_threshold'];$current=0;foreach($subjects as $s)if($s['result']['complete'] && $s['result']['final']>=$threshold)$current+=$s['credit'];
        if($target<=$current+0.000001)return array();$after=$current;$uvs=$subjects;usort($uvs,array($this,'closestFirst'));$changes=array();
        foreach($uvs as $s){if(!$s['result']['complete'] || $s['result']['final']>=$threshold || $s['credit']<=0)continue;
            $change=$this->change($data,$r,$s,$threshold,$evaluation);if(!$change)continue;$changes[]=$change;$after+=$s['credit'];if($after+0.000001>=$target)break;
        }
        return $after+0.000001<$target?array():$changes;
    }
    private function proposal($data,$r,$label,$changes,$key){
        if(!$changes)return null;$subjects=$r['subjects'];$map=array();foreach($changes as $change)$map[$change['key']]=$change;
        foreach($subjects as &$s)if(isset($map[$this->subjectKey($s)]))$s['result']=$map[$this->subjectKey($s)]['result_after'];unset($s);
        return array('key'=>hash('sha256',$r['student']->id.':'.$key),'student'=>$r['student'],'label'=>$label,'changes'=>$changes,'before'=>$r['summary'],
            'after'=>(new AcademicResultCalculator)->summarize($subjects,$data['context']['profile'],$data['context']['periode']));
    }
    public function preview($data,$c){
        $subjects=array();foreach($data['sessions']as $s)if(!$c['semestre'] || $c['semestre']==(int)$s->semestre)$subjects[$s->programme_id.':'.$s->semestre]=true;
        $modules=array();foreach($data['modules']as $key=>$m)if($m['is_grouped'] && (!$c['semestre'] || $c['semestre']==$m['semestre']))$modules[$key]=true;
        foreach($c['matieres']as $key)if(!isset($subjects[$key]))abort(422,'Une matière est absente de la tutelle ou du semestre sélectionné.');
        foreach($c['modules']as $key)if(!isset($modules[$key]))abort(422,'Un module est absent de la tutelle ou du semestre sélectionné.');
        $isModule=in_array($c['type_deliberation'],array('ue_note','ue_credit'),true);
        if($c['portee']==='selection' && !count($c[$isModule?'modules':'matieres']))abort(422,'Cochez au moins une matière ou un module.');
        if($c['portee']==='selection'){$chosen=array_fill_keys($c[$isModule?'modules':'matieres'],true);if($isModule)$modules=$chosen;else$subjects=$chosen;}
        $proposals=array();$skipped=0;$found=false;
        foreach($data['results']as $r){
            if($c['id_eleve']!=='' && (int)$c['id_eleve']!==(int)$r['student']->id)continue;$found=true;
            $pool=array();foreach($r['subjects']as $s)if(isset($subjects[$this->subjectKey($s)]))$pool[]=$s;
            if($c['type_deliberation']==='matiere_note' || $c['type_deliberation']==='individuelle'){
                foreach($pool as $s){$mark=$s['result']['final'];if($mark===null || !$this->inInterval($c['base']==='note100'?$mark*5:$mark,$c))continue;
                    $change=$this->change($data,$r,$s,$c['valeur_cible']/($c['base']==='note100'?5:1),$c['evaluation']);
                    if(!$change){$skipped++;continue;}$proposals[]=$this->proposal($data,$r,$s['nom'],array($change),$this->subjectKey($s));}
            }elseif($c['type_deliberation']==='ue_note'){
                foreach($r['modules']as $key=>$m)if(isset($modules[$key])){
                    if($m['average']===null){$skipped++;continue;}if(!$this->inInterval($c['base']==='note100'?$m['average']*5:$m['average'],$c))continue;
                    $changes=$this->moduleChanges($data,$r,$m,$c['valeur_cible']/($c['base']==='note100'?5:1),$c['evaluation']);
                    if(!$changes){$skipped++;continue;}foreach($changes as &$change)$change['module']=$m['nom'];unset($change);
                    $p=$this->proposal($data,$r,$m['nom'].' · S'.$m['semestre'],$changes,$key);$p['module_before']=$m['average'];$sum=$m['sum'];foreach($changes as $change)$sum+=($change['final_after']-$change['final_before'])*$change['credit'];$p['module_after']=$sum/$m['credits'];$proposals[]=$p;
                }
            }else{
                if($c['type_deliberation']==='ue_credit'){$pool=array();foreach($r['modules']as $key=>$m)if(isset($modules[$key]))foreach($m['subjects']as $s)$pool[]=$s;}
                $credits=0;foreach($pool as $s)if($s['result']['complete'] && $s['result']['final']>=$data['context']['profile']['credit_threshold'])$credits+=$s['credit'];
                if(!$this->inInterval($credits,$c))continue;$changes=$this->creditChanges($data,$r,$pool,$c['valeur_cible'],$c['evaluation']);
                if(!$changes){$skipped++;continue;}$p=$this->proposal($data,$r,'Objectif de crédits sur la sélection',$changes,'credits');$p['credits_before']=$credits;$p['credits_after']=$credits;foreach($changes as $change)$p['credits_after']+=$change['credit'];$proposals[]=$p;
            }
        }
        if($c['id_eleve']!=='' && !$found)abort(403,'Étudiant non inscrit dans ce contexte.');
        return array('proposals'=>$proposals,'skipped'=>$skipped,'fingerprint'=>$this->fingerprint($data));
    }

    public function apply($context,$criteria,$fingerprint,$keys,$requestKey,$selectionHash,$motif,$authorize){
        return DB::transaction(function()use($context,$criteria,$fingerprint,$keys,$requestKey,$selectionHash,$motif,$authorize){
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))',array('academic-deliberation:'.$requestKey));
            $existing=DB::table('academic_deliberations')->where('request_key',$requestKey)->first();
            if($existing){if($existing->selection_hash!==$selectionHash || (int)$existing->id_creator!==(int)Auth::id() || (int)$existing->id_campus!==$context['id_campus'])abort(409,'Cet aperçu a déjà été appliqué avec une autre sélection.');return $existing->id;}
            $service=new AcademicDocumentService;$sessions=$service->sessions($context);$ids=array();$locks=array();
            foreach($sessions as $s){$ids[]=$s->id;$seq=AcademicResultCalculator::sequences($s->semestre);foreach(array($seq['sn'],$seq['rat'])as $sequence){$parity=(int)$s->semestre%2?1:2;$column=$sequence<=4?'sn'.$parity:'ratrappage'.$parity;if((float)$s->$column<=0)continue;$key=$s->semestre.':'.$sequence;$locks[$key]=array($s,$sequence);}}
            ksort($locks);foreach($locks as $lock)(new AcademicAnonymatService)->lock($context,$lock[0],$lock[1]);
            DB::table('session_exams')->whereIn('id',array_unique($ids))->orderBy('id')->lockForUpdate()->get();
            note::where('id_campus',$context['id_campus'])->where('annee_scolaire',$context['year'])->where('id_classe',$context['id_classe'])->whereIn('id_session',array_unique($ids))->orderBy('id')->lockForUpdate()->get();
            $data=$this->data($context);if(!hash_equals($fingerprint,$this->fingerprint($data)))abort(409,'Les notes, inscriptions, groupes ou sessions ont changé. Recalculez l’aperçu avant d’appliquer.');
            $plan=$this->preview($data,$criteria);$available=array();foreach($plan['proposals']as $p)$available[$p['key']]=$p;
            $selected=array();$allChanges=array();$studentIds=array();foreach($keys as $key){if(!isset($available[$key]))abort(422,'Proposition absente de cet aperçu.');$p=$available[$key];$selected[]=$p;$studentIds[$p['student']->id]=true;
                foreach($p['changes']as $change){if(isset($allChanges[$change['id_note']]))abort(422,'Deux propositions modifient la même note.');$allChanges[$change['id_note']]=$change;}}
            if(!count($allChanges))abort(422,'Cochez au moins une proposition.');
            $sessionMap=array();foreach($sessions as $s)$sessionMap[$s->id]=$s;
            foreach($allChanges as $change)call_user_func($authorize,$context,$sessionMap[$change['id_session']],$change['sequence']);
            $now=date('Y-m-d H:i:s');$batch=array('annee_scolaire'=>$context['year'],'modele'=>$context['modele'],'type'=>$criteria['type_deliberation'],'request_key'=>$requestKey,'selection_hash'=>$selectionHash,
                'criteres'=>json_encode($criteria),'motif'=>$motif,'bilans'=>'[]','effectif'=>count($studentIds),'lignes'=>count($allChanges),'id_creator'=>Auth::id(),'created_at'=>$now,'updated_at'=>$now);
            foreach(array('id_campus','id_tutelle','id_classe','id_cycle','id_nniveau')as $key)$batch[$key]=$context[$key];
            $batchId=DB::table('academic_deliberations')->insertGetId($batch);
            foreach($allChanges as $change){$n=note::findOrFail($change['id_note']);$before=$n->toJson();$n->note=$change['note_after'];$n->id_last_editor=Auth::id();
                $n->edition_details='deliberation criteres '.$criteria['type_deliberation'].' vers '.$change['final_after'].'/20; avant '.$change['final_before'].'/20; lot '.$batchId;$n->save();
                DB::table('academic_note_audits')->insert(array('id_note'=>$n->id,'id_campus'=>$context['id_campus'],'id_tutelle'=>$context['id_tutelle'],'id_user'=>Auth::id(),'operation'=>'deliberation','avant'=>$before,'apres'=>$n->toJson(),'created_at'=>$now,'updated_at'=>$now));
            }
            $afterData=$this->data($context);$after=array();foreach($afterData['results']as $r)$after[$r['student']->id]=$r;$bilans=array();
            foreach($data['results']as $r)if(isset($studentIds[$r['student']->id]))$bilans[$r['student']->id]=array('avant'=>$r['summary'],'apres'=>$after[$r['student']->id]['summary']);
            foreach($selected as $p)foreach($p['changes']as $change){$final=null;foreach($after[$change['id_eleve']]['subjects']as $s)if($this->subjectKey($s)===$change['key'])$final=$s['result']['final'];
                if($final===null || abs($final-$change['final_after'])>0.00001)abort(409,'Le résultat recalculé ne correspond pas à l’aperçu. Aucune modification n’a été conservée.');
                $details=$change;unset($details['result_after']);$details['matricule']=$p['student']->matricule;$details['etudiant']=trim($p['student']->nom.' '.$p['student']->prenom);
                foreach(array('classe','cycle','nniveau','tutelle','campus')as $label)$details[$label]=$context[$label]->nom;
                $details['objectif']=$p['label'];$details['bilan']=$bilans[$change['id_eleve']];
                $item=array('id_deliberation'=>$batchId,'details'=>json_encode($details),'created_at'=>$now,'updated_at'=>$now);foreach(array('id_note','id_eleve','id_session','id_classe_matiere','id_matiere','semestre','sequence')as $key)$item[$key]=$change[$key];DB::table('academic_deliberation_items')->insert($item);
            }
            DB::table('academic_deliberations')->where('id',$batchId)->update(array('bilans'=>json_encode($bilans)));
            return $batchId;
        });
    }
}