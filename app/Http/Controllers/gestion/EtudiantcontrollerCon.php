<?php namespace gesecole\Http\Controllers\gestion;

use gesecole\classe;
use gesecole\classeMatiere;
use gesecole\Campus;
use gesecole\eleve;
use gesecole\Etudiant_user;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\inscription;
use gesecole\matiere;
use gesecole\NNiveau;
use gesecole\note;
use gesecole\verrouNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class EtudiantcontrollerCon extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    const ELEVE_USER_CONNECTED = 'eleve_user_connected';
    protected $values = [];

    public function index()
    {
        return view('gestion.login', $this->values);
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
            'username' => 'required',
            'password' => 'required',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        $user = Etudiant_user::where('username', '=', '' . $username)
            ->where('password', '=', $password)
            ->first();


        if ($user) {

            session([EtudiantcontrollerCon::ELEVE_USER_CONNECTED => $user->id]);
            return redirect('/gestion/login/etudiant/annees');

        } else {

            $error = trans('admin.identifiant');
            session(['eleve_login_error' => $error]);
            return redirect('/gestion/login');
        }

    }

    public function liste_annee()
    {
        if (!EtudiantcontrollerCon::isUserConnected()) {
            $error = trans('admin.errorr');
            session(['eleve_login_error' => $error]);
            return redirect('/gestion/login');

        }

        $user = Etudiant_user::find(session(EtudiantcontrollerCon::ELEVE_USER_CONNECTED));
        $eleve = eleve::find($user->id_eleve);

        $ins = inscription::with('classe', 'nniveau', 'cycle', 'campus')->join('etudiant_users', 'etudiant_users.id_eleve', '=', 'inscription.id_eleve')
            ->where('etudiant_users.id', '=', $user->id)
            ->select('inscription.*')
            ->orderBy('inscription.annee_scolaire', 'desc')
            ->get();

        $this->values['inscriptions'] = $ins;
        $this->values['utilisateur'] = $eleve;
        $this->values['page_title'] = trans('admin.year');

        return view('gestion.index', $this->values);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     *
     */


    public function liste_inscription(Request $request)
    {
        if (!EtudiantcontrollerCon::isUserConnected()) {
            $error = trans('admin.erreur_ecrit');
            session(['eleve_login_error' => $error]);
            return redirect('/gestion/login');

        }
        $this->validate($request, [
            'id_inscription' => 'required',
        ]);

        $id_inscription = $request->input('id_inscription');
        $selected_inscription = inscription::with('classe', 'nniveau', 'cycle', 'campus')->find($id_inscription);
        $user = Etudiant_user::find(session(EtudiantcontrollerCon::ELEVE_USER_CONNECTED));
        if (!$selected_inscription || !$user || (int)$selected_inscription->id_eleve !== (int)$user->id_eleve) {
            session(['eleve_login_error' => trans('admin.erreur_ecrit')]);
            return redirect('/gestion/login');
        }

        $eleve = eleve::find($selected_inscription->id_eleve);
        if ($selected_inscription->id_campus) {
            $campus = Campus::find($selected_inscription->id_campus);
            if ($campus) {
                session(array(
                    'campus_id' => $campus->id,
                    'campus_nom' => $campus->nom,
                    'campus_sigle' => $campus->sigle,
                    'campus_localisation' => $campus->localisation,
                    'nom_etab' => $campus->sigle ? $campus->nom.' - '.$campus->sigle : $campus->nom
                ));
            }
        }
        $portalLock = $this->studentPortalLock($selected_inscription->id_campus, $selected_inscription->annee_scolaire);
        if ($portalLock) {
            return view('gestion.note_blocked', array(
                'selected_inscription' => $selected_inscription,
                'eleve' => $eleve,
                'page_title' => trans('admin.notess'),
                'portal_lock' => $portalLock
            ));
        }


        $matieres = matiere::join('classe_matiere', 'matiere.id', '=', 'classe_matiere.id_matiere')
            ->where('classe_matiere.id_classe', '=', $selected_inscription->id_classe)
            ->where('classe_matiere.id_nniveau', '=', $selected_inscription->id_nniveau)
            ->where('classe_matiere.id_cycle', '=', $selected_inscription->id_cycle)
            ->select('matiere.*', 'classe_matiere.coef', 'classe_matiere.semestre', 'classe_matiere.type', 'classe_matiere.id as id_classe_matiere')
            ->orderBy(App::getLocale() == 'en' ? 'matiere.nom_anglais' : 'matiere.nom')
            ->get();

        $array_id_matiere = $matieres->fetch('id')->toArray();
        $noteQuery = note::where('annee_scolaire', '=', $selected_inscription->annee_scolaire)
            ->where('id_eleve', '=', $selected_inscription->id_eleve)
            ->whereIn('id_matiere', $array_id_matiere);

        if (DB::getSchemaBuilder()->hasColumn('note', 'id_campus')) {
            $noteQuery->where(function ($query) use ($selected_inscription) {
                $query->where('id_campus', '=', $selected_inscription->id_campus)
                    ->orWhereNull('id_campus');
            });
        }

        $notes = $noteQuery->get();
        $studentIds = inscription::where('annee_scolaire', '=', $selected_inscription->annee_scolaire)
            ->where('id_campus', '=', $selected_inscription->id_campus)
            ->where('id_classe', '=', $selected_inscription->id_classe)
            ->where('id_cycle', '=', $selected_inscription->id_cycle)
            ->where('id_nniveau', '=', $selected_inscription->id_nniveau)
            ->where('desinscrire', '=', 0)
            ->lists('id_eleve');

        $classNoteCounts = array();
        if (count($studentIds) > 0 && count($array_id_matiere) > 0) {
            $countsQuery = note::select('id_matiere', 'sequence', DB::raw('COUNT(*) as total'))
                ->where('annee_scolaire', '=', $selected_inscription->annee_scolaire)
                ->whereIn('id_eleve', $studentIds)
                ->whereIn('id_matiere', $array_id_matiere);

            if (DB::getSchemaBuilder()->hasColumn('note', 'id_campus')) {
                $countsQuery->where(function ($query) use ($selected_inscription) {
                    $query->where('id_campus', '=', $selected_inscription->id_campus)
                        ->orWhereNull('id_campus');
                });
            }

            $counts = $countsQuery->groupBy('id_matiere', 'sequence')->get();
            foreach ($counts as $count) {
                if (!isset($classNoteCounts[$count->id_matiere])) {
                    $classNoteCounts[$count->id_matiere] = array();
                }
                $classNoteCounts[$count->id_matiere][$count->sequence] = $count->total;
            }
        }

        $noteView = $this->buildStudentNoteView($matieres, $notes, $classNoteCounts);





        $this->values['selected_inscription']=$selected_inscription;
        $this->values['eleve']=$eleve;

        $this->values['matieres']=$matieres;
        $this->values['notes']=$notes;
        $this->values['matieres_avec_notes']=$noteView['avec_notes'];
        $this->values['matieres_sans_notes']=$noteView['sans_notes'];
        $this->values['matieres_avec_notes_par_semestre']=$noteView['avec_notes_par_semestre'];
        $this->values['matieres_sans_notes_par_semestre']=$noteView['sans_notes_par_semestre'];
        $this->values['credits_stats']=$noteView['credits'];
        $this->values['rattrapages_semestre']=$noteView['rattrapages'];
        $this->values['page_title']=trans('admin.notess');


        return view('gestion.note',$this->values);


    }

    public function notesLockedOrAnnees()
    {
        if (!EtudiantcontrollerCon::isUserConnected()) {
            $error = trans('admin.errorr');
            session(['eleve_login_error' => $error]);
            return redirect('/gestion/login');
        }

        $user = Etudiant_user::find(session(EtudiantcontrollerCon::ELEVE_USER_CONNECTED));
        if (!$user) {
            session(['eleve_login_error' => trans('admin.errorr')]);
            return redirect('/gestion/login');
        }

        $eleve = eleve::find($user->id_eleve);
        $inscriptions = inscription::with('classe', 'nniveau', 'cycle', 'campus')
            ->where('id_eleve', '=', $user->id_eleve)
            ->orderBy('annee_scolaire', 'desc')
            ->get();

        foreach ($inscriptions as $inscriptionItem) {
            $portalLock = $this->studentPortalLock($inscriptionItem->id_campus, $inscriptionItem->annee_scolaire);
            if ($portalLock) {
                return view('gestion.note_blocked', array(
                    'selected_inscription' => $inscriptionItem,
                    'eleve' => $eleve,
                    'page_title' => trans('admin.notess'),
                    'portal_lock' => $portalLock
                ));
            }
        }

        return redirect('/gestion/login/etudiant/annees');
    }

    private function studentPortalLock($campusId, $anneeScolaire)
    {
        if (!DB::getSchemaBuilder()->hasColumn('verrou_note', 'target') || !DB::getSchemaBuilder()->hasColumn('verrou_note', 'id_campus')) {
            return null;
        }

        $campusIds = array();
        if ($campusId) {
            $campusIds[] = (int)$campusId;
        } else {
            if (session('campus_id')) {
                $campusIds[] = (int)session('campus_id');
            }
            $currentCampusId = $this->currentCampusId();
            if ($currentCampusId) {
                $campusIds[] = (int)$currentCampusId;
            }
        }
        $campusIds = array_unique($campusIds);

        $now = date('Y-m-d H:i:s');
        $lockQuery = verrouNote::where('target', '=', 'student_portal')
            ->where('is_active', '=', 1)
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });

        if (count($campusIds) > 0) {
            $lockQuery->where(function ($query) use ($campusIds) {
                $query->whereIn('id_campus', $campusIds)->orWhereNull('id_campus');
            });
        }

        $locks = $lockQuery->orderBy('id', 'desc')->get();
        foreach ($locks as $lock) {
            if ($lock->annee_scolaire == $anneeScolaire) {
                return $lock;
            }
        }

        return count($locks) > 0 ? $locks->first() : null;
    }

    private function buildStudentNoteView($matieres, $notes, $classNoteCounts)
    {
        $notesByMatiere = array();
        foreach ($notes as $note) {
            if (!isset($notesByMatiere[$note->id_matiere])) {
                $notesByMatiere[$note->id_matiere] = array();
            }
            $notesByMatiere[$note->id_matiere][$note->sequence] = $note;
        }

        $avecNotes = array();
        $sansNotes = array();
        $avecNotesParSemestre = array(1 => array(), 2 => array());
        $sansNotesParSemestre = array(1 => array(), 2 => array());
        $credits = array(
            1 => array('prevus' => 0, 'valides' => 0),
            2 => array('prevus' => 0, 'valides' => 0)
        );
        $rattrapages = array(1 => array(), 2 => array());

        foreach ($matieres as $matiere) {
            $semestre = ((int)$matiere->semestre == 2) ? 2 : 1;
            $credit = (float)$matiere->coef;
            $credits[$semestre]['prevus'] += $credit;
            $matiereNotes = isset($notesByMatiere[$matiere->id]) ? $notesByMatiere[$matiere->id] : array();
            $row = $this->buildStudentMatiereRow($matiere, $matiereNotes, $semestre, $credit);
            $row['class_has_notes'] = isset($classNoteCounts[$matiere->id]) && count($classNoteCounts[$matiere->id]) > 0;

            if ($row['class_has_notes']) {
                $avecNotes[] = $row;
                $avecNotesParSemestre[$semestre][] = $row;
            } else {
                $sansNotes[] = $row;
                $sansNotesParSemestre[$semestre][] = $row;
            }

            if ($row['is_validated']) {
                $credits[$semestre]['valides'] += $credit;
            } elseif ($row['final_compose'] || $row['class_has_notes']) {
                $rattrapages[$semestre][] = $row;
            }
        }

        return array(
            'avec_notes' => $avecNotes,
            'sans_notes' => $sansNotes,
            'avec_notes_par_semestre' => $avecNotesParSemestre,
            'sans_notes_par_semestre' => $sansNotesParSemestre,
            'credits' => $credits,
            'rattrapages' => $rattrapages
        );
    }

    private function buildStudentMatiereRow($matiere, $notes, $semestre, $credit)
    {
        $ccSeq = $semestre == 1 ? 1 : 2;
        $snSeq = $semestre == 1 ? 3 : 4;
        $ratSeq = $semestre == 1 ? 5 : 6;
        $tpeSeq = $semestre == 1 ? 7 : 8;

        $cc = isset($notes[$ccSeq]) ? (float)$notes[$ccSeq]->note : null;
        $sn = isset($notes[$snSeq]) ? (float)$notes[$snSeq]->note : null;
        $rat = isset($notes[$ratSeq]) ? (float)$notes[$ratSeq]->note : null;
        $tpe = isset($notes[$tpeSeq]) ? (float)$notes[$tpeSeq]->note : null;
        $normalTotal = ($cc !== null ? $cc : 0) + ($sn !== null ? $sn : 0) + ($tpe !== null ? $tpe : 0);
        $useRattrapage = $rat !== null && ($sn === null || $rat > $sn);
        $final = $useRattrapage ? $rat : $sn;
        $total = ($cc !== null ? $cc : 0) + ($final !== null ? $final : 0) + ($tpe !== null ? $tpe : 0);

        return array(
            'matiere' => $matiere,
            'semestre' => $semestre,
            'credit' => $credit,
            'cc' => $cc,
            'sn' => $sn,
            'rattrapage' => $rat,
            'tpe' => $tpe,
            'normal_total' => $normalTotal > 100 ? 100 : round($normalTotal, 2),
            'rattrapage_retenu' => $useRattrapage,
            'total' => $total > 100 ? 100 : round($total, 2),
            'final_compose' => $sn !== null || $rat !== null,
            'is_validated' => ($sn !== null || $rat !== null) && ($total >= 50)
        );
    }


    public function change_password(Request $request){
        $validate_pass=Validator::make($request->all(),
            [
                'ancien_mot_de_passe'=>'required',
                'nouveau_mot_de_passe'=>'required|min:6',
                'password_again'=>'required|min:6',
            ]
        );



        if($validate_pass->fails()){
            $errors=$validate_pass->errors();
            $errors=$errors->all();

            return response()->json($errors);
        }
        $etudiant=Etudiant_user::find(session(EtudiantcontrollerCon::ELEVE_USER_CONNECTED));
        $password=$etudiant->password;
        $apass=$request->input('ancien_mot_de_passe');
        $npass=$request->input('nouveau_mot_de_passe');
        $cpass=$request->input('password_again');

        if (strcmp($npass,$cpass)!=0) {

            return response()->json(['invalid'=>trans('admin.invalide')]);
        }

        if (($apass !=$password)){
            return response()->json(['invalid'=>trans('admin.apass_invalide')]);
        }


        else {
            $etudiant->password=($npass);
            $etudiant->save();
            return response()->json(['success'=>trans('admin.succes')]);
        }
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
        ]);
        $id=$req->input('id');
        $password=$req->input('password');
        $c=Etudiant_user::find($id);
        $c->password=$password;
        $c->save();
        session(['success'=>true]);
        return redirect()->action('admin\EtudiantcontrollerCon@index');
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

    public static function isUserConnected() {
        if(session(EtudiantcontrollerCon::ELEVE_USER_CONNECTED) != null) {
            return true;
        }
        return false;
    }
    public function disconnect_user(){

        Session::forget(EtudiantcontrollerCon::ELEVE_USER_CONNECTED);

        return redirect('/gestion/login');

    }

}

