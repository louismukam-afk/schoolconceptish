<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampusTable extends Migration {
    public function up()
    {
        Schema::create('campus', function(Blueprint $table) {
            $table->increments('id');
            $table->string('nom');
            $table->string('sigle')->nullable();
            $table->string('localisation')->nullable();
            $table->string('ville')->nullable();
            $table->string('numero_agrement')->nullable();
            $table->string('reference')->nullable();
            $table->date('date_creation')->nullable();
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('site_web')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campus');
    }
}
