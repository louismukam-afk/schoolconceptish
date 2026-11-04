<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
ini_set('memory_limit','512M');
require __DIR__.'/../../bootstrap/autoload.php';
$options=getopt('',array('sql:','output:'));
try {
    if(empty($options['sql']) || empty($options['output']))throw new RuntimeException('Usage: php tools/testok/package-sql.php --sql=EXPORT.sql --output=DOSSIER_PRIVE');
    $directory=realpath($options['output']);$root=realpath(__DIR__.'/../..');
    $privateRoot=realpath($root.'/storage/app/import-testok');
    if(!$directory || !$privateRoot || (strcasecmp($directory,$privateRoot)!==0 && stripos($directory.DIRECTORY_SEPARATOR,$privateRoot.DIRECTORY_SEPARATOR)!==0))throw new RuntimeException('Créer le dossier de sortie dans storage/app/import-testok.');
    if(is_file($directory.'/source.json') || is_file($directory.'/manifest.json'))throw new RuntimeException('Un paquet existe déjà dans ce dossier ; choisir un nouveau dossier.');
    if(!copy(__DIR__.'/private.htaccess',$directory.'/.htaccess'))throw new RuntimeException('Protection du dossier impossible.');
    $source=(new gesecole\Services\TestokSqlDumpReader)->read($options['sql']);
    $json=json_encode($source,JSON_UNESCAPED_UNICODE);
    if($json===false)throw new RuntimeException('Encodage UTF-8 invalide.');
    $counts=array();foreach($source['tables'] as $table=>$rows)$counts[$table]=count($rows);
    $manifest=array('format'=>'testok-migration-package-v1','sql_sha256'=>$source['sql_sha256'],'source_sha256'=>hash('sha256',$json),'counts'=>$counts,'excluded_tables'=>$source['excluded_tables']);
    if(file_put_contents($directory.'/source.json',$json,LOCK_EX)===false || file_put_contents($directory.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX)===false)throw new RuntimeException('Écriture du paquet impossible.');
    @chmod($directory.'/source.json',0600);@chmod($directory.'/manifest.json',0600);
    echo json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
}catch(Exception $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}
