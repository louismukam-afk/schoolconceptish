<?php

use Illuminate\Support\Facades\DB;

class ScolariteProfilesTest extends TestCase {
    public function createApplication()
    {
        global $crypt;
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    public function testNewProfilesAreExactAndKeepIndividualSelection()
    {
        DB::beginTransaction();
        try {
            DB::statement('CREATE TEMP TABLE scolarite (id integer, id_cycle integer, id_niveau integer, id_classe integer, id_nniveau integer, annee_scolaire varchar(20), libelle varchar(255), type_scolarite integer) ON COMMIT DROP');
            DB::table('scolarite')->insert(array(
                array('id' => 1, 'id_cycle' => 1, 'id_niveau' => 3, 'id_classe' => null, 'id_nniveau' => null, 'annee_scolaire' => null, 'libelle' => null, 'type_scolarite' => 0),
                array('id' => 2, 'id_cycle' => 1, 'id_niveau' => 3, 'id_classe' => 8, 'id_nniveau' => 2, 'annee_scolaire' => '2026-2027', 'libelle' => 'Externe', 'type_scolarite' => 1),
                array('id' => 3, 'id_cycle' => 1, 'id_niveau' => 3, 'id_classe' => 8, 'id_nniveau' => 2, 'annee_scolaire' => '2026-2027', 'libelle' => 'Interne', 'type_scolarite' => 0),
                array('id' => 4, 'id_cycle' => 1, 'id_niveau' => 3, 'id_classe' => 9, 'id_nniveau' => 2, 'annee_scolaire' => '2026-2027', 'libelle' => 'Autre specialite', 'type_scolarite' => 1),
                array('id' => 5, 'id_cycle' => 1, 'id_niveau' => 3, 'id_classe' => 8, 'id_nniveau' => 2, 'annee_scolaire' => '2027-2028', 'libelle' => 'Autre annee', 'type_scolarite' => 1)
            ));

            $profiles = gesecole\scolarite::matchingNewStyle(1, 3, 8, 2, '2026 - 2027')->lists('id');
            sort($profiles);
            $this->assertEquals(array(2, 3), $profiles);
            $this->assertEquals(1, gesecole\scolarite::matchingOldStyle(1, 3, 0)->first()->id);

            $inscription = new stdClass;
            $inscription->id_scolarite = 3;
            $this->assertEquals('Interne', gesecole\scolarite::forInscription($inscription)->libelle);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::rollBack();
    }

    public function testUnpaidListIncludesStudentWithoutAnyPayment()
    {
        DB::beginTransaction();
        try {
            foreach (array('eleve', 'inscription', 'payement', 'bourses', 'scolarite') as $table) {
                DB::statement('CREATE TEMP TABLE '.$table.' ON COMMIT DROP AS SELECT * FROM public.'.$table.' WITH NO DATA');
            }
            $classe = DB::table('classe')->orderBy('id')->first();
            $niveau = DB::table('n_niveaus')->orderBy('id')->first();
            session(array('year' => '2026-2027', 'campus_id' => 1));

            DB::table('eleve')->insert(array('id' => 920001, 'nom' => 'Sans paiement'));
            DB::table('scolarite')->insert(array('id' => 920001, 'id_niveau' => $classe->id_niveau,
                'id_cycle' => $niveau->id_cycle, 'id_classe' => $classe->id, 'id_nniveau' => $niveau->id,
                'annee_scolaire' => '2026-2027', 'type_scolarite' => 1, 'tranche1' => 300,
                'tranche2' => 0, 'tranche3' => 0, 'tranche4' => 0, 'tranche5' => 0));
            DB::table('inscription')->insert(array('id' => 920001, 'id_eleve' => 920001,
                'id_classe' => $classe->id, 'id_cycle' => $niveau->id_cycle, 'id_nniveau' => $niveau->id,
                'id_scolarite' => 920001, 'type_scolarite' => 1, 'annee_scolaire' => '2026-2027',
                'id_campus' => 1, 'desinscrire' => 0));

            $controller = new gesecole\Http\Controllers\inscriptions\listesController;
            $request = new Illuminate\Http\Request(array('id' => 0, 'option' => 1, 'type' => 0, 'nniveau' => 0));
            $this->assertCount(1, $controller->show($request)->getData()['eleves']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
        DB::rollBack();
    }
}
