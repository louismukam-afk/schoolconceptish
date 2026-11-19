<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use gesecole\Services\AcademicDocumentService;
use gesecole\Http\Controllers\eleves\academicDocumentController;

class AcademicDocumentTestUser extends gesecole\User {
    public function isTeacher() { return false; }
    public function canDoAction($action) { return true; }
}

class AcademicAnonymatTestTeacher extends AcademicDocumentTestUser {
    public function isTeacher() { return true; }
}

class AcademicDocumentWorkflowTest extends TestCase {
    public function createApplication()
    {
        global $crypt;
        $app=require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    public function setUp()
    {
        parent::setUp();
        view()->share('errors',new Illuminate\Support\ViewErrorBag);
        DB::beginTransaction();
        foreach(array('campus','tutelles_academiques','classe','cycle','n_niveaus','campus_specialite','specialite_tutelle','inscription','eleve','classe_matiere','session_exams','matiere','groupe_matieres','codages','note','verrou_note','dispense','personnel','academic_document_generations','academic_note_audits','academic_exam_anonymats','academic_anonymat_audits','academic_deliberations','academic_deliberation_items')as $table){
            DB::statement('CREATE TEMP TABLE '.$table.' ON COMMIT DROP AS SELECT * FROM public.'.$table.' WITH NO DATA');
        }
        foreach(array('note','academic_document_generations','academic_note_audits','academic_exam_anonymats','academic_anonymat_audits','academic_deliberations','academic_deliberation_items')as $table){
            DB::statement('CREATE TEMP SEQUENCE '.$table.'_test_seq');
            DB::statement("ALTER TABLE ".$table." ALTER COLUMN id SET DEFAULT nextval('".$table."_test_seq')");
        }
        DB::table('campus')->insert(array('id'=>910001,'nom'=>'HINTEL TEST'));
        DB::table('tutelles_academiques')->insert(array(array('id'=>910001,'nom'=>'ISABEE TEST','modele_documents'=>'isabee'),array('id'=>910002,'nom'=>'NGA TEST','modele_documents'=>'ngaoundere')));
        DB::table('classe')->insert(array('id'=>910001,'nom'=>'Génie civil test'));
        DB::table('cycle')->insert(array('id'=>910001,'nom'=>'Licence test'));
        DB::table('n_niveaus')->insert(array('id'=>910001,'id_cycle'=>910001,'nom'=>'L3'));
        DB::table('campus_specialite')->insert(array('id_campus'=>910001,'id_classe'=>910001));
        DB::table('specialite_tutelle')->insert(array('id_tutelle'=>910001,'id_classe'=>910001));
        DB::table('eleve')->insert(array('id'=>910001,'nom'=>'ÉTUDIANT TEST','matricule'=>'TEST-910001'));
        DB::table('inscription')->insert(array('id'=>910001,'id_campus'=>910001,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_eleve'=>910001,'annee_scolaire'=>'2026-2027','desinscrire'=>0));
        DB::table('matiere')->insert(array('id'=>910001,'nom'=>'Calcul test'));
        DB::table('classe_matiere')->insert(array('id'=>910001,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>910001,'coef'=>45,'semestre'=>5));
        foreach(array(910001,910002)as $id) DB::table('session_exams')->insert(array('id'=>$id,'id_tutelle'=>$id,'id_classe_matiere'=>910001,'annee_academique'=>'2026-2027','cc1'=>30,'sn1'=>70,'tpe1'=>0,'ratrappage1'=>70));
        foreach(array(array(910001,910001,910001,1,15),array(910002,910001,910001,3,45),array(910003,910001,910002,3,70),array(910004,910002,910001,3,70))as $n){
            DB::table('note')->insert(array('id'=>$n[0],'id_campus'=>$n[1],'id_session'=>$n[2],'sequence'=>$n[3],'note'=>$n[4],'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910001,'id_matiere'=>910001,'annee_scolaire'=>'2026-2027','updated_at'=>'2026-10-30 10:00:00'));
        }
        session(array('campus_id'=>910001,'year'=>'2026-2027'));
        $this->app['request']->setSession($this->app['session.store']);
        $user=new AcademicDocumentTestUser;$user->id=910001;Auth::setUser($user);
    }

    public function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function input($extra=array())
    {
        return array_merge(array('modele'=>'isabee','id_tutelle'=>910001,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'periode'=>'annuel','document'=>'pv'),$extra);
    }

    public function testIncompleteNotesUrlReturnsToSelectionInsteadOfAnException()
    {
        $input=$this->input();unset($input['id_cycle'],$input['id_nniveau']);$input['_token']='not-for-the-url';
        $response=(new academicDocumentController)->notes(Request::create('/eleves/documents-tutelles/notes','GET',$input));
        $this->assertEquals(302,$response->getStatusCode());
        $this->assertContains('/eleves/documents-tutelles?',$response->getTargetUrl());
        $this->assertContains('id_classe=910001',$response->getTargetUrl());
        $this->assertNotContains('_token',$response->getTargetUrl());
        $this->assertTrue(session('errors')->has('contexte'));
    }

    public function testResultsUseOnlySelectedCampusAndTutelleSession()
    {
        $service=new AcademicDocumentService;
        $context=$service->context($this->input(),910001,'2026-2027');
        $data=$service->build($context);
        $this->assertCount(1,$data['sessions']);$this->assertCount(1,$data['results']);
        $this->assertSame(12.0,$data['results'][0]['subjects'][0]['result']['final']);
        $this->assertSame('ADMIS',$data['results'][0]['summary']['decision']);
        DB::table('note')->where('id',910002)->update(array('id_campus'=>null));
        $this->assertFalse($service->build($context)['results'][0]['subjects'][0]['result']['complete']);
    }

    public function testModuleParentsDoNotDuplicateCreditsAndUnevaluatedSubjectsStayAvailableForEntry()
    {
        DB::table('matiere')->insert(array(array('id'=>910002,'nom'=>'UE mère'),array('id'=>910003,'nom'=>'Matière future')));
        foreach(array(910002,910003)as $id){
            DB::table('classe_matiere')->insert(array('id'=>$id,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>$id,'coef'=>10,'semestre'=>5));
            DB::table('session_exams')->insert(array('id'=>$id+10,'id_tutelle'=>910001,'id_classe_matiere'=>$id,'annee_academique'=>'2026-2027','cc1'=>30,'sn1'=>70,'tpe1'=>0,'ratrappage1'=>70));
        }
        DB::table('groupe_matieres')->insert(array('id_classe_matiere'=>910001,'id_matiere_mere'=>910002,'id_classe'=>910001,'id_nniveau'=>910001,'semestre'=>5));
        $service=new AcademicDocumentService;$context=$service->context($this->input(),910001,'2026-2027');
        $this->assertCount(1,$service->build($context)['sessions']);
        $this->assertCount(2,$service->sessions($context));
        $this->assertEquals(45,$service->build($context)['results'][0]['summary']['expected']);
    }

    private function moduleFixture()
    {
        DB::table('matiere')->insert(array(array('id'=>910010,'nom'=>'Module scientifique'),array('id'=>910011,'nom'=>'Physique UV')));
        DB::table('classe_matiere')->where('id',910001)->update(array('coef'=>2));
        DB::table('classe_matiere')->insert(array('id'=>910011,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>910011,'coef'=>1,'semestre'=>5));
        DB::table('session_exams')->insert(array('id'=>910011,'id_tutelle'=>910001,'id_classe_matiere'=>910011,'annee_academique'=>'2026-2027','cc1'=>30,'sn1'=>70,'tpe1'=>0,'ratrappage1'=>70));
        foreach(array(910001,910011)as $id)DB::table('groupe_matieres')->insert(array('id_classe_matiere'=>$id,'id_matiere_mere'=>910010,'id_classe'=>910001,'id_nniveau'=>910001,'semestre'=>1));
        foreach(array(array(910011,1,27),array(910012,3,63))as $n)DB::table('note')->insert(array('id'=>$n[0],'id_campus'=>910001,'id_session'=>910011,'sequence'=>$n[1],'note'=>$n[2],'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910011,'id_matiere'=>910011,'annee_scolaire'=>'2026-2027'));
    }

    public function testNewModuleReportsForAllTutellesPreserveLegacyAndWeightUvCredits()
    {
        $this->moduleFixture();
        foreach(array('isabee','esstic','ngaoundere','soa','polytechnique')as $profile){
            DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>$profile));
            $service=new AcademicDocumentService;
            $context=$service->context($this->input(array('modele'=>$profile)),910001,'2026-2027');
            $original=$service->build($context);
            $data=(new gesecole\Services\AcademicModuleService)->build($original);
            $module=reset($data['results'][0]['modules']);
            $this->assertEquals(14,$module['average']); // (12 * 2 + 18 * 1) / 3
            $this->assertEquals(3,$module['credits']);
            $this->assertCount(2,$module['subjects']);
            $this->assertSame($original['results'][0]['summary'],$data['results'][0]['summary']);
            foreach(array('pv_modules','releve_modules')as $document){
                $html=(new academicDocumentController)->preview(Request::create('/','GET',$this->input(array('modele'=>$profile,'document'=>$document))))->render();
                $this->assertContains('Module scientifique',$html);
                $this->assertContains('Physique UV',$html);
                $this->assertContains('Moyenne du module /20',$html);
                $this->assertContains('14.00',$html);
            }
            foreach(array('pv','pv_groupes','releve')as $document){
                $html=(new academicDocumentController)->preview(Request::create('/','GET',$this->input(array('modele'=>$profile,'document'=>$document))))->render();
                $this->assertNotContains('Moyenne du module /20',$html);
            }
        }
    }

    public function testMissingUvStaysInNewModuleReportsAndDoesNotBecomeZero()
    {
        $this->moduleFixture();
        DB::table('note')->where('id_session',910011)->delete();
        $service=new AcademicDocumentService;$context=$service->context($this->input(),910001,'2026-2027');
        $this->assertCount(1,$service->build($context)['sessions']); // Legacy keeps its evaluated-only behaviour.
        $data=(new gesecole\Services\AcademicModuleService)->build($service->build($context,null,null,false));
        $module=reset($data['results'][0]['modules']);
        $this->assertCount(2,$module['subjects']);$this->assertNull($module['average']);$this->assertFalse($module['complete']);
        foreach(array('pv_modules','releve_modules')as $document){
            $html=(new academicDocumentController)->preview(Request::create('/','GET',$this->input(array('document'=>$document))))->render();
            $this->assertContains('Physique UV',$html);$this->assertContains('Incomplet',$html);
        }
        DB::table('note')->where('id_session',910001)->where('id_campus',910001)->update(array('note'=>0));
        $data=(new gesecole\Services\AcademicModuleService)->build($service->build($context));
        $module=reset($data['results'][0]['modules']);
        $this->assertSame(0.0,$module['average']);$this->assertTrue($module['complete']);
    }

    public function testModuleMembershipUsesIdsAndSemestersAndKeepsUngroupedUvs()
    {
        $this->moduleFixture();
        $service=new AcademicDocumentService;$context=$service->context($this->input(),910001,'2026-2027');
        $data=$service->build($context);
        // Reuse a programme in another semester with a different group of the same name.
        DB::table('matiere')->insert(array('id'=>910020,'nom'=>'Module scientifique'));
        DB::table('groupe_matieres')->insert(array('id_classe_matiere'=>910001,'id_matiere_mere'=>910020,'id_classe'=>910001,'id_nniveau'=>910001,'semestre'=>6));
        $session=clone $data['sessions'][0];$session->semestre=6;$data['sessions'][]=$session;
        $subject=$data['results'][0]['subjects'][0];$subject['semestre']=6;$data['results'][0]['subjects'][]=$subject;
        $session=clone $data['sessions'][0];$session->semestre=7;$data['sessions'][]=$session;
        $subject=$data['results'][0]['subjects'][0];$subject['semestre']=7;$data['results'][0]['subjects'][]=$subject;
        $grouped=(new gesecole\Services\AcademicModuleService)->build($data);
        $this->assertCount(3,$grouped['modules']);
        $this->assertArrayHasKey('s7:module910010',$grouped['modules']);
        $this->assertArrayHasKey('s5:module910010',$grouped['modules']);
        $this->assertArrayHasKey('s6:module910020',$grouped['modules']);
        DB::table('groupe_matieres')->where('id_classe_matiere',910011)->delete();
        $grouped=(new gesecole\Services\AcademicModuleService)->build($data);
        $this->assertArrayHasKey('s5:uv910011',$grouped['modules']);
        $this->assertFalse($grouped['modules']['s5:uv910011']['is_grouped']);
        DB::table('groupe_matieres')->insert(array('id_classe_matiere'=>910001,'id_matiere_mere'=>910020,'id_classe'=>910001,'id_nniveau'=>910001,'semestre'=>5));
        try{(new gesecole\Services\AcademicModuleService)->build($data);$this->fail('Affectation ambiguë acceptée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(422,$e->getStatusCode());}
    }

    public function testModuleCsvAndArchiveContainTheCorrectModuleAverage()
    {
        $this->moduleFixture();$controller=new academicDocumentController;
        foreach(array('pv_modules','releve_modules')as $document){
            $csv=$controller->exportCsv(Request::create('/','GET',$this->input(array('document'=>$document))))->getContent();
            $lines=explode("\n",trim($csv));$this->assertCount(3,$lines);
            $header=str_getcsv($lines[0],';');$this->assertEquals('Moyenne du module /20',$header[23]);
            foreach(array(1,2)as $index){$row=str_getcsv($lines[$index],';');$this->assertEquals('Module scientifique',$row[7]);$this->assertEquals(910010,$row[22]);$this->assertEquals(14,$row[23]);$this->assertEquals(3,$row[24]);}

        }
        $controller->generate(Request::create('/','POST',$this->input(array('document'=>'pv_modules','page_size'=>'a3'))));
        $generation=gesecole\AcademicDocumentGeneration::first();$original=$generation->verifiedContent();
        $this->assertContains('14.00',$original);$this->assertContains('Moyenne du module /20',$original);
        DB::table('note')->where('id',910012)->update(array('note'=>0));
        $this->assertSame($original,$controller->printSaved($generation->id)->getContent());
    }

    public function testZeroCreditModuleCannotInventAnAverage()
    {
        $this->moduleFixture();$service=new AcademicDocumentService;
        $data=$service->build($service->context($this->input(),910001,'2026-2027'));
        foreach($data['sessions']as $session)$session->credit=0;
        foreach($data['results'][0]['subjects']as &$subject)$subject['credit']=0;unset($subject);
        $data=(new gesecole\Services\AcademicModuleService)->build($data);
        $module=reset($data['results'][0]['modules']);
        $this->assertNull($module['average']);$this->assertTrue($module['complete']);$this->assertEquals(0,$module['credits']);
    }

    private function anonymatData($mode='matiere',$sequence=3,$sessionId=910001)
    {
        $service=new AcademicDocumentService;$context=$service->context($this->input(),910001,'2026-2027');$selected=null;
        foreach($service->sessions($context)as $session)if((int)$session->id===$sessionId)$selected=$session;
        $this->assertNotNull($selected);
        return array($context,$selected,new gesecole\Services\AcademicAnonymatService);
    }

    public function testAnonymatsAreStableForAllTutelleProfilesAndDoNotCreateNotes()
    {
        foreach(array('isabee','esstic','ngaoundere','soa','polytechnique')as $profile){
            DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>$profile));
            $service=new AcademicDocumentService;$context=$service->context($this->input(array('modele'=>$profile)),910001,'2026-2027');$sessions=$service->sessions($context);
            $anonymats=new gesecole\Services\AcademicAnonymatService;$before=DB::table('note')->count();
            $created=$anonymats->generate($context,$sessions[0],3,'matiere');$rows=$anonymats->rows($context,$sessions[0],3,'matiere');
            $this->assertEquals($profile==='isabee'?1:0,$created);$this->assertCount(1,$rows);
            $this->assertEquals(910001,$rows[0]->id_eleve);$this->assertEquals(0,$anonymats->generate($context,$sessions[0],3,'matiere'));
            $this->assertEquals($before,DB::table('note')->count());
        }
        $this->assertEquals(1,DB::table('academic_anonymat_audits')->count());
    }

    public function testAnonymousEntryHidesIdentitiesAndStoresGradesInTheRightSession()
    {
        list($context,$session,$anonymats)=$this->anonymatData();$anonymats->generate($context,$session,3,'matiere');$rows=$anonymats->rows($context,$session,3,'matiere');$row=$rows[0];
        $controller=new gesecole\Http\Controllers\eleves\academicAnonymatController;
        $input=$this->input(array('id_session'=>910001,'sequence'=>3,'mode_generation'=>'matiere'));
        $view=$controller->entry(Request::create('/','GET',$input));$html=$view->render();
        $this->assertContains($row->code,$html);$this->assertNotContains('TEST-910001',$html);$this->assertNotContains('TUDIANT TEST',$html);
        $this->assertNotContains('notes_anonymes[910001]',$html);
        $n=gesecole\note::find(910002);
        $input=array_merge($input,array('notes_anonymes'=>array($row->id=>'14'),'versions'=>array($row->id=>hash('sha256',$n->toJson())),'session_fingerprint'=>hash('sha256',json_encode($session))));
        $controller->storeAnonymousNotes(Request::create('/','POST',$input));
        $n=gesecole\note::find(910002);$this->assertEquals(49,$n->note);$this->assertSame($row->code,$n->anonymat);
        $this->assertEquals(910001,$n->id_session);$this->assertEquals(70,gesecole\note::find(910003)->note);$this->assertEquals(70,gesecole\note::find(910004)->note);
        $this->assertEquals(1,DB::table('academic_note_audits')->count());
        try{$controller->storeAnonymousNotes(Request::create('/','POST',$input));$this->fail('Version périmée acceptée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(409,$e->getStatusCode());}
        $current=gesecole\note::find(910002);$input['versions'][$row->id]=hash('sha256',$current->toJson());$input['notes_anonymes'][$row->id]='0';
        $controller->storeAnonymousNotes(Request::create('/','POST',$input));$this->assertEquals(0,gesecole\note::find(910002)->note);
        $current=gesecole\note::find(910002);$input['versions'][$row->id]=hash('sha256',$current->toJson());$input['supprimer']=array($row->id);
        $controller->storeAnonymousNotes(Request::create('/','POST',$input));$this->assertNull(gesecole\note::find(910002));
        $this->assertCount(1,$anonymats->rows($context,$session,3,'matiere'));
    }

    public function testCodeFromAnotherTutelleCannotBeUsedForAnonymousEntry()
    {
        list($context,$session,$anonymats)=$this->anonymatData();$anonymats->generate($context,$session,3,'matiere');
        DB::table('specialite_tutelle')->insert(array('id_tutelle'=>910002,'id_classe'=>910001));
        $service=new AcademicDocumentService;$other=$service->context($this->input(array('modele'=>'ngaoundere','id_tutelle'=>910002)),910001,'2026-2027');$ss=$service->sessions($other);
        $anonymats->generate($other,$ss[0],3,'matiere');$otherRows=$anonymats->rows($other,$ss[0],3,'matiere');
        $ownRows=$anonymats->rows($context,$session,3,'matiere');$this->assertNotEquals($ownRows[0]->code,$otherRows[0]->code);
        $n=gesecole\note::find(910002);
        $input=$this->input(array('id_session'=>910001,'sequence'=>3,'mode_generation'=>'matiere','notes_anonymes'=>array($otherRows[0]->id=>'20'),'versions'=>array($otherRows[0]->id=>hash('sha256',$n->toJson())),'session_fingerprint'=>hash('sha256',json_encode($session))));
        try{(new gesecole\Http\Controllers\eleves\academicAnonymatController)->storeAnonymousNotes(Request::create('/','POST',$input));$this->fail('Anonymat d’une autre tutelle accepté.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        $this->assertEquals(45,gesecole\note::find(910002)->note);$this->assertEquals(0,DB::table('academic_note_audits')->count());
        $different=$context;$different['id_campus']=910002;$this->assertCount(0,$anonymats->rows($different,$session,3,'matiere'));
        $different=$context;$different['year']='2027-2028';$this->assertCount(0,$anonymats->rows($different,$session,3,'matiere'));
    }

    public function testCommonExamCodesAreSharedByUvButSeparatedBySemesterAndRetake()
    {
        $this->moduleFixture();list($context,$session,$anonymats)=$this->anonymatData();
        $anonymats->generate($context,$session,3,'examen');$rows=$anonymats->rows($context,$session,3,'examen');
        $service=new AcademicDocumentService;$second=null;foreach($service->sessions($context)as $s)if((int)$s->id===910011)$second=$s;
        $shared=$anonymats->rows($context,$second,3,'examen');$this->assertEquals($rows[0]->id,$shared[0]->id);
        $this->assertCount(0,$anonymats->rows($context,$session,5,'examen'));
        $this->assertCount(0,$anonymats->rows($context,$session,3,'matiere'));
        $anonymats->generate($context,$session,5,'examen');$retake=$anonymats->rows($context,$session,5,'examen');$this->assertNotEquals($rows[0]->id,$retake[0]->id);
        $cm=(array)DB::table('classe_matiere')->where('id',910001)->first();$cm['id']=910030;$cm['semestre']=6;DB::table('classe_matiere')->insert($cm);
        $se=(array)DB::table('session_exams')->where('id',910001)->first();$se['id']=910030;$se['id_classe_matiere']=910030;$se['cc2']=30;$se['sn2']=70;$se['ratrappage2']=70;DB::table('session_exams')->insert($se);
        foreach($service->sessions($context)as $s)if((int)$s->id===910030)$semester2=$s;
        $this->assertCount(0,$anonymats->rows($context,$semester2,4,'examen'));
        $anonymats->generate($context,$semester2,4,'examen');$pair=$anonymats->rows($context,$semester2,4,'examen');$this->assertNotEquals($rows[0]->id,$pair[0]->id);
        try{$anonymats->generate($context,$semester2,3,'examen');$this->fail('Mauvais semestre accepté.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(422,$e->getStatusCode());}
    }

    public function testHistoricalCodesAreReusedAndDuplicatesBlockTheWholeGeneration()
    {
        DB::table('eleve')->insert(array('id'=>910002,'nom'=>'AUTRE TEST','matricule'=>'AUTRE-TEST'));
        DB::table('inscription')->insert(array('id'=>910002,'id_campus'=>910001,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_eleve'=>910002,'annee_scolaire'=>'2026-2027','desinscrire'=>0));
        DB::table('note')->where('id',910002)->update(array('anonymat'=>'HIST-001'));
        $n=(array)DB::table('note')->where('id',910002)->first();$n['id']=910020;$n['id_eleve']=910002;DB::table('note')->insert($n);
        list($context,$session,$anonymats)=$this->anonymatData();
        try{$anonymats->generate($context,$session,3,'matiere');$this->fail('Code historique dupliqué accepté.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(409,$e->getStatusCode());}
        $this->assertEquals(0,DB::table('academic_exam_anonymats')->count());
        DB::table('note')->where('id',910020)->update(array('anonymat'=>'HIST-002'));
        $this->assertEquals(2,$anonymats->generate($context,$session,3,'matiere'));$rows=$anonymats->rows($context,$session,3,'matiere');
        $this->assertSame('HIST-001',$rows[0]->code);$this->assertSame('HIST-002',$rows[1]->code);$this->assertEquals(45,gesecole\note::find(910002)->note);
    }

    public function testUsedCodesCannotBeRemovedAndUnusedRemovedCodesAreNotReassigned()
    {
        list($context,$session,$anonymats)=$this->anonymatData();$anonymats->generate($context,$session,3,'matiere');$rows=$anonymats->rows($context,$session,3,'matiere');$row=$rows[0];
        try{$anonymats->remove($context,$session,3,'matiere',$row->id,$row->version);$this->fail('Code utilisé supprimé.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(409,$e->getStatusCode());}
        DB::table('note')->where('id',910002)->delete();$anonymats->remove($context,$session,3,'matiere',$row->id,$row->version);
        $this->assertCount(0,$anonymats->rows($context,$session,3,'matiere'));$this->assertEquals(1,DB::table('academic_anonymat_audits')->where('operation','suppression')->count());
        $anonymats->generate($context,$session,3,'matiere');$fresh=$anonymats->rows($context,$session,3,'matiere');$this->assertNotEquals($row->code,$fresh[0]->code);
    }

    public function testAnonymousReportsAndExportsContainNoStudentIdentity()
    {
        list($context,$session,$anonymats)=$this->anonymatData();$anonymats->generate($context,$session,3,'matiere');$rows=$anonymats->rows($context,$session,3,'matiere');
        $controller=new academicDocumentController;$input=$this->input(array('document'=>'anonymat_report','id_session'=>910001,'sequence'=>3));
        $view=$controller->preview(Request::create('/','GET',$input));$html=$view->render();
        $this->assertContains($rows[0]->code,$html);$this->assertNotContains('TEST-910001',$html);$this->assertNotContains('TUDIANT TEST',$html);
        $csv=$controller->exportCsv(Request::create('/','GET',$input))->getContent();
        $this->assertContains($rows[0]->code,$csv);$this->assertNotContains('TEST-910001',$csv);$this->assertNotContains('TUDIANT TEST',$csv);
        $controller->generate(Request::create('/','POST',$input));$g=gesecole\AcademicDocumentGeneration::first();$this->assertEquals(1,$g->effectif);$original=$g->verifiedContent();
        DB::table('note')->where('id',910002)->update(array('note'=>0));$this->assertSame($original,$controller->printSaved($g->id)->getContent());
    }

    public function testTeachersCanEnterAssignedAnonymousGradesButCannotSeeCorrespondenceArchives()
    {
        list($context,$session,$anonymats)=$this->anonymatData();$anonymats->generate($context,$session,3,'matiere');
        $input=$this->input(array('document'=>'anonymat_codes','id_session'=>910001,'sequence'=>3));$controller=new academicDocumentController;
        $controller->generate(Request::create('/','POST',$input));$g=gesecole\AcademicDocumentGeneration::first();
        $teacher=new AcademicAnonymatTestTeacher;$teacher->id=910001;$teacher->id_personnel=910001;Auth::setUser($teacher);
        DB::table('dispense')->insert(array('id_personnel'=>910001,'id_matiere'=>910001,'id_classe'=>910001,'id_nniveau'=>910001,'annee_scolaire'=>'2026-2027'));
        $entry=(new gesecole\Http\Controllers\eleves\academicAnonymatController)->entry(Request::create('/','GET',$input));$this->assertNotNull($entry);
        $redirect=$controller->notes(Request::create('/','GET',$input));$this->assertContains('/notes/anonymats/saisie?',$redirect->getTargetUrl());
        $anonymousController=new gesecole\Http\Controllers\eleves\academicAnonymatController;
        $visible=$anonymousController->index(Request::create('/','GET',$input))->getData();$this->assertFalse(isset($visible['rows'][0]->student));$this->assertFalse(isset($visible['notes']));
        $n=gesecole\note::find(910002);$direct=array_merge($input,array('notes'=>array(910001=>'14'),'versions'=>array(910001=>hash('sha256',$n->toJson()))));
        try{$controller->saveNotes(Request::create('/','POST',$direct));$this->fail('Saisie nominative utilisée pour une évaluation anonymée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        $rows=$anonymats->rows($context,$session,3,'matiere');$row=$rows[0];
        $anonymous=array_merge($input,array('notes_anonymes'=>array($row->id=>'14'),'versions'=>array($row->id=>hash('sha256',$n->toJson())),'session_fingerprint'=>hash('sha256',json_encode($session))));
        $anonymousController->storeAnonymousNotes(Request::create('/','POST',$anonymous));$this->assertEquals(49,gesecole\note::find(910002)->note);
        try{$controller->printSaved($g->id);$this->fail('Correspondance visible au correcteur.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        try{(new gesecole\Http\Controllers\eleves\academicAnonymatController)->generateCodes(Request::create('/','POST',$input));$this->fail('Correcteur autorisé à générer.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        DB::table('verrou_note')->insert(array('sequence'=>3,'annee_scolaire'=>'2026-2027','id_campus'=>910001,'target'=>'note_sequence','is_active'=>true));
        try{(new gesecole\Http\Controllers\eleves\academicAnonymatController)->entry(Request::create('/','GET',$input));$this->fail('Verrou ignoré.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(423,$e->getStatusCode());}
    }

    public function testAnonymatCanExistBeforeGradesAndCreateAZeroGradeWithoutDuplicates()
    {
        DB::table('note')->where('id',910002)->delete();
        list($context,$session,$anonymats)=$this->anonymatData();$before=DB::table('note')->count();
        $anonymats->generate($context,$session,3,'matiere');$this->assertEquals($before,DB::table('note')->count());$rows=$anonymats->rows($context,$session,3,'matiere');$row=$rows[0];
        $input=$this->input(array('id_session'=>910001,'sequence'=>3,'mode_generation'=>'matiere','notes_anonymes'=>array($row->id=>'0'),'versions'=>array($row->id=>'new'),'session_fingerprint'=>hash('sha256',json_encode($session))));
        $controller=new gesecole\Http\Controllers\eleves\academicAnonymatController;$controller->storeAnonymousNotes(Request::create('/','POST',$input));
        $notes=$anonymats->notes($context,$session,3);$this->assertCount(1,$notes);$this->assertEquals(0,$notes[910001]->note);$this->assertSame($row->code,$notes[910001]->anonymat);
        $input['versions'][$row->id]=hash('sha256',$notes[910001]->toJson());$controller->storeAnonymousNotes(Request::create('/','POST',$input));
        $this->assertEquals($before+1,DB::table('note')->count());$this->assertCount(1,$anonymats->notes($context,$session,3));
    }

    private function juryPreview($extra=array())
    {
        $request=Request::create('/','GET',$this->input(array_merge(array('operation'=>'previsualiser','type_deliberation'=>'matiere_note','valeur_cible'=>10),$extra)));
        return (new gesecole\Http\Controllers\eleves\academicDeliberationController)->index($request)->getData();
    }
    private function juryApply($view,$extra=array())
    {
        $input=array();foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode')as $key)$input[$key]=$view['context'][$key];
        $keys=array();foreach($view['plan']['proposals']as $p)$keys[]=$p['key'];
        $input=array_merge($input,array('payload'=>$view['payload'],'signature'=>$view['signature'],'propositions'=>$keys,'motif'=>'Décision du jury de test'),$extra);
        return (new gesecole\Http\Controllers\eleves\academicDeliberationController)->apply(Request::create('/','POST',$input));
    }
    public function testJuryCreditTargetsFollowEachTutellesActualCapitalizationRule()
    {
        foreach(array('isabee'=>10,'esstic'=>8,'soa'=>8,'ngaoundere'=>8,'polytechnique'=>10)as $profile=>$threshold){
            DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>$profile));
            DB::table('note')->where('id',910002)->update(array('note'=>20));
            $view=$this->juryPreview(array('modele'=>$profile,'type_deliberation'=>'credit','valeur_cible'=>45));
            $this->assertCount(1,$view['plan']['proposals']);$change=$view['plan']['proposals'][0]['changes'][0];
            $this->assertEquals($threshold,$change['final_after']);$this->assertEquals($threshold*5-15,$change['note_after']);
            $this->juryApply($view);$this->assertEquals($threshold*5-15,gesecole\note::find(910002)->note);
            $this->assertEquals(15,gesecole\note::find(910001)->note);$this->assertEquals(70,gesecole\note::find(910003)->note);$this->assertEquals(70,gesecole\note::find(910004)->note);
        }
        $this->assertEquals(5,DB::table('academic_deliberations')->count());
    }
    public function testJuryIntervalsUseTheChosenBaseAndNeverInventMissingMarks()
    {
        DB::table('note')->where('id',910002)->update(array('note'=>20));
        $before=DB::table('note')->count();$view=$this->juryPreview(array('base'=>'note100','valeur_cible'=>50,'interval_min'=>35,'interval_max'=>35,'min_inclus'=>1,'max_inclus'=>1));
        $this->assertCount(1,$view['plan']['proposals']);$this->assertEquals(10,$view['plan']['proposals'][0]['changes'][0]['final_after']);
        $exclusive=$this->juryPreview(array('base'=>'note100','valeur_cible'=>50,'interval_min'=>35,'interval_max'=>35,'max_inclus'=>1));$this->assertCount(0,$exclusive['plan']['proposals']);
        $wrongSemester=$this->juryPreview(array('semestre'=>6));$this->assertCount(0,$wrongSemester['plan']['proposals']);
        $unreachable=$this->juryPreview(array('valeur_cible'=>20));$this->assertCount(0,$unreachable['plan']['proposals']);
        $this->assertEquals($before,DB::table('note')->count());$this->assertEquals(0,DB::table('academic_deliberations')->count());
        DB::table('note')->where('id',910002)->delete();$absent=$this->juryPreview();$this->assertCount(0,$absent['plan']['proposals']);$this->assertEquals($before-1,DB::table('note')->count());
    }
    public function testJuryModuleMeanProtectsValidatedUvsAndIncludesAllConfiguredUvs()
    {
        $this->moduleFixture();DB::table('note')->where('id',910002)->update(array('note'=>20));
        DB::table('note')->where('id',910011)->update(array('note'=>15));DB::table('note')->where('id',910012)->update(array('note'=>35));
        DB::table('matiere')->insert(array('id'=>910020,'nom'=>'Module autre semestre'));
        DB::table('groupe_matieres')->insert(array('id_classe_matiere'=>910001,'id_matiere_mere'=>910020,'id_classe'=>910001,'id_nniveau'=>910001,'semestre'=>6));
        $individual=$this->juryPreview();$this->assertSame('Module scientifique',$individual['plan']['proposals'][0]['changes'][0]['module']);
        $view=$this->juryPreview(array('type_deliberation'=>'ue_note','valeur_cible'=>10));
        $this->assertCount(1,$view['plan']['proposals']);$p=$view['plan']['proposals'][0];$this->assertEquals(8,$p['module_before']);$this->assertEquals(10,$p['module_after']);$this->assertCount(1,$p['changes']);
        $this->juryApply($view);$this->assertEquals(35,gesecole\note::find(910002)->note);$this->assertEquals(35,gesecole\note::find(910012)->note);
        DB::table('note')->where('id',910002)->update(array('note'=>20));DB::table('note')->where('id',910012)->delete();
        $incomplete=$this->juryPreview(array('type_deliberation'=>'ue_note'));$this->assertCount(0,$incomplete['plan']['proposals']);
    }
    public function testJuryModuleBundlesAreAtomicWithoutAwardingAllModuleCredits()
    {
        $this->moduleFixture();DB::table('note')->where('id',910002)->update(array('note'=>20));DB::table('note')->where('id',910011)->update(array('note'=>15));DB::table('note')->where('id',910012)->update(array('note'=>20));
        $view=$this->juryPreview(array('type_deliberation'=>'ue_note','valeur_cible'=>14));$this->assertCount(1,$view['plan']['proposals']);$p=$view['plan']['proposals'][0];$this->assertCount(2,$p['changes']);
        try{$this->juryApply($view,array('propositions'=>array(str_repeat('f',64))));$this->fail('Proposition forgée acceptée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(422,$e->getStatusCode());}
        $this->assertEquals(20,gesecole\note::find(910002)->note);$this->juryApply($view);
        $service=new gesecole\Services\AcademicDeliberationService;$data=$service->data($view['context']);$module=reset($data['results'][0]['modules']);
        $this->assertEquals(14,$module['average']);$this->assertEquals(2,$data['results'][0]['summary']['credits']);$this->assertEquals(2,DB::table('academic_deliberation_items')->count());
    }
    public function testJuryModuleCreditSelectionUsesUvCreditsAtTheTutelleThreshold()
    {
        $this->moduleFixture();DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>'soa'));
        DB::table('note')->where('id',910002)->update(array('note'=>20));DB::table('note')->where('id',910011)->update(array('note'=>15));DB::table('note')->where('id',910012)->update(array('note'=>35));
        foreach(array('matiere_credit','ue_credit')as $type){
            $options=array('modele'=>'soa','type_deliberation'=>$type,'valeur_cible'=>$type==='matiere_credit'?2:3,'portee'=>'selection','matieres'=>array('910001:5'),'modules'=>array('s5:module910010'));
            $view=$this->juryPreview($options);$this->assertCount(1,$view['plan']['proposals']);$this->assertEquals(8,$view['plan']['proposals'][0]['changes'][0]['final_after']);
            $this->juryApply($view);$service=new gesecole\Services\AcademicDeliberationService;$data=$service->data($view['context']);$this->assertEquals(3,$data['results'][0]['summary']['credits']);
            DB::table('note')->where('id',910002)->update(array('note'=>20));
        }
        $impossible=$this->juryPreview(array('modele'=>'soa','type_deliberation'=>'credit','valeur_cible'=>60));$this->assertCount(0,$impossible['plan']['proposals']);
    }
    public function testJuryApplicationPreservesMetadataAndAuditsBeforeAfterIdempotently()
    {
        DB::table('note')->where('id',910002)->update(array('note'=>20,'anonymat'=>'CODE-JURY','mois_examen'=>'Avril'));
        $view=$this->juryPreview(array('type_deliberation'=>'individuelle','id_eleve'=>910001));$this->assertCount(1,$view['plan']['proposals']);
        $this->assertContains('aucune note',$this->juryPreviewHtml($view));
        $response=$this->juryApply($view);$this->assertContains('id_lot=',$response->getTargetUrl());
        $note=gesecole\note::find(910002);$this->assertEquals(35,$note->note);$this->assertSame('CODE-JURY',$note->anonymat);$this->assertSame('Avril',$note->mois_examen);
        $this->assertEquals(910001,$note->id_session);$this->assertEquals(910001,$note->id_campus);$this->assertEquals(1,DB::table('academic_note_audits')->where('operation','deliberation')->count());
        $this->juryApply($view);$this->assertEquals(1,DB::table('academic_deliberations')->count());$this->assertEquals(1,DB::table('academic_deliberation_items')->count());
        try{$this->juryApply($view,array('motif'=>'Un autre motif de jury'));$this->fail('Aperçu réutilisé avec une autre décision.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(409,$e->getStatusCode());}
        $d=json_decode(DB::table('academic_deliberation_items')->first()->details,true);$this->assertEquals(7,$d['final_before']);$this->assertEquals(10,$d['final_after']);$this->assertEquals('ADMIS',$d['bilan']['apres']['decision']);
    }
    private function juryPreviewHtml($view){return view('eleves.academic_documents.deliberations',$view)->render();}
    public function testJurySnapshotRejectsAlteredPayloadOtherTutellesAndStaleComponentNotes()
    {
        DB::table('note')->where('id',910002)->update(array('note'=>20));$view=$this->juryPreview();
        $payload=json_decode(base64_decode($view['payload']),true);$payload['criteria']['valeur_cible']=20;
        try{$this->juryApply($view,array('payload'=>base64_encode(json_encode($payload))));$this->fail('Cible client falsifiée acceptée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        DB::table('specialite_tutelle')->insert(array('id_tutelle'=>910002,'id_classe'=>910001));
        try{$this->juryApply($view,array('id_tutelle'=>910002,'modele'=>'ngaoundere'));$this->fail('Aperçu appliqué à une autre tutelle.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        DB::table('campus')->insert(array('id'=>910002,'nom'=>'Campus extérieur'));
        DB::table('campus_specialite')->insert(array('id_campus'=>910002,'id_classe'=>910001));session(array('campus_id'=>910002));
        try{$this->juryApply($view);$this->fail('Aperçu appliqué à un autre campus.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        session(array('campus_id'=>910001,'year'=>'2027-2028'));
        try{$this->juryApply($view);$this->fail('Aperçu appliqué à une autre année.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        session(array('year'=>'2026-2027'));
        DB::table('note')->where('id',910001)->update(array('note'=>16));
        try{$this->juryApply($view);$this->fail('CC modifié depuis l’aperçu ignoré.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(409,$e->getStatusCode());}
        $this->assertEquals(20,gesecole\note::find(910002)->note);$this->assertEquals(0,DB::table('academic_deliberations')->count());
    }
    public function testJurySnapshotRejectsChangedWeightsAndActiveLocks()
    {
        DB::table('note')->where('id',910002)->update(array('note'=>20));$view=$this->juryPreview();DB::table('session_exams')->where('id',910001)->update(array('cc1'=>40,'sn1'=>60));
        try{$this->juryApply($view);$this->fail('Pondération modifiée ignorée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(409,$e->getStatusCode());}
        $view=$this->juryPreview();DB::table('verrou_note')->insert(array('sequence'=>3,'annee_scolaire'=>'2026-2027','id_campus'=>910001,'target'=>'note_sequence','is_active'=>true));
        try{$this->juryApply($view);$this->fail('Verrou de notes ignoré.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(423,$e->getStatusCode());}
        $this->assertEquals(20,gesecole\note::find(910002)->note);$this->assertEquals(0,DB::table('academic_note_audits')->count());
    }
    public function testJuryRetakeAdjustsOnlyTheChosenExaminationAndHonorsNormalPriority()
    {
        DB::table('note')->where('id',910002)->update(array('note'=>20));$n=(array)DB::table('note')->where('id',910002)->first();$n['id']=910030;$n['sequence']=5;$n['note']=25;DB::table('note')->insert($n);
        $view=$this->juryPreview();$this->assertEquals(5,$view['plan']['proposals'][0]['changes'][0]['sequence']);$this->juryApply($view);
        $this->assertEquals(35,gesecole\note::find(910030)->note);$this->assertEquals(20,gesecole\note::find(910002)->note);
        DB::table('note')->where('id',910002)->update(array('note'=>45));$blocked=$this->juryPreview(array('evaluation'=>'rattrapage','valeur_cible'=>13));$this->assertCount(0,$blocked['plan']['proposals']);
    }
    public function testJuryReportIsScopedAndItsSnapshotsSurviveLaterGradeChanges()
    {
        DB::table('note')->where('id',910002)->update(array('note'=>20));$view=$this->juryPreview();$this->juryApply($view);
        $controller=new gesecole\Http\Controllers\eleves\academicDeliberationController;$input=$this->input(array('rapport_semestre'=>5,'rapport_examen'=>'normal'));
        $report=$controller->report(Request::create('/','GET',$input))->getData();$this->assertEquals(1,$report['report']['metrics']->lignes);$this->assertContains('Décision',$controller->report(Request::create('/','GET',$input))->render());
        $csv=$controller->exportReport(Request::create('/','GET',$input))->getContent();$this->assertNotContains('Code anonymat',$csv); // Codes d’anonymat absents des colonnes du rapport.
        $this->assertContains('TEST-910001',$csv);$this->assertContains('7;10;20;35',$csv);
        foreach(array('a4','a3')as $format){$html=$controller->printReport(Request::create('/','GET',array_merge($input,array('page_size'=>$format))))->render();$this->assertContains('size: '.strtoupper($format).' landscape;',$html);$this->assertContains('hintelinstitut@gmail.com',$html);}
        $parent=new academicDocumentController;$parent->generate(Request::create('/','POST',array_merge($input,array('document'=>'deliberation_rapport','page_size'=>'a3'))));$g=gesecole\AcademicDocumentGeneration::first();$original=$g->verifiedContent();$this->assertEquals(1,$g->effectif);
        DB::table('note')->where('id',910002)->update(array('note'=>5));$this->assertSame($original,$parent->printSaved($g->id)->getContent());
        $none=$controller->report(Request::create('/','GET',array_merge($input,array('rapport_examen'=>'rattrapage'))))->getData();$this->assertEquals(0,$none['report']['metrics']->lignes);
        $other=$controller->report(Request::create('/','GET',$this->input(array('modele'=>'ngaoundere','id_tutelle'=>910002))))->getData();$this->assertEquals(0,$other['report']['metrics']->lignes);
        $teacher=new AcademicAnonymatTestTeacher;$teacher->id=910001;Auth::setUser($teacher);
        try{$controller->report(Request::create('/','GET',$input));$this->fail('Rapport administratif visible au correcteur.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
        try{$parent->printSaved($g->id);$this->fail('Archive de délibération visible au correcteur.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertEquals(403,$e->getStatusCode());}
    }

    public function testLegacyDeliberationMustChooseATutelleBeforeShowingItsStudents()
    {
        $request=Request::create('/eleves/notes/deliberations/910001','GET',array('id_classe'=>910001,'cycle'=>910001,'nniveau'=>910001));
        $route=new Illuminate\Routing\Route(array('GET'),'/eleves/notes/deliberations/{id}',array('uses'=>'gesecole\\Http\\Controllers\\eleves\\noteController@deliberationsEtudiants'));
        $request->setRouteResolver(function()use($route){return $route;});
        $response=(new gesecole\Http\Middleware\ProtectTutelleNotes)->handle($request,function(){return response('legacy');});
        $this->assertEquals(302,$response->getStatusCode());$this->assertContains('choisir_deliberation=1',$response->getTargetUrl());
    }

    public function testNgaoundereTranscriptRendersSeparateSemesterPages()
    {
        DB::table('specialite_tutelle')->insert(array('id_tutelle'=>910002,'id_classe'=>910001));
        DB::table('eleve')->where('id',910001)->update(array('nom'=>'<script>bad()</script>'));
        DB::table('classe_matiere')->insert(array('id'=>910005,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>910001,'coef'=>30,'semestre'=>6));
        DB::table('session_exams')->insert(array('id'=>910005,'id_tutelle'=>910002,'id_classe_matiere'=>910005,'annee_academique'=>'2026-2027','cc2'=>30,'sn2'=>70,'tpe2'=>0,'ratrappage2'=>70));
        DB::table('note')->insert(array(array('id'=>910005,'id_campus'=>910001,'id_session'=>910005,'sequence'=>2,'note'=>15,'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910005,'id_matiere'=>910001,'annee_scolaire'=>'2026-2027'),array('id'=>910006,'id_campus'=>910001,'id_session'=>910005,'sequence'=>4,'note'=>45,'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910005,'id_matiere'=>910001,'annee_scolaire'=>'2026-2027')));
        $input=$this->input(array('modele'=>'ngaoundere','id_tutelle'=>910002,'document'=>'releve','periode'=>'annuel'));
        $html=(new academicDocumentController)->preview(Request::create('/','GET',$input))->render();
        $this->assertEquals(2,substr_count($html,'class="ndere-transcript individual"'));
        $this->assertContains('SEMESTRE 5',$html);
        $this->assertContains('SEMESTRE 6',$html);
        $this->assertEquals(1,substr_count($html,'Moyenne generale /20'));
        $this->assertNotContains('<script>bad()</script>',$html);
    }

    public function testEssticTranscriptUsesConfiguredModulesAndScopedGrades()
    {
        DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>'esstic'));
        DB::table('eleve')->where('id',910001)->update(array('nom'=>'<script>bad()</script>'));
        $html=(new academicDocumentController)->preview(Request::create('/','GET',$this->input(array('modele'=>'esstic','document'=>'releve'))))->render();
        $this->assertContains('class="individual esstic-transcript"',$html);
        $this->assertContains('RELEVÉ DE NOTES / TRANSCRIPT',$html);
        $this->assertContains('Calcul test',$html);
        $this->assertContains('Crédits obtenus',$html);
        $this->assertContains('alt="Logo ESSTIC"',$html);
        $this->assertNotContains('<script>bad()</script>',$html);
        $a3=(new academicDocumentController)->preview(Request::create('/','GET',$this->input(array('modele'=>'esstic','document'=>'releve','page_size'=>'a3'))))->render();
        $this->assertContains('size: A3 portrait;',$a3);
        $this->assertContains('class="individual esstic-transcript"',$a3);
    }

    public function testAllDocumentFamiliesRenderWithEscapedStudentData()
    {
        DB::table('eleve')->where('id',910001)->update(array('nom'=>'<script>bad()</script>'));
        $controller=new academicDocumentController;
        foreach(array_keys(config('academic_documents.documents'))as $document){
            foreach(array('a4','a3') as $format){
            $view=$controller->preview(Request::create('/','GET',$this->input(array('document'=>$document,'page_size'=>$format,'id_session'=>910001,'sequence'=>3))));
            $html=$view->render();
            $this->assertContains('<!DOCTYPE html>',$html);
            $this->assertContains('document-'.$document.' paper-'.$format.'"',$html);
            $orientation=in_array($document,array('pv','pv_groupes','pv_modules','attestation','deliberation_rapport','rattrapage_global','report','programme'))?'landscape':'portrait';
            $this->assertContains('size: '.strtoupper($format).' '.$orientation.';',$html);
            $this->assertContains('alt="Logo du campus"',$html);
            $this->assertContains('data:image/jpeg;base64,',$html);
            $this->assertContains('T4-09342',$html);
            $this->assertContains('16-0855/MINESUP/ESUP',$html);
            $this->assertContains('<footer class="document-footer">',$html);
            $this->assertContains('hintelinstitut@gmail.com',$html);
            $this->assertContains('Alouettes',$html);
            $this->assertNotContains('<script>bad()</script>',$html);
            }
        }
    }

    public function testTranscriptSelectionPagesDoNotDuplicateClassesOrScripts()
    {
        $filiere = new \gesecole\niveau;
        $filiere->id = 910001;
        $filiere->nom = 'Filiere test';
        $classe = new \gesecole\classe;
        $classe->id = 910001;
        $classe->id_niveau = $filiere->id;
        $classe->nom = 'Specialite test';
        $autreClasse = new \gesecole\classe;
        $autreClasse->id = 910002;
        $autreClasse->id_niveau = $filiere->id;
        $autreClasse->nom = 'Autre specialite test';
        $cycles = new \Illuminate\Support\Collection(array((object)array('id'=>1,'nom'=>'Licence'),(object)array('id'=>2,'nom'=>'Master')));
        $values = array('titre'=>'Releves', 'classes'=>new \Illuminate\Support\Collection(array($classe,$autreClasse)),
            'classesByNiveau'=>new \Illuminate\Support\Collection(array($filiere->id=>new \Illuminate\Support\Collection(array($classe,$autreClasse)))),
            'niveaux'=>new \Illuminate\Support\Collection(array($filiere)), 'cycles'=>$cycles,
            'nniveaux'=>new \Illuminate\Support\Collection(array((object)array('id'=>1,'id_cycle'=>1,'nom'=>'L1'))));

        $soa = view('eleves.classe_releves_soa', $values)->render();
        $this->assertEquals(1, substr_count($soa, 'id="generate_releves_soa"'));
        $this->assertEquals(1, substr_count($soa, 'function chooseSoaGeneration'));
        $this->assertEquals(1, substr_count($soa, 'class="special" href="#" data-classe="910001"'));

        $ngaoundere = view('eleves.classe_releves_ndere', $values)->render();
        $this->assertEquals(1, substr_count($ngaoundere, 'class="col-md-6 niveau-block"'));
        $this->assertEquals(1, substr_count($ngaoundere, 'data-classe="910001"'));

        $ebolowa = view('eleves.classe_releves_estic', $values)->render();
        $this->assertEquals(2, substr_count($ebolowa, 'class="special cycle-niveau-specialite-link"'));
    }

    public function testTutelleDocumentSelectionOnlyLoadsAssignedExamSessions()
    {
        $view = (new academicDocumentController)->index(Request::create('/eleves/documents-tutelles', 'GET'));
        $choices = $view->getData()['contextChoices'];
        $this->assertCount(1, $choices);
        $this->assertEquals(910001, $choices[0]->id_tutelle);
        $this->assertContains('academic-context', $view->render());
        DB::table('specialite_tutelle')->delete();
        $empty = (new academicDocumentController)->index(Request::create('/eleves/documents-tutelles', 'GET'));
        $this->assertCount(0, $empty->getData()['contextChoices']);
    }

    public function testEssticCertificateMatchesTheSuppliedModel()
    {
        DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>'esstic','nom'=>'Université de Yaoundé II - SOA'));
        DB::table('classe_matiere')->insert(array('id'=>910005,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>910001,'coef'=>45,'semestre'=>6));
        DB::table('session_exams')->insert(array('id'=>910005,'id_tutelle'=>910001,'id_classe_matiere'=>910005,'annee_academique'=>'2026-2027','cc2'=>30,'sn2'=>70,'tpe2'=>0,'ratrappage2'=>70));
        foreach(array(2=>15,4=>45)as $sequence=>$value)DB::table('note')->insert(array('id'=>910010+$sequence,'id_campus'=>910001,'id_session'=>910005,'sequence'=>$sequence,'note'=>$value,'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910005,'id_matiere'=>910001,'annee_scolaire'=>'2026-2027'));

        $controller = new academicDocumentController;
        foreach (array('a4','a3') as $format) {
            $input = $this->input(array('modele'=>'esstic','document'=>'attestation','id_eleve'=>910001,'page_size'=>$format,'attestation_jury_date'=>'2026-09-15'));
            $html = $controller->preview(Request::create('/','GET',$input))->render();
            $text = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
            $this->assertContains('ATTESTATION DE RÉUSSITE DE LICENCE PROFESSIONNELLE', $text);
            $this->assertContains('PROFESSIONAL LICENSE SUCCESS TESTIMONIAL', $text);
            $this->assertContains('N°001/ISH/YDE II-ESSTIC', $text);
            $this->assertContains('15 septembre 2026', $text);
            $this->assertContains('Mention globale', $text);
            $this->assertContains('Yaoundé, le ........................', $text);
            $this->assertContains('alt="Logo ESSTIC"', $html);
            $this->assertContains('esstic-certificate-frame', $html);
            $this->assertNotContains('Moyenne :', $text);
            $this->assertNotContains('Date de soutenance', $text);
        }
    }

    public function testIsabeeCertificateMatchesTheSuppliedModel()
    {
        DB::table('classe_matiere')->insert(array('id'=>910005,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>910001,'coef'=>45,'semestre'=>6));
        DB::table('session_exams')->insert(array('id'=>910005,'id_tutelle'=>910001,'id_classe_matiere'=>910005,'annee_academique'=>'2026-2027','cc2'=>30,'sn2'=>70,'tpe2'=>0,'ratrappage2'=>70));
        foreach(array(2=>15,4=>45) as $sequence=>$value) DB::table('note')->insert(array('id'=>910010+$sequence,'id_campus'=>910001,'id_session'=>910005,'sequence'=>$sequence,'note'=>$value,'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910005,'id_matiere'=>910001,'annee_scolaire'=>'2026-2027'));
        $controller = new academicDocumentController;
        foreach (array('a4','a3') as $format) {
            $input = $this->input(array('modele'=>'isabee','document'=>'attestation','id_eleve'=>910001,'page_size'=>$format,'attestation_jury_date'=>'2026-09-15'));
            $html = $controller->preview(Request::create('/','GET',$input))->render();
            $text = html_entity_decode($html, ENT_QUOTES, 'UTF-8');
            $this->assertContains('ATTESTATION DE RÉUSSITE INGENIEUR DE TRAVAUX', $text);
            $this->assertContains('ENGINEER SUCCESS TESTIMONIAL', $text);
            $this->assertContains('15 Avril 2024', $text);
            $this->assertContains('15 septembre 2026', $text);
            $this->assertContains('Mention Globale', $text);
            $this->assertContains('EBOLOWA, le ........................', $text);
            $this->assertContains('Le Directeur de l\'ISABEE', $text);
            $this->assertContains('alt="Logo ISABEE"', $html);
            $this->assertContains('isabee-corner-tl', $html);
            $this->assertNotContains('Moyenne :', $text);
        }
    }

    public function testBilingualCertificatesUseSelectedTutelleAndPreserveIssueDetailsInArchives()
    {
        $controller=new academicDocumentController;
        DB::table('classe_matiere')->insert(array('id'=>910005,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'id_matiere'=>910001,'coef'=>45,'semestre'=>6));
        DB::table('session_exams')->insert(array('id'=>910005,'id_tutelle'=>910001,'id_classe_matiere'=>910005,'annee_academique'=>'2026-2027','cc2'=>30,'sn2'=>70,'tpe2'=>0,'ratrappage2'=>70));
        foreach(array(2=>15,4=>45)as $sequence=>$value)DB::table('note')->insert(array('id'=>910010+$sequence,'id_campus'=>910001,'id_session'=>910005,'sequence'=>$sequence,'note'=>$value,'id_eleve'=>910001,'id_classe'=>910001,'id_classe_matiere'=>910005,'id_matiere'=>910001,'annee_scolaire'=>'2026-2027'));
        foreach(array('soa'=>'Le Doyen','esstic'=>'Le Directeur de l’ESSTIC','polytechnique'=>'Le Directeur de l’École Nationale Supérieure Polytechnique de Yaoundé','isabee'=>'Le Directeur de l’ISABEE','ngaoundere'=>'Le Directeur de l’Institut Universitaire de Technologie (IUT)')as $model=>$authority){
            DB::table('tutelles_academiques')->where('id',910001)->update(array('modele_documents'=>$model,'nom'=>'Université '.$model));
            $input=$this->input(array('modele'=>$model,'document'=>'attestation','id_eleve'=>910001,'attestation_reference'=>'REF <script>unsafe</script>','attestation_jury_date'=>'2026-09-15','attestation_issue_date'=>'2026-10-30'));
            foreach(array('a4','a3')as $format){
                $html=$controller->preview(Request::create('/','GET',array_merge($input,array('page_size'=>$format))))->render();
                $this->assertContains($authority,html_entity_decode($html,ENT_QUOTES,'UTF-8'));$this->assertContains('Université '.$model,html_entity_decode($html,ENT_QUOTES,'UTF-8'));
                $this->assertContains('SUCCESS TESTIMONIAL',$html);
                $this->assertContains($model==='esstic'?'15 septembre 2026':'15/09/2026',$html);
                $this->assertContains('30/10/2026',$html);
                $this->assertContains('TEST-910001',$html);
                if($model!=='esstic')$this->assertContains('12.00/20',$html);
                $this->assertNotContains('<script>unsafe</script>',$html);
                if(in_array($model,array('soa','ngaoundere','esstic'))){
                    $suffix=$model==='soa'?'/ISH/YDE II - SOA':($model==='esstic'?'/ISH/YDE II-ESSTIC':'UN/IUT/UN/CD-ISH/CS-ISH');
                    $this->assertContains('001'.$suffix,$html);
                    $this->assertNotContains('REF &lt;script&gt;unsafe&lt;/script&gt;',$html);
                }else $this->assertContains('REF &lt;script&gt;unsafe&lt;/script&gt;',$html);
                $this->assertContains('size: '.strtoupper($format).' landscape;',$html);
                $this->assertContains('attestation_jury_date=2026-09-15',html_entity_decode($html));
                if(in_array($model,array('soa','ngaoundere','esstic'))) $this->assertNotContains('attestation_reference=',html_entity_decode($html));
                if($model==='esstic'){
                    $this->assertContains('certificate-esstic',$html);
                    $this->assertContains('alt="Logo ESSTIC"',$html);
                    $this->assertContains('N&deg;001/ISH/YDE II-ESSTIC',$html);
                    $this->assertContains('PROFESSIONAL LICENSE SUCCESS TESTIMONIAL',$html);
                    $this->assertContains('Mention globale',$html);
                    $this->assertContains('Le Directeur de l’ESSTIC',html_entity_decode($html,ENT_QUOTES,'UTF-8'));
                    $this->assertNotContains('12.00/20',$html);
                }
                if($model==='soa'){
                    $this->assertTrue(strpos($html,'alt="Logo du campus"')<strpos($html,'alt="Logo tutelle"'));
                    $this->assertContains('certificate-soa',$html);
                    $this->assertContains('ATTESTATION DE R&Eacute;USSITE DE',$html);
                    $this->assertContains('soa-identity',$html);
                    $this->assertContains('soa-signatures',$html);
                    $this->assertContains('Yaoundé, le 30/10/2026',html_entity_decode($html,ENT_QUOTES,'UTF-8'));
                }
                if($model==='polytechnique'){
                    $this->assertContains('alt="Logo Polytechnique ENSPY"',$html);
                    $this->assertContains('data:image/png;base64,',$html);
                    $this->assertNotContains('certificate-conventions">',$html);
                    $this->assertNotContains('Le Doyen',html_entity_decode($html,ENT_QUOTES,'UTF-8'));
                    $this->assertTrue(strpos($html,'alt="Logo du campus"')<strpos($html,'alt="Logo Polytechnique ENSPY"'));
                }
                if($model==='ngaoundere'){
                    $this->assertContains('alt="Logo IUT"',$html);
                    $this->assertTrue(strpos($html,'alt="Logo du campus"')<strpos($html,'alt="Logo tutelle"'));
                    $this->assertTrue(strpos($html,'alt="Logo tutelle"')<strpos($html,'alt="Logo IUT"'));
                    $this->assertContains('ATTESTATION DE R&Eacute;USSITE DE',$html);
                    $this->assertContains('certificate-reference',$html);
                    $this->assertContains('ngaoundere-identity',$html);
                    $decoded=html_entity_decode($html,ENT_QUOTES,'UTF-8');
                    $this->assertContains('17 février 2021',$decoded);$this->assertContains('Convention Spécifique',$decoded);
                    $this->assertContains('Ngaoundéré, le 30/10/2026',$decoded);
                    $this->assertContains('Le Directeur de l’Institut Supérieur Hintel',$decoded);
                }
            }
        }
        $controller->generate(Request::create('/','POST',$input));
        $g=gesecole\AcademicDocumentGeneration::first();$original=$g->verifiedContent();
        $snapshot=json_decode($g->contexte,true);$this->assertSame('2026-09-15',$snapshot['document_selection']['attestation_jury_date']);
        $this->assertFalse(isset($snapshot['document_selection']['attestation_reference']));
        $this->assertSame('001UN/IUT/UN/CD-ISH/CS-ISH',$snapshot['attestation_numbers'][910001]);
        $this->assertContains('N&deg;001UN/IUT/UN/CD-ISH/CS-ISH',$original);
        DB::table('note')->where('id',910002)->update(array('note'=>5));
        $this->assertSame($original,$controller->printSaved($g->id)->getContent());
        $empty=$controller->preview(Request::create('/','GET',$input))->render();
        $this->assertNotContains('SUCCESS TESTIMONIAL',$empty);$this->assertContains('Aucun étudiant',$empty);
    }

    public function testAutomaticAttestationNumbersAdvancePerStudentAndKeepArchivedNumbers()
    {
        $service=new \gesecole\Services\AcademicAttestationNumberService;
        $context=array('id_campus'=>910001,'modele'=>'ngaoundere','year'=>'2026-2027');
        $students=array(array('student'=>(object)array('id'=>910001)),array('student'=>(object)array('id'=>910002)));
        $first=$service->references($context,$students);
        $this->assertSame('001UN/IUT/UN/CD-ISH/CS-ISH',$first[910001]);
        $this->assertSame('002UN/IUT/UN/CD-ISH/CS-ISH',$first[910002]);
        DB::table('academic_document_generations')->insert(array('id_campus'=>910001,'id_tutelle'=>910002,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'annee_academique'=>'2026-2027','modele'=>'ngaoundere','document'=>'attestation','periode'=>'annuel','effectif'=>2,'contexte'=>json_encode(array('attestation_numbers'=>$first)),'contenu'=>'test','empreinte'=>hash('sha256','test'),'deleted_at'=>date('Y-m-d H:i:s')));
        $later=$service->references($context,array($students[0],array('student'=>(object)array('id'=>910003))));
        $this->assertSame($first[910001],$later[910001]);
        $this->assertSame('003UN/IUT/UN/CD-ISH/CS-ISH',$later[910003]);
        $context['modele']='soa';
        DB::table('academic_document_generations')->insert(array('id_campus'=>910001,'id_tutelle'=>910001,'id_classe'=>910001,'id_cycle'=>910001,'id_nniveau'=>910001,'annee_academique'=>'2026-2027','modele'=>'soa','document'=>'attestation','periode'=>'annuel','effectif'=>1,'contexte'=>json_encode(array('document_selection'=>array('id_eleve'=>910001,'attestation_reference'=>'N° 006/ISH/YDE II - SOA'))),'contenu'=>'test','empreinte'=>hash('sha256','test')));
        $soa=$service->references($context,$students);
        $this->assertSame('006/ISH/YDE II - SOA',$soa[910001]);
        $this->assertSame('007/ISH/YDE II - SOA',$soa[910002]);
        $context['year']='2027-2028';
        $this->assertSame('001/ISH/YDE II - SOA',$service->references($context,array($students[0]))[910001]);
        $context['modele']='esstic';
        $this->assertSame('001/ISH/YDE II-ESSTIC',$service->references($context,array($students[0]))[910001]);
    }

    public function testArchiveIsImmutableAfterGradeChangeAndIsCampusScoped()
    {
        $controller=new academicDocumentController;
        $controller->generate(Request::create('/','POST',$this->input(array('page_size'=>'a3'))));
        $g=gesecole\AcademicDocumentGeneration::first();$html=$g->verifiedContent();
        $this->assertContains('size: A3 landscape;',$html);
        DB::table('note')->where('id',910002)->update(array('note'=>10));
        $this->assertSame($html,$controller->printSaved($g->id)->getContent());
        $this->assertContains('12.00',$html);
        $this->assertNull(gesecole\AcademicDocumentGeneration::forCampus(910002)->find($g->id));
        $g->contenu='altéré';
        try{$g->verifiedContent();$this->fail('L’empreinte doit être vérifiée.');}catch(RuntimeException $e){$this->assertContains('empreinte',$e->getMessage());}
    }

    public function testGradeModificationAndDeletionAreScopedAndAudited()
    {
        $controller=new academicDocumentController;
        $n=gesecole\note::find(910002);
        $input=$this->input(array('id_session'=>910001,'sequence'=>3,'notes'=>array(910001=>'14'),'versions'=>array(910001=>hash('sha256',$n->toJson()))));
        $controller->saveNotes(Request::create('/','POST',$input));
        $this->assertEquals(49,gesecole\note::find(910002)->note);
        $this->assertEquals(70,gesecole\note::find(910003)->note);
        $this->assertEquals(70,gesecole\note::find(910004)->note);
        $this->assertEquals(1,DB::table('academic_note_audits')->count());
        $n=gesecole\note::find(910002);$input['versions'][910001]=hash('sha256',$n->toJson());$input['supprimer']=array(910001);
        $controller->saveNotes(Request::create('/','POST',$input));
        $this->assertNull(gesecole\note::find(910002));$this->assertEquals(2,DB::table('academic_note_audits')->count());
    }

    public function testStaleGradeVersionCannotOverwriteAnotherEditorsChange()
    {
        $n=gesecole\note::find(910002);$version=hash('sha256',$n->toJson());
        DB::table('note')->where('id',910002)->update(array('note'=>46));
        $input=$this->input(array('id_session'=>910001,'sequence'=>3,'notes'=>array(910001=>'14'),'versions'=>array(910001=>$version)));
        try{(new academicDocumentController)->saveNotes(Request::create('/','POST',$input));$this->fail('Version périmée acceptée.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(409,$e->getStatusCode());}
        $this->assertEquals(46,gesecole\note::find(910002)->note);$this->assertEquals(0,DB::table('academic_note_audits')->count());
    }

    public function testActiveCampusLockBlocksGradeWrites()
    {
        DB::table('verrou_note')->insert(array('sequence'=>3,'annee_scolaire'=>'2026-2027','id_campus'=>910001,'target'=>'note_sequence','is_active'=>true));
        $input=$this->input(array('id_session'=>910001,'sequence'=>3,'notes'=>array(910001=>'14'),'versions'=>array(910001=>'new')));
        try{(new academicDocumentController)->saveNotes(Request::create('/','POST',$input));$this->fail('Verrou ignoré.');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$this->assertSame(423,$e->getStatusCode());}
        $this->assertEquals(45,gesecole\note::find(910002)->note);$this->assertEquals(0,DB::table('academic_note_audits')->count());
    }

    public function testLegacyGradeWritesMustChooseTheTutelleFirst()
    {
        $request=Request::create('/eleves/notes/store','POST',array('id_classe'=>910001,'cycle'=>910001,'nniveau'=>910001));
        $route=new Illuminate\Routing\Route(array('POST'),'/eleves/notes/store',array('uses'=>'gesecole\\Http\\Controllers\\eleves\\noteController@store'));
        $request->setRouteResolver(function()use($route){return $route;});
        $response=(new gesecole\Http\Middleware\ProtectTutelleNotes)->handle($request,function(){return response('allowed');});
        $this->assertEquals(302,$response->getStatusCode());$this->assertContains('/eleves/documents-tutelles?',$response->getTargetUrl());
        $this->assertEquals(45,gesecole\note::find(910002)->note);
    }

    public function testNewScopedGradeControllerIsNotRedirectedByLegacyProtection()
    {
        $request=Request::create('/eleves/documents-tutelles/notes','POST',$this->input());
        $route=new Illuminate\Routing\Route(array('POST'),'/eleves/documents-tutelles/notes',array('uses'=>'gesecole\\Http\\Controllers\\eleves\\academicDocumentController@saveNotes'));
        $request->setRouteResolver(function()use($route){return $route;});
        $response=(new gesecole\Http\Middleware\ProtectTutelleNotes)->handle($request,function(){return response('allowed');});
        $this->assertEquals(200,$response->getStatusCode());
    }
}
