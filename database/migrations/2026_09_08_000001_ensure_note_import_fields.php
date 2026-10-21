<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnsureNoteImportFields extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('note')) {
            return;
        }

        Schema::table('note', function (Blueprint $table) {
            if (!Schema::hasColumn('note', 'id_campus')) {
                $table->integer('id_campus')->nullable()->index();
            }
            if (!Schema::hasColumn('note', 'id_classe')) {
                $table->integer('id_classe')->nullable()->index();
            }
            if (!Schema::hasColumn('note', 'id_classe_matiere')) {
                $table->integer('id_classe_matiere')->nullable()->index();
            }
            if (!Schema::hasColumn('note', 'id_session')) {
                $table->integer('id_session')->nullable()->index();
            }
            if (!Schema::hasColumn('note', 'id_creator')) {
                $table->integer('id_creator')->nullable();
            }
            if (!Schema::hasColumn('note', 'id_last_editor')) {
                $table->integer('id_last_editor')->nullable();
            }
            if (!Schema::hasColumn('note', 'edition_details')) {
                $table->text('edition_details')->nullable();
            }
            if (!Schema::hasColumn('note', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('note', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        //
    }
}
