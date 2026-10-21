<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddNomBeneficiaireToDecaissement extends Migration {
    public function up()
    {
        if (Schema::hasTable('decaissement') && !Schema::hasColumn('decaissement', 'nom_beneficiaire')) {
            Schema::table('decaissement', function(Blueprint $table) {
                $table->string('nom_beneficiaire')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('decaissement') && Schema::hasColumn('decaissement', 'nom_beneficiaire')) {
            Schema::table('decaissement', function(Blueprint $table) {
                $table->dropColumn('nom_beneficiaire');
            });
        }
    }
}
