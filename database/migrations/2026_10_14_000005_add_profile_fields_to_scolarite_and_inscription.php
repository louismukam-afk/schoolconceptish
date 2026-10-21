<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProfileFieldsToScolariteAndInscription extends Migration
{
    public function up()
    {
        if (Schema::hasTable('scolarite')) {
            Schema::table('scolarite', function (Blueprint $table) {
                if (!Schema::hasColumn('scolarite', 'libelle')) {
                    $table->string('libelle')->nullable();
                }
                if (!Schema::hasColumn('scolarite', 'annee_scolaire')) {
                    $table->string('annee_scolaire', 20)->nullable()->index();
                }
                if (!Schema::hasColumn('scolarite', 'id_classe')) {
                    $table->integer('id_classe')->nullable()->index();
                }
                if (!Schema::hasColumn('scolarite', 'id_nniveau')) {
                    $table->integer('id_nniveau')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('inscription') && !Schema::hasColumn('inscription', 'id_scolarite')) {
            Schema::table('inscription', function (Blueprint $table) {
                $table->integer('id_scolarite')->nullable()->index();
            });
        }

        $this->backfillInscriptionScolarite();
    }

    private function backfillInscriptionScolarite()
    {
        if (!Schema::hasTable('inscription') || !Schema::hasTable('scolarite') || !Schema::hasTable('classe')) {
            return;
        }

        $inscriptions = DB::table('inscription')
            ->whereNull('id_scolarite')
            ->select('id', 'id_cycle', 'id_classe', 'type_scolarite')
            ->orderBy('id')
            ->get();

        foreach ($inscriptions as $inscription) {
            $classe = DB::table('classe')->where('id', '=', $inscription->id_classe)->first();
            if (!$classe) {
                continue;
            }

            $scolarite = DB::table('scolarite')
                ->where('id_cycle', '=', $inscription->id_cycle)
                ->where('id_niveau', '=', $classe->id_niveau)
                ->where('type_scolarite', '=', $inscription->type_scolarite)
                ->orderBy('id', 'desc')
                ->first();

            if ($scolarite) {
                DB::table('inscription')
                    ->where('id', '=', $inscription->id)
                    ->update(array('id_scolarite' => $scolarite->id));
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('inscription') && Schema::hasColumn('inscription', 'id_scolarite')) {
            Schema::table('inscription', function (Blueprint $table) {
                $table->dropColumn('id_scolarite');
            });
        }

        if (Schema::hasTable('scolarite')) {
            Schema::table('scolarite', function (Blueprint $table) {
                if (Schema::hasColumn('scolarite', 'libelle')) {
                    $table->dropColumn('libelle');
                }
                if (Schema::hasColumn('scolarite', 'annee_scolaire')) {
                    $table->dropColumn('annee_scolaire');
                }
                if (Schema::hasColumn('scolarite', 'id_classe')) {
                    $table->dropColumn('id_classe');
                }
                if (Schema::hasColumn('scolarite', 'id_nniveau')) {
                    $table->dropColumn('id_nniveau');
                }
            });
        }
    }
}
