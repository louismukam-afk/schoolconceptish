<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddCaisseToDecaissement extends Migration {
    public function up()
    {
        if (Schema::hasTable('decaissement') && !Schema::hasColumn('decaissement', 'id_caisse')) {
            Schema::table('decaissement', function(Blueprint $table) {
                $table->integer('id_caisse')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('decaissement') && Schema::hasColumn('decaissement', 'id_caisse')) {
            Schema::table('decaissement', function(Blueprint $table) {
                $table->dropColumn('id_caisse');
            });
        }
    }
}
