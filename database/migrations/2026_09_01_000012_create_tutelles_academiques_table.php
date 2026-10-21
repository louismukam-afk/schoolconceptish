<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTutellesAcademiquesTable extends Migration {
    public function up()
    {
        Schema::create('tutelles_academiques', function(Blueprint $table) {
            $table->increments('id');
            $table->string('nom');
            $table->string('ville')->nullable();
            $table->string('localisation')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->text('description1')->nullable();
            $table->text('description2')->nullable();
            $table->string('ministere_tutelle')->nullable();
            $table->string('faculte')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_faculte')->nullable();
            $table->string('republique_francophone')->nullable();
            $table->string('republique_anglophone')->nullable();
            $table->string('patrie_francophone')->nullable();
            $table->string('patrie_anglophone')->nullable();
            $table->string('ministere_tutelle_anglophone')->nullable();
            $table->string('faculte_anglophone')->nullable();
            $table->string('tutelle_anglophone')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tutelles_academiques');
    }
}
