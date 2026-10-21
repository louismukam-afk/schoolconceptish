<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssignDefaultCampusTo20262027NullInscriptions extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('campus') || !Schema::hasTable('inscription')) {
            return;
        }

        if (!Schema::hasColumn('inscription', 'id_campus') || !Schema::hasColumn('inscription', 'annee_scolaire')) {
            return;
        }

        $campus = DB::table('campus')->orderBy('id')->first();
        if (!$campus) {
            return;
        }

        DB::table('inscription')
            ->whereIn('annee_scolaire', array('2026-2027', '2026 - 2027'))
            ->where(function ($query) {
                $query->whereNull('id_campus')
                    ->orWhere('id_campus', '=', 0);
            })
            ->update(array('id_campus' => $campus->id));
    }

    public function down()
    {
        //
    }
}
