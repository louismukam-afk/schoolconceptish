<?php

namespace gesecole\Http\Controllers\comptabilite;

use gesecole\CategorieBudgetaire;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use gesecole\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class Categorie_budgetairesController extends Controller
{
    public function __construct()
    {
        session(['big_title'=>trans('admin.comptabilite')]);
        $this->renewSchoolYear();
    }

    public function index()
    {
        $c=CategorieBudgetaire::orderBy('libelle')->paginate(30);
        $values= array('titre'=>trans('admin.categorie'),'categorie_budget' => $c);
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        
        return view('comptabilite.categorie_budgetaire', $values);
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
        $this->validate($request, [
            "libelle" => "required",
            "numero_compte" => "required",
        ]);

        $libelle =$request->input('libelle');
        $numero_compte=$request->input('numero_compte');
        $description=$request->input('description');
        $c=new CategorieBudgetaire();
        $c->libelle=$libelle;
        $c->numero_compte=$numero_compte;
        $c->description=$description;
        $c->save();
        session(['success'=>true]);
        return redirect()->action('comptabilite\Categorie_budgetairesController@index');
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
            'libelle'=>'required',
            'numero_compte'=>'required',
        ]);

        $id=$req->input('id');
        $libelle=$req->input('libelle');
        $numero_compte=$req->input('numero_compte');
        $description=$req->input('description');
        
        $c=CategorieBudgetaire::find($id);
        $c->libelle=$libelle;
        $c->numero_compte = $numero_compte;
        $c->description = $description;
        $c->save();
        session(['success'=>true]);
        return redirect()->action('comptabilite\Categorie_budgetairesController@index');
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
            $c=CategorieBudgetaire::find($id);
            $c->delete();
            session(['success'=>true]);
        } catch (QueryException $fe) {
            return  redirect()->to(URL::previous())->withErrors(['constraint_violation' => trans('admin.cannot_delete')]);
        }
        return redirect()->action('comptabilite\Categorie_budgetairesController@index');
    }


}
