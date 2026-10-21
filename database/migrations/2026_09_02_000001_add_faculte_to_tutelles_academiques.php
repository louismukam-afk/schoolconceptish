<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddFaculteToTutellesAcademiques extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tutelles_academiques') && !Schema::hasColumn('tutelles_academiques', 'faculte')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                $table->string('faculte')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tutelles_academiques') && Schema::hasColumn('tutelles_academiques', 'faculte')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                $table->dropColumn('faculte');
            });
        }
    }
}
