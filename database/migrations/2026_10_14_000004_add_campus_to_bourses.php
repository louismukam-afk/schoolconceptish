<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCampusToBourses extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('bourses')) {
            return;
        }

        if (!Schema::hasColumn('bourses', 'id_campus')) {
            Schema::table('bourses', function (Blueprint $table) {
                $table->integer('id_campus')->nullable()->index();
            });
        }

        if (Schema::hasTable('campus')) {
            $campus = DB::table('campus')->orderBy('id')->first();
            if ($campus) {
                DB::table('bourses')
                    ->whereNull('id_campus')
                    ->orWhere('id_campus', '=', 0)
                    ->update(array('id_campus' => $campus->id));
            }
        }

        try {
            Schema::table('bourses', function (Blueprint $table) {
                $table->dropUnique('unique__id_eleve__annee_scolaire');
            });
        } catch (\Exception $e) {
            //
        }

        try {
            Schema::table('bourses', function (Blueprint $table) {
                $table->unique(array('id_eleve', 'annee_scolaire', 'id_campus'), 'unique__id_eleve__annee_scolaire__id_campus');
            });
        } catch (\Exception $e) {
            //
        }
    }

    public function down()
    {
        if (!Schema::hasTable('bourses')) {
            return;
        }

        try {
            Schema::table('bourses', function (Blueprint $table) {
                $table->dropUnique('unique__id_eleve__annee_scolaire__id_campus');
            });
        } catch (\Exception $e) {
            //
        }

        if (Schema::hasColumn('bourses', 'id_campus')) {
            Schema::table('bourses', function (Blueprint $table) {
                $table->dropColumn('id_campus');
            });
        }

        try {
            Schema::table('bourses', function (Blueprint $table) {
                $table->unique(array('id_eleve', 'annee_scolaire'), 'unique__id_eleve__annee_scolaire');
            });
        } catch (\Exception $e) {
            //
        }
    }
}
