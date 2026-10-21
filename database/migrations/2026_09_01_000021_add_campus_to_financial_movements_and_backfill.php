<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCampusToFinancialMovementsAndBackfill extends Migration {
    public function up()
    {
        if (Schema::hasTable('mouvements_financiers') && !Schema::hasColumn('mouvements_financiers', 'id_campus')) {
            Schema::table('mouvements_financiers', function(Blueprint $table) {
                $table->integer('id_campus')->nullable();
            });
        }

        if (!Schema::hasTable('campus')) {
            return;
        }

        $campus = DB::table('campus')->orderBy('id')->first();
        if (!$campus) {
            return;
        }

        if (Schema::hasTable('inscription') && Schema::hasColumn('inscription', 'id_campus')) {
            DB::table('inscription')->whereNull('id_campus')->update(array('id_campus' => $campus->id));
        }

        if (Schema::hasTable('payement') && Schema::hasColumn('payement', 'id_campus')) {
            DB::table('payement')->whereNull('id_campus')->update(array('id_campus' => $campus->id));
        }

        if (Schema::hasTable('mouvements_financiers') && Schema::hasColumn('mouvements_financiers', 'id_campus')) {
            $mouvements = DB::table('mouvements_financiers')->whereNull('id_campus')->get();
            foreach ($mouvements as $mouvement) {
                $idCampus = null;
                if ($mouvement->id_payement) {
                    $payement = DB::table('payement')->where('id', '=', $mouvement->id_payement)->first();
                    if ($payement && $payement->id_campus) {
                        $idCampus = $payement->id_campus;
                    }
                }
                if (!$idCampus && $mouvement->id_inscription) {
                    $inscription = DB::table('inscription')->where('id', '=', $mouvement->id_inscription)->first();
                    if ($inscription && $inscription->id_campus) {
                        $idCampus = $inscription->id_campus;
                    }
                }
                if (!$idCampus) {
                    $idCampus = $campus->id;
                }
                DB::table('mouvements_financiers')->where('id', '=', $mouvement->id)->update(array('id_campus' => $idCampus));
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('mouvements_financiers') && Schema::hasColumn('mouvements_financiers', 'id_campus')) {
            Schema::table('mouvements_financiers', function(Blueprint $table) {
                $table->dropColumn('id_campus');
            });
        }
    }
}
