<?php

namespace gesecole\Http\Controllers\inscriptions;

use gesecole\bourse;
use gesecole\eleve;
use gesecole\inscription;
use Illuminate\Http\Request;
use gesecole\Http\Controllers\Controller;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;

class bourseController extends Controller

{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $idCampus = $this->currentCampusId();

        $boursesQuery = bourse::with('eleve')->orderBy('annee_scolaire', 'desc');
        if (Schema::hasColumn('bourses', 'id_campus')) {
            $boursesQuery->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
        }
        $bourses = $boursesQuery->paginate(50);

        $eleves = eleve::join('inscription', 'inscription.id_eleve', '=', 'eleve.id')
            ->where('inscription.annee_scolaire', '=', session('year'))
            ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
            ->where('inscription.desinscrire', '=', 0)
            ->select(['eleve.id', 'matricule', 'nom', 'prenom','inscription.type_scolarite', 'inscription.id as id_inscription'])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->get();
        return view('inscriptions.bourses.index')->with('bourses', $bourses)
            ->with('eleves', $eleves)
            ->with('titre', trans('admin.bourse'));

        // $elevess = eleve::inscrits()->orderBy('nom')->orderBy('prenom')->get();

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "id_eleve" => "required|numeric",
            "montant" => "required|numeric",
            "annee_scolaire" => "required"
        ]);

        $idCampus = $this->currentCampusId();
        $inscription = inscription::where('id_eleve', '=', $request->input('id_eleve'))
            ->where('annee_scolaire', '=', $request->input('annee_scolaire'))
            ->where('id_campus', '=', $idCampus)
            ->where('desinscrire', '=', 0)
            ->first();

        if (!$inscription) {
            return redirect(URL::previous())->withErrors(array('id_eleve' => "Aucune inscription active n'existe pour cet eleve dans ce campus et cette annee scolaire."));
        }

        $bourseQuery = bourse::where('id_eleve', '=', $request->input('id_eleve'))
            ->where('annee_scolaire', '=', $request->input('annee_scolaire'));
        if (Schema::hasColumn('bourses', 'id_campus')) {
            $bourseQuery->where('id_campus', '=', $idCampus);
        }

        $nn = $bourseQuery->first();
        if (!$nn) {
            $nn = new bourse();
        }
        $nn->id_eleve=$request->input('id_eleve');
        $nn->montant=$request->input('montant');
        $nn->annee_scolaire=$request->input('annee_scolaire');
        if (Schema::hasColumn('bourses', 'id_campus')) {
            $nn->id_campus=$idCampus;
        }
        $nn->save();

        //BourseService::create(new bourse($request->toArray()));
        return redirect(URL::previous())->with('success', trans('admin.succes'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            "id" => "required|numeric",
            "montant" => "required|numeric",
            "annee_scolaire" => "required"
        ]);
        $idCampus = $this->currentCampusId();
        $bourseQuery = bourse::where('id', '=', $request->input('id'));
        if (Schema::hasColumn('bourses', 'id_campus')) {
            $bourseQuery->where('id_campus', '=', $idCampus);
        }
        $nn = $bourseQuery->first();
        if (!$nn) {
            return redirect(URL::previous())->withErrors(array('id' => trans('admin.scholarship_not_exists')));
        }
       // $nn->id_eleve=$request->input('id_eleve');
        $nn->montant=$request->input('montant');
        $nn->annee_scolaire=$request->input('annee_scolaire');
        if (Schema::hasColumn('bourses', 'id_campus')) {
            $nn->id_campus=$idCampus;
        }
        $nn->save();
       // BourseService::update(new bourse($request->toArray()));
        return redirect(URL::previous())->with('success', trans('admin.succes'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $query = bourse::where('id', '=', $id);
        if (Schema::hasColumn('bourses', 'id_campus')) {
            $query->where('id_campus', '=', $this->currentCampusId());
        }
        $bourse = $query->first();
        if (!$bourse) {
            return redirect(URL::previous())->withErrors(array('id' => trans('admin.scholarship_not_exists')));
        }
        $bourse->delete();
        return redirect(URL::previous())->with('success', trans('admin.succes'));
    }
}
