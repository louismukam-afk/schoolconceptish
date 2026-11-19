<?php namespace gesecole\Http\Controllers\eleves;

use gesecole\classe;
use gesecole\cycle;
use gesecole\horaire;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\Jour;
use gesecole\niveau;
use gesecole\NNiveau;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Query\Builder;


class disciplineControlleretd extends Controller {


    public function __construct()
    {
        session(['big_title'=>trans('eleve.big_title')]);
        $this->renewSchoolYear();
    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

    public function index3()
    {
        $cycles = cycle::all();
        $niveaux = niveau::all();

        return view('eleves.appel_index', [
            'cycles' => $cycles,
            'niveaux' => $niveaux
        ]);
    }
    public function classes(Request $request)
    {
        $cycle_id = $request->id_cycle;
        $niveau_id = $request->id_niveau;
        $n_niveau_id = $request->id_n_niveau;

        $classes = classe::where('id_niveau', $niveau_id)
            ->where('id_cycle', $cycle_id)
            ->get();
        // ✅ Stockage en session
        session([
            'id_cycle' => $cycle_id,
            'id_niveau' => $niveau_id,
            'id_n_niveau' => $n_niveau_id
        ]);

        // ✅ Envoi normal à la vue
        return view('eleves.appel_classes', [
            'classes' => $classes
        ]);
    }


    public function classes1(Request $request)
    {
        $cycle_id = $request->id_cycle;
        $niveau_id = $request->id_niveau;

        $classes = classe::where('id_niveau', $niveau_id)
            ->where('id_cycle', $cycle_id)
            ->get();

        return view('eleves.appel_classes', [
            'classes' => $classes
        ]);
    }
    public function selectionJour($classe)
    {
        $classe = classe::findOrFail($classe);

        $cycle = session('id_cycle');
        $niveau = session('id_niveau');
        $n_niveau = session('id_n_niveau');

        $cycleObj = DB::table('cycle')->where('id', $cycle)->first();
        $niveauObj = DB::table('niveau')->where('id', $niveau)->first();
        $nNiveauObj = DB::table('n_niveaus')->where('id', $n_niveau)->first();

        $jours = DB::table('jours')->get();

        return view('eleves.selection_jour', [
            'classe' => $classe,
            'cycle' => $cycleObj,
            'niveau' => $niveauObj,
            'n_niveau' => $nNiveauObj,
            'id_cycle' => $cycle,
            'id_niveau' => $niveau,
            'id_n_niveau' => $n_niveau,
            'jours' => $jours
        ]);
    }

    public function selectionJourGet($classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        $cycle = session('id_cycle');
        $n_niveau = session('id_n_niveau');
        $niveau = session('id_niveau');

        $cycleObj = cycle::find($cycle);
        $nNiveauObj = NNiveau::find($n_niveau);
        $niveauobf=niveau::find($niveau);

        $jours = DB::table('jours')->get();

        return view('eleves.selection_jour', [
            'classe' => $classe,
            'cycle' => $cycleObj,
            'niveau' => $niveauobf,
            'n_niveau' => $nNiveauObj,
            'id_cycle' => $cycle,
            'id_n_niveau' => $n_niveau,
            'jours' => $jours
        ]);
    }

    public function selectionJourv(Request $request,$classe)
    {
      //  $classe = classe::findOrFail($request->id_classe);
        $classe = classe::findOrFail($classe);


        $jours = DB::table('jours')->get();
        $horaires = DB::table('horaire')->orderBy('ordre')->get();

        return view('eleves.selection_jour', [
            'classe' => $classe,
            'id_cycle' => $request->id_cycle,
            'id_niveau' => $request->id_niveau,
            'id_n_niveau' => $request->id_n_niveau,
            'jours' => $jours,
            'horaires' => $horaires
        ]);
    }

    public function selectionJour3($classe_id)
    {
        $classe = classe::findOrFail($classe_id);
        $jours = DB::table('jours')->get();
        $horaires = DB::table('horaire')->orderBy('ordre')->get();

        return view('eleves.selection_jour', compact('classe','jours','horaires'));
    }
    public function getNiveauxByCycle1($cycle_id)
    {
        $cycle = cycle::with('n_niveaux')->findOrFail($cycle_id);

        return response()->json($cycle->n_niveaux);
    }
    public function getNiveauxByCycle($cycle_id)
    {
        $niveaux = NNiveau::where('id_cycle', $cycle_id)->get();

        return response()->json($niveaux);
    }

    public function choisirCycleNiveau($classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        // On récupère tous les cycles
        $cycles = cycle::all();

        // On NE charge pas tous les niveaux ici
        // ils seront filtrés dynamiquement par cycle

        return view('eleves.choix_cycle_niveau', [
            'classe' => $classe,
            'cycles' => $cycles
        ]);
    }

    public function selectionJourPost(Request $request)
    {
        $classe = classe::findOrFail($request->id_classe);

        session([
            'id_cycle'    => $request->id_cycle,        // choisi dans formulaire
            'id_niveau'   => $classe->id_niveau,        // vient de la classe
            'id_n_niveau' => $request->id_n_niveau      // choisi dans formulaire
        ]);

       // dd(session()->all());
        return redirect()->route('appel.selection', $request->id_classe);
    }

    public function index()
    {
        $values= array('titre'=>trans('inscription.title_search'));
        $cy=cycle::all();
        $n=niveau::all();
        $c=classe::all();

        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['classes']=$c;
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }
        return view('eleves/operation',$values);
    }
    public function classesok(Request $request)
    {
        $cycle_id    = $request->id_cycle;
        $niveau_id   = $request->id_niveau;
        $n_niveau_id = $request->id_n_niveau;

        // 🔹 On récupère les classes uniquement via le niveau
        $classes = classe::where('id_niveau', $niveau_id)->get();

        // 🔹 On stocke uniquement les IDs en session
        session([
            'id_cycle'    => $cycle_id,
            'id_niveau'   => $niveau_id,
            'id_n_niveau' => $n_niveau_id
        ]);

        return view('eleves.appel_classes', [
            'classes' => $classes
        ]);
    }
    public function classesokGet($cycle, $niveau, $n_niveau)
    {
        session([
            'id_cycle'    => $cycle,
            'id_niveau'   => $niveau,
            'id_n_niveau' => $n_niveau
        ]);

        $classes = classe::where('id_niveau', $niveau)->get();

        return view('eleves.appel_classes', [
            'classes' => $classes
        ]);
    }
    public function index_etat()
    {
        $values= array('titre'=>trans('inscription.title_search'));
        $cy=cycle::all();
        $n=niveau::all();
        $c=classe::all();
        $n_n=NNiveau::all();

        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['n_niveau']=$n_n;
        $values['classes']=$c;
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }

