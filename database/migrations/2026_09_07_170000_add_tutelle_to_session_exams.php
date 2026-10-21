<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddTutelleToSessionExams extends Migration {
    public function up()
    {
        if (Schema::hasTable('session_exams') && !Schema::hasColumn('session_exams', 'id_tutelle')) {
            Schema::table('session_exams', function(Blueprint $table) {
                $table->integer('id_tutelle')->nullable()->after('id_matiere');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('session_exams') && Schema::hasColumn('session_exams', 'id_tutelle')) {
            Schema::table('session_exams', function(Blueprint $table) {
                $table->dropColumn('id_tutelle');
            });
        }
    }
}
