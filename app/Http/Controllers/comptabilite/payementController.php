<?php namespace gesecole\Http\Controllers\comptabilite;

use gesecole\discipline_personnel;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\parametres;
use gesecole\payement_salaire;
use gesecole\personnel;
use gesecole\salaire;
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

        session(['big_title'=>trans('menu.gestion_personnel')]);
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
        if(session('success')){
            $values['success']=\session('success');
            $ps=payement_salaire::find($values['success']);
            $p=personnel::find($ps->id_personnel);
            $u=User::find($ps->id_user);
            $values['payement']=$ps;
            $values['personnel']=$p;
            $values['user']=$u;
            Session::forget('success');
        }
        return view('comptabilite\search_personnel',$values);
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
            ->where('id_personnel','=',$id)
            ->orderBy('date_p','desc')
            ->first();
        if($ps!= null){
            $values['dernier_payement']=$ps;
            $d=discipline_personnel::whereAnneeScolaire(\session('year'))
                ->where('id_personnel','=',$id)
                ->where('date_d','>=',$ps->date_p)
                ->get();
        }else
            {
                $d=discipline_personnel::whereAnneeScolaire(\session('year'))
                    ->where('id_personnel','=',$id)
                    ->get();
        }

        $values['discipline']=$d;
        $s=salaire::whereIdPersonnel($id)->first();
        if($s!= null){
            $values['salaire']=$s;
        }
        $values['personnel']=$p;

        return view('comptabilite\create-payment',$values);


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
            'variations'=>'numeric',
        ]);

		$id=$request->input('id');
		$variations=$request->input('variations');

        $s=salaire::whereIdPersonnel($id)->first();
        $ps=new payement_salaire();
        $ps->id_personnel=$id;
        $ps->id_user=\Auth::user()->id;
        $ps->montant=$s->montant+$variations;
        $ps->date_p=date('Y-m-d');
        $ps->annee_scolaire=\session('year');
        $ps->save();
        $numero=sprintf('%08d',$ps->id);
        $ps->numero=$numero;
        $ps->save();

        session(['success'=>$ps->id]);
        return redirect()->action('comptabilite\payementController@index') ;


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
