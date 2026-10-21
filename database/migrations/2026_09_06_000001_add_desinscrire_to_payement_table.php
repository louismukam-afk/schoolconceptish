<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddDesinscrireToPayementTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payement') && !Schema::hasColumn('payement', 'desinscrire')) {
            Schema::table('payement', function (Blueprint $table) {
                $table->integer('desinscrire')->default(0);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('payement') && Schema::hasColumn('payement', 'desinscrire')) {
            Schema::table('payement', function (Blueprint $table) {
                $table->dropColumn('desinscrire');
            });
        }
    }
}
