<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AllowFractionalAcademicCredits extends Migration {
    public function up()
    {
        if(DB::connection()->getDriverName()!=='pgsql')return;
        DB::transaction(function(){
            foreach(array('campus_specialite','specialite_tutelle','niveau','cycle','n_niveaus','classe','matiere','codages','classe_matiere','groupe_matieres','eleve','inscription','note')as $table){
                $column=DB::selectOne("SELECT column_default FROM information_schema.columns WHERE table_schema=current_schema() AND table_name=? AND column_name='id'",array($table));
                if(!$column || $column->column_default)continue;
                $sequence=$table.'_id_seq';
                DB::statement('LOCK TABLE '.$table.' IN EXCLUSIVE MODE');
                DB::statement('CREATE SEQUENCE IF NOT EXISTS '.$sequence);
                DB::statement("SELECT setval('".$sequence."',COALESCE((SELECT MAX(id) FROM ".$table."),0)+1,false)");
                DB::statement("ALTER TABLE ".$table." ALTER COLUMN id SET DEFAULT nextval('".$sequence."')");
                DB::statement('ALTER SEQUENCE '.$sequence.' OWNED BY '.$table.'.id');
            }
            DB::statement('ALTER TABLE classe_matiere ALTER COLUMN coef TYPE NUMERIC(8,2)');
        });
    }
    public function down()
    {
        // Les crédits fractionnaires existants ne doivent pas être tronqués.
    }
}
