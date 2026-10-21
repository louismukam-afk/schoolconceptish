<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddEnglishNamesToTutellesAcademiques extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tutelles_academiques')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                if (!Schema::hasColumn('tutelles_academiques', 'ministere_tutelle_anglophone')) {
                    $table->string('ministere_tutelle_anglophone')->nullable();
                }
                if (!Schema::hasColumn('tutelles_academiques', 'faculte_anglophone')) {
                    $table->string('faculte_anglophone')->nullable();
                }
                if (!Schema::hasColumn('tutelles_academiques', 'tutelle_anglophone')) {
                    $table->string('tutelle_anglophone')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tutelles_academiques')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                foreach (array('ministere_tutelle_anglophone', 'faculte_anglophone', 'tutelle_anglophone') as $field) {
                    if (Schema::hasColumn('tutelles_academiques', $field)) {
                        $table->dropColumn($field);
                    }
                }
            });
        }
    }
}
