<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePeriodicTimetableTables extends Migration {

    public function up()
    {
        Schema::table('personnel', function(Blueprint $table)
        {
            if(!Schema::hasColumn('personnel','type_personnel')){
                $table->string('type_personnel',30)->default('enseignant');
            }
            if(!Schema::hasColumn('personnel','mode_horaire')){
                $table->string('mode_horaire',30)->default('strict');
            }
            if(!Schema::hasColumn('personnel','categorie_horaire')){
                $table->string('categorie_horaire',50)->default('standard');
            }
            if(!Schema::hasColumn('personnel','horaire_travail')){
                $table->string('horaire_travail',50)->nullable();
            }
        });

        if(!Schema::hasTable('plage_horaires')){
            Schema::create('plage_horaires', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('libelle');
                $table->time('heure_debut');
                $table->time('heure_fin');
                $table->float('duree_payable')->nullable();
                $table->string('type_plage',30)->default('cours');
                $table->string('type_personnel',30)->default('tous');
                $table->string('periode_journee',30)->default('jour');
                $table->string('format_plage',30)->default('mixte');
                $table->integer('ordre')->default(0);
                $table->string('statut',30)->default('actif');
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('cours_enseignants')){
            Schema::create('cours_enseignants', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('id_personnel');
                $table->integer('id_classe');
                $table->integer('id_matiere');
                $table->integer('id_taux')->nullable();
                $table->date('date_debut');
                $table->date('date_fin');
                $table->integer('semestre')->default(1);
                $table->float('volume_horaire_prevu')->default(0);
                $table->string('annee_scolaire',20);
                $table->string('statut',30)->default('actif');
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('seance_cours')){
            Schema::create('seance_cours', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('id_cours_enseignant');
                $table->integer('id_plage_horaire');
                $table->integer('jour_semaine');
                $table->float('duree_heures')->default(0);
                $table->string('statut',30)->default('programme');
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('emploi_permanents')){
            Schema::create('emploi_permanents', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('id_personnel');
                $table->integer('id_plage_horaire');
                $table->integer('jour_semaine');
                $table->date('date_debut');
                $table->date('date_fin')->nullable();
                $table->string('annee_scolaire',20)->nullable();
                $table->string('statut',30)->default('actif');
                $table->text('observations')->nullable();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('heures_realisees_enseignants')){
            Schema::create('heures_realisees_enseignants', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('id_cours_enseignant');
                $table->integer('id_seance_cours');
                $table->integer('id_personnel');
                $table->integer('id_classe');
                $table->integer('id_matiere');
                $table->date('date_seance');
                $table->integer('jour_semaine');
                $table->integer('id_plage_horaire');
                $table->time('heure_debut_prevue');
                $table->time('heure_fin_prevue');
                $table->time('heure_debut_reelle')->nullable();
                $table->time('heure_fin_reelle')->nullable();
                $table->float('duree_prevue')->default(0);
                $table->float('duree_realisee')->default(0);
                $table->float('montant_taux')->default(0);
                $table->float('montant_total')->default(0);
                $table->string('annee_scolaire',20);
                $table->string('statut',30)->default('realise');
                $table->text('observation')->nullable();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('heures_realisees_enseignants');
        Schema::dropIfExists('emploi_permanents');
        Schema::dropIfExists('seance_cours');
        Schema::dropIfExists('cours_enseignants');
        Schema::dropIfExists('plage_horaires');
    }
}
