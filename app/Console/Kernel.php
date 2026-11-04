<?php namespace gesecole\Console;

use gesecole\parametres;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
include app_path().'\\dumper.php';


class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'gesecole\Console\Commands\ImportTestok',
		'gesecole\Console\Commands\InstallTestokTutelles',
		'gesecole\Console\Commands\PrepareTestok',
		'gesecole\Console\Commands\Inspire',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{

		$schedule->command('inspire')
				 ->hourly();


		//database dump


        $schedule->call(function () {

            $mysqlUserName      = secEnv2(env('DB_USERNAME'));
            $mysqlPassword      = secEnv2(env('DB_PASSWORD'));
            $mysqlHostName      = env('DB_HOST');
            $DbName             = secEnv2(env('DB_DATABASE'));
           // $backup_name        = "mybackup.sql";
            //$tables             = "Your tables";

            try {
                exec('pg_dump  --dbname=postgresql://'.secEnv2(env('DB_USERNAME')).':'.secEnv2(env('DB_PASSWORD')).'@127.0.0.1:5432/'.secEnv2(env('DB_DATABASE')).' > "'.public_path().'\\backups\\'.secEnv2(env('DB_DATABASE')).'_'.date('H-i-s').'_'.date('d-m-Y').'.sql"');

                /*$world_dumper = \Shuttle_Dumper::create(array(
                    'host' => $mysqlHostName,
                    'username' => $mysqlUserName,
                    'password' => $mysqlPassword,
                    'db_name' => $DbName,
                ));*/
                // dump the database  for my sql
                //$world_dumper->dump(public_path().'\\backups\\'.$DbName.'_'.date('H-i-s').'_'.date('d-m-Y').'.sql');


            } catch(\Shuttle_Exception $e) {

                echo "Couldn't dump database: " . $e->getMessage();

                //for postgres

                //exec('pg_dump  --dbname=postgresql://'.secEnv(env('DB_USERNAME')).':'.secEnv(env('DB_PASSWORD')).'@127.0.0.1:5432/'.secEnv(env('DB_DATABASE')).' > '.public_path().'\\backups\\'.secEnv(env('DB_DATABASE')).'_'.date('H-i-s').'_'.date('d-m-Y').'.sql');


            }


        })->cron('0 12 * * * *');

        $schedule->call(function (){
            $dc=parametres::whereNom('dc')->first();
            if ($dc==null)
            {
                $dc=new parametres();
                $dc->nom='dc';
                $dc->valeur=date('Y-m-d H:i:s');
                $dc->save();
            }else{
                $d=date('Y-m-d H:i:s');
                if($d>$dc->valeur)
                {
                    $dc->valeur=date('Y-m-d H:i:s');
                    $dc->save();
                }

            }

        })->cron('30 * * * * *');



	}






















    function Export_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false )
    {
        $mysqli = new mysqli($host,$user,$pass,$name);
        $mysqli->select_db($name);
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables    = $mysqli->query('SHOW TABLES');
        while($row = $queryTables->fetch_row())
        {
            $target_tables[] = $row[0];
        }
        if($tables !== false)
        {
            $target_tables = array_intersect( $target_tables, $tables);
        }
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);
            $fields_amount  =   $result->field_count;
            $rows_num=$mysqli->affected_rows;
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table);
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0)
            {
                while($row = $result->fetch_row())
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )
                    {
                        $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)
                    {
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) );
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ;
                        }
                        else
                        {
                            $content .= '""';
                        }
                        if ($j<($fields_amount-1))
                        {
                            $content.= ',';
                        }
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num)
                    {
                        $content .= ";";
                    }
                    else
                    {
                        $content .= ",";
                    }
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
        $backup_name = $backup_name ? $backup_name : $name.".sql";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");
        echo $content; exit;
    }

}
