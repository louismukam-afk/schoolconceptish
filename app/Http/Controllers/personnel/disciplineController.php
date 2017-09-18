<?php namespace gesecole\Http\Controllers\personnel;

use gesecole\discipline_personnel;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\personnel;
use gesecole\titulaire;
use Illuminate\Http\Request;
use gesecole\parametres;

class disciplineController extends Controller {

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
        return view('personnel\search_discipline_personnel',$values);
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
           'id_personnel'=>'required',
            'type'=>'required|min:0|max:1',
        ]);

        $dp=new discipline_personnel();
        $dp->annee_scolaire=session('year');
        $dp->nombre=$request->input('nombre');
        $dp->heure_arrive=$request->input('heure_arrive');
        $dp->date_d=date('Y-m-d');
        $dp->id_personnel=$request->input('id_personnel');
        $dp->type=$request->input('type');
        $dp->save();

        session(['success'=>true]);
        return redirect()->action('personnel\disciplineController@show',['id'=>$request->input('id_personnel')]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Request $request,$id)
    {
        $option = $request->input('option');
        $p = personnel::find($id);
        $titre = trans('admin.titre_discipline_personnel') . ' ' . $p->nom;
        if ($option == 0){
            $a = discipline_personnel::whereType(0)
                ->where('id_personnel','=',$id)
                ->paginate(30);
            $r = discipline_personnel::whereType(1)
                ->where('id_personnel','=',$id)
                ->paginate(30);
        }
        else{
            $a = discipline_personnel::whereType(0)
                ->where('id_personnel','=',$id)
                ->paginate(30);
            $r = discipline_personnel::whereType(1)
                ->where('id_personnel','=',$id)
                ->paginate(30);
        }
        $values['titre']=$titre;
        $values['abscences']=$a;
        $values['retards']=$r;
        $values['personnel']=$p;

        return view('personnel\abscences_retards',$values);


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
	public function destroy($id_p,$id)
	{
		$dp=discipline_personnel::find($id);
		$dp->delete();
        session(['success'=>true]);
        return redirect()->action('personnel\disciplineController@show',['id'=>$id_p]);
	}

}
