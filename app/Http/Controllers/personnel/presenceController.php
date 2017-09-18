<?php namespace gesecole\Http\Controllers\personnel;

use gesecole\dispense;
use gesecole\horaire;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\matiere;
use gesecole\personnel;
use gesecole\presence_e;
use Illuminate\Http\Request;

class presenceController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request,$id)
	{
		$this->validate($request,[
		   'jour'=>'required|numeric',
        ]);
        $p=personnel::find($id);
		$jour=$request->input('jour');
        $values=array(['titre'=>trans('admin.jour').' '.$p->nom]);
        $pr=presence_e::whereAnneeScolaire(session('year'))
            ->where('id_personnel','=',$id)
            ->where('date_p','=',date('Y-m-d'))
            ->first();

		$h=horaire::all();
		$m=matiere::all();

		$d=dispense::whereAnneeScolaire(session('year'))
            ->where('id_personnel','=',$id)
            ->where('jour','=',$jour)
            ->get();

		$values['presences']=$pr;
        $values['horaires']=$h;
        $values['matieres']=$m;
        $values['dispenses']=$d;

        return view('personnel\presence_enseignant',$values);
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
