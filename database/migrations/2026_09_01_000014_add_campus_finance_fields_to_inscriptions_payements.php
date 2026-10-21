<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCampusFinanceFieldsToInscriptionsPayements extends Migration {
    public function up()
    {
        Schema::table('inscription', function(Blueprint $table) {
            $table->integer('id_campus')->nullable();
            $table->string('operation_source', 20)->nullable();
            $table->integer('id_caisse')->nullable();
            $table->integer('id_banque')->nullable();
            $table->string('numero_versement')->nullable();
            $table->string('reference_operation')->nullable();
            $table->text('demande_operation')->nullable();
        });

        Schema::table('payement', function(Blueprint $table) {
            $table->integer('id_campus')->nullable();
            $table->string('operation_source', 20)->nullable();
            $table->integer('id_caisse')->nullable();
            $table->integer('id_banque')->nullable();
            $table->string('numero_versement')->nullable();
            $table->string('reference_operation')->nullable();
            $table->text('demande_operation')->nullable();
        });
    }

    public function down()
    {
        Schema::table('inscription', function(Blueprint $table) {
            $table->dropColumn(array('id_campus', 'operation_source', 'id_caisse', 'id_banque', 'numero_versement', 'reference_operation', 'demande_operation'));
        });

        Schema::table('payement', function(Blueprint $table) {
            $table->dropColumn(array('id_campus', 'operation_source', 'id_caisse', 'id_banque', 'numero_versement', 'reference_operation', 'demande_operation'));
        });
    }
}
