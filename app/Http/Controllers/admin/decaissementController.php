<?php namespace gesecole\Http\Controllers\admin;

use gesecole\decaissement;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class decaissementController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.comptabilite')]);
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
        $values= array('titre'=>trans('inscription.recherche'));
        $p=personnel::orderBy('nom')->get();
        $values['personnel']=$p;
        if(session('success')){

            $values['success']=session('success');
            Session::forget('success');
        }

        return view('admin\search_decaissement',$values);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$this->validate($request,[
		   'personnel'=>'required|numeric',
            'montant'=>'required|numeric',
            'motif'=>'required',
        ]);
		$d=new decaissement();
		$d->id_personnel=$request->input('personnel');
        $d->montant=$request->input('montant');
        $d->motif=$request->input('motif');
        $d->date=date('Y-m-d');
        $d->annee_scolaire=\session('year');
        $d->save();
        session(['success'=>true]);
        return redirect()->action('admin\decaissementController@index');
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
