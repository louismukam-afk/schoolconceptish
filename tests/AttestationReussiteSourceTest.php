<?php

use Illuminate\Support\Facades\DB;

class AttestationReussiteSourceTest extends TestCase
{
    public function createApplication()
    {
        global $crypt;
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();
        DB::statement('CREATE TEMP TABLE releve_note_generations ON COMMIT DROP AS SELECT * FROM public.releve_note_generations WITH NO DATA');
        session(array('year' => '2026-2027'));
    }

    public function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function testAttestationsComeFromTheSelectedSavedTranscript()
    {
        $cases = array(
            array('licence_ebolowa', 'gesecole\Http\Controllers\eleves\ebolowaAttestationReussiteController', 14, 9, null),
            array('licence_soa', 'gesecole\Http\Controllers\eleves\soaAttestationReussiteController', 14, 9, null),
            array('licence_ngaoundere', 'gesecole\Http\Controllers\eleves\ndereAttestationReussiteController', 72, 45, 6),
        );

        foreach ($cases as $index => $case) {
            list($type, $controllerClass, $passing, $failing, $semester) = $case;
            $selectedId = 950001 + $index * 10;
            $otherId = $selectedId + 1;
            $this->insertTranscript($selectedId, $type, $semester, $passing, $failing);
            $this->insertTranscript($otherId, $type, $semester, $passing + 1, $failing);

            $controller = new $controllerClass();
            $method = new ReflectionMethod($controller, 'buildAttestations');
            $method->setAccessible(true);
            $attestations = $method->invoke($controller, 30, 1, 3, $selectedId);

            $this->assertCount(1, $attestations);
            $this->assertEquals('Etudiant '.$selectedId, $attestations[0]['eleve']->nom);
            $expected = $type == 'licence_ngaoundere' ? $passing : $passing * 5;
            $this->assertEquals($expected, $attestations[0]['moyenne100']);
            $this->assertCount(0, $method->invoke($controller, 30, 2, 3, $selectedId));
        }
    }

    public function testTranscriptSelectorRendersSavedSource()
    {
        $source = (object) array(
            'id' => 950001, 'id_classe' => 30, 'id_cycle' => 1, 'id_nniveau' => 3,
            'created_at' => '2026-11-11 10:00:00', 'nb_releves' => 2,
        );
        $html = view('eleves.partials.attestation_releve_select', array(
            'prefix' => 'attestation_ebolowa',
            'sources_releves' => array($source),
        ))->render();

        $this->assertContains('name="source_releve_id"', $html);
        $this->assertContains('Releve #950001', $html);
        $this->assertContains('data-nniveau="3"', $html);
    }

    private function insertTranscript($id, $type, $semester, $passing, $failing)
    {
        $rows = array(
            array('eleve' => array('id' => $id, 'nom' => 'Etudiant '.$id), 'mgp' => $passing),
            array('eleve' => array('id' => $id + 100, 'nom' => 'Non admis'), 'mgp' => $failing),
        );
        DB::table('releve_note_generations')->insert(array(
            'id' => $id,
            'type' => $type,
            'annee_academique' => '2026-2027',
            'id_niveau' => 10,
            'id_classe' => 30,
            'id_cycle' => 1,
            'id_nniveau' => 3,
            'semestre' => $semester,
            'nb_releves' => 2,
            'hash_generation' => str_repeat('a', 64),
            'contenu' => json_encode(array('PV' => $rows)),
        ));
    }
}
