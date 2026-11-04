<?php namespace gesecole\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use gesecole\Services\TestokImportService;

class ImportTestok extends Command {
    protected $name = 'testok:import';
    protected $description = 'Simuler ou importer les données Test OK avec des correspondances explicites.';

    public function fire()
    {
        try {
            $service=new TestokImportService;
            if(!$this->option('source'))throw new \InvalidArgumentException('--source est obligatoire.');
            $source=$service->load($this->option('source'));
            if($this->option('prepare-map')){
                file_put_contents($this->option('prepare-map'),json_encode($service->template($source),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
                $this->info('Correspondances préparées. Les valeurs null doivent être renseignées ; les rapprochements proposés doivent être vérifiés.');return 0;
            }
            if(!$this->option('mapping'))throw new \InvalidArgumentException('--mapping est obligatoire.');
            $mapping=json_decode(file_get_contents($this->option('mapping')),true);
            if(!is_array($mapping))throw new \InvalidArgumentException('Fichier de correspondances invalide.');
            if($this->option('isolate-invalid')){
                if(!$this->option('quarantine'))throw new \InvalidArgumentException('--quarantine requis pour conserver les anomalies.');
                $isolated=$service->isolateInvalid($source,$mapping);
                if(file_put_contents($this->option('quarantine'),json_encode($isolated['quarantine'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),LOCK_EX)===false)throw new \RuntimeException('Écriture du rapport d’anomalies impossible.');
                $source=$isolated['source'];$this->line(json_encode(array('quarantine'=>$isolated['quarantine']['counts'],'reasons'=>$isolated['quarantine']['reasons']),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            }
            $plan=$service->plan($source,$mapping);
            if($this->option('report'))file_put_contents($this->option('report'),json_encode($plan,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            $this->line(json_encode(array('counts'=>$plan['counts'],'campus_id'=>$plan['campus_id'],'errors_count'=>count($plan['errors']),'first_errors'=>array_slice($plan['errors'],0,20)),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
            if(!$plan['ready']){$this->error('Correspondances ou données à corriger. Aucune donnée importée.');return 1;}
            if(!$this->option('commit')){$this->info('Simulation réussie. Utiliser --commit pour appliquer cette reprise en transaction.');return 0;}
            $service->import($source,$mapping);$this->info('Reprise terminée. Les données existantes différentes n’ont pas été écrasées.');return 0;
        }catch(\Exception $e){$this->error($e->getMessage());return 1;}
    }

    protected function getOptions()
    {
        return array(
            array('source',null,InputOption::VALUE_REQUIRED,'Export JSON privé Test OK.'),
            array('mapping',null,InputOption::VALUE_REQUIRED,'Correspondances validées.'),
            array('prepare-map',null,InputOption::VALUE_REQUIRED,'Écrire un modèle de correspondances.'),
            array('report',null,InputOption::VALUE_REQUIRED,'Écrire le rapport de simulation.'),
            array('commit',null,InputOption::VALUE_NONE,'Appliquer la reprise après une simulation sans erreur.'),
            array('isolate-invalid',null,InputOption::VALUE_NONE,'Isoler les anomalies et importer seulement les lignes valides.'),
            array('quarantine',null,InputOption::VALUE_REQUIRED,'Rapport privé conservant les lignes isolées et leur motif.'),
        );
    }
}
