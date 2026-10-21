<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AlterPvGenerationsSemestreToString extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE pv_generations ALTER COLUMN semestre TYPE VARCHAR(30) USING semestre::varchar");
    }

    public function down()
    {
        DB::statement("ALTER TABLE pv_generations ALTER COLUMN semestre TYPE INTEGER USING CASE WHEN semestre = 'annuel' THEN 0 ELSE semestre::integer END");
    }
}