<?php
use gesecole\Services\TestokDataMigrationService;
use Illuminate\Support\Facades\DB;

class TestokDataMigrationTest extends TestCase {
    private $folder;
    public function createApplication()
    {
        global $crypt;
        $app=require __DIR__.'/../bootstrap/app.php';$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();return $app;
    }
    public function setUp()
    {
        parent::setUp();DB::beginTransaction();
        foreach(array('campus','testok_import_links') as $table)DB::statement('CREATE TEMP TABLE '.$table.' ON COMMIT DROP AS SELECT * FROM public.'.$table.' WITH NO DATA');
        DB::table('campus')->insert(array('id'=>71));
        $this->folder=storage_path('app/import-testok/test-migration-'.uniqid());mkdir($this->folder,0700);
        $tables=array_fill_keys(array('annee','cycle','niveaux','filiere','specialite','codematiere','matiere','programme_specialite','groupe_matiere','session_examen','etudiant','inscription','notes'),array());
        $json=json_encode(array('format'=>'testok-schoolconcept-v1','sql_sha256'=>TestokDataMigrationService::SQL_SHA256,'tables'=>$tables));
        file_put_contents($this->folder.'/source.json',$json);
        file_put_contents($this->folder.'/manifest.json',json_encode(array('format'=>'testok-migration-package-v1','sql_sha256'=>TestokDataMigrationService::SQL_SHA256,'source_sha256'=>hash('sha256',$json))));
    }
    public function tearDown()
    {
        foreach(array('manifest.json','source.json') as $file)unlink($this->folder.'/'.$file);
        rmdir($this->folder);DB::rollBack();parent::tearDown();
    }
    public function testAlteredPayloadIsRejected()
    {
        file_put_contents($this->folder.'/source.json',' ' ,FILE_APPEND);
        $this->setExpectedException('RuntimeException','Empreinte');
        (new TestokDataMigrationService)->run($this->folder,71);
    }
    public function testMissingCampusIsRejected()
    {
        $this->setExpectedException('RuntimeException','campus existant');
        (new TestokDataMigrationService)->run($this->folder,999);
    }
    public function testPreviousImportRequiresLocalCorrespondences()
    {
        DB::table('testok_import_links')->insert(array('source'=>'testok-hintel','entite'=>'etudiant','legacy_id'=>1,'target_id'=>2));
        try{(new TestokDataMigrationService)->run($this->folder,71);$this->fail('Correspondances requises.');}
        catch(RuntimeException $e){$this->assertContains('--mapping',$e->getMessage());}
        $this->assertEquals(1,DB::table('testok_import_links')->count());
    }
}
