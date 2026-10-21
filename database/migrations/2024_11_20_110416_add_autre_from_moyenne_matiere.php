<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutreFromMoyenneMatiere extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('moyenne_matiere', function (Blueprint $table) {

            if (!Schema::hasColumn('moyenne_matiere', 'id_cycle')) {
                $table->integer('id_cycle')->default(0);
            }
            if (!Schema::hasColumn('moyenne_matiere', 'id_nniveau')) {
                $table->integer('id_nniveau')->default(0);
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
        Schema::table('moyenne_matiere', function (Blueprint $table) {
            if (Schema::hasColumn('moyenne_matiere', 'id_cycle')) {
                $table->dropColumn('id_cycle');
            }
            if (Schema::hasColumn('moyenne_matiere', 'id_nniveau')) {
                $table->dropColumn('id_nniveau');
            }
        });
    }


}
