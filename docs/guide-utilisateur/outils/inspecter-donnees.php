<?php
// Lecture des métadonnées de contexte pour choisir des captures existantes.
// Aucune création, suppression ou modification de données.
require __DIR__.'/../../../bootstrap/autoload.php';
$app = require __DIR__.'/../../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$db=$app->make('db')->connection();
$db->beginTransaction();
$db->statement('SET TRANSACTION READ ONLY');
$queries=array(
 'generations'=>'SELECT id,type,annee_academique,id_classe,id_cycle,id_nniveau,nb_releves FROM releve_note_generations ORDER BY id DESC LIMIT 100',
 'pv'=>'SELECT id,id_classe,id_cycle,id_nniveau,annee_academique,type_pv,semestre FROM pv_generations ORDER BY id DESC LIMIT 20',
 'contextes'=>'SELECT i.id_classe,i.id_cycle,i.id_nniveau, count(*) as effectif FROM inscription i WHERE i.annee_scolaire = (SELECT valeur FROM parametres WHERE nom = \'annee_scolaire\' LIMIT 1) GROUP BY i.id_classe,i.id_cycle,i.id_nniveau ORDER BY count(*) DESC LIMIT 12'
);
$out=array();
foreach($queries as $key=>$sql){$out[$key]=$db->select($sql);}
$db->rollBack();
file_put_contents(__DIR__.'/../contextes-captures.json',json_encode($out,JSON_PRETTY_PRINT));
echo json_encode($out,JSON_PRETTY_PRINT);
