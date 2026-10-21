<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddLogoFaculteToTutellesAcademiques extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tutelles_academiques') && !Schema::hasColumn('tutelles_academiques', 'logo_faculte')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                $table->string('logo_faculte')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tutelles_academiques') && Schema::hasColumn('tutelles_academiques', 'logo_faculte')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                $table->dropColumn('logo_faculte');
            });
        }
    }
}
