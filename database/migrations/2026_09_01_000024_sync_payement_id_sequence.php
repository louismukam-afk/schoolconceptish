<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncPayementIdSequence extends Migration
{
    public function up()
    {
        if (Schema::hasTable('payement')) {
            DB::statement("
                SELECT setval(
                    pg_get_serial_sequence('payement', 'id'),
                    COALESCE((SELECT MAX(id) FROM payement), 0) + 1,
                    false
                )
            ");
        }
    }

    public function down()
    {
        //
    }
}
