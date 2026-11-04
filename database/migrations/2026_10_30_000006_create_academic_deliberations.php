<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateAcademicDeliberations extends Migration {
    public function up(){DB::transaction(function(){
        Schema::create('academic_deliberations',function(Blueprint $t){
            $t->increments('id');foreach(array('id_campus','id_tutelle','id_classe','id_cycle','id_nniveau','id_creator')as $key)$t->integer($key);
            $t->string('annee_scolaire',20);$t->string('modele',30);$t->string('type',30);$t->string('request_key',64)->unique();$t->string('selection_hash',64);
            $t->text('criteres');$t->text('motif');$t->text('bilans');$t->integer('effectif');$t->integer('lignes');$t->timestamps();
            $t->index(array('id_campus','id_tutelle','annee_scolaire'),'academic_delib_context_idx');
        });
        Schema::create('academic_deliberation_items',function(Blueprint $t){
            $t->increments('id');foreach(array('id_deliberation','id_note','id_eleve','id_session','id_classe_matiere','id_matiere','semestre','sequence')as $key)$t->integer($key);
            $t->text('details');$t->timestamps();$t->foreign('id_deliberation')->references('id')->on('academic_deliberations');
            $t->index(array('id_deliberation','semestre','sequence'),'academic_delib_item_idx');
        });
    });}
    public function down(){Schema::drop('academic_deliberation_items');Schema::drop('academic_deliberations');}
}