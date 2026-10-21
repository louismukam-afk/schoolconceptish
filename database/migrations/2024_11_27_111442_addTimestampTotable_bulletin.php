<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimestampTotableBulletin extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('moyenne_classe', function(Blueprint $table)
        {
            if (!Schema::hasColumn('moyenne_classe', 'created_at')) {
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
            if (!Schema::hasColumn('moyenne_classe', 'updated_at')) {
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
        });
        Schema::table('moyenne_eleve', function(Blueprint $table)
        {
            if (!Schema::hasColumn('moyenne_eleve', 'created_at')) {
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
            if (!Schema::hasColumn('moyenne_eleve', 'updated_at')) {
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
        });
        Schema::table('moyenne_matiere', function(Blueprint $table)
        {
            if (!Schema::hasColumn('moyenne_matiere', 'created_at')) {
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
            if (!Schema::hasColumn('moyenne_matiere', 'updated_at')) {
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
        });
        Schema::table('bulletin_genere', function(Blueprint $table)
        {
            if (!Schema::hasColumn('bulletin_genere', 'created_at')) {
                $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            }
            if (!Schema::hasColumn('bulletin_genere', 'updated_at')) {
                $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));
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
        Schema::table('bulletin_genere', function(Blueprint $table)
        {
            if (Schema::hasColumn('bulletin_genere', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('bulletin_genere', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
        Schema::table('moyenne_classe', function(Blueprint $table)
        {
            if (Schema::hasColumn('moyenne_classe', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('moyenne_classe', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
        Schema::table('moyenne_eleve', function(Blueprint $table)
        {
            if (Schema::hasColumn('moyenne_eleve', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('moyenne_eleve', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
        Schema::table('moyenne_matiere', function(Blueprint $table)
        {
            if (Schema::hasColumn('moyenne_matiere', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('moyenne_matiere', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
	}

}
