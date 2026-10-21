<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniversityFieldsToTimetableTables extends Migration {

    public function up()
    {
        if(Schema::hasTable('cours_enseignants')){
            Schema::table('cours_enseignants', function(Blueprint $table)
            {
                if(!Schema::hasColumn('cours_enseignants','id_cycle')) $table->integer('id_cycle')->default(0)->index();
                if(!Schema::hasColumn('cours_enseignants','id_niveau')) $table->integer('id_niveau')->default(0)->index();
                if(!Schema::hasColumn('cours_enseignants','id_nniveau')) $table->integer('id_nniveau')->default(0)->index();
            });
            DB::statement("UPDATE cours_enseignants SET id_niveau = COALESCE((SELECT id_niveau FROM classe WHERE classe.id = cours_enseignants.id_classe), 0) WHERE COALESCE(id_niveau,0)=0");
            DB::statement("UPDATE cours_enseignants SET id_cycle = COALESCE((SELECT id_cycle FROM n_niveaus WHERE n_niveaus.id = cours_enseignants.id_nniveau), COALESCE((SELECT id_cycle FROM niveau WHERE niveau.id = cours_enseignants.id_niveau), 0)) WHERE COALESCE(id_cycle,0)=0");
            DB::statement("UPDATE cours_enseignants SET semestre = COALESCE(id_trimestre, semestre, 1) WHERE semestre IS NULL OR semestre = 0");
        }

        if(Schema::hasTable('heures_realisees_enseignants')){
            Schema::table('heures_realisees_enseignants', function(Blueprint $table)
            {
                if(!Schema::hasColumn('heures_realisees_enseignants','id_cycle')) $table->integer('id_cycle')->default(0)->index();
                if(!Schema::hasColumn('heures_realisees_enseignants','id_niveau')) $table->integer('id_niveau')->default(0)->index();
                if(!Schema::hasColumn('heures_realisees_enseignants','id_nniveau')) $table->integer('id_nniveau')->default(0)->index();
                if(!Schema::hasColumn('heures_realisees_enseignants','semestre')) $table->integer('semestre')->default(1)->index();
            });
            DB::statement("UPDATE heures_realisees_enseignants SET id_cycle = COALESCE((SELECT id_cycle FROM cours_enseignants WHERE cours_enseignants.id = heures_realisees_enseignants.id_cours_enseignant), 0), id_niveau = COALESCE((SELECT id_niveau FROM cours_enseignants WHERE cours_enseignants.id = heures_realisees_enseignants.id_cours_enseignant), 0), id_nniveau = COALESCE((SELECT id_nniveau FROM cours_enseignants WHERE cours_enseignants.id = heures_realisees_enseignants.id_cours_enseignant), 0), semestre = COALESCE((SELECT semestre FROM cours_enseignants WHERE cours_enseignants.id = heures_realisees_enseignants.id_cours_enseignant), 1)");
        }
    }

    public function down()
    {
        if(Schema::hasTable('heures_realisees_enseignants')){
            Schema::table('heures_realisees_enseignants', function(Blueprint $table)
            {
                if(Schema::hasColumn('heures_realisees_enseignants','semestre')) $table->dropColumn('semestre');
                if(Schema::hasColumn('heures_realisees_enseignants','id_nniveau')) $table->dropColumn('id_nniveau');
                if(Schema::hasColumn('heures_realisees_enseignants','id_niveau')) $table->dropColumn('id_niveau');
                if(Schema::hasColumn('heures_realisees_enseignants','id_cycle')) $table->dropColumn('id_cycle');
            });
        }
        if(Schema::hasTable('cours_enseignants')){
            Schema::table('cours_enseignants', function(Blueprint $table)
            {
                if(Schema::hasColumn('cours_enseignants','id_nniveau')) $table->dropColumn('id_nniveau');
                if(Schema::hasColumn('cours_enseignants','id_niveau')) $table->dropColumn('id_niveau');
                if(Schema::hasColumn('cours_enseignants','id_cycle')) $table->dropColumn('id_cycle');
            });
        }
    }
}
