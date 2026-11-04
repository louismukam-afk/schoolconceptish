<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;

class TestokCatalogService {
    private $counts=array();
    private $warnings=array();

    private function normalized($value)
    {
        $value=iconv('UTF-8','ASCII//TRANSLIT//IGNORE',trim($value));
        return preg_replace('/[^A-Z0-9]/','',strtoupper($value));
    }

    private function resolve($table,$name,$values,$candidate=null)
    {
        if($candidate && DB::table($table)->where('id',$candidate)->exists())return (int)$candidate;
        $matches=DB::table($table)->whereRaw('LOWER(TRIM(nom)) = ?',array(mb_strtolower(trim($name),'UTF-8')))->lists('id');
        if(count($matches)===1)return (int)$matches[0];
        if(count($matches)>1){
            // Une nouvelle référence conserve l'identité source sans fusionner
            // arbitrairement des références historiques homonymes.
            $this->warnings[]='Référence Test OK distincte créée dans '.$table.' pour le nom ambigu : '.$name;
            if($table==='classe' && isset($values['code'])){
                $byCode=DB::table($table)->where('code',$values['code'])->where('nom',$name)->lists('id');
                if(count($byCode)===1)return (int)$byCode[0];
            }
        }
        $values['created_at']=date('Y-m-d H:i:s');$values['updated_at']=date('Y-m-d H:i:s');
        $id=DB::table($table)->insertGetId($values);
        $this->counts[$table]=isset($this->counts[$table])?$this->counts[$table]+1:1;
        return $id;
    }

