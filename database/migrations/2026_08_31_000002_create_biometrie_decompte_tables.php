<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateBiometrieDecompteTables extends Migration {

    public function up()
    {
        if(!Schema::hasTable('biometrie_imports')){
            Schema::create('biometrie_imports', function(Blueprint $table)
            {
                $table->increments('id');
                $table->string('libelle')->nullable();
                $table->date('date_debut');
                $table->date('date_fin');
                $table->string('fichier')->nullable();
                $table->string('statut')->default('importe');
                $table->string('type_import')->default('cours');
                $table->integer('total_lignes')->default(0);
                $table->integer('total_consolidees')->default(0);
                $table->integer('total_non_associees')->default(0);
                $table->text('observations')->nullable();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        } elseif(!Schema::hasColumn('biometrie_imports','type_import')){
            Schema::table('biometrie_imports', function(Blueprint $table)
            {
                $table->string('type_import')->default('cours');
            });
        }

        if(!Schema::hasTable('biometrie_pointages')){
            Schema::create('biometrie_pointages', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('id_biometrie_import')->index();
                $table->integer('id_personnel')->nullable()->index();
                $table->string('departement')->nullable();
                $table->string('nom_biometrie')->nullable()->index();
                $table->string('numero_biometrie')->nullable()->index();
                $table->dateTime('date_heure_pointage')->index();
                $table->string('location_id')->nullable();
                $table->string('id_number')->nullable();
                $table->string('verify_code')->nullable();
                $table->string('card_no')->nullable();
                $table->text('raw_data')->nullable();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('biometrie_personnel_mappings')){
            Schema::create('biometrie_personnel_mappings', function(Blueprint $table)
            {
                $table->increments('id');
                $table->integer('id_personnel')->index();
                $table->string('nom_biometrie')->nullable()->index();
                $table->string('numero_biometrie')->nullable()->index();
                $table->integer('id_user')->default(0);
                $table->timestamps();
            });
        }

        if(Schema::hasTable('heures_realisees_enseignants') && !Schema::hasColumn('heures_realisees_enseignants','id_biometrie_import')){
            Schema::table('heures_realisees_enseignants', function(Blueprint $table)
            {
                $table->integer('id_biometrie_import')->nullable()->index();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('biometrie_personnel_mappings');
        Schema::dropIfExists('biometrie_pointages');
        Schema::dropIfExists('biometrie_imports');
    }
}
