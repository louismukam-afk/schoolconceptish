<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AttachExistingSpecialitesToDefaultCampus extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('campus') || !Schema::hasTable('classe') || !Schema::hasTable('campus_specialite')) {
            return;
        }

        $campus = DB::table('campus')->orderBy('id')->first();
        if (!$campus) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $classes = DB::table('classe')->select('id')->orderBy('id')->get();

        foreach ($classes as $classe) {
            $exists = DB::table('campus_specialite')
                ->where('id_campus', '=', $campus->id)
                ->where('id_classe', '=', $classe->id)
                ->first();

            if ($exists) {
                continue;
            }

            $data = array(
                'id_campus' => $campus->id,
                'id_classe' => $classe->id
            );

            if (Schema::hasColumn('campus_specialite', 'created_at')) {
                $data['created_at'] = $now;
            }

            if (Schema::hasColumn('campus_specialite', 'updated_at')) {
                $data['updated_at'] = $now;
            }

            if (Schema::hasColumn('campus_specialite', 'id_creator')) {
                $data['id_creator'] = 0;
            }

            if (Schema::hasColumn('campus_specialite', 'id_last_editor')) {
                $data['id_last_editor'] = 0;
            }

            DB::table('campus_specialite')->insert($data);
        }
    }

    public function down()
    {
        //
    }
}
