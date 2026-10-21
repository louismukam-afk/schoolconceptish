<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIdLigneAndIdCategorie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('decaissement', function(Blueprint $table)
        {

            $table->integer('id_ligne_budgetaire')->default(0);
            $table->integer('id_categorie_budgetaire')->default(0);
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('decaissement', function(Blueprint $table)
        {

            $table->dropColumn('id_ligne_budgetaire');
            $table->dropColumn('id_categorie_budgetaire');

        });
    }
}
