<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddRepublicAndPatrieFieldsToTutellesAcademiques extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tutelles_academiques')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                if (!Schema::hasColumn('tutelles_academiques', 'republique_francophone')) {
                    $table->string('republique_francophone')->nullable();
                }
                if (!Schema::hasColumn('tutelles_academiques', 'republique_anglophone')) {
                    $table->string('republique_anglophone')->nullable();
                }
                if (!Schema::hasColumn('tutelles_academiques', 'patrie_francophone')) {
                    $table->string('patrie_francophone')->nullable();
                }
                if (!Schema::hasColumn('tutelles_academiques', 'patrie_anglophone')) {
                    $table->string('patrie_anglophone')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tutelles_academiques')) {
            Schema::table('tutelles_academiques', function(Blueprint $table) {
                foreach (array('republique_francophone', 'republique_anglophone', 'patrie_francophone', 'patrie_anglophone') as $field) {
                    if (Schema::hasColumn('tutelles_academiques', $field)) {
                        $table->dropColumn($field);
                    }
                }
            });
        }
    }
}
