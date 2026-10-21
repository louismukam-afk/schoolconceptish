<?php namespace gesecole\Http\Controllers;

use gesecole\classe;
use gesecole\cycle;
use gesecole\decaissement;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\personnel;
use Illuminate\Support\Facades\DB;
use gesecole\parametres;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class searchController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        //session(['big_title'=>trans('admin.menu_recherche')]);
        $this->renewSchoolYear();
    }

	public function personnel(Request $req)
	{
        $pattern=$req->input('search');
        $view=$req->input('view');
        $titre=$req->input('titre');
        $p=DB::table('personnel')->whereRaw('lower(nom) LIKE ?',['%'.strtolower($pattern).'%'])
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->orderBy('nom')
            ->paginate(60);
        $values= array('titre'=>$titre);
        $values['personnel']=$p;
        session(['search_result'=>true]);

        return view($view,$values);
	}

    public function retrait(Request $req)
    {
        $pattern=$req->input('search');
        $view=$req->input('view');
        $titre=$req->input('titre');
        $p=personnel::whereRaw('lower(nom) LIKE ?',['%'.strtolower($pattern).'%'])
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->orderBy('nom')
            ->get();
        $arr=[];
        foreach ($p as $personnel){
            array_push($arr,$personnel->id);
        }
        $d=decaissement::whereIn('id_personnel',$arr)->where('annee_scolaire','=',\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('date','desc')->paginate(30);
        $values= array('titre'=>$titre);
        $values['personnel']=$p;
        session(['search_result'=>true]);
        $values['decaissement']=$d;

        return view($view,$values);
    }
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function eleve(Request $req)
	{
        $pattern=$req->input('search');
        $view=$req->input('view');
        if($view == 'inscriptions/search_carte_eleve') {
            $e=DB::table('eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($this->currentCampusId()))
                ->where('inscription.desinscrire', '=', 0)
                ->whereRaw('(lower(eleve.nom) LIKE ? OR lower(eleve.prenom) LIKE ? OR lower(eleve.matricule) LIKE ?)', ['%'.strtolower($pattern).'%', '%'.strtolower($pattern).'%', '%'.strtolower($pattern).'%'])
                ->select('eleve.*')
                ->orderBy('nom')->orderBy('prenom')
                ->paginate(60);
        } else {
            $e=DB::table('eleve')->whereRaw('lower(nom) LIKE ?',['%'.strtolower($pattern).'%'])
                ->orWhereRaw('lower(prenom) LIKE ?',['%'.strtolower($pattern).'%'])
                ->orWhereRaw('lower(matricule) LIKE ?',['%'.strtolower($pattern).'%'])
                ->orderBy('nom')->orderBy('prenom')
                ->paginate(60);
        }
        //$e=DB::table('eleve')->whereRaw("nom LIKE '%?%' or prenom LIKE '%?%' or matricule LIKE '%?%'",[$pattern,$pattern,$pattern])->get();
        //$e=eleve::whereRaw("nom LIKE '%?%' or prenom LIKE '%?%' or matricule LIKE '%?%'",[$pattern,$pattern,$pattern]);
        $values= array('titre'=>trans('inscription.title_search'));
        $values['eleves']=$e;
        session(['search_result'=>true]);

        return view($view,$values);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function select_classe(Request $request)
	{
	    $url=$request->input('url');
        $titre=trans('admin.selectionner_classe');
        $m=classe::orderBy('nom')->get();
        $cy=cycle::all();
        $n=niveau::all();
        $nn=NNiveau::all();
        $values= array('titre'=>$titre,'classes'=>$m);
        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['nniveaux']=$nn;
        $values['url']=$url;

        if(session('success')){
            Session::forget('success');
            $values['success']=true;

        }
        return view('inscriptions/selection_classe',$values);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
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
	public function update($id)
	{
		//
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
