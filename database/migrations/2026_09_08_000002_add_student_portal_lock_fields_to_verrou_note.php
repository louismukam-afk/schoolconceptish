<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStudentPortalLockFieldsToVerrouNote extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('verrou_note')) {
            return;
        }

        Schema::table('verrou_note', function (Blueprint $table) {
            if (!Schema::hasColumn('verrou_note', 'id_campus')) {
                $table->integer('id_campus')->nullable()->index();
            }
            if (!Schema::hasColumn('verrou_note', 'target')) {
                $table->string('target')->default('note_sequence')->index();
            }
            if (!Schema::hasColumn('verrou_note', 'is_active')) {
                $table->boolean('is_active')->default(1);
            }
            if (!Schema::hasColumn('verrou_note', 'starts_at')) {
                $table->dateTime('starts_at')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'ends_at')) {
                $table->dateTime('ends_at')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'objectif')) {
                $table->string('objectif')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'message')) {
                $table->text('message')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'id_creator')) {
                $table->integer('id_creator')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'id_last_editor')) {
                $table->integer('id_last_editor')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('verrou_note', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        //
    }
}
