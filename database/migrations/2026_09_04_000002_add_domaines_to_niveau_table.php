<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddDomainesToNiveauTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('niveau') && !Schema::hasColumn('niveau', 'domaines')) {
            Schema::table('niveau', function (Blueprint $table) {
                $table->string('domaines')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('niveau') && Schema::hasColumn('niveau', 'domaines')) {
            Schema::table('niveau', function (Blueprint $table) {
                $table->dropColumn('domaines');
            });
        }
    }
}
