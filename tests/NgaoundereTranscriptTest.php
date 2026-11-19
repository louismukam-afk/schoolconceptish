<?php

use gesecole\Services\NgaoundereTranscriptService;

class NgaoundereTranscriptTest extends TestCase {
    public function createApplication()
    {
        global $crypt;
        $app=require __DIR__.'/../bootstrap/app.php';
        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        return $app;
    }

    private function subject($semester, $credit, $mark)
    {
        return array('semestre'=>$semester,'credit'=>$credit,'result'=>array('complete'=>$mark!==null,'final'=>$mark));
    }

    public function testAnnualTranscriptHasOnePagePerSemesterAndSeparateBalances()
    {
        $context=array('profile'=>config('academic_documents.profiles.ngaoundere'),'periode'=>'annuel');
        $subjects=array($this->subject(5,20,14.0),$this->subject(5,10,12.0),$this->subject(6,30,10.0));
        $annual=(new \gesecole\Services\AcademicResultCalculator)->summarize($subjects,$context['profile'],'annuel');
        $pages=(new NgaoundereTranscriptService)->pages(array(array('student'=>(object)array('id'=>1),'subjects'=>$subjects,'summary'=>$annual)),$context);

        $this->assertCount(2,$pages);
        $this->assertSame(5,$pages[0]['semester']);
        $this->assertCount(2,$pages[0]['subjects']);
        $this->assertEquals(30,$pages[0]['summary']['expected']);
        $this->assertSame(13.33,$pages[0]['summary']['average']);
        $this->assertNull($pages[0]['annual']);
        $this->assertSame(6,$pages[1]['semester']);
        $this->assertEquals(30,$pages[1]['summary']['expected']);
        $this->assertSame(10.0,$pages[1]['summary']['average']);
        $this->assertEquals(60,$pages[1]['annual']['expected']);
        $this->assertSame($annual['decision'],$pages[1]['annual']['decision']);
    }

    public function testSingleSemesterNeverShowsAnAnnualDecision()
    {
        $context=array('profile'=>config('academic_documents.profiles.ngaoundere'),'periode'=>'s2');
        $subjects=array($this->subject(6,30,null));
        $pages=(new NgaoundereTranscriptService)->pages(array(array('student'=>(object)array('id'=>1),'subjects'=>$subjects,'summary'=>array('decision'=>'A DELIBERER'))),$context);

        $this->assertCount(1,$pages);
        $this->assertTrue($pages[0]['summary']['incomplete']);
        $this->assertNull($pages[0]['annual']);
        $this->assertSame('Note manquante',NgaoundereTranscriptService::observation(null));
    }
}
