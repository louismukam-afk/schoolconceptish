<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuditFieldsToNewTables extends Migration {
    public function up()
    {
        Schema::table('campus', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('banques', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('caisses', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('tutelles_academiques', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('campus_user', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('campus_specialite', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('specialite_tutelle', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
        Schema::table('mouvements_financiers', function(Blueprint $table) {
            $table->integer('id_creator')->nullable();
            $table->integer('id_last_editor')->nullable();
        });
    }

    public function down()
    {
        Schema::table('mouvements_financiers', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('specialite_tutelle', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('campus_specialite', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('campus_user', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('tutelles_academiques', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('caisses', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('banques', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
        Schema::table('campus', function(Blueprint $table) {
            $table->dropColumn(array('id_creator', 'id_last_editor'));
        });
    }
}
