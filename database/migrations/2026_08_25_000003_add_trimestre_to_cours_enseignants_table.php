<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTrimestreToCoursEnseignantsTable extends Migration {

    public function up()
    {
        if(Schema::hasTable('cours_enseignants') && !Schema::hasColumn('cours_enseignants','id_trimestre')){
            Schema::table('cours_enseignants', function(Blueprint $table)
            {
                $table->integer('id_trimestre')->default(1);
            });
            if(Schema::hasColumn('cours_enseignants','semestre')){
                DB::statement('UPDATE cours_enseignants SET id_trimestre = semestre WHERE semestre BETWEEN 1 AND 3');
            }
        }
    }

    public function down()
    {
        if(Schema::hasTable('cours_enseignants') && Schema::hasColumn('cours_enseignants','id_trimestre')){
            Schema::table('cours_enseignants', function(Blueprint $table)
            {
                $table->dropColumn('id_trimestre');
            });
        }
    }
}
