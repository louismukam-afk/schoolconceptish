<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePvGenerationsTable extends Migration
{
    public function up()
    {
        Schema::create('pv_generations', function (Blueprint $table) {

            $table->increments('id');

            // Contexte du PV
            $table->integer('id_classe')->unsigned();
            $table->integer('id_cycle')->unsigned();
            $table->integer('id_nniveau')->unsigned();

            // AnnÃƒÂ©e acadÃƒÂ©mique
            $table->string('annee_academique', 50);

            // 1, 2 ou annuel
            $table->string('semestre', 30);

            /*
             * Type de PV :
             * bts
             * hnd
             * etc.
             */
            $table->string('type_pv', 50);

            /*
             * PÃƒÂ©riode couverte par le PV
             */
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            /*
             * Date rÃƒÂ©elle de gÃƒÂ©nÃƒÂ©ration
             */
            $table->dateTime('date_generation');

            /*
             * Nombre d'ÃƒÂ©tudiants prÃƒÂ©sents
             * au moment de la gÃƒÂ©nÃƒÂ©ration
             */
            $table->integer('nombre_etudiants')->default(0);

            /*
             * Nom du fichier gÃƒÂ©nÃƒÂ©rÃƒÂ©
             */
            $table->string('nom_fichier', 255)->nullable();

            /*
             * Utilisateur ayant gÃƒÂ©nÃƒÂ©rÃƒÂ© le PV
             */
            $table->integer('id_user')->unsigned()->nullable();

            /*
             * Etat du PV :
             * brouillon
             * genere
             * valide
             * archive
             */
            $table->string('statut', 30)->default('genere');

            $table->timestamps();

            /*
             * Index utiles pour les recherches
             */
            $table->index('id_classe');
            $table->index('id_cycle');
            $table->index('id_nniveau');
            $table->index('annee_academique');
            $table->index('semestre');
            $table->index('type_pv');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pv_generations');
    }
}
