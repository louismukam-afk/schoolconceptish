<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedDefaultCampusCaisses extends Migration {
    public function up()
    {
        if (DB::table('campus')->count() == 0) {
            DB::table('campus')->insert(array(
                'nom' => 'Campus principal',
                'sigle' => 'CP',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }

        if (DB::table('caisses')->where('type_caisse', '=', 'entree')->count() == 0) {
            DB::table('caisses')->insert(array(
                'nom' => 'Caisse principale entree',
                'type_caisse' => 'entree',
                'solde' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }

        if (DB::table('caisses')->where('type_caisse', '=', 'sortie')->count() == 0) {
            DB::table('caisses')->insert(array(
                'nom' => 'Caisse principale sortie',
                'type_caisse' => 'sortie',
                'solde' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ));
        }
    }

    public function down()
    {
        DB::table('caisses')->whereIn('nom', array('Caisse principale entree', 'Caisse principale sortie'))->delete();
        DB::table('campus')->where('nom', '=', 'Campus principal')->delete();
    }
}
