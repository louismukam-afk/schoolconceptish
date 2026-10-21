<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixSessionExamsIdSequence extends Migration {
    public function up()
    {
        if (!Schema::hasTable('session_exams') || DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $column = DB::selectOne(
            "select column_default from information_schema.columns where table_name = ? and column_name = ?",
            ['session_exams', 'id']
        );

        if ($column && $column->column_default) {
            return;
        }

        DB::statement("create sequence if not exists session_exams_id_seq");
        DB::statement("select setval('session_exams_id_seq', coalesce((select max(id) from session_exams), 0) + 1, false)");
        DB::statement("alter table session_exams alter column id set default nextval('session_exams_id_seq')");
        DB::statement("alter sequence session_exams_id_seq owned by session_exams.id");
    }

    public function down()
    {
        //
    }
}
