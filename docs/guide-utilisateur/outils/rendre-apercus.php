<?php
// Aperçus locaux des vues existantes. Pas de route ajoutée à l'application.
// La base impose une transaction READ ONLY. Les initialisations et l'archivage
// automatiques sont neutralisés uniquement dans les classes évaluées en mémoire.
require __DIR__.'/../../../bootstrap/autoload.php';
$app=require __DIR__.'/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
config(array('session.driver'=>'array','app.debug'=>false));
$app->make('session')->start();
$app->make('request')->setSession($app->make('session')->driver());
\View::share('errors',new \Illuminate\Support\MessageBag());
session(array('year'=>'2025-2026','campus_id'=>1,'nom_etab'=>'INSTITUT SUPERIEUR HINTEL'));
\URL::forceRootUrl('http://127.0.0.1:8080/ISH/app/public');
$db=$app->make('db')->connection();$db->beginTransaction();$db->statement('SET TRANSACTION READ ONLY');
$user=\gesecole\User::where('name','mukam')->first();
\Auth::onceUsingId($user->id);
function readonlyClass($relative,$neutralized){
    $file=__DIR__.'/../../../app/Http/Controllers/'.$relative.'.php';
    $src=file_get_contents($file);$tokens=token_get_all($src);$offset=0;$ranges=array();
    for($i=0;$i<count($tokens);$i++){
        $token=$tokens[$i];$value=is_array($token)?$token[1]:$token;
        if(is_array($token)&&$token[0]===T_FUNCTION){
            $method=null;$j=$i+1;
            while($j<count($tokens)){if(is_array($tokens[$j])&&$tokens[$j][0]===T_STRING){$method=$tokens[$j][1];break;}if($tokens[$j]==='(')break;$j++;}
            if(in_array($method,$neutralized)){
                $pos=$offset+strlen($value);$j=$i+1;
                while($tokens[$j]!=='{'){$pos+=strlen(is_array($tokens[$j])?$tokens[$j][1]:$tokens[$j]);$j++;}
                $start=$pos+1;$pos++;$j++;$depth=1;
                while($depth){$t=$tokens[$j];if($t==='{')$depth++;elseif($t==='}')$depth--;if($depth)$pos+=strlen(is_array($t)?$t[1]:$t);$j++;}
                $ranges[]=array($start,$pos-$start);
            }
        }
        $offset+=strlen($value);
    }
    foreach(array_reverse($ranges) as $r)$src=substr_replace($src,' return null; ',$r[0],$r[1]);
    eval(substr($src,5));
}
$dir=__DIR__.'/../apercus';if(!is_dir($dir))mkdir($dir,0777,true);
$group=isset($argv[1])?$argv[1]:'pv';
$outputs=array();
try{
if($group==='pv'){
readonlyClass('eleves/noteController',array('savePvGenerationRecord'));
$c=new \gesecole\Http\Controllers\eleves\noteController();
foreach(array('bts','hnd','licence_soa','licence_ngaoundere','licence_ebolowa','licence_bamenda') as $format){
    $request=\Illuminate\Http\Request::create('/apercu','GET',array('sequence'=>3,'cycle'=>1,'nniveau'=>6,'pv_type'=>$format));
    $view=$c->print_note($request,20);$name='pv-'.$format;
    file_put_contents($dir.'/'.$name.'.html',$view->render());$outputs[]=$name;
}
}elseif($group==='emploi'){
readonlyClass('personnel/emploiTempsController',array('seedDefaultPlages'));
$c=new \gesecole\Http\Controllers\personnel\emploiTempsController();
$request=\Illuminate\Http\Request::create('/apercu','GET');
$views=array('emploi-creer'=>$c->createCours($request),'emploi-plages'=>$c->plages(),'emploi-specialites'=>$c->specialites($request),'emploi-enseignants'=>$c->enseignants($request));
foreach($views as $name=>$view){file_put_contents($dir.'/'.$name.'.html',$view->render());$outputs[]=$name;}
}elseif($group==='releves'){
foreach(array('soa'=>'soareleveController','ndere'=>'nderereleveController','ebolowa'=>'esticreleveController','bamenda'=>'bamendareleveController','bts'=>'bts_releveSessionController') as $kind=>$class){
if(isset($argv[2])&&$argv[2]!==$kind)continue;
try{
$full='gesecole\\Http\\Controllers\\eleves\\'.$class;$c=new $full();
$request=\Illuminate\Http\Request::create('/apercu','GET',array('id'=>20,'cycle'=>1,'nniveau'=>6,'trimestre'=>1,'semestre'=>1,'save'=>0));
$view=$c->create($request);$name='releve-'.$kind;
file_put_contents($dir.'/'.$name.'.html',$view->render());$outputs[]=$name;
}catch(\Exception $e){echo $kind.': '.$e->getMessage()."\n";}
}
}elseif($group==='attestations'){
foreach(array('ndere'=>'ndereAttestationReussiteController','soa'=>'soaAttestationReussiteController','ebolowa'=>'ebolowaAttestationReussiteController') as $kind=>$class){
if(isset($argv[2])&&$argv[2]!==$kind)continue;
try{
$full='gesecole\\Http\\Controllers\\eleves\\'.$class;$c=new $full();
$method=new \ReflectionMethod($full,'buildAttestations');$method->setAccessible(true);$att=$method->invoke($c,20,1,6);
if(count($att)===0){
    $student=(object)array('nom'=>'ETUDIANT','prenom'=>'EXEMPLE PEDAGOGIQUE','date_naiss'=>'2003-01-01','lieu_naiss'=>'VILLE EXEMPLE','sexe'=>'M','matricule'=>'DEMONSTRATION');
    $att=array(array('eleve'=>$student,'moyenne100'=>65,'moyenne20'=>13));
}
$generation=new \stdClass();$generation->id=0;$generation->annee_academique='2025-2026';$generation->created_at='2026-10-21';
$values=array('titre'=>'Apercu attestation '.$kind,'attestations'=>$att,'classe'=>\gesecole\classe::find(20),'cycle'=>\gesecole\cycle::find(1),'nniveau'=>\gesecole\NNiveau::find(6),'niveau'=>\gesecole\niveau::find(\gesecole\classe::find(20)->id_niveau),'campus'=>\gesecole\Campus::find(1),'tutelle'=>null,'generation'=>$generation,'periode_debut'=>null,'periode_fin'=>null,'page_size'=>'A4');
if($kind!=='ndere')$values=array_merge($values,array('attestation_city'=>$kind==='soa'?'Soa':'Ebolowa','attestation_home_url'=>'/eleves/attestations_reussite_'.$kind,'attestation_history_url'=>'/eleves/attestations_reussite_'.$kind.'/historique','attestation_signature_primary'=>'Le Doyen','attestation_signature_primary_en'=>'The Dean','attestation_template'=>$kind,'attestation_page_orientation'=>'portrait'));
$name='attestation-'.$kind;$view=view('eleves/print_attestations_reussite_ndere',$values);file_put_contents($dir.'/'.$name.'.html',$view->render());$outputs[]=$name;
}catch(\Exception $e){echo $kind.': '.$e->getMessage()."\n";}
}
}elseif($group==='notes'){
$c=new \gesecole\Http\Controllers\eleves\noteController();
$params=array('id'=>20,'cycle'=>1,'nniveau'=>6);
if(isset($argv[2])&&$argv[2]==='grille'){
$cm=\gesecole\classeMatiere::where('id_classe',20)->where('id_cycle',1)->where('id_nniveau',6)->first();
$params['id']=$cm->id_matiere;$params['sequence']=1;
$request=\Illuminate\Http\Request::create('/apercu','POST',$params);$view=$c->create($request,20);$name='note-grille';
}else{
$request=\Illuminate\Http\Request::create('/apercu','GET',$params);$view=isset($argv[2])&&$argv[2]==='afficher'?$c->matiere_show($request):$c->matiere($request);$name=isset($argv[2])&&$argv[2]==='afficher'?'note-afficher':'note-matieres';
}
if(!($view instanceof \Illuminate\View\View)&&isset($argv[2])&&$argv[2]==='grille'){
    // Affichage du modèle de grille avec les données existantes, sans ouvrir un verrou.
    $p=\gesecole\Session_exam::where('id_classe_matiere',$cm->id)->where('annee_academique',session('year'))->first();
    $view=view('eleves/eleves_notes',array('titre'=>'Notes','matiere'=>\gesecole\matiere::find($cm->id_matiere),'eleves'=>\DB::select('SELECT e.id,e.nom,e.prenom FROM eleve e JOIN inscription i ON i.id_eleve=e.id WHERE i.id_classe=20 AND i.id_cycle=1 AND i.id_nniveau=6 AND i.annee_scolaire=? ORDER BY e.nom',array(session('year'))),'notes'=>\gesecole\note::where('id_matiere',$cm->id_matiere)->where('sequence',1)->where('annee_scolaire',session('year'))->get(),'pourcentage'=>$p?$p->cc1:0,'libelleType'=>'CC 1','annee_scolaire'=>session('year'),'max'=>$cm->max,'id_cycle'=>1,'id_classe'=>20,'id_nniveau'=>6,'sequence'=>1));
}
if($view instanceof \Illuminate\View\View){file_put_contents($dir.'/'.$name.'.html',$view->render());$outputs[]=$name;}else echo "Grille non disponible : verifier sessions et verrous.\n";
}
$db->rollBack();
echo json_encode(array('group'=>$group,'apercus'=>$outputs,'base'=>'transaction lecture seule, annulée'))."\n";
}catch(\Exception $e){$db->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
