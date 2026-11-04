<?php

use gesecole\Services\AcademicResultCalculator;
use gesecole\Services\TestokImportService;

class AcademicResultCalculatorTest extends TestCase {
    public function createApplication()
    {
        global $crypt;
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }
    public function testWeightedContributionsBelowTwentyAreNotTreatedAsRawMarks()
    {
        $result=(new AcademicResultCalculator)->subject(array(1=>15,3=>35),array('cc'=>30,'sn'=>70,'tpe'=>0,'rat'=>70),1);
        $this->assertSame(10.0,$result['normal']);
        $this->assertTrue($result['valid']);
        $this->assertSame('N',$result['session']);
    }

    public function testZeroIsARealMarkAndMissingMarkRequiresDeliberation()
    {
        $calculator=new AcademicResultCalculator;
        $zero=$calculator->subject(array(1=>0,3=>0),array('cc'=>30,'sn'=>70,'tpe'=>0,'rat'=>70),1);
        $missing=$calculator->subject(array(1=>15),array('cc'=>30,'sn'=>70,'tpe'=>0,'rat'=>70),1);
        $this->assertSame(0.0,$zero['final']);$this->assertTrue($zero['complete']);
        $this->assertNull($missing['final']);$this->assertFalse($missing['complete']);
    }

    public function testRetakeUsesTheRetakeContributionAndPreservesPassedNormalSession()
    {
        $calculator=new AcademicResultCalculator;$weights=array('cc'=>30,'sn'=>50,'tpe'=>20,'rat'=>50);
        $result=$calculator->subject(array(2=>15,4=>10,8=>10,6=>35),$weights,6);
        $this->assertSame(7.0,$result['normal']);$this->assertSame(12.0,$result['final']);$this->assertSame('R',$result['session']);
        $passed=$calculator->subject(array(2=>15,4=>35,8=>10,6=>45),$weights,6);
        $this->assertSame(12.0,$passed['final']);$this->assertSame('N',$passed['session']);
    }

    public function testTutelleAdmissionRulesRemainDistinct()
    {
        $calculator=new AcademicResultCalculator;
        $subject=array('credit'=>45,'semestre'=>5,'result'=>array('complete'=>true,'final'=>8.0));
        $soa=$calculator->summarize(array($subject),config('academic_documents.profiles.soa'),'annuel');
        $isabee=$calculator->summarize(array($subject),config('academic_documents.profiles.isabee'),'annuel');
        $this->assertSame('ADMIS',$soa['decision']);$this->assertSame('NON ADMIS',$isabee['decision']);
        $this->assertEquals(45,$soa['credits']);$this->assertEquals(0,$isabee['credits']);
    }

    public function testNgaoundereAnnualAverageUsesBothSemestersAndFlagsEquality()
    {
        $calculator=new AcademicResultCalculator;$profile=config('academic_documents.profiles.ngaoundere');
        $subjects=array(array('credit'=>30,'semestre'=>1,'result'=>array('complete'=>true,'final'=>8.0)),array('credit'=>30,'semestre'=>2,'result'=>array('complete'=>true,'final'=>12.0)));
        $result=$calculator->summarize($subjects,$profile,'annuel');
        $this->assertSame(10.0,$result['average']);$this->assertSame('A DELIBERER',$result['decision']);
        $subjects[1]['result']['final']=14.0;
        $this->assertSame('ADMIS',$calculator->summarize($subjects,$profile,'annuel')['decision']);
    }

    public function testInvalidWeightConfigurationCannotProduceAValidatedResult()
    {
        $result=(new AcademicResultCalculator)->subject(array(1=>15,3=>35),array('cc'=>30,'sn'=>50,'tpe'=>0,'rat'=>50),1);
        $this->assertFalse($result['complete']);$this->assertFalse($result['valid']);
    }

    public function testLegacyYearAndExamMappingsAreExplicit()
    {
        $this->assertSame('2024-2025',TestokImportService::schoolYear('2024 - 2025'));
        $this->assertSame(5,TestokImportService::sequence('RATRAPPAGE'));
        $this->assertSame(6,TestokImportService::sequence('RATTRAPAGE2'));
        $this->assertSame(array('cc'=>1,'sn'=>3,'rat'=>5,'tpe'=>7),AcademicResultCalculator::sequences(5));
    }

    public function testLegacyGradesAndLinearMgp()
    {
        foreach(array('6.99'=>'F','7'=>'D','8'=>'D+','9'=>'C-','10'=>'C','14'=>'B+','17'=>'A+')as $mark=>$grade)$this->assertSame($grade,AcademicResultCalculator::grade((float)$mark));
        $this->assertNull(AcademicResultCalculator::grade(null));
        $summary=(new AcademicResultCalculator)->summarize(array(array('credit'=>45,'semestre'=>5,'result'=>array('complete'=>true,'final'=>15.0))),config('academic_documents.profiles.isabee'),'annuel');
        $this->assertSame(3.0,$summary['mgp']);$this->assertSame('Bien',$summary['mention']);
        $semester=(new AcademicResultCalculator)->summarize(array(array('credit'=>30,'semestre'=>5,'result'=>array('complete'=>true,'final'=>15.0))),config('academic_documents.profiles.isabee'),'s1');
        $this->assertSame('SEMESTRE EVALUE',$semester['decision']);
    }
}
