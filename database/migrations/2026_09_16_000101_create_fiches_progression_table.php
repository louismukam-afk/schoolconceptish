<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFichesProgressionTable extends Migration {

    public function up()
    {
        if (!Schema::hasTable('fiches_progression')) {
            Schema::create('fiches_progression', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_personnel')->index();
                $table->integer('id_cours_enseignant')->index();
                $table->string('annee_scolaire')->index();
                $table->integer('id_campus')->nullable()->index();
                $table->string('titre')->nullable();
                $table->text('description')->nullable();
                $table->string('fichier_original');
                $table->string('fichier_path');
                $table->string('fichier_extension', 20);
                $table->string('fichier_mime')->nullable();
                $table->integer('id_user')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('fiches_progression');
    }
}
