<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Discipline1 extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (Schema::hasTable('discipline')) {
            return;
        }

		Schema::create('discipline', function(Blueprint $table)
		{
            $table->increments('id');
            $table->integer('id_eleve')->default(0);
            $table->integer('id_personnel')->default(0);
            $table->string('annee_scolaire',9);
            $table->integer('sequence')->default(0);
            $table->integer('type');
            $table->integer('nb_heures')->default(0);
            $table->integer('just')->default(0);
            $table->integer('retard')->default(0);
            $table->integer('nb_jours')->default(0);
            $table->integer('exclus_def')->default(0);
            $table->string('description')->nullable();
            $table->date('ddate');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('discipline', function(Blueprint $table)
		{
			//
		});
	}

}
