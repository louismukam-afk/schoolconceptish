<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
class CreateAcademicExamAnonymats extends Migration {
    public function up()
    {
        DB::transaction(function(){
            Schema::create('academic_exam_anonymats',function(Blueprint $table){
                $table->increments('id');$table->string('scope_key',64);$table->string('code',100);
                foreach(array('id_campus','id_tutelle','id_classe','id_cycle','id_nniveau','id_eleve')as $column)$table->integer($column);
                $table->integer('id_session'); // 0 : code commun aux UV du semestre.
                $table->string('annee_scolaire',20);$table->integer('semestre');$table->integer('sequence');
                $table->string('mode_generation',10);$table->integer('id_creator')->nullable();$table->timestamps();
                $table->unique(array('scope_key','id_eleve'),'academic_anon_student_unique');
                $table->unique(array('scope_key','code'),'academic_anon_code_unique');
                $table->index(array('id_campus','id_tutelle','annee_scolaire'),'academic_anon_context_idx');
            });
            Schema::create('academic_anonymat_audits',function(Blueprint $table){
                $table->increments('id');$table->integer('id_anonymat');$table->integer('id_campus');$table->integer('id_tutelle');
                $table->string('annee_scolaire',20);$table->integer('id_user')->nullable();$table->string('operation',20);
                $table->text('avant')->nullable();$table->text('apres')->nullable();$table->timestamps();
            });
        });
    }
    public function down(){Schema::drop('academic_anonymat_audits');Schema::drop('academic_exam_anonymats');}
}