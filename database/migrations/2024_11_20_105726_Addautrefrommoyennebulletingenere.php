<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addautrefrommoyennebulletingenere extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('bulletin_genere', function (Blueprint $table) {

            if (!Schema::hasColumn('bulletin_genere', 'id_cycle')) {
                $table->integer('id_cycle')->default(0);
            }
            if (!Schema::hasColumn('bulletin_genere', 'id_nniveau')) {
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
        Schema::table('bulletin_genere', function (Blueprint $table) {
            if (Schema::hasColumn('bulletin_genere', 'id_cycle')) {
                $table->dropColumn('id_cycle');
            }
            if (Schema::hasColumn('bulletin_genere', 'id_nniveau')) {
                $table->dropColumn('id_nniveau');
            }
        });
    }


}
