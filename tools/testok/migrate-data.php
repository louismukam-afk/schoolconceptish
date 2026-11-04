<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
ini_set('memory_limit','512M');set_time_limit(0);
require __DIR__.'/../../bootstrap/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$kernel=$app->make('Illuminate\Contracts\Console\Kernel');$kernel->bootstrap();
$options=getopt('',array('package:','campus:','mapping:','commit'));
try {
    if(empty($options['package']) || empty($options['campus']))throw new RuntimeException('Usage: php tools/testok/migrate-data.php --package=storage/app/import-testok/PAQUET --campus=ID [--mapping=CORRESPONDANCES_DE_CE_SERVEUR.json] [--commit]');
    $mapping=isset($options['mapping'])?$options['mapping']:null;
    if(!isset($options['commit'])){
        $report=(new gesecole\Services\TestokDataMigrationService)->run($options['package'],$options['campus'],$mapping,false);
        echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\nSimulation terminée : toutes les écritures ont été annulées. Les séquences PostgreSQL peuvent avancer.\n";
    }else{
        $app->instance('testok.data_migration',array('package'=>$options['package'],'campus'=>$options['campus'],'mapping'=>$mapping));
        $code=$kernel->call('migrate',array('--path'=>'database/migrations-testok-data','--force'=>true));
        echo $kernel->output();exit($code);
    }
}catch(Exception $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}
