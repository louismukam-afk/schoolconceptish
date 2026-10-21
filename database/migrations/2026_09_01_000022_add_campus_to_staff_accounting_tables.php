<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCampusToStaffAccountingTables extends Migration {
    public function up()
    {
        $tables = array('personnel', 'decaissement', 'payement_salaire');
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'id_campus')) {
                Schema::table($tableName, function(Blueprint $table) {
                    $table->integer('id_campus')->nullable();
                });
            }
        }

        if (!Schema::hasTable('campus')) {
            return;
        }

        $campus = DB::table('campus')->orderBy('id')->first();
        if (!$campus) {
            return;
        }

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'id_campus')) {
                DB::table($tableName)->whereNull('id_campus')->update(array('id_campus' => $campus->id));
            }
        }
    }

    public function down()
    {
        foreach (array('personnel', 'decaissement', 'payement_salaire') as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'id_campus')) {
                Schema::table($tableName, function(Blueprint $table) {
                    $table->dropColumn('id_campus');
                });
            }
        }
    }
}
