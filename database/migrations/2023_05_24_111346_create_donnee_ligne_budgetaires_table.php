<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDonneeLigneBudgetairesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('donnee_ligne_budgetaires', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idLigneBudgetaire')->default(0);
            $table->integer('idCategorieBudgetaire')->default(0);
            $table->double('montant_total')->default(0);
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
        Schema::dropIfExists('donnee_ligne_budgetaires');
    }
}
