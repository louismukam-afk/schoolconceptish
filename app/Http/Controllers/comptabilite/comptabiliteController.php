<?php namespace gesecole\Http\Controllers\comptabilite;

use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\parametres;
use gesecole\decaissement;
use gesecole\payement_salaire;
use gesecole\payement;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class comptabiliteController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.comptabilite')]);
        $this->renewSchoolYear();
    }

	public function index()
	{
        $pe=payement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->get();

        $total_e=0;
        foreach ($pe as $pay){
        $total_e+=$pay->montant;
        }


        $ps=payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->get();

        $d=decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->get();




        $total_ps=0;
        foreach ($ps as $pay){
        $total_ps+=$pay->net_a_payer;
        }
        $total_d=0;
        foreach ($d as $pay){
            $total_d+=$pay->montant;
        }

        $values= array('entrees'=>$total_e,'sorties'=>$total_ps+$total_d);
        return view('comptabilite.index',$values);
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
