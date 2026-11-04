<?php

use Illuminate\Support\Facades\DB;
use gesecole\Services\TestokImportService;

class TestokImportWorkflowTest extends TestCase {
    public function createApplication()
    {
        global $crypt;
        $app=require __DIR__.'/../bootstrap/app.php';$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();return $app;
    }
    public function setUp()
    {
        parent::setUp();DB::beginTransaction();
        foreach(array('campus','eleve','n_niveaus','classe_matiere','specialite_tutelle','campus_specialite','session_exams','inscription','note','testok_import_links','tutelles_academiques','niveau','classe')as $table)DB::statement('CREATE TEMP TABLE '.$table.' ON COMMIT DROP AS SELECT * FROM public.'.$table.' WITH NO DATA');
        foreach(array('eleve','session_exams','inscription','note','testok_import_links')as $table){DB::statement('CREATE TEMP SEQUENCE '.$table.'_import_test_seq');DB::statement("ALTER TABLE ".$table." ALTER COLUMN id SET DEFAULT nextval('".$table."_import_test_seq')");}
        DB::table('campus')->insert(array('id'=>1));DB::table('eleve')->insert(array('id'=>70));DB::table('n_niveaus')->insert(array('id'=>10,'id_cycle'=>20));
        DB::table('classe_matiere')->insert(array('id'=>90,'id_classe'=>30,'id_cycle'=>20,'id_nniveau'=>10,'id_matiere'=>40,'coef'=>45,'semestre'=>5));
        DB::table('campus_specialite')->insert(array('id_campus'=>1,'id_classe'=>30));
        foreach(array(1,2)as $id)DB::table('specialite_tutelle')->insert(array('id_tutelle'=>$id,'id_classe'=>30));
        DB::table('tutelles_academiques')->insert(array(array('id'=>1,'modele_documents'=>'ngaoundere'),array('id'=>2,'modele_documents'=>'polytechnique')));
        DB::table('inscription')->insert(array('id'=>55,'id_eleve'=>70,'annee_scolaire'=>'2025-2026','id_campus'=>1,'id_classe'=>30,'id_nniveau'=>10,'id_cycle'=>20,'montant'=>75000,'date_inscription'=>'2025-09-17','edition_details'=>'Inscription SchoolConcept existante.'));
    }
    public function tearDown(){DB::rollBack();parent::tearDown();}
    private function source()
    {
        $tables=array_fill_keys(array('annee','cycle','niveaux','filiere','specialite','codematiere','matiere','programme_specialite','groupe_matiere','session_examen','etudiant','inscription','notes'),array());
        $tables['annee'][]=array('IDANNEE'=>3,'ANNEES'=>'2025 - 2026');
        $tables['etudiant'][]=array('IDETUDIANT'=>7);
        $tables['programme_specialite'][]=array('IDPROGRAMMESP'=>9,'IDFILIERE'=>7,'IDSPECIALITE'=>6,'IDCYCLE'=>5,'IDNIVEAU'=>4,'IDMATIERE'=>8,'CREDIT'=>45,'SEMESTRE'=>5);
        $tables['session_examen'][]=array('IDSESSION'=>12,'IDPROGRAMMESP'=>9,'IDANNEE'=>3,'CC'=>30,'SN'=>70,'TPE'=>0,'RATRAPPAGE'=>70,'CC2'=>0,'SN2'=>0,'TPE2'=>0,'RATRAPPAGE2'=>0);
        $tables['inscription'][]=array('IDINSCRIPTION'=>11,'IDETUDIANT'=>7,'IDSPECIALITE'=>6,'IDCYCLE'=>5,'IDNIVEAU'=>4,'IDANNEE'=>3);
        foreach(array(array(100,'CC',15),array(101,'SN',45))as $row)$tables['notes'][]=array('IDNOTE'=>$row[0],'IDETUDIANT'=>7,'IDSESSION'=>12,'IDPROGRAMMESP'=>9,'IDANNEE'=>3,'TYPE_EXAMEN'=>$row[1],'NOTE'=>$row[2],'IDSPECIALITE'=>6,'IDCYCLE'=>5,'IDNIVEAU'=>4,'IDMATIERE'=>8,'ANONYMATS'=>'A7','MOIS'=>'juin');
        return array('format'=>'testok-schoolconcept-v1','exported_at'=>'2026-10-30T10:00:00+00:00','tables'=>$tables);
    }
    private function mapping()
    {
        return array('source'=>'unit-test','campus_id'=>1,'inscription_date_by_year'=>array('2025-2026'=>'2026-10-30'),'etudiants'=>array(7=>70),'programmes'=>array(9=>90),'tutelles_by_programme'=>array(9=>array(1,2)),'specialites'=>array(6=>30),'cycles'=>array(5=>20),'niveaux'=>array(4=>10),'matieres'=>array(8=>40));
    }
    public function testImportReusesExistingEnrolmentAndIsIdempotent()
    {
        $service=new TestokImportService;$source=$this->source();$mapping=$this->mapping();
        $before=DB::table('inscription')->first();
        $this->assertTrue($service->plan($source,$mapping)['ready']);
        $service->import($source,$mapping);
        $this->assertEquals(0,$service->stats()['inscriptions_creees']);
        $this->assertEquals(1,$service->stats()['inscriptions_reutilisees']);
        $this->assertEquals(4,$service->stats()['notes_creees']);
        $this->assertEquals(2,DB::table('session_exams')->count());$this->assertEquals(4,DB::table('note')->count());$this->assertEquals(1,DB::table('inscription')->count());
        $this->assertEquals(1,DB::table('note')->distinct()->count('id_campus'));
        $this->assertEquals(15,DB::table('note')->where('sequence',1)->first()->note);
        $links=DB::table('testok_import_links')->count();$service->import($source,$mapping);
        $this->assertEquals(4,$service->stats()['notes_reutilisees']);
        $this->assertEquals(4,DB::table('note')->count());$this->assertEquals($links,DB::table('testok_import_links')->count());
        $this->assertEquals($before,DB::table('inscription')->first());
        $this->assertEquals(55,DB::table('testok_import_links')->where('entite','inscription')->first()->target_id);
        $this->assertEquals(1,DB::table('eleve')->count());
    }
    public function testConflictingExistingGradeIsNeverOverwritten()
    {
        $service=new TestokImportService;$service->import($this->source(),$this->mapping());
        $id=DB::table('note')->where('sequence',3)->first()->id;DB::table('note')->where('id',$id)->update(array('note'=>43));
        try{$service->import($this->source(),$this->mapping());$this->fail('Un conflit doit arrêter la reprise.');}catch(RuntimeException $e){$this->assertContains('différente',$e->getMessage());}
        $this->assertEquals(43,DB::table('note')->where('id',$id)->first()->note);$this->assertEquals(4,DB::table('note')->count());
    }
    public function testContradictorySourceGradesAreBothIsolated()
    {
        $source=$this->source();$duplicate=$source['tables']['notes'][1];$duplicate['IDNOTE']=102;$duplicate['NOTE']=43;$source['tables']['notes'][]=$duplicate;
        $result=(new TestokImportService)->isolateInvalid($source,$this->mapping());
        $this->assertCount(1,$result['source']['tables']['notes']);$this->assertCount(2,$result['quarantine']['records']['notes']);
        $this->assertEquals(0,DB::table('note')->count());
    }

