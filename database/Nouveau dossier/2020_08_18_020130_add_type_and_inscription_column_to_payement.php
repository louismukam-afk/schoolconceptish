<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeAndInscriptionColumnToPayement extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payement', function(Blueprint $table)
		{
			$table->integer('id_inscription')->default(0);
			$table->integer('type')->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('payement', function(Blueprint $table)
		{
			$table->dropColumn('id_inscription');
			$table->dropColumn('type');
		});
	}

}
