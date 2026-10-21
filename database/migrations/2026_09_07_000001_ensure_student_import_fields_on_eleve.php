<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnsureStudentImportFieldsOnEleve extends Migration
{
    public function up()
    {
        Schema::table('eleve', function (Blueprint $table) {
            if (!Schema::hasColumn('eleve', 'telephone_wathsapp_eleve')) {
                $table->string('telephone_wathsapp_eleve')->default(0);
            }
            if (!Schema::hasColumn('eleve', 'moyenne_generale')) {
                $table->float('moyenne_generale')->default(0);
            }
            if (!Schema::hasColumn('eleve', 'serie')) {
                $table->string('serie')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'localisation_ecole')) {
                $table->string('localisation_ecole')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'source_financement')) {
                $table->string('source_financement')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'canal_decouvert')) {
                $table->string('canal_decouvert')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'site_internet')) {
                $table->string('site_internet')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'abonnement')) {
                $table->string('abonnement')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'telephone_wathsapp_pere')) {
                $table->string('telephone_wathsapp_pere')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'telephone_wathsapp_mere')) {
                $table->string('telephone_wathsapp_mere')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'telephone_wathsapp_tuteur')) {
                $table->string('telephone_wathsapp_tuteur')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'telephone_wathsapp_autre')) {
                $table->string('telephone_wathsapp_autre')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'abonnement_valide')) {
                $table->string('abonnement_valide')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'chaine')) {
                $table->string('chaine')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'numero_conseille')) {
                $table->string('numero_conseille')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('eleve', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('eleve', function (Blueprint $table) {
            foreach ($this->addedColumns() as $column) {
                if (Schema::hasColumn('eleve', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addedColumns()
    {
        return array(
            'telephone_wathsapp_eleve',
            'moyenne_generale',
            'serie',
            'localisation_ecole',
            'source_financement',
            'canal_decouvert',
            'site_internet',
            'abonnement',
            'telephone_wathsapp_pere',
            'telephone_wathsapp_mere',
            'telephone_wathsapp_tuteur',
            'telephone_wathsapp_autre',
            'abonnement_valide',
            'chaine',
            'numero_conseille',
            'created_at',
            'updated_at',
        );
    }
}
