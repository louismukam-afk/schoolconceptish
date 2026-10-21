<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsExamLegacyTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		if (Schema::hasTable('sessions_exam')) {
			return;
		}

		Schema::create('sessions_exam', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('cc1')->default(0);
            $table->integer('tpe1')->default(0);
            $table->integer('sn1')->default(0);
            $table->integer('ratrappage1')->default(0);
            $table->integer('cc2')->default(0);
            $table->integer('tpe2')->default(0);
            $table->integer('sn2')->default(0);
            $table->integer('ratrappage2')->default(0);
            $table->integer('id_classe_matiere')->default(0);
            $table->integer('id_matiere')->default(0);
            $table->string('annee_academique');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		if (Schema::hasTable('sessions_exam')) {
			Schema::drop('sessions_exam');
		}
	}

}
