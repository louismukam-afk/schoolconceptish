<?php

use Illuminate\Support\Facades\DB;

class HistoricalCampusControllerStub extends gesecole\Http\Controllers\Controller {
    public function filter($column = 'id_campus', $year = null)
    {
        return $this->campusVisibilitySql($column, $year);
    }
}

class HistoricalCampusVisibilityTest extends TestCase {
    public function createApplication()
    {
        global $crypt;
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    public function testHistoricalRuleAndExplicitReportYear()
    {
        $controller = new HistoricalCampusControllerStub;
        foreach (array('2025-2026', '2026-2027', '2027-2028', 'invalid', '') as $year) {
            session(array('year' => $year));
            $sql = $controller->filter('p.id_campus');
            $this->assertSame($year === '2025-2026', strpos($sql, 'IS NULL') !== false);
            $this->assertSame(1, substr_count($sql, '?'));
        }
        $this->assertContains('IS NULL', $controller->filter('i.id_campus', '2025 - 2026'));
        $this->assertNotContains('IS NULL', $controller->filter('i.id_campus', '2025-2027'));
    }

    public function testHistoricalListsAndAccountingPreserveCampusIsolation()
    {
        DB::beginTransaction();
        try {
            foreach (array('eleve', 'inscription', 'payement', 'personnel', 'payement_salaire', 'decaissement', 'discipline') as $table) {
                DB::statement('CREATE TEMP TABLE '.$table.' ON COMMIT DROP AS SELECT * FROM public.'.$table.' WITH NO DATA');
            }
            DB::table('eleve')->insert(array('id' => 910001, 'nom' => 'Legacy test'));
            $classId = DB::table('classe')->orderBy('id')->first()->id;
            $levelId = DB::table('n_niveaus')->orderBy('id')->first()->id;
            foreach (array('2025-2026', '2026-2027') as $year) {
                foreach (array(1, 2, null) as $campus) {
                    DB::table('inscription')->insert(array('id_eleve' => 910001, 'annee_scolaire' => $year, 'id_campus' => $campus, 'desinscrire' => 0, 'id_classe' => $classId, 'id_nniveau' => $levelId));
                    DB::table('payement')->insert(array('id_eleve' => 910001, 'annee_scolaire' => $year, 'id_campus' => $campus, 'montant' => 100, 'date_payement' => '2025-10-01'));
                    DB::table('payement_salaire')->insert(array('annee_scolaire' => $year, 'id_campus' => $campus, 'net_a_payer' => 20));
                    DB::table('decaissement')->insert(array('annee_scolaire' => $year, 'id_campus' => $campus, 'montant' => 10));
                }
            }
            foreach (array(1, 2, null) as $campus) {
                DB::table('personnel')->insert(array('nom' => 'Test staff', 'id_campus' => $campus));
            }
            DB::table('discipline')->insert(array('id_eleve' => 910001, 'annee_scolaire' => '2025-2026', 'type' => 0, 'just' => 0, 'nb_heures' => 2));

            foreach (array('2025-2026' => 2, '2026-2027' => 1) as $year => $expectedCount) {
                session(array('year' => $year, 'campus_id' => 1));
                $inscriptions = new gesecole\Http\Controllers\inscriptions\inscriptionController;
                $this->assertEquals($expectedCount, $inscriptions->index()->getData()['nb_eleves']);
                $lists = new gesecole\Http\Controllers\inscriptions\listesController;
                $this->assertCount($expectedCount, $lists->show(new Illuminate\Http\Request(array('id' => 0, 'nniveau' => 0)))->getData()['eleves']);
                $payments = new gesecole\Http\Controllers\inscriptions\payementController;
                $this->assertCount($expectedCount, $payments->index_liste(910001)->getData()['payements']);
                $staff = new gesecole\Http\Controllers\personnel\personnelController;
                $this->assertCount($expectedCount, $staff->index_personnel()->getData()['personnel']);
                $accounting = new gesecole\Http\Controllers\comptabilite\comptabiliteController;
                $totals = $accounting->index()->getData();
                $this->assertEquals(100 * $expectedCount, $totals['entrees']);
                $this->assertEquals(30 * $expectedCount, $totals['sorties']);
            }
            session(array('year' => '2025-2026'));
            $discipline = new gesecole\Http\Controllers\eleves\disciplineController;
            $this->assertCount(1, $discipline->create(910001)->getData()['discipline']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::rollBack();
    }

    public function testTargetedMigrationLeavesOtherYearsAndAmbiguousPaymentsUntouched()
    {
        require_once __DIR__.'/../database/migrations/campus_2026_2027/2026_10_16_000003_attach_2026_2027_payments_to_inscription_campus.php';
        DB::beginTransaction();
        try {
            DB::statement('CREATE TEMP TABLE inscription (id integer, id_eleve integer, annee_scolaire text, id_campus integer) ON COMMIT DROP');
            DB::statement('CREATE TEMP TABLE payement (id integer, id_eleve integer, annee_scolaire text, id_inscription integer, id_campus integer, montant integer) ON COMMIT DROP');
            DB::statement('CREATE TEMP TABLE mouvements_financiers (id integer, id_payement integer, id_campus integer, montant integer) ON COMMIT DROP');
            DB::statement("INSERT INTO inscription VALUES
                (10, 1, '2026-2027', 1), (11, 1, '2025-2026', 1),
                (20, 2, '2026-2027', 1), (21, 2, '2026-2027', 2),
                (30, 3, '2026 - 2027', 2), (40, 4, '2027-2028', 1)");
            DB::statement("INSERT INTO payement VALUES
                (1, 1, '2026-2027', 10, NULL, 100),
                (2, 1, '2026-2027', 0, NULL, 100),
                (3, 1, '2025-2026', 11, NULL, 100),
                (4, 2, '2026-2027', 0, NULL, 100),
                (5, 2, '2026-2027', 21, NULL, 100),
                (6, 3, '2026 - 2027', 0, 0, 100),
                (7, 4, '2027-2028', 40, NULL, 100),
                (8, 1, '2026-2027', 10, 2, 100),
                (9, 9, '2026-2027', 10, NULL, 100),
                (10, 1, '2026-2027', 999, NULL, 100)");
            DB::statement('INSERT INTO mouvements_financiers VALUES (1, 1, NULL, 100), (2, 3, NULL, 100), (3, 1, 2, 100)');
            $migration = new Attach20262027PaymentsToInscriptionCampus;
            $migration->up();
            $payments = DB::table('payement')->orderBy('id')->get();
            $campuses = array_map(function ($p) { return $p->id_campus === null ? null : (int) $p->id_campus; }, $payments);
            $this->assertSame(array(1, 1, null, null, 2, 2, null, 2, null, null), $campuses);
            $this->assertEquals(1000, DB::table('payement')->sum('montant'));
            $this->assertEquals(1, DB::table('mouvements_financiers')->where('id', 1)->first()->id_campus);
            $this->assertNull(DB::table('mouvements_financiers')->where('id', 2)->first()->id_campus);
            $this->assertEquals(2, DB::table('mouvements_financiers')->where('id', 3)->first()->id_campus);
            $migration->up();
            $this->assertEquals($payments, DB::table('payement')->orderBy('id')->get());
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::rollBack();
    }

    public function testBudgetReportIncludesHistoricalUnassignedWithdrawals()
    {
        DB::beginTransaction();
        try {
            foreach (array('ligne_budgetaires', 'donnee_ligne_budgetaires', 'categorie_budgetaires', 'decaissement') as $table) {
                DB::statement('CREATE TEMP TABLE '.$table.' ON COMMIT DROP AS SELECT * FROM public.'.$table.' WITH NO DATA');
            }
            DB::table('ligne_budgetaires')->insert(array('id' => 1, 'id_campus' => null));
            DB::table('categorie_budgetaires')->insert(array('id' => 1, 'libelle' => 'Test category'));
            DB::table('donnee_ligne_budgetaires')->insert(array('id' => 1, 'id_ligne_budgetaire' => 1, 'id_categorie_budgetaire' => 1, 'montant' => 100));
            foreach (array(1, 2, null) as $campus) {
                DB::table('decaissement')->insert(array('id_ligne_budgetaire' => 1, 'id_categorie_budgetaire' => 1, 'id_campus' => $campus, 'montant' => 10));
            }
            session(array('year' => '2025-2026', 'campus_id' => 1));
            $controller = new gesecole\Http\Controllers\comptabilite\LigneBudgetController;
            $this->assertEquals(20, $controller->report(1)->getData()['lineData'][0]->realized_amount);
            DB::table('ligne_budgetaires')->where('id', 1)->update(array('id_campus' => 1));
            session(array('year' => '2026-2027'));
            $this->assertEquals(10, $controller->report(1)->getData()['lineData'][0]->realized_amount);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::rollBack();
    }
}
