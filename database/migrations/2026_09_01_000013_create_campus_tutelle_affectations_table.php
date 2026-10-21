<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampusTutelleAffectationsTable extends Migration {
    public function up()
    {
        Schema::create('campus_user', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('id_campus')->unsigned();
            $table->integer('id_user')->unsigned();
            $table->timestamps();
            $table->unique(array('id_campus', 'id_user'));
        });

        Schema::create('campus_specialite', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('id_campus')->unsigned();
            $table->integer('id_classe')->unsigned();
            $table->timestamps();
            $table->unique(array('id_campus', 'id_classe'));
        });

        Schema::create('specialite_tutelle', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('id_tutelle')->unsigned();
            $table->integer('id_classe')->unsigned();
            $table->timestamps();
            $table->unique(array('id_tutelle', 'id_classe'));
        });
    }

    public function down()
    {
        Schema::dropIfExists('specialite_tutelle');
        Schema::dropIfExists('campus_specialite');
        Schema::dropIfExists('campus_user');
    }
}
