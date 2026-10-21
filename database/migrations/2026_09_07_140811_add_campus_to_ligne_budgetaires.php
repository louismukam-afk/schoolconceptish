<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCampusToLigneBudgetaires extends Migration {
    public function up()
    {
        if (Schema::hasTable('ligne_budgetaires') && !Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            Schema::table('ligne_budgetaires', function(Blueprint $table) {
                $table->integer('id_campus')->nullable();
            });
        }

        if (!Schema::hasTable('campus') || !Schema::hasTable('ligne_budgetaires')) {
            return;
        }

        $campus = DB::table('campus')->orderBy('id')->first();
        if ($campus && Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            DB::table('ligne_budgetaires')->whereNull('id_campus')->update(array('id_campus' => $campus->id));
        }
    }

    public function down()
    {
        if (Schema::hasTable('ligne_budgetaires') && Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            Schema::table('ligne_budgetaires', function(Blueprint $table) {
                $table->dropColumn('id_campus');
            });
        }
    }
}
