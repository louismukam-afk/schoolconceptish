<?php

use Illuminate\Support\Facades\DB;

class LegacyPaymentCampusesTest extends TestCase {
    public function createApplication()
    {
        // The legacy bootstrap exposes this encrypter through secEnv2().
        global $crypt;
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    public function testOnlyUnambiguousLegacyCampusesAreRestored()
    {
        require_once __DIR__.'/../database/migrations/2026_10_16_000002_restore_legacy_payment_campuses.php';
        DB::beginTransaction();
        try {
            // Temporary tables shadow the real tables for this connection only.
            DB::statement('CREATE TEMP TABLE inscription (id integer, id_eleve integer, annee_scolaire text, id_campus integer) ON COMMIT DROP');
            DB::statement('CREATE TEMP TABLE payement (id integer, id_eleve integer, annee_scolaire text, id_inscription integer, id_campus integer) ON COMMIT DROP');
            DB::statement("INSERT INTO inscription VALUES
                (10, 1, '2026-2027', 1),
                (20, 2, '2026-2027', 1), (21, 2, '2026-2027', 2),
                (30, 3, '2026-2027', NULL),
                (40, 4, '2026-2027', 2), (41, 4, '2026-2027', 2),
                (50, 5, '2026-2027', 1), (51, 5, '2026-2027', NULL)");
            DB::statement("INSERT INTO payement VALUES
                (1, 1, '2026-2027', 10, NULL),
                (2, 1, '2026-2027', 0, NULL),
                (3, 1, '2026-2027', NULL, NULL),
                (4, 2, '2026-2027', 0, NULL),
                (5, 3, '2026-2027', 0, NULL),
                (6, 4, '2026-2027', 0, NULL),
                (7, 5, '2026-2027', 0, NULL),
                (8, 1, '2026-2027', 0, 2),
                (9, 1, '2025-2026', 10, NULL),
                (10, 2, '2026-2027', 10, NULL),
                (11, 9, '2026-2027', 0, NULL),
                (12, 1, '2026-2027', 999, NULL),
                (13, 2, '2026-2027', 21, NULL)");

            $migration = new RestoreLegacyPaymentCampuses;
            $migration->up();
            $firstRun = DB::table('payement')->orderBy('id')->get();
            $actual = array_map(function ($payment) {
                return $payment->id_campus === null ? null : (int) $payment->id_campus;
            }, $firstRun);
            $this->assertSame(array(1, 1, 1, null, null, 2, null, 2, null, null, null, null, 2), $actual);
            $migration->up();
            $this->assertEquals($firstRun, DB::table('payement')->orderBy('id')->get());
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::rollBack();
    }
}
