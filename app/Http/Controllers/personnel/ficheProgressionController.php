<?php namespace gesecole\Http\Controllers\personnel;

use gesecole\CoursEnseignant;
use gesecole\FicheProgression;
use gesecole\Http\Controllers\Controller;
use gesecole\classe;
use gesecole\cycle;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ficheProgressionController extends Controller {

    public function __construct()
    {
        session(array('big_title' => trans('menu.gestion_personnel')));
        $this->renewSchoolYear();
        $this->ensureTable();
    }

    public function index(Request $request)
    {
        $annee = $request->input('annee_scolaire', session('year'));
        $idPersonnel = $request->input('id_personnel');
        $idCampus = $this->currentCampusId();

        $enseignants = personnel::whereRaw($this->campusVisibilitySql('id_campus', $annee), array($idCampus))
            ->where(function ($query) {
                if (Schema::hasColumn('personnel', 'type_personnel')) {
                    $query->whereIn('type_personnel', array('enseignant', 'vacataire'));
                } elseif (Schema::hasColumn('personnel', 'type')) {
                    $query->where('type', '=', 0);
                }
            })
            ->orderBy('nom')
            ->get();

        $enseignantIds = $enseignants->lists('id');
        if (!is_array($enseignantIds)) {
            $enseignantIds = $enseignantIds->toArray();
        }

        $coursQuery = CoursEnseignant::with(array('personnel', 'cycle', 'filiere', 'nniveau', 'classe', 'matiere'))
            ->where('annee_scolaire', '=', $annee);
        if (count($enseignantIds)) {
            $coursQuery->whereIn('id_personnel', $enseignantIds);
        } else {
            $coursQuery->whereRaw('1 = 0');
        }
        if ($idPersonnel) {
            $coursQuery->where('id_personnel', '=', $idPersonnel);
        }
        $cours = $coursQuery->orderBy('id_personnel')->orderBy('semestre')->get();

        $fichesQuery = FicheProgression::with(array('personnel', 'cours.matiere', 'cours.classe', 'cours.nniveau', 'campus'))
            ->where('annee_scolaire', '=', $annee)
            ->whereRaw($this->campusVisibilitySql('id_campus', $annee), array($idCampus));
        if ($idPersonnel) {
            $fichesQuery->where('id_personnel', '=', $idPersonnel);
        }
        $fiches = $fichesQuery->orderBy('created_at', 'desc')->get();

        $years = FicheProgression::select('annee_scolaire')
            ->groupBy('annee_scolaire')
            ->orderBy('annee_scolaire', 'desc')
            ->lists('annee_scolaire');
        if (!is_array($years)) {
            $years = $years->toArray();
        }
        if (!in_array(session('year'), $years)) {
            $years[] = session('year');
        }
        rsort($years);

        return view('personnel.fiches_progression.index', array(
            'titre' => 'Fiches de progression',
            'enseignants' => $enseignants,
            'cours' => $cours,
            'fiches' => $fiches,
            'years' => $years,
            'selectedYear' => $annee,
            'selectedPersonnel' => $idPersonnel,
            'campus_actif' => $this->currentCampus()
        ));
    }

    public function enseignants(Request $request)
    {
        $annee = $request->input('annee_scolaire', session('year'));
        $search = trim($request->input('q'));
        $enseignants = $this->enseignantsQuery($search)->paginate(30);

        return view('personnel.fiches_progression.enseignants', array(
            'titre' => 'Fiches de progression - Enseignants',
            'enseignants' => $enseignants,
            'years' => $this->years(),
            'selectedYear' => $annee,
            'search' => $search,
            'campus_actif' => $this->currentCampus()
        ));
    }

    public function coursEnseignant(Request $request, $id)
    {
        $annee = $request->input('annee_scolaire', session('year'));
        $enseignant = $this->enseignantsQuery(null, $annee)->where('id', '=', $id)->first();
        if (!$enseignant) {
            abort(404);
        }

        $cours = CoursEnseignant::with(array('personnel', 'cycle', 'filiere', 'nniveau', 'classe', 'matiere'))
            ->where('annee_scolaire', '=', $annee)
            ->where('id_personnel', '=', $enseignant->id)
            ->orderBy('id_classe')
            ->orderBy('id_nniveau')
            ->orderBy('semestre')
            ->get();

        $ficheRows = FicheProgression::where('annee_scolaire', '=', $annee)
            ->whereRaw($this->campusVisibilitySql('id_campus', $annee), array($this->currentCampusId()))
            ->where('id_personnel', '=', $enseignant->id)
            ->get();
        $fichesParCours = array();
        foreach ($ficheRows as $fiche) {
            if (!isset($fichesParCours[$fiche->id_cours_enseignant])) {
                $fichesParCours[$fiche->id_cours_enseignant] = array();
            }
            $fichesParCours[$fiche->id_cours_enseignant][] = $fiche;
        }

        $groupes = array();
        foreach ($cours as $item) {
            $specialite = $item->classe ? $item->classe->nom : 'Specialite non renseignee';
            $niveau = $item->nniveau ? $item->nniveau->nom : 'Niveau non renseigne';
            $key = $specialite.' / '.$niveau;
            if (!isset($groupes[$key])) {
                $groupes[$key] = array();
            }
            $groupes[$key][] = $item;
        }

        return view('personnel.fiches_progression.cours_enseignant', array(
            'titre' => 'Ajouter une fiche de progression',
            'enseignant' => $enseignant,
            'groupes' => $groupes,
            'fichesParCours' => $fichesParCours,
            'years' => $this->years(),
            'selectedYear' => $annee,
            'campus_actif' => $this->currentCampus()
        ));
    }

    public function fichesEnseignant(Request $request, $id)
    {
        $annee = $request->input('annee_scolaire', session('year'));
        $enseignant = $this->enseignantsQuery(null, $annee)->where('id', '=', $id)->first();
        if (!$enseignant) {
            abort(404);
        }

        $fiches = FicheProgression::with(array('personnel', 'cours.matiere', 'cours.classe', 'cours.nniveau', 'campus'))
            ->where('annee_scolaire', '=', $annee)
            ->whereRaw($this->campusVisibilitySql('id_campus', $annee), array($this->currentCampusId()))
            ->where('id_personnel', '=', $enseignant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('personnel.fiches_progression.fiches_enseignant', array(
            'titre' => 'Fiches de progression de l enseignant',
            'enseignant' => $enseignant,
            'fiches' => $fiches,
            'years' => $this->years(),
            'selectedYear' => $annee,
            'campus_actif' => $this->currentCampus()
        ));
    }

    public function parSpecialite(Request $request)
    {
        return $this->specialiteView($request, true);
    }

    public function manquantes(Request $request)
    {
        return $this->specialiteView($request, false);
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'id_personnel' => 'required|numeric',
            'id_cours_enseignant' => 'required|numeric',
            'annee_scolaire' => 'required',
            'fiche' => 'required|mimes:pdf,doc,docx'
        ));

        $cours = CoursEnseignant::where('id', '=', $request->input('id_cours_enseignant'))
            ->where('id_personnel', '=', $request->input('id_personnel'))
            ->where('annee_scolaire', '=', $request->input('annee_scolaire'))
            ->first();
        if (!$cours) {
            return redirect()->back()->withErrors(array('id_cours_enseignant' => 'Ce cours ne correspond pas a cet enseignant pour cette annee academique.'))->withInput();
        }

        $file = $request->file('fiche');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->withErrors(array('fiche' => 'Fichier invalide.'))->withInput();
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $storageDir = storage_path('app/fiches_progression/'.$request->input('annee_scolaire'));
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        $storedName = date('YmdHis').'_'.$request->input('id_personnel').'_'.$request->input('id_cours_enseignant').'.'.$extension;
        $file->move($storageDir, $storedName);

        FicheProgression::create(array(
            'id_personnel' => $request->input('id_personnel'),
            'id_cours_enseignant' => $request->input('id_cours_enseignant'),
            'annee_scolaire' => $request->input('annee_scolaire'),
            'id_campus' => $this->currentCampusId(),
            'titre' => $request->input('titre'),
            'description' => $request->input('description'),
            'fichier_original' => $file->getClientOriginalName(),
            'fichier_path' => 'app/fiches_progression/'.$request->input('annee_scolaire').'/'.$storedName,
            'fichier_extension' => $extension,
            'fichier_mime' => $file->getClientMimeType(),
            'id_user' => Auth::user() ? Auth::user()->id : null
        ));

        session(array('success' => true));
        if ($request->input('redirect_to')) {
            return redirect()->to($request->input('redirect_to'));
        }
        return redirect()->route('fiches_progression', array(
            'annee_scolaire' => $request->input('annee_scolaire'),
            'id_personnel' => $request->input('id_personnel')
        ));
    }

    public function show($id)
    {
        $fiche = FicheProgression::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if (!$fiche) {
            abort(404);
        }
        $path = storage_path($fiche->fichier_path);
        if (!File::exists($path)) {
            abort(404);
        }

        $contentType = $fiche->fichier_mime ? $fiche->fichier_mime : ($fiche->fichier_extension == 'pdf' ? 'application/pdf' : 'application/octet-stream');
        return Response::make(File::get($path), 200, array(
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="'.$fiche->fichier_original.'"'
        ));
    }

    public function download($id)
    {
        $fiche = FicheProgression::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if (!$fiche) {
            abort(404);
        }
        $path = storage_path($fiche->fichier_path);
        if (!File::exists($path)) {
            abort(404);
        }
        return Response::download($path, $fiche->fichier_original);
    }

    public function destroy($id)
    {
        $fiche = FicheProgression::where('id', '=', $id)->where('id_campus', '=', $this->currentCampusId())->first();
        if ($fiche) {
            $path = storage_path($fiche->fichier_path);
            if (File::exists($path)) {
                File::delete($path);
            }
            $fiche->delete();
            session(array('success' => true));
        }
        return redirect()->back();
    }

    private function ensureTable()
    {
        if (Schema::hasTable('fiches_progression')) {
            return;
        }
        Schema::create('fiches_progression', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_personnel')->index();
            $table->integer('id_cours_enseignant')->index();
            $table->string('annee_scolaire')->index();
            $table->integer('id_campus')->nullable()->index();
            $table->string('titre')->nullable();
            $table->text('description')->nullable();
            $table->string('fichier_original');
            $table->string('fichier_path');
            $table->string('fichier_extension', 20);
            $table->string('fichier_mime')->nullable();
            $table->integer('id_user')->nullable();
            $table->timestamps();
        });
    }

    private function enseignantsQuery($search = null, $year = null)
    {
        $query = personnel::whereRaw($this->campusVisibilitySql('id_campus', $year), array($this->currentCampusId()))
            ->where(function ($q) {
                if (Schema::hasColumn('personnel', 'type_personnel')) {
                    $q->whereIn('type_personnel', array('enseignant', 'vacataire'));
                } elseif (Schema::hasColumn('personnel', 'type')) {
                    $q->where('type', '=', 0);
                }
            });
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'LIKE', '%'.$search.'%')
                    ->orWhere('matricule', 'LIKE', '%'.$search.'%');
            });
        }
        return $query->orderBy('nom');
    }

    private function years()
    {
        $years = FicheProgression::select('annee_scolaire')
            ->groupBy('annee_scolaire')
            ->orderBy('annee_scolaire', 'desc')
            ->lists('annee_scolaire');
        if (!is_array($years)) {
            $years = $years->toArray();
        }
        if (!in_array(session('year'), $years)) {
            $years[] = session('year');
        }
        rsort($years);
        return $years;
    }

    private function specialiteView(Request $request, $avecFiche)
    {
        $annee = $request->input('annee_scolaire', session('year'));
        $idCycle = $request->input('id_cycle');
        $idNiveau = $request->input('id_niveau');
        $idClasse = $request->input('id_classe');
        $idNniveau = $request->input('id_nniveau');
        $enseignantIds = $this->enseignantsQuery(null, $annee)->lists('id');
        if (!is_array($enseignantIds)) {
            $enseignantIds = $enseignantIds->toArray();
        }

        $ficheRows = FicheProgression::with(array('personnel', 'cours.matiere', 'cours.classe', 'cours.nniveau'))
            ->where('annee_scolaire', '=', $annee)
            ->whereRaw($this->campusVisibilitySql('id_campus', $annee), array($this->currentCampusId()))
            ->get();
        $fichesParCours = array();
        foreach ($ficheRows as $fiche) {
            if (!isset($fichesParCours[$fiche->id_cours_enseignant])) {
                $fichesParCours[$fiche->id_cours_enseignant] = array();
            }
            $fichesParCours[$fiche->id_cours_enseignant][] = $fiche;
        }

        $coursQuery = CoursEnseignant::with(array('personnel', 'cycle', 'filiere', 'nniveau', 'classe', 'matiere'))
            ->where('annee_scolaire', '=', $annee);
        if (count($enseignantIds)) {
            $coursQuery->whereIn('id_personnel', $enseignantIds);
        } else {
            $coursQuery->whereRaw('1 = 0');
        }
        if ($idCycle) {
            $coursQuery->where('id_cycle', '=', $idCycle);
        }
        if ($idNiveau) {
            $coursQuery->where('id_niveau', '=', $idNiveau);
        }
        if ($idClasse) {
            $coursQuery->where('id_classe', '=', $idClasse);
        }
        if ($idNniveau) {
            $coursQuery->where('id_nniveau', '=', $idNniveau);
        }

        $cours = $coursQuery->orderBy('id_classe')->orderBy('id_nniveau')->orderBy('id_personnel')->get();
        $rows = array();
        foreach ($cours as $item) {
            $hasFiche = isset($fichesParCours[$item->id]) && count($fichesParCours[$item->id]) > 0;
            if (($avecFiche && $hasFiche) || (!$avecFiche && !$hasFiche)) {
                $rows[] = $item;
            }
        }

        return view($avecFiche ? 'personnel.fiches_progression.par_specialite' : 'personnel.fiches_progression.manquantes', array(
            'titre' => $avecFiche ? 'Cours avec fiches de progression' : 'Cours sans fiches de progression',
            'rows' => $rows,
            'fichesParCours' => $fichesParCours,
            'years' => $this->years(),
            'selectedYear' => $annee,
            'cycles' => cycle::orderBy('nom')->get(),
            'niveaux' => niveau::orderBy('nom')->get(),
            'classes' => classe::with('niveau')->orderBy('nom')->get(),
            'nniveaux' => NNiveau::orderBy('id_cycle')->orderBy('nom')->get(),
            'selectedCycle' => $idCycle,
            'selectedNiveau' => $idNiveau,
            'selectedClasse' => $idClasse,
            'selectedNniveau' => $idNniveau,
            'campus_actif' => $this->currentCampus()
        ));
    }
}
