<?php namespace gesecole\Http\Controllers\comptabilite;

use Carbon\Carbon;
use gesecole\decaissement;
use gesecole\eleve;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\payement;
use gesecole\payement_salaire;
use gesecole\personnel;
use Illuminate\Support\Facades\Session;
use gesecole\parametres;
use Illuminate\Http\Request;

class bilanComptaController extends Controller {

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

	public function index(Request $request)
	{

        $this->validate($request,[
           'date_debut' => 'date',
            'date_fin' => 'date',
        ]);
        $tt=trans('admin.toute_lannee');
        $option=$request->input('option');
        $titre=trans('admin.bilan_entrees');

        $date_debut=$request->input('date_debut');
        $date_fin=$request->input('date_fin');
        $date_range=$request->input('date_range');

        if($date_range && ($date_debut != null || $date_fin != null)) {
            if($date_debut!=null && $date_fin!=null){
                $p=payement::with('eleve')->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_payement','>=',(new \DateTime($date_debut))->format('Y-m-d'))
                    ->where('date_payement','<=',(new \DateTime($date_fin))->format('Y-m-d'))
                    ->orderBy('date_payement','desc')
                    ->get();

            } elseif ($date_debut != null && $date_fin == null) {
                $p=payement::with('eleve')->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_payement','>=',(new \DateTime($date_debut))->format('Y-m-d'))
                    ->orderBy('date_payement','desc')
                    ->get();
            } else {
                $p=payement::with('eleve')->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_payement','<=',(new \DateTime($date_fin))->format('Y-m-d'))
                    ->orderBy('date_payement','desc')
                    ->get();
            }
        }
       else{
            if($option=='month')
            {
                $tt=trans('admin.mois');

                $dd=date('Y-m-01');
                $df=date('Y-m-t');

                $p=payement::with('eleve')->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_payement','>=',(new \DateTime($dd))->format('Y-m-d'))
                    ->where('date_payement','<=',(new \DateTime($df))->format('Y-m-d'))
                    ->orderBy('date_payement','desc')
                    ->get();


            }
            elseif ($option=='week'){
                $tt=trans('admin.semaine');

                $monday = Carbon::now()->startOfWeek()->toDateString();
                $sunday = Carbon::now()->endOfWeek()->toDateString();
                $p=payement::with('eleve')->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_payement','>=',(new \DateTime($monday))->format('Y-m-d'))
                    ->where('date_payement','<=',(new \DateTime($sunday))->format('Y-m-d'))
                    ->orderBy('date_payement','desc')
                    ->get();

            }
            elseif ($option=='today'){
                $tt=trans('admin.today');

                $d = date('Y-m-d');
                $p=payement::with('eleve')->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_payement','=', $d)
                    ->orderBy('date_payement','created_at')
                    ->get();
            }
            else{
                $tt=trans('admin.toute_lannee');
                $p=payement::with('eleve.inscriptions.classe', 'eleve.inscriptions.nniveau')
                ->whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('date_payement','desc')->get();

            }

        }



        $total=0;
        foreach ($p as $pay){
            $total+=$pay->montant;
        }
        $values= array('titre'=>$titre,'payements'=>$p,'total'=>$total,'table_titre'=>$tt);
        if($date_range && ($date_debut != null || $date_fin != null))
        {
            $values['date_debut']=$date_debut;
            $values['date_fin']=$date_fin;
            $values['table_titre'] = $date_debut . ' - ' . $date_fin;

        }
        if(session('success')){
            $values['success']=true;
            Session::forget('success');
        }
        $values['annee_scolaire'] = \session('year');



        return view('comptabilite.bilanEntrees',$values);
	}

    public function indexSorties(Request $request)
    {
        $this->validate($request, [
            'date_debut' => 'date',
            'date_fin' => 'date',
        ]);

        $titre = trans('admin.bilan_sorties');

        $tt = trans('admin.toute_lannee');
        $option = $request->input('option');
        $date_debut = $request->input('date_debut');
        $date_fin = $request->input('date_fin');
        $date_range=$request->input('date_range');

        if($date_range && ($date_debut != null || $date_fin != null)) {
            if ($date_debut != null && $date_fin != null) {
                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_p', '>=', (new \DateTime($date_debut))->format('Y-m-d'))
                    ->where('date_p', '<=', (new \DateTime($date_fin))->format('Y-m-d'))
                    ->orderBy('date_p', 'desc')
                    ->get();

                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date', '>=', (new \DateTime($date_debut))->format('Y-m-d'))
                    ->where('date', '<=', (new \DateTime($date_fin))->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->get();

            } elseif ($date_debut != null && $date_fin == null) {
                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_p', '>=', (new \DateTime($date_debut))->format('Y-m-d'))
                    ->orderBy('date_p', 'desc')
                    ->get();

                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date', '>=', (new \DateTime($date_debut))->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->get();
            } else {
                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_p', '<=', (new \DateTime($date_fin))->format('Y-m-d'))
                    ->orderBy('date_p', 'desc')
                    ->get();

                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date', '<=', (new \DateTime($date_fin))->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->get();
                }
        }
            else {
            if ($option == 'month') {
                $tt = trans('admin.mois');

                $dd = date('Y-m-01');
                $df = date('Y-m-t');

                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_p', '>=', (new \DateTime($dd))->format('Y-m-d'))
                    ->where('date_p', '<=', (new \DateTime($df))->format('Y-m-d'))
                    ->orderBy('date_p', 'desc')
                    ->get();
                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date', '>=', (new \DateTime($dd))->format('Y-m-d'))
                    ->where('date', '<=', (new \DateTime($df))->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->get();


            } elseif ($option == 'week') {
                $tt = trans('admin.semaine');

                $monday = Carbon::now()->startOfWeek()->toDateString();
                $sunday = Carbon::now()->endOfWeek()->toDateString();
                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_p', '>=', (new \DateTime($monday))->format('Y-m-d'))
                    ->where('date_p', '<=', (new \DateTime($sunday))->format('Y-m-d'))
                    ->orderBy('date_p', 'desc')
                    ->get();

                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date', '>=', (new \DateTime($monday))->format('Y-m-d'))
                    ->where('date', '<=', (new \DateTime($sunday))->format('Y-m-d'))
                    ->orderBy('date', 'desc')
                    ->get();

            } elseif ($option == 'today') {
                $tt = trans('admin.today');

                $d = date('Y-m-d');
                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date_p', '=', $d)
                    ->orderBy('date_p', 'desc')
                    ->get();

                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                    ->where('date', '=', $d)
                    ->orderBy('date', 'desc')
                    ->get();

            } else {
                $tt = trans('admin.toute_lannee');
                $p = payement_salaire::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('date_p', 'desc')->get();

                $d = decaissement::whereAnneeScolaire(\session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('date', 'desc')->get();


            }
        }


            $total_p = 0;
            foreach ($p as $pay) {
                $total_p += $pay->net_a_payer;
            }
            $total_d = 0;
            foreach ($d as $pay) {
                $total_d += $pay->montant;
            }
            $pers = personnel::all();
            $values = array('titre' => $titre, 'payements' => $p, 'total' => $total_p, 'total_d' => $total_d, 'decaissements' => $d, 'personnels' => $pers, 'table_titre' => $tt);
            if ($date_range && ($date_debut != null || $date_fin != null)) {
                $values['date_debut'] = $date_debut;
                $values['date_fin'] = $date_fin;
                $values['table_titre'] = $date_debut . ' - ' . $date_fin;
            }
            if (session('success')) {
                $values['success'] = true;
                Session::forget('success');
            }


            return view('comptabilite.bilanSorties', $values);
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
