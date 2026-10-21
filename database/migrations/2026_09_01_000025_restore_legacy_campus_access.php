<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreLegacyCampusAccess extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('campus')) {
            return;
        }

        $legacyCampus = DB::table('campus')->orderBy('id')->first();
        if (!$legacyCampus) {
            return;
        }

        $hasLegacyInscriptions = Schema::hasTable('inscription') && Schema::hasColumn('inscription', 'id_campus')
            ? DB::table('inscription')->where('id_campus', '=', $legacyCampus->id)->count()
            : 0;
        $hasLegacyPayements = Schema::hasTable('payement') && Schema::hasColumn('payement', 'id_campus')
            ? DB::table('payement')->where('id_campus', '=', $legacyCampus->id)->count()
            : 0;

        if (($hasLegacyInscriptions + $hasLegacyPayements) > 0 && $legacyCampus->nom == 'Campus principal') {
            DB::table('campus')->where('id', '=', $legacyCampus->id)->update(array(
                'nom' => 'Campus historique',
                'sigle' => $legacyCampus->sigle ? $legacyCampus->sigle : 'HIST',
                'localisation' => $legacyCampus->localisation ? $legacyCampus->localisation : 'Donnees anterieures',
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }

        $this->attachUsersToCampus($legacyCampus->id);
    }

    private function attachUsersToCampus($idCampus)
    {
        if (!Schema::hasTable('users') || !Schema::hasTable('campus_user')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $users = DB::table('users')->select('id')->get();
        foreach ($users as $user) {
            $exists = DB::table('campus_user')
                ->where('id_campus', '=', $idCampus)
                ->where('id_user', '=', $user->id)
                ->first();
            if (!$exists) {
                $data = array(
                    'id_campus' => $idCampus,
                    'id_user' => $user->id,
                    'created_at' => $now,
                    'updated_at' => $now
                );
                foreach (array('id_creator', 'id_last_editor') as $field) {
                    if (Schema::hasColumn('campus_user', $field)) {
                        $data[$field] = $user->id;
                    }
                }
                DB::table('campus_user')->insert($data);
            }
        }
    }

    public function down()
    {
        //
    }
}
