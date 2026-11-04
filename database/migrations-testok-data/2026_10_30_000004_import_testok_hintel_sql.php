<?php

use Illuminate\Database\Migrations\Migration;
use gesecole\Services\TestokDataMigrationService;

class ImportTestokHintelSql extends Migration {
    public function up()
    {
        if(!app()->bound('testok.data_migration'))throw new RuntimeException('Lancer cette migration avec php tools/testok/migrate-data.php ; campus et paquet privés requis.');
        $options=app('testok.data_migration');
        (new TestokDataMigrationService)->run($options['package'],$options['campus'],$options['mapping'],true);
    }

    public function down()
    {
        throw new RuntimeException('Reprise académique irréversible automatiquement : restaurer la sauvegarde préalable pour annuler, afin de préserver les modifications faites après la reprise.');
    }
}
