<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEleveForeignKey extends Migration {

    private function foreignKeyExists($table, $constraint)
    {
        return count(DB::select(
            "SELECT 1 FROM information_schema.table_constraints
             WHERE constraint_schema = current_schema()
               AND table_name = ?
               AND constraint_name = ?
               AND constraint_type = 'FOREIGN KEY'",
            array($table, $constraint)
        )) > 0;
    }

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        //Table inscription
        if (!$this->foreignKeyExists('inscription', 'inscription_id_eleve_foreign')) {
            DB::delete('DELETE FROM inscription WHERE id_eleve NOT IN (SELECT id FROM eleve);');
            Schema::table('inscription', function(Blueprint $table) {
                $table->foreign('id_eleve')->references('id')->on('eleve')->onDelete('cascade');
            });
        }

        //Table note
        if (!$this->foreignKeyExists('note', 'note_id_eleve_foreign')) {
            Schema::table('note', function(Blueprint $table) {
                $table->foreign('id_eleve')->references('id')->on('eleve')->onDelete('cascade');
            });
        }

        //Table parent
        if (!$this->foreignKeyExists('parent', 'parent_id_eleve_foreign')) {
            DB::delete('DELETE FROM parent WHERE id_eleve NOT IN (SELECT id FROM eleve);');
            Schema::table('parent', function(Blueprint $table) {
                $table->foreign('id_eleve')->references('id')->on('eleve')->onDelete('cascade');
            });
        }

        //Table payement
        if (!$this->foreignKeyExists('payement', 'payement_id_eleve_foreign')) {
            DB::delete('DELETE FROM payement WHERE id_eleve NOT IN (SELECT id FROM eleve);');
            Schema::table('payement', function(Blueprint $table) {
                $table->foreign('id_eleve')->references('id')->on('eleve')->onDelete('cascade');
            });
        }

        //Table photo
        if (!$this->foreignKeyExists('photos', 'photos_eleve_id_foreign')) {
            DB::delete('DELETE FROM photos WHERE eleve_id NOT IN (SELECT id FROM eleve);');
            Schema::table('photos', function(Blueprint $table) {
                $table->foreign('eleve_id')->references('id')->on('eleve')->onDelete('cascade');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if ($this->foreignKeyExists('inscription', 'inscription_id_eleve_foreign')) {
            Schema::table('inscription', function(Blueprint $table) {
                $table->dropForeign(['id_eleve']);
            });
        }

        if ($this->foreignKeyExists('note', 'note_id_eleve_foreign')) {
            Schema::table('note', function(Blueprint $table) {
                $table->dropForeign(['id_eleve']);
            });
        }

        if ($this->foreignKeyExists('parent', 'parent_id_eleve_foreign')) {
            Schema::table('parent', function(Blueprint $table) {
                $table->dropForeign(['id_eleve']);
            });
        }

        if ($this->foreignKeyExists('payement', 'payement_id_eleve_foreign')) {
            Schema::table('payement', function(Blueprint $table) {
                $table->dropForeign(['id_eleve']);
            });
        }

        if ($this->foreignKeyExists('photos', 'photos_eleve_id_foreign')) {
            Schema::table('photos', function(Blueprint $table) {
                $table->dropForeign(['eleve_id']);
            });
        }
	}

}
