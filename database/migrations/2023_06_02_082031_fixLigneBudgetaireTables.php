<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixLigneBudgetaireTables extends Migration
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
            $table->renameColumn('"idLigneBudgetaire"', 'id_ligne_budgetaire');
            $table->renameColumn('"idCategorieBudgetaire"', 'id_categorie_budgetaire');

            $table->foreign('id_ligne_budgetaire')->references('id')->on('ligne_budgetaires')->onDelete('cascade');
            $table->foreign('id_categorie_budgetaire')->references('id')->on('categorie_budgetaires')->onDelete('restrict');
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
            $table->dropForeign(['id_ligne_budgetaire']);
            $table->dropForeign(['id_categorie_budgetaire']);

            $table->renameColumn('id_ligne_budgetaire', '"idLigneBudgetaire"');
            $table->renameColumn('id_categorie_budgetaire', '"idCategorieBudgetaire"');
        });
    }
}
