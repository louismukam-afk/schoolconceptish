<?php namespace gesecole\Http\Controllers\comptabilite;

use gesecole\discipline_personnel;
use gesecole\fonction;
use gesecole\fonction_personnel;
use gesecole\heure_realise;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\parametres;
use gesecole\payement_salaire;
use gesecole\personnel;
use gesecole\salaire;
use gesecole\taux;
use gesecole\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class payementController extends Controller {

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
	   // \session(['success'=>16]);
        $titre=trans('admin.search_personnel');
        $values= array('titre'=>$titre);
       /* if(session('success')){
            $values['success']=\session('success');
            $ps=payement_salaire::find($values['success']);
            $p=personnel::find($ps->id_personnel);
            $s=salaire::whereIdPersonnel($ps->id_personnel)->first();
            if($s == null){
                $s=new salaire();
                $s->montant=0;
            }
            $f=fonction_personnel::whereIdPersonnel($ps->id_personnel)->get();
            $fo=fonction::all();
            $u=User::find($ps->id_user);
            $values['payement']=$ps;
            $values['personnel']=$p;
            $values['fonctions']=$f;
            $values['salaire']=$s;
            $values['fonctions2']=$fo;
            $values['user']=$u;
            Session::forget('success');
        }*/
        return view('comptabilite.search_personnel',$values);
	}



    public function print_bulletin($id)
    {
        $titre=trans('admin.search_personnel');
        $values= array('titre'=>$titre);
            $ps=payement_salaire::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
            if(!$ps) { abort(404); }
            $p=personnel::find($ps->id_personnel);
            $s=salaire::whereIdPersonnel($ps->id_personnel)->first();
            $pa=parametres::where('nom','=','nom_etablissement')->first();
        if($s== null){
            $s=new salaire();
            $s->montant=0;
        }
            $f=fonction_personnel::whereIdPersonnel($ps->id_personnel)->get();
            $fo=fonction::all();
            $diff=date_diff((new \DateTime($p->date_entree)),(new \DateTime(date('Y-m-d'))));
            $u=User::find($ps->id_user);
        $values['success']=$id;
        $values['etab']=$pa;

        $values['payement']=$ps;
            $values['diff']=$diff;
            $values['personnel']=$p;
            $values['fonctions']=$f;
            $values['salaire']=$s;
            $values['fonctions2']=$fo;
            $values['user']=$u;
            Session::forget('success');

        return view('comptabilite.search_personnel',$values);
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
        $p=personnel::find($id);
        $titre=trans('admin.payement').' '.$p->nom;

        $values= array('titre'=>$titre);

        $ps=payement_salaire::whereAnneeScolaire(\session('year'))
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->where('id_personnel','=',$id)
            ->orderBy('date_p','desc')
            ->first();
        if($ps!= null){
            $values['dernier_payement']=$ps;

        }

        $hrs=heure_realise::whereAnneeScolaire(\session('year'))
            ->where('id_personnel','=',$id)
            ->where('date_heure_realisee','>=',$ps?$ps->date_p:'1900-01-01')
            ->get();
        $hr=$hrs->sum('nombre');

        $sumHr=0;
        $sumTaux=0;$nTaux=0;


        foreach ($hrs as $heure)
        {
            $sumHr+= $heure->nombre*$heure->taux;
            $sumTaux+=$heure->taux;
            $nTaux++;
        }
        if($hr!=0)
        {
            $t=round(($sumHr/$hr),2);
        }
        else
            $t=0;


        $dd=date('Y-m-01');
        $df=date('Y-m-t');




        $values['somme_heure']=$sumHr;

        $values['heure_realisee']=$hr;
        $values['taux']=$t;

        $s=salaire::whereIdPersonnel($id)->first();
        if($s== null){
            $s=new salaire();
            $s->montant=0;
        }
        $values['dd']=$dd;
        $values['df']=$df;
        $values['salaire']=$s;

        $values['personnel']=$p;

        return view('comptabilite.create-payment',$values);


    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$this->validate($request,[
		   'id'=>'required|numeric',
            'heure_realisee'=>'required|numeric',
            'taux'=>'required|numeric',
            'total'=>'required|numeric',
            'mois'=>'required|numeric',
            'primes'=>'numeric',
            'acomptes'=>'numeric',
            'CNPS'=>'numeric',
            'cas_sociaux'=>'numeric',
            'assurances'=>'numeric',
            'IRPP'=>'numeric',
            'CAC'=>'numeric',
            'CFC'=>'numeric',
            'CRTV'=>'numeric',
            'taxe_communale'=>'numeric',
            'autre_retenues'=>'numeric',
            'du'=>'date|required',
            'au'=>'date|required',
        ]);

		$id=$request->input('id');

        $heure_realisee=$request->input('heure_realisee');
        $taux=$request->input('taux');
        $total=$request->input('total');
        $mois=$request->input('mois');
        $primes=$request->input('primes');
        $acomptes=$request->input('acomptes');
        $CNPS=$request->input('CNPS');
        $cas_sociaux=$request->input('cas_sociaux');
        $assurances=$request->input('assurances');
        $IRPP=$request->input('IRPP');
        $CAC=$request->input('CAC');
        $CFC=$request->input('CFC');
        $CRTV=$request->input('CRTV');
        $taxe_communale=$request->input('taxe_communale');
        $autres_retenues=$request->input('autre_retenues');
        $du=$request->input('du');
        $au=$request->input('au');


        $total_retenu=$acomptes+$CNPS+$cas_sociaux+$assurances+$autres_retenues+$IRPP+$CAC+$CFC+$CRTV+$taxe_communale;

        $total=$total+$primes;

        $ps=new payement_salaire();
        $ps->id_personnel=$id;
        $ps->id_user=\Auth::user()->id;
        $ps->montant=floatval($total-$total_retenu);
        $ps->date_p=date('Y-m-d');
        $ps->mois=$mois;
        $ps->heure_realise=$heure_realisee;
        $ps->taux=$taux;
        $ps->total=floatval($total);
        $ps->primes=floatval($primes);
        $ps->acomptes=floatval($acomptes);
        $ps->cnps=floatval($CNPS);
        $ps->IRPP=floatval($IRPP);
        $ps->CAC=floatval($CAC);
        $ps->CFC=floatval($CFC);
        $ps->CRTV=floatval($CRTV);
        $ps->pension=floatval($taxe_communale);
        $ps->periode=(new \DateTime($du))->format('d-m-Y').' - '.(new \DateTime($au))->format('d-m-Y');
        $ps->cas_sociaux=floatval($cas_sociaux);
        $ps->assurances=floatval($assurances);
        $ps->autre_retenues=floatval($autres_retenues);
        $ps->total_retenu=floatval($total_retenu);
        $ps->net_a_payer=floatval($total-$total_retenu)   ;
        $ps->annee_scolaire=\session('year');
        $ps->id_campus=$this->currentCampusId();

        $current_user = \Auth::user();
        $ps->id_creator = $current_user->id;
        $ps->edition_details = 'net_a_payer : ' . $total - $total_retenu;
        $ps->id_last_editor = $current_user->id;

        $ps->save();
        $numero=sprintf('%08d',$ps->id);
        $ps->numero=$numero;

        $ps->save();

      
        return redirect()->action('comptabilite\payementController@print_bulletin',[$ps->id]) ;


    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $p=personnel::find($id);
        $titre=trans('admin.payement').' '.$p->nom;
        $values= array('titre'=>$titre);
        $py=payement_salaire::whereAnneeScolaire(\session('year'))
            ->where('id_personnel','=',$id)
            ->get();
        $values['payements']=$py;
        $values['personnel']=$p;
        return view('comptabilite.liste_paiement',$values);
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
