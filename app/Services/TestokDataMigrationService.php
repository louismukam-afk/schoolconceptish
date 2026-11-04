<?php namespace gesecole\Services;

use Illuminate\Support\Facades\DB;

class TestokDataMigrationService {
    const SQL_SHA256 = '864c6fa1728e60d0f0bedf33af757568264bd9aae7f6ba04c03993151c5af604';

    public function run($directory,$campus,$mappingFile=null,$commit=false)
    {
        $directory=realpath($directory);
        $private=realpath(storage_path('app/import-testok'));
        if(!$directory || !$private || (strcasecmp($directory,$private)!==0 && stripos($directory.DIRECTORY_SEPARATOR,$private.DIRECTORY_SEPARATOR)!==0))throw new \RuntimeException('Paquet requis dans storage/app/import-testok.');
        $manifest=json_decode(file_get_contents($directory.'/manifest.json'),true);
        if(!is_array($manifest) || !isset($manifest['format'],$manifest['sql_sha256'],$manifest['source_sha256']) || $manifest['format']!=='testok-migration-package-v1' || $manifest['sql_sha256']!==self::SQL_SHA256)throw new \RuntimeException('Ce paquet ne correspond pas au SQL de la migration du 30 octobre 2026.');
        if(!hash_equals($manifest['source_sha256'],hash_file('sha256',$directory.'/source.json')))throw new \RuntimeException('Empreinte du fichier de données incorrecte.');
        $service=new TestokImportService;
        $source=$service->load($directory.'/source.json');
        if(!isset($source['sql_sha256']) || $source['sql_sha256']!==self::SQL_SHA256)throw new \RuntimeException('Origine du paquet incorrecte.');
        if(!ctype_digit((string)$campus) || (int)$campus<=0 || !DB::table('campus')->where('id',(int)$campus)->exists())throw new \RuntimeException('Indiquer un campus existant sur CE serveur.');
        if(!$mappingFile && is_file($directory.'/correspondances-cible.json')){
            $saved=json_decode(file_get_contents($directory.'/correspondances-cible.json'),true);
            if(!isset($saved['source_sha256'],$saved['campus_id'],$saved['mapping']) || $saved['source_sha256']!==$manifest['source_sha256'] || (int)$saved['campus_id']!==(int)$campus)throw new \RuntimeException('Correspondances enregistrées pour un autre paquet ou campus.');
            $mapping=$saved['mapping'];
        }elseif($mappingFile){
            $mapping=json_decode(file_get_contents($mappingFile),true);
            if(!is_array($mapping) || !isset($mapping['source'],$mapping['campus_id']) || (int)$mapping['campus_id']!==(int)$campus)throw new \RuntimeException('Correspondances invalides ou campus différent.');
        }else $mapping=null;
        $active=false;
        try {
            DB::connection()->disableQueryLog();
            DB::beginTransaction();$active=true;
            DB::select('SELECT pg_advisory_xact_lock(?)',array(187321030));
            if(!$mapping && DB::table('testok_import_links')->where('source','testok-hintel')->exists())throw new \RuntimeException('Une reprise Test OK existe déjà : fournir ses correspondances locales avec --mapping. Ne pas réutiliser les identifiants d’un autre serveur.');
            if(!$mapping)$mapping=$service->template($source);
            if($mapping['source']!=='testok-hintel')throw new \RuntimeException('Identifiant de reprise inattendu.');
            foreach(array('isabee','esstic','ngaoundere','soa','polytechnique') as $model){
                if(DB::table('tutelles_academiques')->where('modele_documents',$model)->count()!==1)throw new \RuntimeException('Installer une tutelle unique pour le modèle '.$model.' avant la reprise.');
            }
            $prepared=(new TestokCatalogService)->prepare($source,$mapping,(int)$campus);
            $mapping=$prepared['mapping'];
            $isolated=$service->isolateInvalid($source,$mapping);
            $this->write($directory.'/anomalies-migration.json',$isolated['quarantine']);
            $plan=$service->plan($isolated['source'],$mapping);
            $report=array('source_sha256'=>$manifest['source_sha256'],'campus_id'=>(int)$campus,'mode'=>$commit?'migration':'simulation','politique'=>'reutiliser_ou_creer_si_absent','catalogues'=>$prepared['created'],'avertissements'=>$prepared['warnings'],'plan'=>$plan,'anomalies'=>$isolated['quarantine']['counts']);
            $this->write($directory.'/rapport-migration.json',$report);
            if(!$plan['ready'])throw new \RuntimeException('Reprise impossible : consulter rapport-migration.json.');
            // Exécuter aussi les écritures en simulation pour détecter les conflits,
            // puis annuler l'ensemble, catalogues compris.
            $service->import($isolated['source'],$mapping);
            $createdStudents=isset($prepared['created']['eleve'])?$prepared['created']['eleve']:0;
            $report['traitement']=array_merge($service->stats(),array('etudiants_crees'=>$createdStudents,'etudiants_reutilises'=>$plan['counts']['etudiant']-$createdStudents));
            $report['verification']=(new TestokImportVerifier)->verify($isolated['source'],$mapping);
            $this->write($directory.'/rapport-migration.json',$report);
            if(!$report['verification']['ok'])throw new \RuntimeException('Contrôle des notes et tutelles échoué ; reprise annulée. Consulter rapport-migration.json.');
            if($commit){
                $this->write($directory.'/correspondances-cible.json.pending',array('source_sha256'=>$manifest['source_sha256'],'campus_id'=>(int)$campus,'mapping'=>$mapping));
                DB::commit();$active=false;
                if(!rename($directory.'/correspondances-cible.json.pending',$directory.'/correspondances-cible.json'))throw new \RuntimeException('Données importées ; conserver correspondances-cible.json.pending et le renommer avant toute relance.');
            }else{DB::rollBack();$active=false;}
            return array('mode'=>$report['mode'],'campus_id'=>(int)$campus,'politique'=>$report['politique'],'counts'=>$plan['counts'],'traitement'=>$report['traitement'],'anomalies'=>$report['anomalies'],'verification'=>$report['verification']);
        }catch(\Exception $e){if($active)DB::rollBack();throw $e;}
    }

    private function write($file,$value)
    {
        $json=json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        if($json===false || file_put_contents($file,$json,LOCK_EX)===false)throw new \RuntimeException('Écriture du rapport privé impossible.');
        @chmod($file,0600);
    }
}
