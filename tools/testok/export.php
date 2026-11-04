<?php
// À exécuter avec le PHP disposant de mysqli, sur la machine de Test OK.
// Lecture seule : aucune instruction SQL d'écriture dans le logiciel source.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$options = getopt('', array('source-root:', 'output:'));
if (empty($options['source-root']) || empty($options['output'])) {
    fwrite(STDERR, "Usage: php tools/testok/export.php --source-root=CHEMIN_TESTOK --output=EXPORT_PRIVE.json\n"); exit(1);
}
$root = realpath($options['source-root']);
if (!$root || !is_file($root.'/connexion.php')) { fwrite(STDERR, "Sources Test OK introuvables.\n"); exit(1); }
$publicRoot = realpath(__DIR__.'/../../public');
$outputDir = realpath(dirname($options['output']));
if (!$outputDir || ($publicRoot && strpos(strtolower($outputDir.DIRECTORY_SEPARATOR), strtolower($publicRoot.DIRECTORY_SEPARATOR)) === 0)) {
    fwrite(STDERR, "Choisissez un dossier privé existant, hors de public.\n"); exit(1);
}
$privateFolder=realpath(__DIR__.'/../../storage/app/import-testok');
if($privateFolder && strcasecmp($outputDir,$privateFolder)===0 && !is_file($outputDir.'/.htaccess'))copy(__DIR__.'/private.htaccess',$outputDir.'/.htaccess');
mysqli_report(MYSQLI_REPORT_OFF);
ob_start(); require $root.'/connexion.php'; ob_end_clean();
if (!isset($con) || !$con) { fwrite(STDERR, "Connexion à Test OK indisponible.\n"); exit(1); }
$con->set_charset('utf8mb4');
$con->query('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
$tables = array('annee','cycle','niveaux','filiere','specialite','codematiere','matiere','programme_specialite','groupe_matiere','session_examen','etudiant','inscription','notes');
$data = array(); $counts = array();
foreach ($tables as $table) {
    $result = $con->query('SELECT * FROM `'.$table.'`');
    if (!$result) { $con->rollback(); fwrite(STDERR, "Table source manquante : ".$table."\n"); exit(1); }
    $data[$table] = array();
    while ($row = $result->fetch_assoc()) $data[$table][] = array_change_key_case($row, CASE_UPPER);
    $counts[$table] = count($data[$table]);
}
foreach(array('parent','domaine','ecole','tutelle')as $table){
    $result=$con->query('SELECT * FROM `'.$table.'`');if(!$result)continue;
    $data[$table]=array();while($row=$result->fetch_assoc())$data[$table][]=array_change_key_case($row,CASE_UPPER);
    $counts[$table]=count($data[$table]);
}
$con->rollback();
$payload = array('format' => 'testok-schoolconcept-v1', 'exported_at' => date('c'), 'tables' => $data);
$json = json_encode($payload, JSON_UNESCAPED_UNICODE);
if ($json === false) { fwrite(STDERR, "Export impossible : encodage invalide.\n"); exit(1); }
if (file_put_contents($options['output'], $json, LOCK_EX) === false) { fwrite(STDERR, "Écriture impossible.\n"); exit(1); }
@chmod($options['output'], 0600);
echo json_encode(array('counts' => $counts, 'sha256' => hash('sha256', $json)), JSON_PRETTY_PRINT)."\n";