    private function attach($table,$values)
    {
        $query=DB::table($table);foreach($values as $k=>$v)$query->where($k,$v);
        if(!$query->exists())DB::table($table)->insert(array_merge($values,array('created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'))));
    }

    public function prepare($source,$mapping,$campusId)
    {
        if(!DB::table('campus')->where('id',$campusId)->exists())throw new \InvalidArgumentException('Campus absent.');
        $mapping['campus_id']=(int)$campusId;
        if(!isset($mapping['filieres']))$mapping['filieres']=array();
        $tutelles=DB::table('tutelles_academiques')->whereNotNull('modele_documents')->lists('id','modele_documents');
        foreach($source['tables']['filiere']as $f){
            $id=$f['IDFILIERE'];
            $mapping['filieres'][$id]=$this->resolve('niveau',$f['NOM_FILIERE'],array('nom'=>$f['NOM_FILIERE'],'nom_anglais'=>$f['NAME_FILIERE']),isset($mapping['filieres'][$id])?$mapping['filieres'][$id]:null);
        }
        foreach($source['tables']['cycle']as $c){
            $id=$c['IDCYCLE'];$mapping['cycles'][$id]=$this->resolve('cycle',$c['NOM_CYCLE'],array('nom'=>$c['NOM_CYCLE']),isset($mapping['cycles'][$id])?$mapping['cycles'][$id]:null);
        }
        $levels=DB::table('n_niveaus')->get();
        foreach($source['tables']['niveaux']as $n){
            $id=$n['IDNIVEAU'];$target=isset($mapping['niveaux'][$id])?$mapping['niveaux'][$id]:null;
            if(!$target){
                $norm=$this->normalized($n['NOM_NIVEAU']);$candidates=array();
                foreach($levels as $level){
                    $candidate=$this->normalized($level->nom);
                    if($candidate===$norm || ($n['IDCYCLE']==1 && $candidate==='LICENCE'.preg_replace('/\D/','',$norm)) || ($n['IDCYCLE']==2 && $candidate==='INGENIEURN'.$norm))$candidates[]=$level->id;
                }
                if(count($candidates)===1)$target=$candidates[0];
            }
            if(!$target){
                $cycleId=$mapping['cycles'][$n['IDCYCLE']];
                if(!$cycleId){$mapping['niveaux'][$id]=null;continue;}
                $existing=DB::table('n_niveaus')->where('nom',$n['NOM_NIVEAU'])->where('id_cycle',$cycleId)->first();
                $target=$existing?$existing->id:DB::table('n_niveaus')->insertGetId(array('nom'=>$n['NOM_NIVEAU'],'id_cycle'=>$cycleId,'ordre'=>(int)preg_replace('/\D/','',$n['NOM_NIVEAU']),'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
            }
            $mapping['niveaux'][$id]=(int)$target;
        }
        foreach($source['tables']['specialite']as $s){
            $id=$s['IDSPECIALITE'];$f=$mapping['filieres'][$s['IDFILIERE']];
            if(!$f){$mapping['specialites'][$id]=null;continue;}
            // Une spécialité existante portant le même nom conserve sa filière et son code.
            $target=$this->resolve('classe',$s['NOM_SPECIALITE'],array('nom'=>$s['NOM_SPECIALITE'],'nom_anglais'=>$s['NAME_SPECIALITE'],'code'=>$s['CODE']?:'TK-'.$id,'id_niveau'=>$f,'capacite'=>(int)$s['CAPACITE']),isset($mapping['specialites'][$id])?$mapping['specialites'][$id]:null);
            $mapping['specialites'][$id]=$target;
            if(!$target)continue;
            $this->attach('campus_specialite',array('id_campus'=>$campusId,'id_classe'=>$target));
            foreach(TestokTutellePolicy::models($s['IDFILIERE'])as $model){
                if(!isset($tutelles[$model]))throw new \RuntimeException('Tutelle non installée : '.$model);
                $this->attach('specialite_tutelle',array('id_tutelle'=>$tutelles[$model],'id_classe'=>$target));
            }
        }
        // Appliquer aussi la répartition demandée aux spécialités déjà présentes
        // dans SchoolConcept, notamment celles de génie mécanique.
        foreach(DB::table('niveau')->get()as $f){
            $name=$this->normalized($f->nom);$models=array();
            if(strpos($name,'AGRICULTURE')!==false || strpos($name,'ELEVAGE')!==false)$models=array('isabee');
            elseif(strpos($name,'HOTELLERIE')!==false || strpos($name,'TOURISME')!==false || $name==='GESTION' || $name==='SCIENCESDEGESTION')$models=array('soa');
            elseif(strpos($name,'INFORMATIONETCOMMUNICATION')!==false)$models=array('esstic');
            elseif(strpos($name,'MECANIQUE')!==false)$models=array('polytechnique');
            elseif(strpos($name,'GENIECIVIL')!==false || strpos($name,'GENICIVIL')!==false || strpos($name,'GENIEELECTRIQUE')!==false || strpos($name,'GENIEINFORMATIQUE')!==false || strpos($name,'TELECOM')!==false || strpos($name,'CYBERSECURITE')!==false)$models=array('ngaoundere','polytechnique');
            if(!$models)continue;
            foreach(DB::table('classe')->where('id_niveau',$f->id)->lists('id')as $classId){
                if(!DB::table('campus_specialite')->where('id_campus',$campusId)->where('id_classe',$classId)->exists())continue;
                foreach($models as $model)$this->attach('specialite_tutelle',array('id_tutelle'=>$tutelles[$model],'id_classe'=>$classId));
            }
        }
        if(isset($source['tables']['domaine'])){
            $domains=array();foreach($source['tables']['domaine']as $domain)$domains[$domain['IDDOMAINE']]=$domain['NOMDOMAINE'];
            $byFiliere=array();foreach($source['tables']['specialite']as $s)if(isset($s['IDDOMAINE']) && isset($domains[$s['IDDOMAINE']]))$byFiliere[$s['IDFILIERE']][]=$domains[$s['IDDOMAINE']];
            foreach($byFiliere as $id=>$names)if(!empty($mapping['filieres'][$id])){
                $f=DB::table('niveau')->where('id',$mapping['filieres'][$id])->first();
                if($f && !$f->domaines)DB::table('niveau')->where('id',$f->id)->update(array('domaines'=>implode(' / ',array_unique($names))));
            }
        }
        foreach($source['tables']['matiere']as $m){
            $id=$m['IDMATIERE'];$mapping['matieres'][$id]=$this->resolve('matiere',$m['NOM_MATIERE'],array('nom'=>$m['NOM_MATIERE'],'nom_anglais'=>$m['NOM_MATIERE']),isset($mapping['matieres'][$id])?$mapping['matieres'][$id]:null);
        }
        foreach($source['tables']['codematiere']as $c){
            $id=$c['IDCODEMATIERE'];$mapping['codes'][$id]=$this->resolve('codages',$c['NOM_CODE'],array('nom'=>$c['NOM_CODE']),isset($mapping['codes'][$id])?$mapping['codes'][$id]:null);
        }
        $types=array('fondamentale'=>0,'complementaire'=>1,'specialisation'=>2);
        foreach($source['tables']['programme_specialite']as $p){
            $id=$p['IDPROGRAMMESP'];$mapping['programmes'][$id]=null;
            if(empty($mapping['specialites'][$p['IDSPECIALITE']]) || empty($mapping['matieres'][$p['IDMATIERE']]) || empty($mapping['niveaux'][$p['IDNIVEAU']]) || empty($mapping['codes'][$p['IDCODEMATIERE']]))continue;
            $level=DB::table('n_niveaus')->where('id',$mapping['niveaux'][$p['IDNIVEAU']])->first();
            $values=array('id_classe'=>$mapping['specialites'][$p['IDSPECIALITE']],'id_matiere'=>$mapping['matieres'][$p['IDMATIERE']],'id_nniveau'=>$level->id,'id_cycle'=>$level->id_cycle,'coef'=>(float)$p['CREDIT'],'semestre'=>(int)$p['SEMESTRE'],'id_code'=>(string)$mapping['codes'][$p['IDCODEMATIERE']]);
            $query=DB::table('classe_matiere');foreach($values as $k=>$v)$query->where($k,$v);$existing=$query->get();
            if(count($existing)>1){$this->warnings[]='Programmes cibles ambigus : '.$id;continue;}
            if(count($existing))$target=$existing[0]->id;
            else{$values['max']=20;$values['type']=isset($types[strtolower($p['TYPE'])])?$types[strtolower($p['TYPE'])]:0;$values['created_at']=date('Y-m-d H:i:s');$values['updated_at']=date('Y-m-d H:i:s');$target=DB::table('classe_matiere')->insertGetId($values);$this->counts['classe_matiere']=isset($this->counts['classe_matiere'])?$this->counts['classe_matiere']+1:1;}
            $mapping['programmes'][$id]=(int)$target;
            $mapping['tutelles_by_programme'][$id]=array();
            foreach(TestokTutellePolicy::models($p['IDFILIERE'])as $model)$mapping['tutelles_by_programme'][$id][]=(int)$tutelles[$model];
        }
        foreach($source['tables']['groupe_matiere']as $g){
            $id=$g['IDGROUPE'];$mapping['groupes'][$id]=null;
            if(empty($mapping['programmes'][$g['IDPROGRAMMESP']]) || empty($mapping['matieres'][$g['IDMATIERE_MERE']]) || empty($mapping['specialites'][$g['IDSPECIALITE']]) || empty($mapping['niveaux'][$g['IDNIVEAU']]))continue;
            $values=array('id_classe_matiere'=>$mapping['programmes'][$g['IDPROGRAMMESP']],'id_matiere_mere'=>$mapping['matieres'][$g['IDMATIERE_MERE']],'id_classe'=>$mapping['specialites'][$g['IDSPECIALITE']],'id_nniveau'=>$mapping['niveaux'][$g['IDNIVEAU']],'semestre'=>(int)$g['SEMESTRE']);
            $query=DB::table('groupe_matieres');foreach($values as $k=>$v)$query->where($k,$v);$existing=$query->first();
            if($existing)$target=$existing->id;
            else{$values['created_at']=date('Y-m-d H:i:s');$values['updated_at']=date('Y-m-d H:i:s');$target=DB::table('groupe_matieres')->insertGetId($values);}
            $mapping['groupes'][$id]=$target;
        }
        foreach($source['tables']['etudiant']as $s){
            $id=$s['IDETUDIANT'];if(!empty($mapping['etudiants'][$id]))continue;
            $mapping['etudiants'][$id]=null;
            $matches=TestokImportService::studentMatches($s);
            if(count($matches)>1){$this->warnings[]='Étudiant ambigu : source '.$id.' ; arbitrage requis, aucune création.';continue;}
            if(count($matches)===1){$mapping['etudiants'][$id]=(int)$matches[0];continue;}
            $birth=TestokImportService::birthDate($s['DATE_NAISSANCE']);
            if(!$birth || trim($s['NOM'])===''){$this->warnings[]='Identité ou date de naissance invalide : étudiant source '.$id;continue;}
            $values=array('nom'=>$s['NOM'],'date_naiss'=>$birth,'lieu_naiss'=>isset($s['LIEU_NAISSANCE'])?$s['LIEU_NAISSANCE']:'','sexe'=>'','nationalite'=>'','quartier'=>isset($s['ADRESSE'])?$s['ADRESSE']:'','matricule'=>trim($s['MATRICULE']),'email'=>isset($s['EMAIL'])?$s['EMAIL']:'','tel'=>isset($s['TELEPHONE'])?$s['TELEPHONE']:'','edition_details'=>'Reprise Test OK ; champs absents dans la source laissés vides.','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s'));
            $mapping['etudiants'][$id]=DB::table('eleve')->insertGetId($values);
            $this->counts['eleve']=isset($this->counts['eleve'])?$this->counts['eleve']+1:1;
        }
        foreach($source['tables']['annee'] as $a){$year=TestokImportService::schoolYear($a['ANNEES']);if(empty($mapping['inscription_date_by_year'][$year]))$mapping['inscription_date_by_year'][$year]=substr($source['exported_at'],0,10);}
        return array('mapping'=>$mapping,'created'=>$this->counts,'warnings'=>$this->warnings);
    }
}
