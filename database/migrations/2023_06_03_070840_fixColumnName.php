<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('donnee_ligne_budgetaires', function(Blueprint $table)
        {
            $table->renameColumn('montant_total', 'montant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('donnee_ligne_budgetaires', function(Blueprint $table)
        {
            $table->renameColumn('montant', 'montant_total');
        });
    }
}
