<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreMouvementsFinanciersIdSequence extends Migration
{
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'pgsql' || !Schema::hasTable('mouvements_financiers')) {
            return;
        }

        DB::transaction(function () {
            DB::statement('LOCK TABLE mouvements_financiers IN ACCESS EXCLUSIVE MODE');
            $column = DB::selectOne(
                "SELECT column_default FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ? AND column_name = 'id'",
                array('mouvements_financiers')
            );

            if (!$column || $column->column_default) {
                return;
            }

            DB::statement('CREATE SEQUENCE IF NOT EXISTS mouvements_financiers_id_seq');
            DB::statement("SELECT setval('mouvements_financiers_id_seq', COALESCE((SELECT MAX(id) FROM mouvements_financiers), 0) + 1, false)");
            DB::statement("ALTER TABLE mouvements_financiers ALTER COLUMN id SET DEFAULT nextval('mouvements_financiers_id_seq')");
            DB::statement('ALTER SEQUENCE mouvements_financiers_id_seq OWNED BY mouvements_financiers.id');
        });
    }

    public function down()
    {
        // Preserve the identifier sequence used by existing financial records.
    }
}
