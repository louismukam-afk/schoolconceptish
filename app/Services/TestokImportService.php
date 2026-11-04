<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;

class TestokImportService {
    private $stats=array();
    public function stats(){return $this->stats;}
    private $tables = array('annee','cycle','niveaux','filiere','specialite','codematiere','matiere','programme_specialite','groupe_matiere','session_examen','etudiant','inscription','notes');

    public function load($file)
    {
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data) || !isset($data['format']) || $data['format'] !== 'testok-schoolconcept-v1') throw new \InvalidArgumentException('Format d’export Test OK invalide.');
        foreach ($this->tables as $table) if (!isset($data['tables'][$table]) || !is_array($data['tables'][$table])) throw new \InvalidArgumentException('Table absente : '.$table);
        return $data;
    }

    public static function schoolYear($value)
    {
        $value = preg_replace('/\s+/', '', $value);
        if (!preg_match('/^(\d{4})-(\d{4})$/', $value, $m) || (int) $m[2] !== (int) $m[1] + 1) throw new \InvalidArgumentException('Année académique invalide.');
        return $value;
    }

    public static function sequence($type)
    {
        $map = array('CC'=>1,'CC2'=>2,'SN'=>3,'SN2'=>4,'RATRAPPAGE'=>5,'RATRAPPAGE2'=>6,'RATTRAPAGE'=>5,'RATTRAPAGE2'=>6,'TPE'=>7,'TPE2'=>8);
        $type = strtoupper(trim($type));
        if (!isset($map[$type])) throw new \InvalidArgumentException('Type d’examen inconnu : '.$type);
        return $map[$type];
    }

    public function template($source)
    {
        $mapping = array('source' => 'testok-hintel', 'campus_id' => null, 'etudiants' => array(), 'programmes' => array(), 'tutelles_by_programme' => array(), 'groupes' => array());
        foreach (array('cycle'=>array('cycles','IDCYCLE','NOM_CYCLE','cycle','nom'), 'niveaux'=>array('niveaux','IDNIVEAU','NOM_NIVEAU','n_niveaus','nom'), 'specialite'=>array('specialites','IDSPECIALITE','NOM_SPECIALITE','classe','nom'), 'matiere'=>array('matieres','IDMATIERE','NOM_MATIERE','matiere','nom'), 'codematiere'=>array('codes','IDCODEMATIERE','NOM_CODE','codages','nom')) as $table=>$definition) {
            list($key,$id,$name,$target,$column)=$definition; $mapping[$key]=array();
            foreach ($source['tables'][$table] as $row) {
                $matches=DB::table($target)->whereRaw('LOWER(TRIM('.$column.')) = ?',array(mb_strtolower(trim($row[$name]),'UTF-8')))->lists('id');
                $mapping[$key][$row[$id]]=count($matches)===1?(int)$matches[0]:null;
            }
        }
        foreach($source['tables']['programme_specialite'] as $p) {
            $idClasse=isset($mapping['specialites'][$p['IDSPECIALITE']])?$mapping['specialites'][$p['IDSPECIALITE']]:null;
            $idCycle=isset($mapping['cycles'][$p['IDCYCLE']])?$mapping['cycles'][$p['IDCYCLE']]:null;
            $idNniveau=isset($mapping['niveaux'][$p['IDNIVEAU']])?$mapping['niveaux'][$p['IDNIVEAU']]:null;
            $idMatiere=isset($mapping['matieres'][$p['IDMATIERE']])?$mapping['matieres'][$p['IDMATIERE']]:null;
            $matches=$idClasse && $idCycle && $idNniveau && $idMatiere?DB::table('classe_matiere')->where('id_classe',$idClasse)->where('id_cycle',$idCycle)->where('id_nniveau',$idNniveau)->where('id_matiere',$idMatiere)->where('coef',$p['CREDIT'])->where('semestre',$p['SEMESTRE'])->lists('id'):array();
            $mapping['programmes'][$p['IDPROGRAMMESP']]=count($matches)===1?(int)$matches[0]:null;
            $mapping['tutelles_by_programme'][$p['IDPROGRAMMESP']]=null;
        }
        foreach($source['tables']['etudiant'] as $s) {
            $matches=self::studentMatches($s);
            $mapping['etudiants'][$s['IDETUDIANT']]=count($matches)===1?(int)$matches[0]:null;
        }
        foreach($source['tables']['groupe_matiere'] as $g) $mapping['groupes'][$g['IDGROUPE']]=null;
        return $mapping;
    }

    public static function birthDate($raw)
    {
        $raw=trim($raw);
        foreach(array('Y-m-d','d-m-Y','d/m/Y') as $format){$d=\DateTime::createFromFormat('!'.$format,$raw);if($d && $d->format($format)===$raw)return $d->format('Y-m-d');}
        return null;
    }

    public static function studentMatches($row)
    {
        $matches=trim($row['MATRICULE'])!==''?DB::table('eleve')->whereRaw('TRIM(matricule) = ?',array(trim($row['MATRICULE'])))->lists('id'):array();
        if(!count($matches) && trim($row['NOM'])!=='')$matches=DB::table('eleve')->whereRaw("LOWER(TRIM(nom || ' ' || COALESCE(prenom, ''))) = ?",array(mb_strtolower(trim($row['NOM']),'UTF-8')))->lists('id');
        return $matches;
    }

    private function mapped($mapping,$key,$id)
    {
        if(!isset($mapping[$key][$id]) || !ctype_digit((string)$mapping[$key][$id]) || (int)$mapping[$key][$id]<=0) throw new \InvalidArgumentException('Correspondance requise : '.$key.' / '.$id);
        return (int)$mapping[$key][$id];
    }

    private function tutelles($mapping,$programmeId)
    {
        if(empty($mapping['tutelles_by_programme'][$programmeId]))throw new \InvalidArgumentException('Tutelle requise pour programme '.$programmeId);
        $ids=(array)$mapping['tutelles_by_programme'][$programmeId];
        foreach($ids as $id)if(!ctype_digit((string)$id) || (int)$id<=0)throw new \InvalidArgumentException('Tutelle invalide pour programme '.$programmeId);
        return array_values(array_unique(array_map('intval',$ids)));
    }

    private function cycleForLevel($mapping,$legacyLevel)
    {
        $level=DB::table('n_niveaus')->where('id',$this->mapped($mapping,'niveaux',$legacyLevel))->first();
        if(!$level)throw new \InvalidArgumentException('Niveau cible absent : '.$legacyLevel);
        return (int)$level->id_cycle;
    }

    private function existingInscription($row,$mapping,$year,$lock=false,&$created=null)
    {
        $created=false;
        $identity=array('id_eleve'=>$this->mapped($mapping,'etudiants',$row['IDETUDIANT']),
            'id_campus'=>(int)$mapping['campus_id'],
            'id_classe'=>$this->mapped($mapping,'specialites',$row['IDSPECIALITE']),
            'id_nniveau'=>$this->mapped($mapping,'niveaux',$row['IDNIVEAU']),
            'id_cycle'=>$this->cycleForLevel($mapping,$row['IDNIVEAU']));
        $query=DB::table('inscription')->where('annee_scolaire',$year);
        foreach($identity as $key=>$value)$query->where($key,$value);
        if($lock)$query->lockForUpdate();
        $rows=$query->get();
        if(count($rows)>1)throw new \RuntimeException('Inscription cible ambiguë : source '.$row['IDINSCRIPTION'].' ; aucune création.');
        if(count($rows))return $rows[0];
        // Un contexte différent existant pour la même année doit être arbitré,
        // plutôt que créer une deuxième inscription sur une mauvaise correspondance.
        if(DB::table('inscription')->where('id_eleve',$identity['id_eleve'])->where('annee_scolaire',$year)->exists())throw new \RuntimeException('Inscription existante dans un autre contexte : source '.$row['IDINSCRIPTION'].' ; vérifier campus, spécialité, cycle et niveau.');
        if(!$lock)return null;
        $date=isset($mapping['inscription_date_by_year'][$year])?$mapping['inscription_date_by_year'][$year]:null;
        $parsed=\DateTime::createFromFormat('!Y-m-d',(string)$date);
        if(!$parsed || $parsed->format('Y-m-d')!==$date)throw new \RuntimeException('Date de reprise requise pour créer une inscription : '.$year);
        $values=array_merge($identity,array('annee_scolaire'=>$year,'date_inscription'=>$date,'montant'=>0,'edition_details'=>'Reprise académique Test OK ; aucun paiement importé.','created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
        $id=DB::table('inscription')->insertGetId($values);
        $created=true;
        return DB::table('inscription')->where('id',$id)->first();
    }

    public function plan($source,$mapping)
    {
        $errors=array();$counts=array();$years=array();$programmes=array();$sessions=array();$students=array();$inscriptions=array();$identities=array();$enrolments=array();
        foreach($this->tables as $t)$counts[$t]=count($source['tables'][$t]);
        if(empty($mapping['source']) || !preg_match('/^[a-zA-Z0-9_-]{1,80}$/',$mapping['source']))$errors[]='Identifiant de source requis (lettres, chiffres, tirets).';
        $campus=isset($mapping['campus_id'])?(int)$mapping['campus_id']:0;
        if(!$campus || !DB::table('campus')->where('id',$campus)->exists())$errors[]='Campus destinataire requis.';
        foreach($source['tables']['annee']as $a){try{$years[$a['IDANNEE']]=self::schoolYear($a['ANNEES']);}catch(\Exception $e){$errors[]=$e->getMessage().' ID '.$a['IDANNEE'];}}
        foreach($source['tables']['etudiant']as $s){
            $students[$s['IDETUDIANT']]=$s;
            try{$id=$this->mapped($mapping,'etudiants',$s['IDETUDIANT']);if(!DB::table('eleve')->where('id',$id)->exists())throw new \InvalidArgumentException('Étudiant cible absent : '.$id);}catch(\Exception $e){$errors[]=$e->getMessage();}
        }
        foreach($source['tables']['programme_specialite']as $p){
            $programmes[$p['IDPROGRAMMESP']]=$p;
            try{
                $target=$this->mapped($mapping,'programmes',$p['IDPROGRAMMESP']);$tutelles=$this->tutelles($mapping,$p['IDPROGRAMMESP']);
                $models=DB::table('tutelles_academiques')->whereIn('id',$tutelles)->lists('modele_documents');
                $expected=TestokTutellePolicy::models($p['IDFILIERE']);sort($models);sort($expected);
                if($models!==$expected)throw new \InvalidArgumentException('Tutelles incompatibles avec la filière du programme '.$p['IDPROGRAMMESP']);
                $cm=DB::table('classe_matiere')->where('id',$target)->first();
                if(!$cm)throw new \InvalidArgumentException('Programme cible absent : '.$target);
                if((int)$cm->id_classe!==$this->mapped($mapping,'specialites',$p['IDSPECIALITE']) || (int)$cm->id_cycle!==$this->cycleForLevel($mapping,$p['IDNIVEAU']) || (int)$cm->id_nniveau!==$this->mapped($mapping,'niveaux',$p['IDNIVEAU']) || (int)$cm->id_matiere!==$this->mapped($mapping,'matieres',$p['IDMATIERE']))throw new \InvalidArgumentException('Programme cible incompatible : '.$p['IDPROGRAMMESP']);
                if((float)$cm->coef!==(float)$p['CREDIT'] || (int)$cm->semestre!==(int)$p['SEMESTRE'])throw new \InvalidArgumentException('Crédits / semestre différents : programme '.$p['IDPROGRAMMESP']);
                foreach($tutelles as $tutelle)if(!DB::table('specialite_tutelle')->where('id_tutelle',$tutelle)->where('id_classe',$cm->id_classe)->exists())throw new \InvalidArgumentException('Spécialité non affectée à la tutelle : '.$p['IDPROGRAMMESP']);
                if(!DB::table('campus_specialite')->where('id_campus',$campus)->where('id_classe',$cm->id_classe)->exists())throw new \InvalidArgumentException('Spécialité non affectée au campus : '.$p['IDPROGRAMMESP']);
            }catch(\Exception $e){$errors[]=$e->getMessage();}
        }
        foreach($source['tables']['session_examen']as $s){$sessions[$s['IDSESSION']]=$s;if(!isset($programmes[$s['IDPROGRAMMESP']]) || !isset($years[$s['IDANNEE']]))$errors[]='Session orpheline : '.$s['IDSESSION'];}
        foreach($source['tables']['inscription']as $i){
            $inscriptions[$i['IDINSCRIPTION']]=$i;
            try{
                $year=isset($years[$i['IDANNEE']])?$years[$i['IDANNEE']]:null;
                if(!$year)throw new \InvalidArgumentException('Année absente pour inscription '.$i['IDINSCRIPTION']);
                foreach(array('etudiants'=>'IDETUDIANT','specialites'=>'IDSPECIALITE','niveaux'=>'IDNIVEAU')as $key=>$column)$this->mapped($mapping,$key,$i[$column]);
                $this->existingInscription($i,$mapping,$year);
                $identity=$this->mapped($mapping,'etudiants',$i['IDETUDIANT']).':'.$year.':'.$campus.':'.$this->mapped($mapping,'specialites',$i['IDSPECIALITE']).':'.$this->mapped($mapping,'niveaux',$i['IDNIVEAU']);
                if(isset($identities[$identity]))throw new \InvalidArgumentException('Plusieurs inscriptions source pour le même étudiant / année / campus : '.$i['IDINSCRIPTION']);
                $identities[$identity]=true;
                $enrolments[$i['IDETUDIANT'].':'.$i['IDANNEE'].':'.$i['IDSPECIALITE'].':'.$i['IDNIVEAU']]=true;
            }catch(\Exception $e){$errors[]=$e->getMessage();}
        }
        $noteKeys=array();
        foreach($source['tables']['notes']as $n){
            try{
                $seq=self::sequence($n['TYPE_EXAMEN']);
                if(!isset($sessions[$n['IDSESSION']]) || !isset($programmes[$n['IDPROGRAMMESP']]) || !isset($years[$n['IDANNEE']]) || !isset($students[$n['IDETUDIANT']]))throw new \InvalidArgumentException('Note orpheline : '.$n['IDNOTE']);
                if(!isset($enrolments[$n['IDETUDIANT'].':'.$n['IDANNEE'].':'.$n['IDSPECIALITE'].':'.$n['IDNIVEAU']]))throw new \InvalidArgumentException('Inscription existante non résolue pour note '.$n['IDNOTE']);
                $se=$sessions[$n['IDSESSION']];
                if($se['IDPROGRAMMESP']!=$n['IDPROGRAMMESP'] || $se['IDANNEE']!=$n['IDANNEE'])throw new \InvalidArgumentException('Session incohérente pour note '.$n['IDNOTE']);
                $p=$programmes[$n['IDPROGRAMMESP']];
                foreach(array('IDMATIERE','IDSPECIALITE','IDNIVEAU','IDCYCLE','IDFILIERE') as $column)if(isset($p[$column],$n[$column]) && $p[$column]!=$n[$column])throw new \InvalidArgumentException('Contexte du programme incohérent pour note '.$n['IDNOTE']);
                $columns=array(1=>'CC',2=>'CC2',3=>'SN',4=>'SN2',5=>'RATRAPPAGE',6=>'RATRAPPAGE2',7=>'TPE',8=>'TPE2');$max=(float)$se[$columns[$seq]];
                if(!is_numeric($n['NOTE']) || (float)$n['NOTE']<0 || (float)$n['NOTE']>$max || $max<=0)throw new \InvalidArgumentException('Contribution hors pondération : note '.$n['IDNOTE']);
                $key=$n['IDETUDIANT'].':'.$n['IDSESSION'].':'.$seq;
                if(isset($noteKeys[$key]))throw new \InvalidArgumentException('Doublon source de note : '.$n['IDNOTE']);$noteKeys[$key]=true;
            }catch(\Exception $e){$errors[]=$e->getMessage();}
        }
        return array('counts'=>$counts,'campus_id'=>$campus,'years'=>$years,'errors'=>array_values(array_unique($errors)),'ready'=>!count($errors));
    }

    public function isolateInvalid($source,$mapping)
    {
        $valid=$source;$quarantine=array();$years=array();$programmes=array();$sessions=array();$students=array();$enrolments=array();$inscSeen=array();
        foreach(array('etudiant','programme_specialite','session_examen','inscription','notes')as $table){$valid['tables'][$table]=array();$quarantine[$table]=array();}
        foreach($source['tables']['annee']as $a){try{$years[$a['IDANNEE']]=self::schoolYear($a['ANNEES']);}catch(\Exception $e){}}
        foreach($source['tables']['etudiant']as $s){
            if(!empty($mapping['etudiants'][$s['IDETUDIANT']]) && DB::table('eleve')->where('id',$mapping['etudiants'][$s['IDETUDIANT']])->exists()){$students[$s['IDETUDIANT']]=true;$valid['tables']['etudiant'][]=$s;}
            else $quarantine['etudiant'][]=array('reason'=>'Identité ambiguë ou données de création invalides ; arbitrage requis.','row'=>$s);
        }
        foreach($source['tables']['programme_specialite']as $p){
            if(!empty($mapping['programmes'][$p['IDPROGRAMMESP']]) && !empty($mapping['tutelles_by_programme'][$p['IDPROGRAMMESP']])){$programmes[$p['IDPROGRAMMESP']]=$p;$valid['tables']['programme_specialite'][]=$p;}
            else $quarantine['programme_specialite'][]=array('reason'=>'Programme ou tutelle non résolu.','row'=>$p);
        }
        $sessionGroups=array();
        foreach($source['tables']['session_examen']as $s){
            if(!isset($programmes[$s['IDPROGRAMMESP']]) || !isset($years[$s['IDANNEE']])){$quarantine['session_examen'][]=array('reason'=>'Programme ou année absent.','row'=>$s);continue;}
            $key=$mapping['programmes'][$s['IDPROGRAMMESP']].':'.$years[$s['IDANNEE']].':'.implode(',',$this->tutelles($mapping,$s['IDPROGRAMMESP']));
            $sessionGroups[$key][]=$s;
        }
        $canonicalSessions=array();
        foreach($sessionGroups as $key=>$group){
            $signatures=array();foreach($group as $s){$weights=array();foreach(array('CC','TPE','SN','RATRAPPAGE','CC2','TPE2','SN2','RATRAPPAGE2')as $column)$weights[]=(int)$s[$column];$signatures[implode(':',$weights)]=true;}
            if(count($signatures)>1){foreach($group as $s)$quarantine['session_examen'][]=array('reason'=>'Pondérations différentes pour le même programme / année / tutelle.','row'=>$s);continue;}
            foreach($group as $s){$sessions[$s['IDSESSION']]=$s;$canonicalSessions[$s['IDSESSION']]=$key;$valid['tables']['session_examen'][]=$s;}
        }
        foreach($source['tables']['inscription']as $i){
            $reason=null;
            if(!isset($students[$i['IDETUDIANT']]))$reason='Étudiant non résolu.';
            elseif(!isset($years[$i['IDANNEE']]))$reason='Année absente de Test OK.';
            elseif(empty($mapping['specialites'][$i['IDSPECIALITE']]) || empty($mapping['niveaux'][$i['IDNIVEAU']]))$reason='Spécialité ou niveau non résolu.';
            if(!$reason){try{$this->existingInscription($i,$mapping,$years[$i['IDANNEE']]);}catch(\Exception $e){$reason=$e->getMessage();}}
            $key=$i['IDETUDIANT'].':'.$i['IDANNEE'].':'.$i['IDSPECIALITE'].':'.$i['IDNIVEAU'];
            if(!$reason && isset($inscSeen[$key]))$reason='Inscription source en double.';
            if($reason)$quarantine['inscription'][]=array('reason'=>$reason,'row'=>$i);
            else{$inscSeen[$key]=true;$enrolments[$key]=true;$valid['tables']['inscription'][]=$i;}
        }
        $noteGroups=array();$columns=array(1=>'CC',2=>'CC2',3=>'SN',4=>'SN2',5=>'RATRAPPAGE',6=>'RATRAPPAGE2',7=>'TPE',8=>'TPE2');
        foreach($source['tables']['notes']as $n){
            $reason=null;$seq=null;
            try{$seq=self::sequence($n['TYPE_EXAMEN']);}catch(\Exception $e){$reason=$e->getMessage();}
            if(!$reason && (!isset($sessions[$n['IDSESSION']]) || !isset($programmes[$n['IDPROGRAMMESP']]) || !isset($years[$n['IDANNEE']]) || !isset($students[$n['IDETUDIANT']])))$reason='Étudiant, programme, année ou session non résolu.';
            if(!$reason){
                $s=$sessions[$n['IDSESSION']];$p=$programmes[$n['IDPROGRAMMESP']];
                if($s['IDPROGRAMMESP']!=$n['IDPROGRAMMESP'] || $s['IDANNEE']!=$n['IDANNEE'])$reason='La session ne correspond pas au programme ou à l’année de la note.';
                foreach(array('IDMATIERE','IDSPECIALITE','IDNIVEAU','IDCYCLE','IDFILIERE')as $column)if(isset($n[$column],$p[$column]) && $n[$column]!=$p[$column])$reason='Le contexte de la note ne correspond pas au programme.';
                if(!isset($enrolments[$n['IDETUDIANT'].':'.$n['IDANNEE'].':'.$n['IDSPECIALITE'].':'.$n['IDNIVEAU']]))$reason='Inscription source cohérente absente.';
                $max=(float)$s[$columns[$seq]];
                if(!is_numeric($n['NOTE']) || (float)$n['NOTE']<0 || (float)$n['NOTE']>$max || $max<=0)$reason='Contribution hors pondération ou évaluation désactivée.';
            }
            if($reason){$quarantine['notes'][]=array('reason'=>$reason,'row'=>$n);continue;}
            $key=$mapping['etudiants'][$n['IDETUDIANT']].':'.$canonicalSessions[$n['IDSESSION']].':'.$seq;
            $noteGroups[$key][]=$n;
        }
        foreach($noteGroups as $group){
            $signatures=array();foreach($group as $n)$signatures[(string)(float)$n['NOTE'].'|'.$n['ANONYMATS'].'|'.$n['MOIS']]=true;
            if(count($signatures)>1){foreach($group as $n)$quarantine['notes'][]=array('reason'=>'Notes contradictoires pour une même évaluation : arbitrage requis.','row'=>$n);continue;}
            $valid['tables']['notes'][]=$group[0];
            foreach(array_slice($group,1)as $n)$quarantine['notes'][]=array('reason'=>'Doublon identique conservé dans le rapport, sans création d’une deuxième note.','row'=>$n);
        }
        $counts=array();$reasons=array();foreach($quarantine as $table=>$rows){$counts[$table]=count($rows);foreach($rows as $r){if(!isset($reasons[$table][$r['reason']]))$reasons[$table][$r['reason']]=0;$reasons[$table][$r['reason']]++;}}
        return array('source'=>$valid,'quarantine'=>array('format'=>'testok-quarantine-v1','source'=>$mapping['source'],'created_at'=>date('c'),'counts'=>$counts,'reasons'=>$reasons,'records'=>$quarantine));
    }

    private function remember($source,$entity,$legacyId,$targetId,$row)
    {
        $json=json_encode($row);$hash=hash('sha256',$json);
        $existing=DB::table('testok_import_links')->where('source',$source)->where('entite',$entity)->where('legacy_id',$legacyId)->first();
        if($existing){if(!hash_equals($existing->empreinte,$hash) || (int)$existing->target_id!==(int)$targetId)throw new \RuntimeException('Source modifiée depuis la reprise : '.$entity.' '.$legacyId);return;}
        DB::table('testok_import_links')->insert(array('source'=>$source,'entite'=>$entity,'legacy_id'=>$legacyId,'target_id'=>$targetId,'donnees_source'=>$json,'empreinte'=>$hash,'created_at'=>date('Y-m-d H:i:s'),'updated_at'=>date('Y-m-d H:i:s')));
    }

    public function import($source,$mapping)
    {
        $plan=$this->plan($source,$mapping);if(!$plan['ready'])throw new \RuntimeException('Simulation incomplète : aucune donnée importée.');
        $this->stats=array('inscriptions_creees'=>0,'inscriptions_reutilisees'=>0,'sessions_creees'=>0,'sessions_reutilisees'=>0,'notes_creees'=>0,'notes_reutilisees'=>0);
        DB::connection()->disableQueryLog();
        if(class_exists('Barryvdh\Debugbar\Facade'))\Barryvdh\Debugbar\Facade::disable();
        $service=$this;
        return DB::transaction(function()use($source,$mapping,$plan,$service){
            // Verrou transactionnel PostgreSQL : deux reprises concurrentes de la même source ne se croisent pas.
            DB::select('SELECT pg_advisory_xact_lock(hashtext(?))',array('testok-import-'.$mapping['source']));
            $campus=$plan['campus_id'];$years=$plan['years'];$now=date('Y-m-d H:i:s');$sessionMap=array();$programmes=array();
            foreach($source['tables']['programme_specialite']as $p){$programmes[$p['IDPROGRAMMESP']]=$p;$service->remember($mapping['source'],'programme_specialite',$p['IDPROGRAMMESP'],$service->mapped($mapping,'programmes',$p['IDPROGRAMMESP']),$p);}
            foreach($source['tables']['etudiant']as $s)$service->remember($mapping['source'],'etudiant',$s['IDETUDIANT'],$service->mapped($mapping,'etudiants',$s['IDETUDIANT']),$s);
            foreach($source['tables']['session_examen']as $s){
                $cmId=$service->mapped($mapping,'programmes',$s['IDPROGRAMMESP']);$p=$programmes[$s['IDPROGRAMMESP']];
                foreach($service->tutelles($mapping,$s['IDPROGRAMMESP']) as $tutelle){
                $values=array('id_classe_matiere'=>$cmId,'id_matiere'=>$service->mapped($mapping,'matieres',$p['IDMATIERE']),'id_tutelle'=>$tutelle,'annee_academique'=>$years[$s['IDANNEE']]);
                foreach(array('CC'=>'cc1','TPE'=>'tpe1','SN'=>'sn1','RATRAPPAGE'=>'ratrappage1','CC2'=>'cc2','TPE2'=>'tpe2','SN2'=>'sn2','RATRAPPAGE2'=>'ratrappage2')as $from=>$to)$values[$to]=(int)$s[$from];
                $query=DB::table('session_exams')->where('id_classe_matiere',$cmId)->where('id_tutelle',$tutelle)->where('annee_academique',$values['annee_academique']);$existing=$query->get();
                if(count($existing)>1)throw new \RuntimeException('Sessions cibles ambiguës.');
                if(count($existing)){ $target=$existing[0];foreach($values as $k=>$v)if((string)$target->$k!==(string)$v)throw new \RuntimeException('Session cible différente : '.$target->id);$id=$target->id;$service->stats['sessions_reutilisees']++; }
                else{$values['created_at']=$now;$values['updated_at']=$now;$id=DB::table('session_exams')->insertGetId($values);$service->stats['sessions_creees']++;}
                $sessionMap[$s['IDSESSION']][$tutelle]=$id;$service->remember($mapping['source'],'session_examen_'.$tutelle,$s['IDSESSION'],$id,$s);
                }
            }
            foreach($source['tables']['inscription']as $i){
                $created=false;$existing=$service->existingInscription($i,$mapping,$years[$i['IDANNEE']],true,$created);
                $service->stats[$created?'inscriptions_creees':'inscriptions_reutilisees']++;
                $service->remember($mapping['source'],'inscription',$i['IDINSCRIPTION'],$existing->id,$i);
            }
            foreach($source['tables']['notes']as $n){
                foreach($service->tutelles($mapping,$n['IDPROGRAMMESP']) as $tutelle){
                $identity=array('id_eleve'=>$service->mapped($mapping,'etudiants',$n['IDETUDIANT']),'annee_scolaire'=>$years[$n['IDANNEE']],'id_campus'=>$campus,'id_session'=>$sessionMap[$n['IDSESSION']][$tutelle],'sequence'=>self::sequence($n['TYPE_EXAMEN']));
                $values=array_merge($identity,array('id_matiere'=>$service->mapped($mapping,'matieres',$n['IDMATIERE']),'id_classe'=>$service->mapped($mapping,'specialites',$n['IDSPECIALITE']),'id_classe_matiere'=>$service->mapped($mapping,'programmes',$n['IDPROGRAMMESP']),'note'=>(float)$n['NOTE'],'anonymat'=>$n['ANONYMATS'],'mois_examen'=>$n['MOIS']));
                $q=DB::table('note');foreach($identity as $k=>$v)$q->where($k,$v);$rows=$q->get();
                if(count($rows)>1)throw new \RuntimeException('Note cible ambiguë.');
                if(count($rows)){ $id=$rows[0]->id;foreach($values as $k=>$v)if((string)$rows[0]->$k!==(string)$v)throw new \RuntimeException('Note cible différente : '.$id);$service->stats['notes_reutilisees']++; }
                else{$values['created_at']=$now;$values['updated_at']=$now;$values['edition_details']='Import Test OK : contribution pondérée conservée.';$id=DB::table('note')->insertGetId($values);$service->stats['notes_creees']++;}
                $service->remember($mapping['source'],'notes_'.$tutelle,$n['IDNOTE'],$id,$n);
                }
            }
            return $plan['counts'];
        });
    }
}
