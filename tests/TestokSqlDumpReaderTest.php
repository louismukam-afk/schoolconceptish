<?php
use gesecole\Services\TestokSqlDumpReader;

class TestokSqlDumpReaderTest extends PHPUnit_Framework_TestCase {
    private function read($extra)
    {
        $sql="-- Export de test\n";
        foreach(array('annee','cycle','niveaux','filiere','specialite','codematiere','matiere','programme_specialite','groupe_matiere','session_examen','etudiant','inscription','notes') as $table)$sql.='CREATE TABLE `'.$table.'` (`id` int);' . "\n";
        $file=tempnam(sys_get_temp_dir(),'testok-sql-');file_put_contents($file,$sql.$extra);
        try{$result=(new TestokSqlDumpReader)->read($file);}catch(Exception $e){unlink($file);throw $e;}
        unlink($file);return $result;
    }
    public function testEscapedStringsNullAndExcludedAccounts()
    {
        $data=$this->read(<<<'SQL'
/* commentaire ; */
INSERT INTO `etudiant` (`idetudiant`,`nom`,`email`,`matricule`) VALUES
(1, 'L\'élève; -- # /* test */', NULL, '00042'),
(2, 'D''Arcy\\nom\nligne', 'test@example.invalid', '42');
INSERT INTO `notes` (`idnote`,`note`) VALUES (1, 2.5e1), (2, -1);
INSERT INTO `utilisateur` (`login`,`password`) VALUES ('secret', 'non-exporte');
DROP TABLE `etudiant`;
SQL
        );
        $this->assertEquals("L'élève; -- # /* test */",$data['tables']['etudiant'][0]['NOM']);
        $this->assertNull($data['tables']['etudiant'][0]['EMAIL']);
        $this->assertEquals('00042',$data['tables']['etudiant'][0]['MATRICULE']);
        $this->assertEquals("D'Arcy\\nom\nligne",$data['tables']['etudiant'][1]['NOM']);
        $this->assertEquals('2.5e1',$data['tables']['notes'][0]['NOTE']);
        $this->assertEquals(array('utilisateur'),$data['excluded_tables']);
        $this->assertArrayNotHasKey('utilisateur',$data['tables']);
        $this->assertCount(2,$data['tables']['etudiant']);
    }
    public function testExpressionsAreRejected()
    {
        $this->setExpectedException('RuntimeException','non littérale');
        $this->read("INSERT INTO `notes` (`idnote`,`note`) VALUES (1, NOW());");
    }
    public function testTruncatedDumpIsRejected()
    {
        $this->setExpectedException('RuntimeException','incomplet');
        $this->read("INSERT INTO `notes` (`idnote`,`note`) VALUES (1, 'inachevé");
    }
    public function testExtraColumnsAreRejected()
    {
        $this->setExpectedException('RuntimeException');
        $this->read("INSERT INTO `notes` (`idnote`,`note`) VALUES (1, 2, 3);");
    }
}
