<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixActivityLogsIdSequence extends Migration {
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || !Schema::hasTable('activity_logs')) {
            return;
        }

        DB::transaction(function () {
            DB::statement('LOCK TABLE activity_logs IN ACCESS EXCLUSIVE MODE');
            $column = DB::selectOne(
                "SELECT column_default FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = ?",
                array('activity_logs', 'id')
            );

            if (!$column || $column->column_default) {
                return;
            }

            DB::statement('CREATE SEQUENCE IF NOT EXISTS activity_logs_id_seq');
            DB::statement("SELECT setval('activity_logs_id_seq', COALESCE((SELECT MAX(id) FROM activity_logs), 0) + 1, false)");
            DB::statement("ALTER TABLE activity_logs ALTER COLUMN id SET DEFAULT nextval('activity_logs_id_seq')");
            DB::statement('ALTER SEQUENCE activity_logs_id_seq OWNED BY activity_logs.id');
        });
    }

    public function down()
    {
        // Preserve automatic IDs and existing audit records on rollback.
    }
}
