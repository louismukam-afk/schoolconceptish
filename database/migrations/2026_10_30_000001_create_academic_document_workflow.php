<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAcademicDocumentWorkflow extends Migration {
    public function up()
    {
        DB::transaction(function() {
        Schema::table('tutelles_academiques', function (Blueprint $table) {
            $table->string('modele_documents', 30)->nullable();
        });
        Schema::create('academic_document_generations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_campus')->unsigned();
            $table->integer('id_tutelle')->unsigned();
            $table->integer('id_classe')->unsigned();
            $table->integer('id_cycle')->unsigned();
            $table->integer('id_nniveau')->unsigned();
            $table->string('annee_academique', 20);
            $table->string('modele', 30);
            $table->string('document', 40);
            $table->string('periode', 10);
            $table->integer('effectif')->default(0);
            $table->text('contexte');
            $table->text('contenu');
            $table->string('empreinte', 64);
            $table->integer('id_creator')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(array('id_campus', 'id_tutelle', 'annee_academique'), 'academic_docs_context_idx');
        });
        Schema::create('academic_note_audits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_note')->unsigned();
            $table->integer('id_campus')->unsigned();
            $table->integer('id_tutelle')->unsigned();
            $table->integer('id_user')->unsigned()->nullable();
            $table->string('operation', 20);
            $table->text('avant')->nullable();
            $table->text('apres')->nullable();
            $table->timestamps();
        });
        Schema::create('testok_import_links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source', 80);
            $table->string('entite', 40);
            $table->integer('legacy_id');
            $table->integer('target_id');
            $table->text('donnees_source');
            $table->string('empreinte', 64);
            $table->timestamps();
            $table->unique(array('source', 'entite', 'legacy_id'), 'testok_import_identity_unique');
        });
        Schema::table('note', function (Blueprint $table) {
            $table->string('anonymat', 100)->nullable();
            $table->string('mois_examen', 100)->nullable();
        });
        });
    }

    public function down()
    {
        Schema::table('tutelles_academiques', function (Blueprint $table) {
            $table->dropColumn('modele_documents');
        });
        Schema::table('note', function (Blueprint $table) {
            $table->dropColumn(array('anonymat', 'mois_examen'));
        });
        Schema::drop('testok_import_links');
        Schema::drop('academic_note_audits');
        Schema::drop('academic_document_generations');
    }
}
