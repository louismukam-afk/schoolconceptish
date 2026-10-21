<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMouvementsFinanciersTable extends Migration {
    public function up()
    {
        Schema::create('mouvements_financiers', function(Blueprint $table) {
            $table->increments('id');
            $table->string('type_operation', 30);
            $table->string('source', 20)->nullable();
            $table->integer('id_caisse')->nullable();
            $table->integer('id_banque')->nullable();
            $table->integer('id_caisse_source')->nullable();
            $table->integer('id_caisse_destination')->nullable();
            $table->decimal('montant', 15, 2);
            $table->date('date_operation');
            $table->string('motif')->nullable();
            $table->string('numero_versement')->nullable();
            $table->string('reference')->nullable();
            $table->text('demande')->nullable();
            $table->integer('id_payement')->nullable();
            $table->integer('id_inscription')->nullable();
            $table->integer('id_user')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mouvements_financiers');
    }
}
