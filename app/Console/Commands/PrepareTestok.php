<?php namespace gesecole\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use gesecole\Services\TestokImportService;
use gesecole\Services\TestokCatalogService;

class PrepareTestok extends Command {
    protected $name='testok:prepare';
    protected $description='Préparer les catalogues et correspondances Test OK sans écraser les références existantes.';
    public function fire()
    {
        $active=false;
        try{
            if(!$this->option('source') || !$this->option('mapping') || !$this->option('campus'))throw new \InvalidArgumentException('--source, --mapping et --campus requis.');
            $service=new TestokImportService;$source=$service->load($this->option('source'));
            $mapping=is_file($this->option('mapping'))?json_decode(file_get_contents($this->option('mapping')),true):$service->template($source);
            DB::beginTransaction();$active=true;
            $result=(new TestokCatalogService)->prepare($source,$mapping,(int)$this->option('campus'));
            if($this->option('commit')){
                $json=json_encode($result['mapping'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
                if(file_put_contents($this->option('mapping').'.pending',$json,LOCK_EX)===false)throw new \RuntimeException('Écriture des correspondances impossible.');
                DB::commit();$active=false;
                if(!rename($this->option('mapping').'.pending',$this->option('mapping')))throw new \RuntimeException('Catalogues préparés ; récupérer les correspondances dans le fichier .pending.');
            }else{DB::rollBack();$active=false;}
            $this->line(json_encode(array('created'=>$result['created'],'warnings'=>$result['warnings']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            $this->info($this->option('commit')?'Catalogues et correspondances enregistrés. Aucune note importée à cette étape.':'Simulation des catalogues terminée ; transaction annulée. --commit permet de conserver les catalogues.');return 0;
        }catch(\Exception $e){if($active)DB::rollBack();$this->error($e->getMessage());return 1;}
    }
    protected function getOptions()
    {
        return array(array('source',null,InputOption::VALUE_REQUIRED,'Export privé.'),array('mapping',null,InputOption::VALUE_REQUIRED,'Correspondances.'),array('campus',null,InputOption::VALUE_REQUIRED,'Campus cible.'),array('commit',null,InputOption::VALUE_NONE,'Conserver les catalogues préparés.'));
    }
}
