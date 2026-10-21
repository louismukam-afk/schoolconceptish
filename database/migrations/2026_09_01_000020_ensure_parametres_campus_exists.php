<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use gesecole\Campus;

class EnsureParametresCampusExists extends Migration {
    public function up()
    {
        if (!Schema::hasTable('campus') || !Schema::hasTable('parametres')) {
            return;
        }

        $params = DB::table('parametres')->lists('valeur', 'nom');
        $nom = isset($params['nom_etablissement']) && $params['nom_etablissement'] ? $params['nom_etablissement'] : 'Campus principal';

        $exists = DB::table('campus')->where('nom', '=', $nom)->first();
        if ($exists) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $campus = Campus::attributesFromParametres($params);
        $campus['created_at'] = $now;
        $campus['updated_at'] = $now;

        foreach (array('ville', 'numero_agrement', 'reference', 'date_creation', 'logo', 'autorisation_ouverture', 'arrete_creation', 'boite_postale', 'id_creator', 'id_last_editor') as $field) {
            if (Schema::hasColumn('campus', $field)) {
                $campus[$field] = null;
            }
        }

        DB::table('campus')->insert($campus);
    }

    public function down()
    {
        //
    }
}