        return view('eleves/ajouter_etat',$values);
    }


    public function index1()
    {
        $values= array('titre'=>trans('inscription.title_search'));
        $cy=cycle::all();
        $n=niveau::all();
        $c=classe::all();
        $n_n=NNiveau::all();

        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['n_niveau']=$n_n;
        $values['classes']=$c;
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }

        return view('eleves/ajouter',$values);
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
    public function formulairequidonne(Request $request)
    {
        // Sécurisation minimale
        $this->validate($request, [
            'id_classe' => 'required',
            'id_cycle' => 'required',
            'id_n_niveau' => 'required',
            'id_niveau' => 'required',
            'id_jour' => 'required',
            'date_discipline' => 'required'
        ]);


       /* $request->validate([
            'id_classe' => 'required',
            'id_cycle' => 'required',
            'id_n_niveau' => 'required',
            'id_jour' => 'required',
            'date_discipline' => 'required'
        ]);*/

        // Classe
        $classe = classe::findOrFail($request->id_classe);

        // Cycle / Niveau / Sous-niveau
        $cycle = DB::table('cycle')->where('id', $request->id_cycle)->first();
        $niveau = DB::table('niveau')->where('id', $request->id_niveau)->first();
        $jours = DB::table('jours')->where('id', $request->id_jour)->first();
        $n_niveau = DB::table('n_niveaus')
            ->where('id_cycle', $request->id_cycle)
            ->first();

        // 🔥 Récupérer toutes les plages horaires du jour sélectionné
        $horaires = DB::table('horaire')
            ->where('type', '=', 0)
            ->orderBy('ordre')
            ->get();


        // Élèves inscrits
        $eleves = DB::select("
        SELECT e.*
        FROM eleve e
        JOIN inscription i ON e.id = i.id_eleve
        WHERE i.id_classe = ?
        AND i.annee_scolaire = ?
        ORDER BY e.nom, e.prenom
    ", [$request->id_classe, session('year')]);

        return view('eleves.appel_formulaire', [
            'classe'          => $classe,
            'cycle'           => $cycle,
            'niveau'          => $niveau,
            'n_niveau'        => $n_niveau,
            'eleves'          => $eleves,
            'horaires'        => $horaires,
            'id_cycle'        => $request->id_cycle,
            'id_n_niveau' => ($n_niveau) ? $n_niveau->id : 0,
            'id_niveau'     => $request->id_niveau,
            'id_jour'         => $request->id_jour,
            'date_discipline' => $request->date_discipline
        ]);


    }
    public function formulaire(Request $request)
    {
        $classe = classe::findOrFail($request->id_classe);

        // Récupérer les élèves inscrits
        $eleves = DB::select("
        SELECT e.*
        FROM eleve e, inscription i
        WHERE e.id = i.id_eleve
        AND i.id_classe = ?
        AND i.id_cycle = ?
        AND i.id_nniveau = ?
        AND i.annee_scolaire = ?
        ORDER BY e.nom, e.prenom
    ", [$request->id_classe,
            $request->id_cycle,
            $request->id_n_niveau,
            session('year')]);

        // Récupérer toutes les plages horaires actives
        $horaires = DB::table('horaire')
            ->where('type', 0)
            ->orderBy('ordre')
            ->get();
        $cycle = DB::table('cycle')->where('id', $request->id_cycle)->first();

        $niveau = DB::table('niveau')->where('id', $request->id_niveau)->first();
        $jours = DB::table('jours')->where('id', $request->id_jour)->first();
        $n_niveau = DB::table('n_niveaus')
            ->where('id_cycle', $request->id_cycle)
            ->where('id', $request->id_n_niveau)
            ->first();
       // dd($cycle,$niveau,$n_niveau);



        // Charger les présences déjà enregistrées pour cette classe et cette date
        $presences = DB::table('discipline__new__etds')
            ->where('id_classe', $request->id_classe)
            ->where('date_discipline', $request->date_discipline)
            ->get();

        // Transformer en tableau pratique [eleve_id][horaire_id] = statut
        $presenceArray = array();

        foreach ($presences as $p) {
            $presenceArray[$p->id_eleve][$p->id_horaire] = $p->statuts_discipline;
        }

       /* return view('eleves.appel_formulaire', array(
            'classe' => $classe,
            'eleves' => $eleves,
            'horaires' => $horaires,
            'presenceArray' => $presenceArray,
            'id_jour' => $request->id_jour,
            'jours'=>$jours,
            'date_discipline' => $request->date_discipline,
            'id_cycle' => session('id_cycle'),
            'id_niveau' => session('id_niveau'),
            'id_n_niveau' => session('id_n_niveau')
        ));*/
        return view('eleves.appel_formulaire', array(
            'classe' => $classe,
            'eleves' => $eleves,
            'horaires' => $horaires,
            'presenceArray' => $presenceArray,

            'id_jour' => $request->id_jour,
            'jours' => $jours,
            'date_discipline' => $request->date_discipline,

            // 🔥 IMPORTANT : on envoie les objets
            'cycle' => $cycle,
            'niveau' => $niveau,
            'n_niveau' => $n_niveau,

            // ids si besoin
            'id_cycle' => $request->id_cycle,
            'id_niveau' => $request->id_niveau,
            'id_n_niveau' => $request->id_n_niveau
        ));

    }

    public function formulairev(Request $request)
    {
       // dd($request->all());

        $classe = classe::findOrFail($request->id_classe);

        $eleves = DB::select("
        SELECT e.*
        FROM eleve e, inscription i
        WHERE e.id = i.id_eleve
        AND i.id_classe = ?
        AND i.annee_scolaire = ?
        ORDER BY e.nom, e.prenom
    ", [$request->id_classe, session('year')]);

        return view('eleves.appel_formulaire', [
            'classe' => $classe,
            'eleves' => $eleves,
            'id_jour' => $request->id_jour,
            'id_horaire' => $request->id_horaire,
            'date_discipline' => $request->date_discipline
        ]);
    }
    public function genererEtat(Request $request)
    {
        $date_debut = $request->date_debut;
        $date_fin   = $request->date_fin;

        $etat = DB::table('discipline__new__etds as d')
            ->join('eleve as e', 'e.id', '=', 'd.id_eleve')
            ->join('classe as c', 'c.id', '=', 'd.id_classe')
            ->join('niveau as n', 'n.id', '=', 'c.id_niveau')
            ->join('n_niveaus as nn', 'nn.id', '=', 'd.id_n_niveau')
            ->join('cycle as cy', 'cy.id', '=', 'd.id_cycle')
            ->whereBetween('d.date_discipline', [$date_debut, $date_fin])
            ->where('d.statuts_discipline', 1) // absences uniquement
                ->where('annee_scolaire', session('year'))
            ->groupBy(
                'e.id',
                'e.nom',
                'e.prenom',
                'c.nom',
                'n.nom',
                'nn.nom',
                'cy.nom'
            )
            ->select(
                'e.nom',
                'e.prenom',
                'c.nom as classe',
                'n.nom as niveau',
                'nn.nom as n_niveaus',
                'cy.nom as cycle',
                DB::raw('SUM(d.nb_heures) as total_absence')
            )
            ->orderBy('cycle')
            ->orderBy('niveau')
            ->orderBy('classe')
            ->orderBy('e.nom')
            ->get();

        return view('eleves.etat_resultat', [
            'etat' => $etat,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin
        ]);
    }
    public function formPeriodeEtat($classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        $cycles     = cycle::all();
        $n_niveaus  = NNiveau::all();   // ← IMPORTANT

        return view('eleves.etat_periode', [
            'classe'     => $classe,
            'cycles'     => $cycles,
            'n_niveaus'  => $n_niveaus
        ]);
    }

    public function formPeriodeEtatDetaille($classe_id)
    {

        $classe = classe::findOrFail($classe_id);

        $cycles     = cycle::all();
        $n_niveaus  = NNiveau::all();   // ← IMPORTANT
        $niveaus  = niveau::where('id',$classe->id_niveau)->first();   // ← IMPORTANT

        return view('eleves.etat_periode_detaille', [
            'classe'     => $classe,
            'cycles'     => $cycles,
            'n_niveaus'  => $n_niveaus,
            'niveau'  => $niveaus
        ]);
    }
    public function genererEtatDetaille(Request $request, $classe_id)
    {
        $date_debut = $request->date_debut;
        $date_fin = $request->date_fin;
        $id_cycle = $request->id_cycle;
        $id_niveau = $request->id_niveau;
        $id_n_niveau = $request->id_n_niveau;
        $annee = session('year');
       // dd($request);

        $classe = classe::findOrFail($classe_id);
        $cycle = cycle::find($request->id_cycle);
        $niveau = niveau::find($request->id_niveau);
        $n_niveau = NNiveau::find($request->id_n_niveau);
        // 🔹 Toutes les plages horaires
        $horaires = DB::table('horaire')
            ->where('type', 0)
            ->orderBy('ordre')
            ->get();

        // 🔹 Absences groupées par élève et plage horaire
        $absences = DB::table('discipline__new__etds as d')
            ->join('eleve as e', 'e.id', '=', 'd.id_eleve')
            ->where('d.id_classe', $classe_id)
            ->where('d.id_cycle', $id_cycle)
            ->where('d.id_n_niveau', $id_n_niveau)
            ->where('d.id_niveau', $id_niveau)
            ->whereBetween('d.date_discipline', [$date_debut, $date_fin])
            ->where('d.annee_scolaire', $annee)
            ->where('d.statuts_discipline', 1)
            ->select(
                'd.id_eleve',
                'e.nom',
                'e.prenom',
                'd.id_horaire',
                DB::raw('SUM(d.nb_heures) as total_heures')
            )
            ->groupBy('d.id_eleve', 'e.nom', 'e.prenom', 'd.id_horaire')
            ->get();

        // 🔹 Transformation tableau [eleve][horaire] = heures
        $tableau = [];
        $totauxEleve = [];

        foreach ($absences as $a) {

            $tableau[$a->id_eleve]['nom'] = $a->nom;
            $tableau[$a->id_eleve]['prenom'] = $a->prenom;
            $tableau[$a->id_eleve]['heures'][$a->id_horaire] = $a->total_heures;

            if (!isset($totauxEleve[$a->id_eleve])) {
                $totauxEleve[$a->id_eleve] = 0;
            }

            $totauxEleve[$a->id_eleve] += $a->total_heures;
        }

        return view('eleves.etat_resultat_specialite_detaille', [
            'classe' => $classe,
            'cycle' => $cycle,
            'niveau' => $niveau,
            'n_niveau' => $n_niveau,
            'horaires' => $horaires,
            'tableau' => $tableau,
            'totauxEleve' => $totauxEleve,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin
        ]);
    }


    public function getNNiveauxByCycle($cycle_id)
    {
        try {

            $n_niveaux = NNiveau::where('id_cycle', $cycle_id)
                ->orderBy('nom')
                ->get();

            return response()->json($n_niveaux);

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ], 500);

        }
    }



    public function formPeriodeEtat1($classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        return view('eleves.etat_periode', [
            'classe' => $classe
        ]);
    }
    public function genererEtatSpecialite(Request $request, $classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        $data = DB::table('discipline__new__etds as d')
            ->join('eleve as e', 'e.id', '=', 'd.id_eleve')
            ->join('cycle as cy', 'cy.id', '=', 'd.id_cycle')
            ->join('niveau as n', 'n.id', '=', 'd.id_niveau')
            ->join('n_niveaus as nn', 'nn.id', '=', 'd.id_n_niveau')
            ->select(
                'e.nom',
                'e.prenom',
                'cy.nom as cycle',
                'n.nom as filiere',
                'nn.nom as sous_niveau',
                DB::raw("SUM(d.nb_heures) as total_absence")
            )
            ->where('d.id_classe', $classe_id)
            ->whereBetween('d.date_discipline', [
                $request->date_debut,
                $request->date_fin
            ])
            ->where('d.annee_scolaire', session('year'))
            ->where('d.statuts_discipline', 1)
            ->groupBy(
                'e.nom',
                'e.prenom',
                'cy.nom',
                'n.nom',
                'nn.nom'
            )
            ->orderBy('total_absence', 'desc')
            ->get();

        return view('eleves.etat_resultat_specialite', [
            'data' => $data,
            'classe' => $classe,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin
        ]);
    }

    public function genererEtatFiltre(Request $request)
    {
        $data = DB::table('discipline__new__etds as d')
            ->join('eleve as e', 'e.id', '=', 'd.id_eleve')
            ->join('classe as c', 'c.id', '=', 'd.id_classe')
            ->join('cycle as cy', 'cy.id', '=', 'd.id_cycle')
            ->join('niveau as n', 'n.id', '=', 'd.id_niveau')
            ->join('n_niveaus as nn', 'nn.id', '=', 'd.id_n_niveau')
            ->select(
                'e.nom',
                'e.prenom',
                'cy.nom as cycle',
                'n.nom as filiere',
                'nn.nom as sous_niveau',
                'c.nom as specialite',
                DB::raw("SUM(d.nb_heures) as total_absence")
            )
            ->whereBetween('d.date_discipline', [
                $request->date_debut,
                $request->date_fin
            ])
            ->where('d.annee_scolaire', $request->annee_scolaire)
            ->where('d.statuts_discipline', 1)
            ->when($request->id_cycle, function ($q) use ($request) {
                $q->where('d.id_cycle', $request->id_cycle);
            })
            ->when($request->id_niveau, function ($q) use ($request) {
                $q->where('d.id_niveau', $request->id_niveau);
            })
            ->when($request->id_classe, function ($q) use ($request) {
                $q->where('d.id_classe', $request->id_classe);
            })
            ->when($request->id_n_niveau, function ($q) use ($request) {
                $q->where('d.id_n_niveau', $request->id_n_niveau);
            })
            ->groupBy(
                'e.nom',
                'e.prenom',
                'cy.nom',
                'n.nom',
                'nn.nom',
                'c.nom'
            )
            ->orderBy('total_absence', 'desc')
            ->get();

        return view('eleves.etat_resultat_filtre', [
            'data' => $data,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin
        ]);
    }

    public function genererEtatFiltre1(Request $request)
    {
        $date_debut = $request->date_debut;
        $date_fin = $request->date_fin;

        $id_cycle = $request->id_cycle;
        $id_niveau = $request->id_niveau;
        $id_n_niveau = $request->id_n_niveau;
        $id_classe = $request->id_classe;
        $annee = $request->annee_scolaire;

        // Récupération des infos descriptives
        $cycle = DB::table('cycle')->where('id', $id_cycle)->first();
        $niveau = DB::table('niveau')->where('id', $id_niveau)->first();
        $n_niveau = DB::table('n_niveaus')->where('id', $id_n_niveau)->first();
        $classe = DB::table('classe')->where('id', $id_classe)->first();

        // Requête principale
        $data = DB::select("
        SELECT 
            e.id,
            e.matricule,
            e.nom,
            e.prenom,
            COALESCE(SUM(d.nb_heures), 0) as total_absence
        FROM eleve e
        JOIN inscription i ON e.id = i.id_eleve
        LEFT JOIN discipline__new__etds d ON 
            d.id_eleve = e.id
            AND d.id_cycle = ?
            AND d.id_niveau = ?
            AND d.id_n_niveau = ?
            AND d.id_classe = ?
            AND d.statuts_discipline = 1
            AND d.annee_scolaire = ?
            AND d.date_discipline BETWEEN ? AND ?
        WHERE i.id_classe = ?
        AND i.annee_scolaire = ?
        GROUP BY e.id
        ORDER BY e.nom, e.prenom
    ", [
            $id_cycle,
            $id_niveau,
            $id_n_niveau,
            $id_classe,
            $annee,
            $date_debut,
            $date_fin,
            $id_classe,
            $annee
        ]);

        return view('eleves.etat_resultat_filtre', [
            'data' => $data,
            'cycle' => $cycle,
            'niveau' => $niveau,
            'n_niveau' => $n_niveau,
            'classe' => $classe,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'annee' => $annee
        ]);
    }

    /*use Maatwebsite\Excel\Facades\Excel;
    use App\Exports\EtatAbsenceExport;*/

    public function exportExcelEtat($date_debut, $date_fin)
    {
        $data = DB::table('discipline__new__etds as d')
            ->join('eleve as e', 'e.id', '=', 'd.id_eleve')
            ->join('classe as c', 'c.id', '=', 'd.id_classe')
            ->join('niveau as n', 'n.id', '=', 'c.id_niveau')
            ->join('n_niveaus as nn', 'nn.id', '=', 'd.id_n_niveau')
            ->join('cycle as cy', 'cy.id', '=', 'd.id_cycle')
            ->whereBetween('d.date_discipline', [$date_debut, $date_fin])
            ->where('d.statuts_discipline', 1) // Absences seulement
            ->groupBy(
                'e.id',
                'e.nom',
                'e.prenom',
                'c.nom',
                'n.nom',
                'nn.nom',
                'cy.nom'
            )
            ->select(
                'e.nom',
                'e.prenom',
                'c.nom as classe',
                'n.nom as filiere',
                'nn.nom as n_niveau',
                'cy.nom as cycle',
                DB::raw('SUM(d.nb_heures) as total_absence')
            )
            ->orderBy('cy.nom')
            ->orderBy('n.nom')
            ->orderBy('c.nom')
            ->orderBy('e.nom')
            ->get();

        return Excel::download(
            new EtatAbsenceExport($data, $date_debut, $date_fin),
            'etat_absence_'.$date_debut.'_au_'.$date_fin.'.xlsx'
        );
    }


    public function storePresence(Request $request)
    {
        $user = \Auth::user();
        $canCreate = $user->canDoAction(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE);
        $canUpdate = $user->canDoAction(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE);
        // Sécurité minimale
        if (!$request->presence) {
            return redirect()->back()->with('error', 'Aucune donnée envoyée');
        }
        $existingRecords = array();
        foreach ($request->presence as $eleve_id => $horaires) {
            foreach ($horaires as $horaire_id => $statut) {
                $existingRecords[$eleve_id][$horaire_id] = DB::table('discipline__new__etds')
                    ->where('id_eleve', $eleve_id)
                    ->where('id_horaire', $horaire_id)
                    ->where('date_discipline', $request->date_discipline)
                    ->first();
                if ($existingRecords[$eleve_id][$horaire_id] ? !$canUpdate : !$canCreate) {
                    abort(403);
                }
            }
        }
//        dd($request->id_jour);
       // dd($request->all());

        $idPersonnel = \Auth::user()->id;
        $dateNow = date('Y-m-d H:i:s');

        foreach ($request->presence as $eleve_id => $horaires) {

            foreach ($horaires as $horaire_id => $statut) {

                // Vérifier si l'enregistrement existe déjà
                $exist = $existingRecords[$eleve_id][$horaire_id];

                if ($exist) {

                    // UPDATE
                    DB::table('discipline__new__etds')
                        ->where('id', $exist->id)
                        ->update([
                            'id_jour'            => $request->id_jour,
                            'statuts_discipline' => $statut,
                            'id_personnel'       => $idPersonnel,
                            'annee_scolaire'     => session('year'),
                            'type'               => 0,
                            'nb_heures'          => ($statut == 1) ? 2 : 0,
                            'heure_debut'        => '',
                            'heure_fin'          => '',
                            'id_classe'          => $request->id_classe,
                            'id_cycle'           => $request->id_cycle,
                            'id_niveau'          => $request->id_niveau,
                            'id_n_niveau'        => $request->id_n_niveau,
                            'updated_at'         => $dateNow
                        ]);

                } else {

                    // INSERT
                    DB::table('discipline__new__etds')
                        ->insert([
                            'id_eleve'           => $eleve_id,
                            'id_horaire'         => $horaire_id,
                            'id_jour'            => $request->id_jour,
                            'statuts_discipline' => $statut,
                            'id_personnel'       => $idPersonnel,
                            'annee_scolaire'     => session('year'),
                            'type'               => 0,
                            'nb_heures'          => ($statut == 1) ? 2 : 0,
                            'heure_debut'        => '',
                            'heure_fin'          => '',
                            'id_classe'          => $request->id_classe,
                            'id_cycle'           => $request->id_cycle,
                            'id_niveau'          => $request->id_niveau,
                            'id_n_niveau'        => $request->id_n_niveau,
                            'date_discipline'    => $request->date_discipline,
                            'created_at'         => $dateNow,
                            'updated_at'         => $dateNow
                        ]);
                }
            }
        }

        return redirect()
            ->route('appel.selection', $request->id_classe)
            ->with('success', true);
    }

    public function storePresencesecond(Request $request)
    {
        foreach ($request->presence as $eleve_id => $horaires)
        {
            foreach ($horaires as $horaire_id => $statut)
            {
                DB::table('discipline__new__etds')
                    ->updateOrInsert(
                        [
                            'id_eleve'       => $eleve_id,
                            'id_horaire'     => $horaire_id,
                            'date_discipline'=> $request->date_discipline
                        ],
                        [
                            'id_jour'        => $request->id_jour,
                            'statuts_discipline' => $statut,
                            'id_personnel'   => \Auth::user()->id,
                            'annee_scolaire' => session('year'),
                            'type'           => 0,
                            'nb_heures'      => ($statut == 1) ? 2 : 0,
                            'heure_debut'    => '',
                            'heure_fin'      => '',
                            'id_classe'      => $request->id_classe,
                            'id_cycle'       => $request->id_cycle,
                            'id_niveau'      => $request->id_niveau,
                            'id_n_niveau'    => $request->id_n_niveau,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]
                    );
            }
        }

        return redirect()
            ->route('appel.selection',
                $request->id_classe,
                $request->id_cycle,
                $request->id_niveau,
                $request->id_n_niveau)
            ->with('success', true);
    }



    public function storePresencev(Request $request)
    {
      // dd($request->all());
        foreach ($request->presence as $eleve_id => $statut)
        {
            DB::table('discipline__new__etds')->insert([
                'id_horaire' => $request->id_horaire,
                'id_jour' => $request->id_jour,
                'statuts_discipline' => $statut,
                'id_eleve' => $eleve_id,
                'id_personnel' => \Auth::user()->id,
                'annee_scolaire' => session('year'),
                'type' => 0,
                'nb_heures' => ($statut == 1) ? 2 : 0,
                'heure_debut' => '',
                'heure_fin' => '',
                'id_classe' => $request->id_classe,
                'id_cycle' => 0,
                'id_niveau' => 0,
                'id_n_niveau' => 0,
                'date_discipline' => $request->date_discipline,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        return redirect()
            ->route('appel.selection', $request->id_classe)
            ->with('success', true);

        //return redirect()->back()->with('success',true);
    }

    public function formulaire1($classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        // Récupération dynamique des jours
        $jours = Jour::orderBy('id')->get();

        // Récupération dynamique des horaires
        $horaires = horaire::orderBy('ordre')->get();

        $eleves = DB::select("
        SELECT e.* 
        FROM eleve e, inscription i
        WHERE e.id = i.id_eleve
        AND i.id_classe = ?
        AND i.annee_scolaire = ?
        ORDER BY e.nom, e.prenom
    ", [$classe_id, session('year')]);

        return view('eleves.appel_formulaire', [
            'classe' => $classe,
            'eleves' => $eleves,
            'jours' => $jours,
            'horaires' => $horaires
        ]);
    }
    public function selectionJour1($classe_id)
    {
        $classe = classe::findOrFail($classe_id);

        $jours = Jour::orderBy('id')->get();
        $horaires = DB::table('horaire')
            ->orderBy('ordre')
            ->get();

        return view('eleves.selection_jour', [
            'classe' => $classe,
            'jours' => $jours,
            'horaires' => $horaires
        ]);
    }

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