    public function testMissingEnrolmentIsCreatedOnceWithItsNotes()
    {
        DB::table('inscription')->delete();$service=new TestokImportService;
        $source=$this->source();$mapping=$this->mapping();
        $this->assertTrue($service->plan($source,$mapping)['ready']);
        $isolated=$service->isolateInvalid($source,$mapping);
        $this->assertCount(0,$isolated['quarantine']['records']['inscription']);
        $service->import($source,$mapping);$this->assertEquals(1,$service->stats()['inscriptions_creees']);
        $service->import($source,$mapping);$this->assertEquals(0,$service->stats()['inscriptions_creees']);
        $this->assertEquals(1,DB::table('inscription')->count());$this->assertEquals(4,DB::table('note')->count());
        $this->assertEquals(0,DB::table('inscription')->first()->montant);
    }

    public function testAmbiguousExistingEnrolmentsAreNeverSelectedArbitrarily()
    {
        $duplicate=(array)DB::table('inscription')->first();$duplicate['id']=56;DB::table('inscription')->insert($duplicate);
        $service=new TestokImportService;$isolated=$service->isolateInvalid($this->source(),$this->mapping());
        $this->assertContains('ambiguë',$isolated['quarantine']['records']['inscription'][0]['reason']);
        $this->assertCount(2,$isolated['quarantine']['records']['notes']);
        $this->assertFalse($service->plan($this->source(),$this->mapping())['ready']);
        $this->assertEquals(2,DB::table('inscription')->count());
    }

