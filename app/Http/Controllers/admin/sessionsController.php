<?php namespace gesecole\Http\Controllers\admin;

use gesecole\classe;
use gesecole\classeMatiere;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\cycle;
use gesecole\inscription;
use gesecole\matiere;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parametres;
use gesecole\Session_exam;
use gesecole\TutelleAcademique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class sessionsController extends Controller {
    private $sessionExamIdNeedsManualValue = null;

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title' => trans('admin.dashboard')]);

        $year = session('year');
        if ($year == null) {
            $p = parametres::whereNom('annee_scolaire')->first();

            if (!$p) {
                $p = new parametres();
                $p->nom = 'annee_scolaire';
                $p->valeur = '2017-2018';
                $p->save();
            }
            session(['year' => $p->valeur]);
        }
    }

    /**
     * Liste des sessions pour une classe / niveau, à partir du programme de classe
     * URL type : /admin/sessions?id=1&cycle=2&nniveau=3
     */
    public function index(Request $request, $id_classe_matiere)
    {
        $id_classe  = $request->get('id_classe');   // envoyé dans l’URL
        $id_matiere = $request->get('id_matiere');  // envoyé dans l’URL
        $annee      = session('year');

        // Liste des années académiques (pour une future liste déroulante)
        $annees = inscription::select('annee_scolaire')
            ->distinct()
            ->orderBy('annee_scolaire', 'desc')
            ->get();

        // 1. Classe
        $classe = classe::findOrFail($id_classe);

        // 2. Programme (classe_matiere)
        $programme = classeMatiere::findOrFail($id_classe_matiere);

        // 3. Niveau + cycle
        $id_nniveau = $programme->id_nniveau;
        $id_cycle   = property_exists($programme, 'id_cycle')
            ? $programme->id_cycle
            : null;

        $niveau = NNiveau::find($id_nniveau);

        // 4. Matière (pour afficher son nom dans le titre)
        $matiere = matiere::find($id_matiere); // ⚠️ change en "matiere" si besoin
        $libelle_matiere = '';
        if ($matiere) {
            $libelle_matiere = (app()->getLocale() == 'en')
                ? $matiere->nom_anglais
                : $matiere->nom;
        }

        // 5. On garde un tableau $programmes pour rester compatible avec la vue
        $programmes = [$programme];
        $tutelles = $this->tutellesForClasse($id_classe);
        $selectedTutelle = $request->get('id_tutelle');
        if(!$selectedTutelle && count($tutelles) == 1) {
            $selectedTutelle = $tutelles[0]->id;
        }

        // 6. Sessions de ce programme + année
      /*  $sessions = Session_exam::where('id_classe_matiere', $id_classe_matiere)
            ->where('annee_academique', $annees)
            ->get();*/
        $sessionsQuery = Session_exam::where('id_classe_matiere', $id_classe_matiere);
        if($this->sessionExamHasTutelle() && $selectedTutelle) {
            $sessionsQuery->where('id_tutelle', '=', $selectedTutelle);
        }
        if($this->sessionExamHasTutelle()) {
            $sessionsQuery->with('tutelle');
        }
        $sessions = $sessionsQuery->orderBy('annee_academique', 'desc')->get();


        $values = [
            'titre'           => trans('admin.sessions_titre'),
            'classe'          => $classe,
            'id_cycle'        => $id_cycle,
            'niveau'          => $niveau,
            'id_nniveau'      => $id_nniveau,
            'programmes'      => $programmes,
            'sessions'        => $sessions,
            'annees'          => $annees,
            'annee'           => $annee,
            'id_matiere'      => $id_matiere,
            'libelle_matiere' => $libelle_matiere,
            'tutelles'        => $tutelles,
            'selected_tutelle'=> $selectedTutelle,
            'has_session_tutelle' => $this->sessionExamHasTutelle(),
        ];

        if(session('success')){
            \Session::forget('success');
            $values['success']=true;
        }


        return view('admin/sessions', $values);
    }

    public function globalIndex(Request $request)
    {
        $annee = $request->get('annee_academique', session('year'));
        $selected = [
            'annee_academique' => $annee,
            'id_tutelle' => $request->get('id_tutelle'),
            'id_classe' => $request->get('id_classe'),
            'id_cycle' => $request->get('id_cycle'),
            'id_filiere' => $request->get('id_filiere'),
            'id_nniveau' => $request->get('id_nniveau'),
            'semestre' => $request->get('semestre'),
        ];

        $programmes = $this->globalProgrammeQuery($selected)->get(['classe_matiere.*']);
        $programmeIds = $programmes->lists('id');
        $selectedTutelleSpecialites = $this->classesForTutelle($selected['id_tutelle']);
        $existingSessions = 0;
        if(count($programmeIds) > 0) {
            $existingQuery = Session_exam::whereIn('id_classe_matiere', $programmeIds)->where('annee_academique', $annee);
            if($this->sessionExamHasTutelle() && $selected['id_tutelle']) {
                $existingQuery->where('id_tutelle', '=', $selected['id_tutelle']);
            } elseif($this->sessionExamHasTutelle()) {
                $existingQuery->whereNull('id_tutelle');
            }
            $existingSessions = $existingQuery->count();
        }
        $targetSpecialites = $this->countDistinctValues($programmes, 'id_classe');
        $targetCycles = $this->countDistinctValues($programmes, 'id_cycle');

        $values = [
            'titre' => 'Sessions examens globales',
            'annee' => $annee,
            'annees' => inscription::select('annee_scolaire')->distinct()->orderBy('annee_scolaire', 'desc')->get(),
            'tutelles' => TutelleAcademique::orderBy('nom')->get(),
            'classes' => $this->availableSessionClasses($selected),
            'selected_tutelle_specialites' => $selectedTutelleSpecialites,
            'cycles' => cycle::orderBy('nom')->get(),
            'filieres' => niveau::orderBy('nom')->get(),
            'nniveaux' => NNiveau::orderBy('nom')->get(),
            'selected' => $selected,
            'target_programmes' => $programmes->count(),
            'target_specialites' => $targetSpecialites,
            'target_cycles' => $targetCycles,
            'existing_sessions' => $existingSessions,
        ];

        if(session('success')){
            \Session::forget('success');
            $values['success']=true;
        }
        if(session('global_session_success')){
            $values['global_session_success'] = session('global_session_success');
            \Session::forget('global_session_success');
        }

        return view('admin.sessions_global', $values);
    }

    public function globalStore(Request $request)
    {
        $this->validate($request, [
            'annee_academique'  => 'required',
            'id_tutelle'        => 'required|numeric',
            'cc1'           => 'numeric|min:0|max:100',
            'tpe1'          => 'numeric|min:0|max:100',
            'sn1'           => 'numeric|min:0|max:100',
            'ratrappage1'   => 'numeric|min:0|max:100',
            'cc2'           => 'numeric|min:0|max:100',
            'tpe2'          => 'numeric|min:0|max:100',
            'sn2'           => 'numeric|min:0|max:100',
            'ratrappage2'   => 'numeric|min:0|max:100',
        ]);

        $selected = [
            'annee_academique' => $request->input('annee_academique'),
            'id_tutelle' => $request->input('id_tutelle'),
            'id_classe' => $request->input('id_classe'),
            'id_cycle' => $request->input('id_cycle'),
            'id_filiere' => $request->input('id_filiere'),
            'id_nniveau' => $request->input('id_nniveau'),
            'semestre' => $request->input('semestre'),
        ];

        $programmes = $this->globalProgrammeQuery($selected)->get(['classe_matiere.*']);
        if($programmes->count() == 0) {
            return redirect()->to(url('/admin/sessions/global'))->withErrors(['sessions' => 'Aucune matiere trouvee pour ces criteres.']);
        }

        $data = $this->sessionPercentagesFromRequest($request);
        $created = 0;
        $updated = 0;

        DB::transaction(function() use ($programmes, $selected, $data, &$created, &$updated) {
            foreach($programmes as $programme) {
                $sessionQuery = Session_exam::where('id_classe_matiere', '=', $programme->id)
                    ->where('annee_academique', '=', $selected['annee_academique']);

                if($this->sessionExamHasTutelle() && $selected['id_tutelle']) {
                    $sessionQuery->where('id_tutelle', '=', $selected['id_tutelle']);
                } elseif($this->sessionExamHasTutelle()) {
                    $sessionQuery->whereNull('id_tutelle');
                }

                $session = $sessionQuery->first();

                if(!$session) {
                    $session = new Session_exam();
                    $session->id_classe_matiere = $programme->id;
                    $session->id_matiere = $programme->id_matiere;
                    $session->annee_academique = $selected['annee_academique'];
                    if($this->sessionExamHasTutelle()) {
                        $session->id_tutelle = $selected['id_tutelle'] ? $selected['id_tutelle'] : null;
                    }
                    $created++;
                } else {
                    $updated++;
                }

                $this->fillSessionPercentages($session, $data);
                $this->prepareSessionExamId($session);
                $session->save();
            }
        });

        session(['global_session_success' => $created.' session(s) creee(s), '.$updated.' session(s) mise(s) a jour.']);

        return redirect()->to(url('/admin/sessions/global?'.$this->globalQueryString($selected)));
    }
    public function index3(Request $request, $id_classe_matiere)
    {
        $id_classe  = $request->get('id_classe');   // envoyé dans l’URL
        $id_matiere = $request->get('id_matiere');  // envoyé dans l’URL
        $annee      = session('year');
        $annees = inscription::select('annee_scolaire')
            ->distinct()
            ->orderBy('annee_scolaire', 'desc')
            ->get();
        // 1. Classe
        $classe = classe::findOrFail($id_classe);

        // 2. Programme (classe_matiere)
        $programme = classeMatiere::findOrFail($id_classe_matiere);

        // 3. Récupérer cycle / nniveau à partir du programme
        $id_nniveau = $programme->id_nniveau;
        $id_cycle   = property_exists($programme, 'id_cycle')
            ? $programme->id_cycle
            : null;
// 4. Niveau (objet complet, envoyé à la vue)
        $niveau = NNiveau::find($id_nniveau);
        // 4. Pour garder la même vue (qui boucle sur $programmes)
        $programmes = [$programme];

        // ⚠️ Attention au nom de la colonne :
        // si dans ta table c'est "classe_matiere", garde comme ça :
        $sessions = Session_exam::where('id_classe_matiere', $id_classe_matiere)
            ->where('annee_academique',$annee)
            ->get();

        $values = [
            'titre'      => trans('admin.sessions_titre'),
            'classe'     => $classe,
            'id_cycle'   => $id_cycle,
            'niveau'     => $niveau,     // 🔹 envoyé à la vue
            'id_nniveau' => $id_nniveau,
            'programmes' => $programmes,
            'sessions'   => $sessions,
            'annees'   => $annees,
            'annee'      => $annee,
            'id_matiere' => $id_matiere
        ];

        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }

        return view('admin/sessions', $values);
    }

    // store / update / destroy restent comme tu les avais adaptés

    public function indexko(Request $request, $id_classe_matiere)
    {
        $id_classe  = $request->get('id_classe');
        $id_matiere = $request->get('id_matiere');
        $id_cycle   = $request->get('cycle');
        $id_nniveau = $request->get('nniveau');
        $annee      = session('year');

        // Classe
        $classe = classe::findOrFail($id_classe);

        // Le programme (classe_matiere) concerné
        $programme = classeMatiere::findOrFail($id_classe_matiere);

        // Pour garder la même vue (qui boucle sur $programmes)
        $programmes = [$programme];

        // Session(s) pour cette classe_matiere et cette année
        $sessions = Session_exam::where('id_classe_matiere', $id_classe_matiere)
            ->where('annee_academique', $annee)
            ->get();

        $titre = trans('admin.sessions_titre');

        $values = [
            'titre'      => $titre,
            'classe'     => $classe,
            'id_cycle'   => $id_cycle,
            'id_nniveau' => $id_nniveau,
            'programmes' => $programmes,
            'sessions'   => $sessions,
        ];

        // Gestion du message de succès (comme dans ton codagesController)
        if (session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }

        return view('admin/sessions', $values);
    }


    public function index1(Request $request)
    {
        $id_classe   = $request->get('id');
        $id_cycle    = $request->get('cycle');
        $id_nniveau  = $request->get('nniveau');
        $annee       = session('year');

        $classe = classe::findOrFail($id_classe);

        // Programmes de la classe (classe_matiere)
        $programmes = classeMatiere::where('id_classe', $id_classe)
            ->where('id_nniveau', $id_nniveau)
            ->get();

        // Sessions existantes pour ces programmes
        $sessions = Session_exam::whereIn('classe_matiere', $programmes->pluck('id'))
            ->where('annee_academique', $annee)
            ->get();

        $titre = trans('admin.sessions_titre'); // à ajouter dans tes fichiers de langue

        $values = [
            'titre'      => $titre,
            'classe'     => $classe,
            'id_cycle'   => $id_cycle,
            'id_nniveau' => $id_nniveau,
            'programmes' => $programmes,
            'sessions'   => $sessions,
        ];

        if (session('success')) {
            Session_exam::forget('success');
            $values['success'] = true;
        }

        return view('admin/sessions', $values);
    }

    /**
     * Création d'une session
     */

    public function store(Request $request)
    {
        $this->validate($request, [
            'id_classe_matiere' => 'required|numeric',
            'id_matiere'        => 'required|numeric',
            'id_tutelle'        => 'numeric',
            'annee_academique'  => 'required',   // 🔹 sélectionnée dans le formulaire

            // Tous les champs de pourcentage doivent être numériques (0–100)
            'cc1'           => 'numeric|min:0|max:100',
            'tpe1'          => 'numeric|min:0|max:100',
            'sn1'           => 'numeric|min:0|max:100',
            'ratrappage1'   => 'numeric|min:0|max:100',
            'cc2'           => 'numeric|min:0|max:100',
            'tpe2'          => 'numeric|min:0|max:100',
            'sn2'           => 'numeric|min:0|max:100',
            'ratrappage2'   => 'numeric|min:0|max:100',
        ]);

        // 🔹 On ne prend plus la session('year'), mais la valeur choisie dans le select
        if($this->sessionExamHasTutelle() && !$request->input('id_tutelle')) {
            return redirect()->back()->withErrors(['id_tutelle' => 'Choisissez la tutelle de cette session.']);
        }

        $annee = $request->input('annee_academique');

        $sessionQuery = Session_exam::where('id_classe_matiere', '=', $request->input('id_classe_matiere'))
            ->where('annee_academique', '=', $annee);
        if($this->sessionExamHasTutelle()) {
            $sessionQuery->where('id_tutelle', '=', $request->input('id_tutelle'));
        }

        $s = $sessionQuery->first();
        if(!$s) {
            $s = new Session_exam();
            $s->id_classe_matiere  = $request->input('id_classe_matiere');
            $s->id_matiere         = $request->input('id_matiere');
        }
        if($this->sessionExamHasTutelle()) {
            $s->id_tutelle = $request->input('id_tutelle') ? $request->input('id_tutelle') : null;
        }

        $s->cc1         = $request->input('cc1', 0);
        $s->tpe1        = $request->input('tpe1', 0);
        $s->sn1         = $request->input('sn1', 0);
        $s->ratrappage1 = $request->input('ratrappage1', 0);

        $s->cc2         = $request->input('cc2', 0);
        $s->tpe2        = $request->input('tpe2', 0);
        $s->sn2         = $request->input('sn2', 0);
        $s->ratrappage2 = $request->input('ratrappage2', 0);

        $s->annee_academique = $annee;   // 🔹 année venant du formulaire
        $this->prepareSessionExamId($s);
        $s->save();

        session(['success' => true]);

        // 🔹 Redirection vers la même page de sessions de la matière
        return redirect()->to(
            '/admin/sessions/' . $request->input('id_classe_matiere')
            . '?id_classe=' . $request->input('id_classe')
            . '&id_matiere=' . $request->input('id_matiere')
            . '&id_tutelle=' . $request->input('id_tutelle')
        );
    }

    public function store2(Request $request)
    {
        $this->validate($request, [
            'id_classe_matiere'  => 'required|numeric',
            'id_matiere'         => 'required|numeric',
            'annee_academique'   => 'required',   // ✅ année choisie dans le formulaire
            'cc1'                => 'nullable|numeric',
            'tpe1'               => 'nullable|numeric',
            'sn1'                => 'nullable|numeric',
            'ratrappage1'        => 'nullable|numeric',
            'cc2'                => 'nullable|numeric',
            'tpe2'               => 'nullable|numeric',
            'sn2'                => 'nullable|numeric',
            'ratrappage2'        => 'nullable|numeric',
        ]);

        // ⚠️ On ne prend plus session('year') mais la valeur envoyée par le formulaire
        $annee = $request->input('annee_academique');

        $s = new Session_exam();
        $s->id_classe_matiere  = $request->input('id_classe_matiere');
        $s->id_matiere         = $request->input('id_matiere');
        $s->cc1                = $request->input('cc1', 0);
        $s->tpe1               = $request->input('tpe1', 0);
        $s->sn1                = $request->input('sn1', 0);
        $s->ratrappage1        = $request->input('ratrappage1', 0);
        $s->cc2                = $request->input('cc2', 0);
        $s->tpe2               = $request->input('tpe2', 0);
        $s->sn2                = $request->input('sn2', 0);
        $s->ratrappage2        = $request->input('ratrappage2', 0);
        $s->annee_academique   = $annee;               // ✅ année choisie
        $this->prepareSessionExamId($s);
        $s->save();

        session(['success' => true]);

        // On renvoie vers la même page (à adapter selon ton routing)
        return redirect()->to('/admin/sessions?id='
            .$request->input('id_classe')
            .'&cycle='.$request->input('id_cycle')
            .'&nniveau='.$request->input('id_nniveau')
        );
    }
    public function update(Request $request)
    {
        $this->validate($request, [
            'id'              => 'required|numeric',
            'annee_academique'=> 'required',
            'id_tutelle'      => 'numeric',

            'cc1'           => 'numeric|min:0|max:100',
            'tpe1'          => 'numeric|min:0|max:100',
            'sn1'           => 'numeric|min:0|max:100',
            'ratrappage1'   => 'numeric|min:0|max:100',
            'cc2'           => 'numeric|min:0|max:100',
            'tpe2'          => 'numeric|min:0|max:100',
            'sn2'           => 'numeric|min:0|max:100',
            'ratrappage2'   => 'numeric|min:0|max:100',
        ]);

        if($this->sessionExamHasTutelle() && !$request->input('id_tutelle')) {
            return redirect()->back()->withErrors(['id_tutelle' => 'Choisissez la tutelle de cette session.']);
        }

        $s = Session_exam::findOrFail($request->input('id'));
        if($this->sessionExamHasTutelle()) {
            $s->id_tutelle = $request->input('id_tutelle');
        }

        $s->cc1         = $request->input('cc1', 0);
        $s->tpe1        = $request->input('tpe1', 0);
        $s->sn1         = $request->input('sn1', 0);
        $s->ratrappage1 = $request->input('ratrappage1', 0);

        $s->cc2         = $request->input('cc2', 0);
        $s->tpe2        = $request->input('tpe2', 0);
        $s->sn2         = $request->input('sn2', 0);
        $s->ratrappage2 = $request->input('ratrappage2', 0);

        $s->annee_academique = $request->input('annee_academique');  // 🔹 update année
        $s->save();

        session(['success' => true]);

        return redirect()->to(
            '/admin/sessions/' . $request->input('id_classe_matiere')
            . '?id_classe=' . $request->input('id_classe')
            . '&id_matiere=' . $request->input('id_matiere')
            . '&id_tutelle=' . $request->input('id_tutelle')
        );
    }


    public function store1(Request $request)
    {
        $this->validate($request, [
            'id_classe_matiere'  => 'required|numeric',
            'id_matiere'         => 'required|numeric',
            'cc1'             => 'nullable|numeric',
            'tpe1'            => 'nullable|numeric',
            'sn1'             => 'nullable|numeric',
            'ratrappage1'     => 'nullable|numeric',
            'cc2'             => 'nullable|numeric',
            'tpe2'            => 'nullable|numeric',
            'sn2'             => 'nullable|numeric',
            'ratrappage2'     => 'nullable|numeric',
        ]);

        $annee = session('year');

        $s = new Session_exam();
        $s->id_classe_matiere  = $request->input('id_classe_matiere');
        $s->id_matiere         = $request->input('id_matiere');
        $s->cc1             = $request->input('cc1', 0);
        $s->tpe1            = $request->input('tpe1', 0);
        $s->sn1             = $request->input('sn1', 0);
        $s->ratrappage1     = $request->input('ratrappage1', 0);
        $s->cc2             = $request->input('cc2', 0);
        $s->tpe2            = $request->input('tpe2', 0);
        $s->sn2             = $request->input('sn2', 0);
        $s->ratrappage2     = $request->input('ratrappage2', 0);
        $s->annee_academique = $annee;
        $this->prepareSessionExamId($s);
        $s->save();

        session(['success' => true]);

        // On renvoie vers la même page (classe / cycle / niveau)
        return redirect()->to('/admin/sessions?id='
            .$request->input('id_classe')
            .'&cycle='.$request->input('id_cycle')
            .'&nniveau='.$request->input('id_nniveau')
        );
    }

    /**
     * Mise à jour d'une session
     */
    public function update1(Request $request)
    {
        $this->validate($request, [
            'id'             => 'required|numeric',
            'cc1'            => 'nullable|numeric',
            'tpe1'           => 'nullable|numeric',
            'sn1'            => 'nullable|numeric',
            'ratrappage1'    => 'nullable|numeric',
            'cc2'            => 'nullable|numeric',
            'tpe2'           => 'nullable|numeric',
            'sn2'            => 'nullable|numeric',
            'ratrappage2'    => 'nullable|numeric',
        ]);

        $s = Session_exam::findOrFail($request->input('id'));

        $s->cc1         = $request->input('cc1', 0);
        $s->tpe1        = $request->input('tpe1', 0);
        $s->sn1         = $request->input('sn1', 0);
        $s->ratrappage1 = $request->input('ratrappage1', 0);
        $s->cc2         = $request->input('cc2', 0);
        $s->tpe2        = $request->input('tpe2', 0);
        $s->sn2         = $request->input('sn2', 0);
        $s->ratrappage2 = $request->input('ratrappage2', 0);
        $s->save();

        session(['success' => true]);

        return redirect()->to('/admin/sessions?id='
            .$request->input('id_classe')
            .'&cycle='.$request->input('id_cycle')
            .'&nniveau='.$request->input('id_nniveau')
        );
    }

    /**
     * Suppression d'une session
     */

    public function destroy($id, Request $request)
    {
        // On supprime la session d'examen
        Session_exam::destroy($id);
        session(['success' => true]);

        // On récupère les paramètres envoyés dans l’URL (GET)
        $id_classe         = $request->get('id_classe');
        $id_matiere        = $request->get('id_matiere');
        $id_classe_matiere = $request->get('id_classe_matiere');

        // 🔁 On revient sur la même page que store()
        return redirect()->to(
            '/admin/sessions/' . $id_classe_matiere
            . '?id_classe=' . $id_classe
            . '&id_matiere=' . $id_matiere
        );
    }

    private function globalProgrammeQuery($filters)
    {
        $query = classeMatiere::join('classe', 'classe_matiere.id_classe', '=', 'classe.id')
            ->join('matiere', 'classe_matiere.id_matiere', '=', 'matiere.id');

        if(Schema::hasTable('campus_specialite')) {
            $campusClasses = DB::table('campus_specialite')
                ->where('id_campus', '=', $this->currentCampusId())
                ->lists('id_classe');
            if(count($campusClasses) > 0) {
                $query->whereIn('classe_matiere.id_classe', $campusClasses);
            }
        }

        if(isset($filters['id_tutelle']) && $filters['id_tutelle'] && Schema::hasTable('specialite_tutelle')) {
            $tutelleClasses = DB::table('specialite_tutelle')
                ->where('id_tutelle', '=', $filters['id_tutelle'])
                ->lists('id_classe');
            if(count($tutelleClasses) > 0) {
                $query->whereIn('classe_matiere.id_classe', $tutelleClasses);
            }
        }

        if(isset($filters['id_classe']) && $filters['id_classe']) {
            $query->where('classe_matiere.id_classe', '=', $filters['id_classe']);
        }

        if(isset($filters['id_cycle']) && $filters['id_cycle']) {
            $query->where('classe_matiere.id_cycle', '=', $filters['id_cycle']);
        }

        if(isset($filters['id_filiere']) && $filters['id_filiere']) {
            $query->where('classe.id_niveau', '=', $filters['id_filiere']);
        }

        if(isset($filters['id_nniveau']) && $filters['id_nniveau']) {
            $query->where('classe_matiere.id_nniveau', '=', $filters['id_nniveau']);
        }

        if(isset($filters['semestre']) && $filters['semestre'] !== '' && $filters['semestre'] !== null) {
            $query->where('classe_matiere.semestre', '=', $filters['semestre']);
        }

        return $query->orderBy('classe.nom')->orderBy('matiere.nom');
    }

    private function sessionExamHasTutelle()
    {
        return Schema::hasColumn('session_exams', 'id_tutelle');
    }

    private function tutellesForClasse($idClasse)
    {
        if(!Schema::hasTable('specialite_tutelle')) {
            return TutelleAcademique::orderBy('nom')->get();
        }

        $ids = DB::table('specialite_tutelle')
            ->where('id_classe', '=', $idClasse)
            ->lists('id_tutelle');

        if(count($ids) <= 0) {
            return TutelleAcademique::orderBy('nom')->get();
        }

        return TutelleAcademique::whereIn('id', $ids)->orderBy('nom')->get();
    }

    private function classesForTutelle($idTutelle)
    {
        if(!$idTutelle || !Schema::hasTable('specialite_tutelle')) {
            return classe::whereRaw('1=0')->get();
        }

        $ids = DB::table('specialite_tutelle')
            ->where('id_tutelle', '=', $idTutelle)
            ->lists('id_classe');

        if(count($ids) <= 0) {
            return classe::whereRaw('1=0')->get();
        }

        return classe::whereIn('id', $ids)->orderBy('nom')->get();
    }

    private function availableSessionClasses($filters = [])
    {
        $query = classe::orderBy('nom');

        if(Schema::hasTable('campus_specialite')) {
            $classes = DB::table('campus_specialite')
                ->where('id_campus', '=', $this->currentCampusId())
                ->lists('id_classe');
            if(count($classes) > 0) {
                $query->whereIn('id', $classes);
            }
        }

        if(isset($filters['id_tutelle']) && $filters['id_tutelle'] && Schema::hasTable('specialite_tutelle')) {
            $tutelleClasses = DB::table('specialite_tutelle')
                ->where('id_tutelle', '=', $filters['id_tutelle'])
                ->lists('id_classe');
            if(count($tutelleClasses) > 0) {
                $query->whereIn('id', $tutelleClasses);
            }
        }

        if(isset($filters['id_filiere']) && $filters['id_filiere']) {
            $query->where('id_niveau', '=', $filters['id_filiere']);
        }

        if((isset($filters['id_cycle']) && $filters['id_cycle']) || (isset($filters['id_nniveau']) && $filters['id_nniveau'])) {
            $programmeClasses = classeMatiere::select('id_classe')->distinct();

            if(isset($filters['id_cycle']) && $filters['id_cycle']) {
                $programmeClasses->where('id_cycle', '=', $filters['id_cycle']);
            }

            if(isset($filters['id_nniveau']) && $filters['id_nniveau']) {
                $programmeClasses->where('id_nniveau', '=', $filters['id_nniveau']);
            }

            $query->whereIn('id', $programmeClasses->lists('id_classe'));
        }

        return $query->get();
    }

    private function sessionPercentagesFromRequest(Request $request)
    {
        return [
            'cc1' => $request->input('cc1', 0),
            'tpe1' => $request->input('tpe1', 0),
            'sn1' => $request->input('sn1', 0),
            'ratrappage1' => $request->input('ratrappage1', 0),
            'cc2' => $request->input('cc2', 0),
            'tpe2' => $request->input('tpe2', 0),
            'sn2' => $request->input('sn2', 0),
            'ratrappage2' => $request->input('ratrappage2', 0),
        ];
    }

    private function fillSessionPercentages(Session_exam $session, $data)
    {
        $session->cc1 = $data['cc1'];
        $session->tpe1 = $data['tpe1'];
        $session->sn1 = $data['sn1'];
        $session->ratrappage1 = $data['ratrappage1'];
        $session->cc2 = $data['cc2'];
        $session->tpe2 = $data['tpe2'];
        $session->sn2 = $data['sn2'];
        $session->ratrappage2 = $data['ratrappage2'];
    }

    private function prepareSessionExamId(Session_exam $session)
    {
        if($session->exists || !$this->sessionExamIdNeedsManualValue()) {
            return;
        }

        $session->id = ((int) Session_exam::max('id')) + 1;
    }

    private function sessionExamIdNeedsManualValue()
    {
        if($this->sessionExamIdNeedsManualValue !== null) {
            return $this->sessionExamIdNeedsManualValue;
        }

        if(DB::connection()->getDriverName() !== 'pgsql') {
            $this->sessionExamIdNeedsManualValue = false;
            return $this->sessionExamIdNeedsManualValue;
        }

        $column = DB::selectOne(
            "select column_default from information_schema.columns where table_name = ? and column_name = ?",
            ['session_exams', 'id']
        );

        $this->sessionExamIdNeedsManualValue = !$column || !$column->column_default;

        return $this->sessionExamIdNeedsManualValue;
    }

    private function globalQueryString($filters)
    {
        $params = [];
        foreach($filters as $key => $value) {
            if($value !== '' && $value !== null) {
                $params[$key] = $value;
            }
        }

        return http_build_query($params);
    }

    private function countDistinctValues($items, $field)
    {
        $values = [];
        foreach($items as $item) {
            if(isset($item->$field)) {
                $values[$item->$field] = true;
            }
        }

        return count($values);
    }



    public function destroy1($id, Request $request)
    {
        Session_exam::destroy($id);
        session(['success' => true]);

        $id_classe  = $request->get('id');
        $id_cycle   = $request->get('cycle');
        $id_nniveau = $request->get('nniveau');

        return redirect()->to('/admin/sessions?id='
            .$id_classe.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau);
    }

}
