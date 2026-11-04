<?php namespace gesecole\Console\Commands;

use Illuminate\Console\Command;

class InstallTestokTutelles extends Command {
    protected $name = 'testok:tutelles';
    protected $description = 'Créer les cinq tutelles Test OK et référencer les logos repris.';
    public function fire()
    {
        require_once database_path('seeds/TestokTutellesSeeder.php');
        (new \TestokTutellesSeeder)->run();
        $this->info('Tutelles prêtes : ISABEE / Ebolowa, ESSTIC, Ngaoundéré, SOA et Polytechnique. Les tutelles déjà configurées ont été conservées.');
    }
}
