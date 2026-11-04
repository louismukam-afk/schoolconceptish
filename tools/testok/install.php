<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
require __DIR__.'/../../bootstrap/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';
$kernel=$app->make('Illuminate\Contracts\Console\Kernel');$kernel->bootstrap();
$folder=storage_path('app/import-testok');if(!is_dir($folder))mkdir($folder,0700,true);
if(!is_file($folder.'/.htaccess'))copy(__DIR__.'/private.htaccess',$folder.'/.htaccess');
$migrationFolder=$folder.'/migrations';if(!is_dir($migrationFolder))mkdir($migrationFolder,0700,true);
foreach(array('2026_10_30_000001_create_academic_document_workflow.php','2026_10_30_000002_restore_academic_sequences.php','2026_10_30_000003_allow_fractional_academic_credits.php','2026_10_30_000005_create_academic_exam_anonymats.php','2026_10_30_000006_create_academic_deliberations.php')as $name){
    if(!copy(database_path('migrations/'.$name),$migrationFolder.'/'.$name)){fwrite(STDERR,"Préparation de la migration impossible.\n");exit(1);}
}
$code=$kernel->call('migrate',array('--path'=>'storage/app/import-testok/migrations','--force'=>true));echo $kernel->output();if($code)exit($code);
$code=$kernel->call('testok:tutelles');echo $kernel->output();exit($code);
