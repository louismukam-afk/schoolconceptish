<?php namespace gesecole\Http\Controllers;

use gesecole\Campus;
use gesecole\parametres;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController {

	use DispatchesCommands, ValidatesRequests;

    public function renewSchoolYear() {
        $year=session('year');
        if($year==null){
            $p=parametres::whereNom('annee_scolaire')->get()->first();

            if(count($p)<=0){
                $p=new parametres();
                $p->nom='annee_scolaire';
                $p->valeur='2017-2018';
                $p->save();
                session(['year'=>$p->valeur]);
            }else{
                session(['year'=>$p->valeur]);
            }
        }
    }

    protected function currentCampusId()
    {
        $id = session('campus_id');
        if($id) {
            return $id;
        }

        $campus = Campus::orderBy('id')->first();
        if($campus) {
            $nom = $campus->nom;
            if($campus->sigle) {
                $nom .= ' - '.$campus->sigle;
            }
            session(array(
                'campus_id' => $campus->id,
                'campus_nom' => $nom,
                'campus_sigle' => $campus->sigle,
                'campus_localisation' => $campus->localisation,
                'nom_etab' => $nom
            ));
            return $campus->id;
        }

        return null;
    }

    protected function currentCampus()
    {
        $id = $this->currentCampusId();
        return $id ? Campus::find($id) : null;
    }

    protected function isHistoricalSchoolYear($year = null)
    {
        $year = preg_replace('/\s+/', '', (string) ($year === null ? session('year') : $year));
        return preg_match('/^(\d{4})-(\d{4})$/', $year, $parts)
            && (int) $parts[2] === (int) $parts[1] + 1
            && (int) $parts[1] < (int) config('campus.first_school_year', 2026);
    }

    protected function campusVisibilitySql($column = 'id_campus', $year = null)
    {
        if (!preg_match('/^(?:[a-zA-Z_][a-zA-Z0-9_]*\.)?id_campus$/', $column)) {
            throw new \InvalidArgumentException('Invalid campus column');
        }

        // Keep one binding so raw reports and query builders share the same rule.
        return $this->isHistoricalSchoolYear($year)
            ? '('.$column.' = ? OR '.$column.' IS NULL)'
            : $column.' = ?';
    }
}