    public function testAnotherCampusDoesNotCauseAnEnrolmentToBeCreated()
    {
        DB::table('inscription')->update(array('id_campus'=>2));
        $service=new TestokImportService;
        $this->assertFalse($service->plan($this->source(),$this->mapping())['ready']);
        $this->assertCount(1,$service->isolateInvalid($this->source(),$this->mapping())['quarantine']['records']['inscription']);
        $this->assertEquals(2,DB::table('inscription')->first()->id_campus);
    }

    public function testSourceYearWithSpacesNeedsNoNewEnrolmentOrDate()
    {
        $mapping=$this->mapping();unset($mapping['inscription_date_by_year']);
        (new TestokImportService)->import($this->source(),$mapping);
        $this->assertEquals(1,DB::table('inscription')->count());
        $this->assertEquals('2025-2026',DB::table('inscription')->first()->annee_scolaire);
        $this->assertEquals(4,DB::table('note')->count());
    }

    public function testCatalogCreatesMissingStudentThenReusesItForNotes()
    {
        $source=$this->source();
        foreach($source['tables'] as $table=>$rows)if($table!=='etudiant')$source['tables'][$table]=array();
        $source['tables']['etudiant'][0]=array('IDETUDIANT'=>7,'NOM'=>'Étudiant déjà inscrit','MATRICULE'=>'SOURCE-7','DATE_NAISSANCE'=>'2001-05-17');
        $mapping=$this->mapping();$mapping['etudiants'][7]=null;
        $result=(new gesecole\Services\TestokCatalogService)->prepare($source,$mapping,1);
        $id=$result['mapping']['etudiants'][7];$this->assertGreaterThan(0,$id);
        $this->assertEquals(2,DB::table('eleve')->count());
        $this->assertEquals(1,$result['created']['eleve']);
        DB::table('inscription')->delete();
        $full=$this->source();$mapping=$this->mapping();$mapping['etudiants'][7]=$id;
        (new TestokImportService)->import($full,$mapping);
        $check=(new gesecole\Services\TestokImportVerifier)->verify($full,$mapping);
        $this->assertTrue($check['ok']);$this->assertEquals(4,$check['verifiees']);
        $again=(new gesecole\Services\TestokCatalogService)->prepare($source,$result['mapping'],1);
        $this->assertEquals($id,$again['mapping']['etudiants'][7]);$this->assertEquals(2,DB::table('eleve')->count());
    }

    public function testWrongTutelleAndMissingSharedTutelleAreRejected()
    {
        $mapping=$this->mapping();$mapping['tutelles_by_programme'][9]=array(1);
        $this->assertFalse((new TestokImportService)->plan($this->source(),$mapping)['ready']);
        $mapping=$this->mapping();DB::table('tutelles_academiques')->where('id',2)->update(array('modele_documents'=>'soa'));
        $this->assertFalse((new TestokImportService)->plan($this->source(),$mapping)['ready']);
    }

