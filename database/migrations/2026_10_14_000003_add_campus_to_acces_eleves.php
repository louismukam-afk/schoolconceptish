<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCampusToAccesEleves extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('acces_eleves')) {
            return;
        }

        if (!Schema::hasColumn('acces_eleves', 'id_campus')) {
            Schema::table('acces_eleves', function (Blueprint $table) {
                $table->integer('id_campus')->nullable()->index();
            });
        }

        if (!Schema::hasTable('campus')) {
            return;
        }

        $campus = DB::table('campus')->orderBy('id')->first();
        if (!$campus) {
            return;
        }

        DB::table('acces_eleves')
            ->whereNull('id_campus')
            ->update(array('id_campus' => $campus->id));
    }

    public function down()
    {
        if (Schema::hasTable('acces_eleves') && Schema::hasColumn('acces_eleves', 'id_campus')) {
            Schema::table('acces_eleves', function (Blueprint $table) {
                $table->dropColumn('id_campus');
            });
        }
    }
}
