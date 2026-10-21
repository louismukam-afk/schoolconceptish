<?php namespace gesecole\Http\Controllers\eleves;

use gesecole\classe;
use gesecole\cycle;
use gesecole\discipline;
use gesecole\eleve;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parametres;
use Illuminate\Support\Facades\Session;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class disciplineController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('eleve.big_title')]);
        $this->renewSchoolYear();
    }


    public function index()
	{
        $values= array('titre'=>trans('inscription.title_search'));
        $cy=cycle::all();
        $n=niveau::all();
        $c=classe::all();

        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['classes']=$c;
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }

        return view('eleves/search',$values);
	}

    public function search(Request $request)
    {
        $pattern=$request->input('search');
        $e=DB::select('SELECT e.matricule,e.id, e.nom,e.prenom,e.date_naiss,e.lieu_naiss,e.sexe,c.nom as nom_classe,c.nom_anglais as nom_classe_anglais FROM eleve e,inscription i,classe c WHERE (lower(e.nom) LIKE ? OR lower(e.prenom) LIKE ? OR lower(e.matricule) LIKE ?) AND e.id=i.id_eleve AND i.id_classe= c.id AND annee_scolaire=?',['%'.strtolower($pattern).'%','%'.strtolower($pattern).'%','%'.strtolower($pattern).'%',session('year')]);
        $values= array('titre'=>trans('inscription.title_search'));
        $cy=cycle::all();
        $n=niveau::all();
        $c=classe::all();

        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['classes']=$c;
        $values['eleves']=$e;
        session(['search_result'=>true]);

        return view('eleves/search',$values);
    }
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
	    $e=eleve::find($id);
        $values= array('titre'=>trans('eleve.titre_discipline'));
        $values['pers']=$e;

        $d=discipline::whereAnneeScolaire(session('year'))->where('id_eleve','=',$id)->get();
        $abscence_inj=$d->where('type',0,false)
            ->where('just','0',false)
            ->sum('nb_heures');
        $abscence_just=$d->where('type',0,false)
            ->where('just','1',false)
            ->sum('nb_heures');
        $retard=$d->where('type',1,false)
            ->count();
        $avertissement=$d->where('type',3,false)
            ->count();
        $blame=$d->where('type',4,false)
            ->count();
        $exclusion=$d->where('type',2,false)
            ->sum('nb_jours');
        $exclu_def=$d->where('type',5,false)
            ->first();
        $values['abscence_just']=$abscence_just;
        $values['abscence_inj']=$abscence_inj;
        $values['retard']=$retard;
        $values['avertissement']=$avertissement;
        $values['blame']=$blame;
        $values['exclusion']=$exclusion;
        if($exclu_def!=null)
            $values['exclus_def']=$exclu_def;







        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }

        /*
        $d=discipline::whereAnneeScolaire(session('year'))->where('id_eleve','=',$id)->first();
        if($d==null){
            $d=new discipline();
            $d->id_eleve=$id;
            $d->annee_scolaire=session('year');
            $d->save();
        }
        $values['discipline']=$d;


        */

        return view('eleves/discipline',$values);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request,$id)
	{
	    $type=$request->input('type');
	    $just=$request->input('just');
        $e=eleve::find($id);
        $d=null;
        if($type==0) {
            $d = discipline::whereAnneeScolaire(session('year'))
                ->where('id_eleve', '=', $id)
                ->where('type', '=', $type)
                ->where('just', '=', $just)
                ->orderBy('ddate','desc')
                ->get();
        }else
        {

                $d = discipline::whereAnneeScolaire(session('year'))
                    ->where('id_eleve', '=', $id)
                    ->where('type', '=', $type)
                    ->orderBy('ddate','desc')
                    ->get();
        }
        return response()->json($d);

    }

    public function justify(Request $request)
    {
        $justify=$request->input('justify');
        foreach ($justify as $j)
        {
            $jst=discipline::find($j);

            $jst->just=1;
            $current_user = \Auth::user();
            $jst->edition_details = 'just = 1';
            $jst->id_last_editor = $current_user->id;
            $jst->save();
        }

        return response('',200);
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request)

	{
		$this->validate($request,[
            'id'=>'required|numeric',
		   'type'=>'required|numeric',
            "nb_heures"=>"numeric",
            "nb_jours"=>"numeric",
        ]);
        $id=$request->input('id');
		$type=$request->input('type');
        $nb_heures=$request->input('nb_heures');
        $nb_jours=$request->input('nb_jours');
        $desc=$request->input('description');
        $date=$request->input('date');
        $sequence=$request->input('sequence');

		$d=new discipline();
		$d->id_eleve=$id;
		$d->type=$type;
		$d->description=$desc;
		$d->annee_scolaire=\session('year');
		if($date==null)
		    $d->ddate=date('Y-m-d');
		else
            $d->ddate=$date;
        if($sequence!=null)
            $d->sequence=$sequence;
		if($type==0)
		    $d->nb_heures=$nb_heures;
        if($type==2)
            $d->nb_jours=$nb_jours;
        if($type==5)
            $d->exclus_def=1;

        $current_user = \Auth::user();
        $d->id_last_editor = $current_user->id;

        $d->save();
        session(['success'=>true]);
        return redirect()->action('eleves\disciplineController@create',[$id]);
    }


    public function state(Request $request)
    {
        $id=$request->input('id');

        $nniveau=$request->input('nniveau');
        $cycle=$request->input('cycle');

        $values= array('titre'=>trans('admin.etat_disc'));

        $c=null;
        if($id==0){
            $c=null;
            $e=DB::select('SELECT * FROM eleve e WHERE e.id In(SELECT id_eleve FROM inscription i WHERE annee_scolaire=?   ) ORDER BY nom,prenom',[session('year')]);
        }
        else{
            $c=classe::find($id);
            $e=DB::select('SELECT * FROM eleve e WHERE e.id In(SELECT id_eleve FROM inscription i ,classe c WHERE annee_scolaire=? AND i.id_classe=c.id AND c.id=?  AND i.id_cycle=? AND i.id_nniveau=?  ) ORDER BY nom,prenom',[session('year'),$id,$cycle,$nniveau]);
        }

        $list_e=[];
        $i=0;
        foreach ($e as $eleve){
            $list_e[$i]=$eleve->id;
            $i++;
        }

        $d=discipline::whereAnneeScolaire(session('year'))
            ->whereIn('id_eleve',$list_e)
            ->get();
        $ii=0;
        $discList=array();

        foreach ($list_e as $el1)
        {
            $abscence_inj=$d->where('type',0,false)
                ->where('id_eleve',$el1,false)
                ->where('just','0',false)
                ->sum('nb_heures');
            $abscence_just=$d->where('type',0,false)
                ->where('id_eleve',$el1,false)
                ->where('just','1',false)
                ->sum('nb_heures');
            $retard=$d->where('type',1,false)
                ->where('id_eleve',$el1,false)
                ->count();
            $avertissement=$d->where('type',3,false)
                ->where('id_eleve',$el1,false)
                ->count();
            $blame=$d->where('type',4,false)
                ->where('id_eleve',$el1,false)
                ->count();
            $exclusion=$d->where('type',2,false)
                ->where('id_eleve',$el1,false)
                ->sum('nb_jours');


            $discList[$ii]=new etat_disciplinaire($el1,$avertissement,$exclusion,$blame,$retard,$abscence_inj,$abscence_just);
            $ii++;
        }
        $values['classe']=$c;
        $values['nniveau']=NNiveau::find($nniveau);

        $values['eleves']=$e;
        $values['disciplines']=$discList;

        return view('eleves/etat_discplinaire',$values);

    }
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}

class etat_disciplinaire
{
    public $id_eleve;
    public $avertissement;
    public $exclusion;
    public $blame;
    public $retard;
    public $abscence_inj;
    public $abscence_just;

    /**
     * disc constructor.
     * @param $id_eleve
     * @param $avertissement
     * @param $exclusion
     * @param $blame
     * @param $retard
     * @param $abscence_inj
     * @param $abscence_just
     */
    public function __construct($id_eleve, $avertissement, $exclusion, $blame, $retard, $abscence_inj, $abscence_just)
    {
        $this->id_eleve = $id_eleve;
        $this->avertissement = $avertissement;
        $this->exclusion = $exclusion;
        $this->blame = $blame;
        $this->retard = $retard;
        $this->abscence_inj = $abscence_inj;
        $this->abscence_just = $abscence_just;
    }


}