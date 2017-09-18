<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\matiere;
use gesecole\personnel;
use gesecole\taux;
use Illuminate\Support\Facades\Session;
use gesecole\parametres;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class tauxController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.dashboard')]);
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
	public function index()
	{

        $titre=trans('admin.search_personnel');
        $values= array('titre'=>$titre);
        return view('admin\search_personnel_taux',$values);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
        $p=personnel::find($id);
        $titre=trans('admin.taux').' : '.$p->nom;
        $t=taux::whereIdPersonnel($id)->get();
        $ma=$t->fetch('id_matiere')->toArray();
        $m=matiere::all();
        $ms=matiere::whereNotIn('id',$ma)->get();


        $values= array('titre'=>$titre);
        $values['taux']=$t;
        $values['personnel']=$p;
        $values['matieres']=$m;
        $values['matiere_sans']=$ms;



        if(session('success')){
            Session::forget('success');
            $values['success']=true;

        }
        return view('admin\taux',$values);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$this->validate($request,[
		   'id_personnel'=>'required|numeric',
            'id_matiere'=>'required|numeric',
            'taux'=>'required|numeric',
        ]);

		$t=new taux();
		$t->id_personnel=$request->input('id_personnel');
        $t->id_matiere=$request->input('id_matiere');
        $t->taux=$request->input('taux');

        $t->save();
        \session(['success'=>true]);
        return redirect()->action('admin\tauxController@create',[$t->id_personnel]);
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
		$t=taux::find($id);
		$t->delete();
        \session(['success'=>true]);
        return redirect()->action('admin\tauxController@create',[$t->id_personnel]);
	}

}
