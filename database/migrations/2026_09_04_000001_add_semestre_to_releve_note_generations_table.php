<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSemestreToReleveNoteGenerationsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('releve_note_generations', 'semestre')) {
            Schema::table('releve_note_generations', function (Blueprint $table) {
                $table->integer('semestre')->nullable()->after('periode_fin');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('releve_note_generations', 'semestre')) {
            Schema::table('releve_note_generations', function (Blueprint $table) {
                $table->dropColumn('semestre');
            });
        }
    }
}