    public function testVerifierDetectsWrongStudentTutelleAndMissingNote()
    {
        $source=$this->source();$mapping=$this->mapping();(new TestokImportService)->import($source,$mapping);
        $verifier=new gesecole\Services\TestokImportVerifier;
        $this->assertTrue($verifier->verify($source,$mapping)['ok']);
        $id=DB::table('note')->first()->id;DB::table('note')->where('id',$id)->update(array('id_eleve'=>999));
        $this->assertFalse($verifier->verify($source,$mapping)['ok']);
        DB::table('note')->where('id',$id)->update(array('id_eleve'=>70));
        DB::table('session_exams')->where('id_tutelle',2)->update(array('id_tutelle'=>1));
        $this->assertFalse($verifier->verify($source,$mapping)['ok']);
        DB::table('note')->where('id',$id)->delete();
        $this->assertFalse($verifier->verify($source,$mapping)['ok']);
    }

    public function testAmbiguousStudentAndInvalidBirthAreNotInvented()
    {
        $source=$this->source();foreach($source['tables'] as $table=>$rows)if($table!=='etudiant')$source['tables'][$table]=array();
        $source['tables']['etudiant'][0]=array('IDETUDIANT'=>7,'NOM'=>'Homonyme','MATRICULE'=>'DOUBLE','DATE_NAISSANCE'=>'2001-05-17');
        DB::table('eleve')->insert(array(array('id'=>71,'matricule'=>'DOUBLE'),array('id'=>72,'matricule'=>'DOUBLE')));
        $mapping=$this->mapping();$mapping['etudiants'][7]=null;
        $result=(new gesecole\Services\TestokCatalogService)->prepare($source,$mapping,1);
        $this->assertNull($result['mapping']['etudiants'][7]);$this->assertEquals(3,DB::table('eleve')->count());
        $source['tables']['etudiant'][0]['MATRICULE']='ABSENT';$source['tables']['etudiant'][0]['DATE_NAISSANCE']='31-02-2001';
        $result=(new gesecole\Services\TestokCatalogService)->prepare($source,$mapping,1);
        $this->assertNull($result['mapping']['etudiants'][7]);$this->assertEquals(3,DB::table('eleve')->count());
    }

    public function filiereTutelleCases()
    {
        return array(array(1,array('isabee')),array(2,array('soa')),array(3,array('soa')),array(4,array('esstic')),
            array(5,array('ngaoundere','polytechnique')),array(6,array('ngaoundere','polytechnique')),
            array(7,array('ngaoundere','polytechnique')),array(8,array('ngaoundere','polytechnique')),
            array(9,array('ngaoundere','polytechnique')),array(10,array('ngaoundere','polytechnique')));
    }

    /** @dataProvider filiereTutelleCases */
    public function testEveryFiliereImportsNotesIntoExactlyItsApprovedTutelles($filiere,$models)
    {
        DB::table('tutelles_academiques')->delete();$ids=array();
        foreach(array('isabee','soa','esstic','ngaoundere','polytechnique') as $index=>$model){
            $id=11+$index;$ids[$model]=$id;DB::table('tutelles_academiques')->insert(array('id'=>$id,'modele_documents'=>$model));
            DB::table('specialite_tutelle')->insert(array('id_tutelle'=>$id,'id_classe'=>30));
        }
        $source=$this->source();$source['tables']['programme_specialite'][0]['IDFILIERE']=$filiere;
        foreach($source['tables']['notes'] as &$note)$note['IDFILIERE']=$filiere;unset($note);
        $mapping=$this->mapping();$mapping['tutelles_by_programme'][9]=array();
        foreach($models as $model)$mapping['tutelles_by_programme'][9][]=$ids[$model];
        (new TestokImportService)->import($source,$mapping);
        $check=(new gesecole\Services\TestokImportVerifier)->verify($source,$mapping);
        $this->assertTrue($check['ok']);$this->assertEquals(2*count($models),$check['verifiees']);
        $this->assertEquals($models,array_keys($check['par_tutelle']));
        foreach($models as $model)$this->assertEquals(2,$check['par_tutelle'][$model]['verifiees']);
        $this->assertEquals(1,DB::table('inscription')->count());
    }
}
