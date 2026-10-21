<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLetterAmountToPayment extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('payement', function(Blueprint $table)
		{
			if (!Schema::hasColumn('payement', 'montant_lettre')) {
				$table->string('montant_lettre')->nullable();
			}
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
			if (Schema::hasColumn('payement', 'montant_lettre')) {
				$table->dropColumn('montant_lettre');
			}
		});
	}

}
