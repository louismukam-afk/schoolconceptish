<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBanquesCaissesTable extends Migration {
    public function up()
    {
        Schema::create('banques', function(Blueprint $table) {
            $table->increments('id');
            $table->string('nom');
            $table->string('localisation')->nullable();
            $table->string('numero_compte')->nullable();
            $table->string('ville')->nullable();
            $table->decimal('solde', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('caisses', function(Blueprint $table) {
            $table->increments('id');
            $table->string('nom');
            $table->string('type_caisse', 20)->default('entree');
            $table->text('description')->nullable();
            $table->decimal('solde', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caisses');
        Schema::dropIfExists('banques');
    }
}
