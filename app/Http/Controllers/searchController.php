<?php namespace gesecole\Http\Controllers;

use gesecole\decaissement;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
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

	public function personnel(Request $req)
	{
        $pattern=$req->input('search');
        $view=$req->input('view');
        $titre=$req->input('titre');
        $p=DB::table('personnel')->where('nom', 'LIKE', '%'.$pattern.'%')
            ->orderBy('nom')
            ->paginate(30);
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
        $p=personnel::where('nom', 'LIKE', '%'.$pattern.'%')
            ->orderBy('nom')
            ->get();
        $arr=[];
        foreach ($p as $personnel){
            array_push($arr,$personnel->id);
        }
        $d=decaissement::whereIn('id_personnel',$arr)->where('annee_scolaire','=',\session('year'))->orderBy('date','desc')->paginate(30);
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
        $e=DB::table('eleve')->where('nom', 'LIKE', '%'.$pattern.'%')
            ->orWhere('prenom', 'LIKE', '%'.$pattern.'%')
            ->orWhere('matricule', 'LIKE', '%'.$pattern.'%')
            ->orderBy('nom')->orderBy('prenom')
            ->paginate(30);
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
	public function store()
	{
		//
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
