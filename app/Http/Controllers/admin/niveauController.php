<?php namespace gesecole\Http\Controllers\admin;

use gesecole\cycle;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\niveau;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class niveauController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.dashboard')]);
        $this->renewSchoolYear();
    }


	public function index()
	{
        $n=niveau::orderBy(App::getLocale() == 'en'? 'nom_anglais' : 'nom')->paginate(30);
        $values= array('titre'=>trans('admin.level'),'niveaux'=>$n);
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        return view('admin/niveau',$values);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{


	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $req)
	{
        $this->validate($req,[
            'nom'=>'required',
        ]);

        $nom=$req->input('nom');
        $nom_anglais=$req->input('nom_anglais');
        $domaines=$req->input('domaines');
        $n=new niveau();
        $n->nom=$nom;
        $n->nom_anglais=$nom_anglais;
        $n->domaines=$domaines;
        $n->save();
        session(['success'=>true]);
        return redirect()->action('admin\niveauController@index');
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
	public function update(Request $req)
	{
        $this->validate($req,[
            'id'=>'required|numeric',
            'nom'=>'required',
        ]);

        $id=$req->input('id');
        $nom=$req->input('nom');
        $nom_anglais=$req->input('nom_anglais');
        $domaines=$req->input('domaines');
        $n=niveau::find($id);
        $n->nom=$nom;
        $n->nom_anglais=$nom_anglais;
        $n->domaines=$domaines;
        $n->save();
        session(['success'=>true]);
        return redirect()->action('admin\niveauController@index');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{

        try {
            $c=niveau::find($id);
            $c->delete();
            session(['success'=>true]);
        } catch (QueryException $fe) {
            return  redirect()->to(URL::previous())->withErrors(['constraint_violation' => trans('admin.cannot_delete')]);
        }
        return redirect()->to(URL::previous());
	}

}
