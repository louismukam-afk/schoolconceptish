<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreLegacyCampusNameFromParametres extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('campus') || !Schema::hasTable('parametres')) {
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

        if (($hasLegacyInscriptions + $hasLegacyPayements) <= 0) {
            return;
        }

        $params = DB::table('parametres')->lists('valeur', 'nom');
        $data = array(
            'nom' => isset($params['nom_etablissement']) && $params['nom_etablissement'] ? $params['nom_etablissement'] : $legacyCampus->nom,
            'sigle' => isset($params['initiales_etablissement']) && $params['initiales_etablissement'] ? $params['initiales_etablissement'] : $legacyCampus->sigle,
            'localisation' => isset($params['adresse']) && $params['adresse'] ? $params['adresse'] : $legacyCampus->localisation,
            'telephone' => isset($params['telephone']) && $params['telephone'] ? $params['telephone'] : $legacyCampus->telephone,
            'email' => isset($params['email']) && $params['email'] ? $params['email'] : $legacyCampus->email,
            'site_web' => isset($params['web']) && $params['web'] ? $params['web'] : $legacyCampus->site_web,
            'updated_at' => date('Y-m-d H:i:s')
        );

        DB::table('campus')->where('id', '=', $legacyCampus->id)->update($data);
    }

    public function down()
    {
        //
    }
}
