<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnsureInscriptionImportFields extends Migration
{
    public function up()
    {
        if (Schema::hasTable('inscription')) {
            Schema::table('inscription', function (Blueprint $table) {
                $this->ensureInscriptionColumns($table);
            });
        }

        if (Schema::hasTable('payement')) {
            Schema::table('payement', function (Blueprint $table) {
                $this->ensurePayementColumns($table);
            });
        }
    }

    public function down()
    {
        //
    }

    private function ensureInscriptionColumns(Blueprint $table)
    {
        if (!Schema::hasColumn('inscription', 'date_inscription')) {
            $table->date('date_inscription')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'id_eleve')) {
            $table->integer('id_eleve')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'annee_scolaire')) {
            $table->string('annee_scolaire', 9)->nullable();
        }
        if (!Schema::hasColumn('inscription', 'id_classe')) {
            $table->integer('id_classe')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'id_cycle')) {
            $table->integer('id_cycle')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'id_nniveau')) {
            $table->integer('id_nniveau')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'type_scolarite')) {
            $table->integer('type_scolarite')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'montant')) {
            $table->float('montant')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'lettre')) {
            $table->string('lettre')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'mode_paye')) {
            $table->string('mode_paye')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'ref_versement')) {
            $table->string('ref_versement')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'desinscrire')) {
            $table->integer('desinscrire')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'id_campus')) {
            $table->integer('id_campus')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'operation_source')) {
            $table->string('operation_source', 20)->nullable();
        }
        if (!Schema::hasColumn('inscription', 'id_caisse')) {
            $table->integer('id_caisse')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'id_banque')) {
            $table->integer('id_banque')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'numero_versement')) {
            $table->string('numero_versement')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'reference_operation')) {
            $table->string('reference_operation')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'demande_operation')) {
            $table->text('demande_operation')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'id_creator')) {
            $table->integer('id_creator')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'id_last_editor')) {
            $table->integer('id_last_editor')->default(0);
        }
        if (!Schema::hasColumn('inscription', 'edition_details')) {
            $table->string('edition_details', 512)->nullable();
        }
        if (!Schema::hasColumn('inscription', 'created_at')) {
            $table->timestamp('created_at')->nullable();
        }
        if (!Schema::hasColumn('inscription', 'updated_at')) {
            $table->timestamp('updated_at')->nullable();
        }
    }

    private function ensurePayementColumns(Blueprint $table)
    {
        if (!Schema::hasColumn('payement', 'id_eleve')) {
            $table->integer('id_eleve')->default(0);
        }
        if (!Schema::hasColumn('payement', 'annee_scolaire')) {
            $table->string('annee_scolaire')->nullable();
        }
        if (!Schema::hasColumn('payement', 'date_payement')) {
            $table->date('date_payement')->nullable();
        }
        if (!Schema::hasColumn('payement', 'montant')) {
            $table->float('montant')->default(0);
        }
        if (!Schema::hasColumn('payement', 'motif')) {
            $table->string('motif', 1000)->nullable();
        }
        if (!Schema::hasColumn('payement', 'numero')) {
            $table->string('numero', 20)->nullable();
        }
        if (!Schema::hasColumn('payement', 'lettre')) {
            $table->string('lettre')->nullable();
        }
        if (!Schema::hasColumn('payement', 'mode_paye')) {
            $table->string('mode_paye')->nullable();
        }
        if (!Schema::hasColumn('payement', 'ref_versement')) {
            $table->string('ref_versement')->nullable();
        }
        if (!Schema::hasColumn('payement', 'type')) {
            $table->integer('type')->default(0);
        }
        if (!Schema::hasColumn('payement', 'id_inscription')) {
            $table->integer('id_inscription')->default(0);
        }
        if (!Schema::hasColumn('payement', 'desinscrire')) {
            $table->integer('desinscrire')->default(0);
        }
        if (!Schema::hasColumn('payement', 'id_campus')) {
            $table->integer('id_campus')->nullable();
        }
        if (!Schema::hasColumn('payement', 'operation_source')) {
            $table->string('operation_source', 20)->nullable();
        }
        if (!Schema::hasColumn('payement', 'id_caisse')) {
            $table->integer('id_caisse')->nullable();
        }
        if (!Schema::hasColumn('payement', 'id_banque')) {
            $table->integer('id_banque')->nullable();
        }
        if (!Schema::hasColumn('payement', 'numero_versement')) {
            $table->string('numero_versement')->nullable();
        }
        if (!Schema::hasColumn('payement', 'reference_operation')) {
            $table->string('reference_operation')->nullable();
        }
        if (!Schema::hasColumn('payement', 'demande_operation')) {
            $table->text('demande_operation')->nullable();
        }
        if (!Schema::hasColumn('payement', 'id_creator')) {
            $table->integer('id_creator')->default(0);
        }
        if (!Schema::hasColumn('payement', 'id_last_editor')) {
            $table->integer('id_last_editor')->default(0);
        }
        if (!Schema::hasColumn('payement', 'edition_details')) {
            $table->string('edition_details', 512)->nullable();
        }
        if (!Schema::hasColumn('payement', 'created_at')) {
            $table->timestamp('created_at')->nullable();
        }
        if (!Schema::hasColumn('payement', 'updated_at')) {
            $table->timestamp('updated_at')->nullable();
        }
    }
}
