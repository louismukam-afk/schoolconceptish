<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class add_autre_from_moyenne_eleve extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('moyenne_eleve', function (Blueprint $table) {

            $table->integer('id_cycle');
            $table->integer('id_nniveau');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moyenne_eleve');
    }

}
