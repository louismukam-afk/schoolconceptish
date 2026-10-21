<?php namespace gesecole\Http\Controllers\admin;

use gesecole\AccessRole;
use gesecole\AccessRoleAction;
use gesecole\Action;
use gesecole\fonction;
use gesecole\Http\Controllers\Controller;
use gesecole\personnel;
use gesecole\User;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use ReflectionClass;

class AccessControlController extends Controller {
    public function __construct()
    {
        session(['big_title' => trans('admin.dashboard')]);
    }

    public function index(Request $request)
    {
        $this->ensureAccessControlSchema();

        $search = $request->input('search');
        $usersQuery = User::with('accessRoles')->orderBy('fullname')->orderBy('name');

        if($search) {
            $term = strtolower($search);
            $usersQuery->whereRaw('lower(name) LIKE ? OR lower(fullname) LIKE ? OR lower(email) LIKE ?', [
                '%'.$term.'%',
                '%'.$term.'%',
                '%'.$term.'%',
            ]);
        }

        $personnelFonctions = [];
        if(Schema::hasTable('fonction_personnel')) {
            $fonctionRows = DB::table('fonction_personnel')
                ->where('annee_scolaire', '=', session('year'))
                ->orderBy('id', 'desc')
                ->get();

            foreach($fonctionRows as $fonctionRow) {
                if(!isset($personnelFonctions[$fonctionRow->id_personnel])) {
                    $personnelFonctions[$fonctionRow->id_personnel] = $fonctionRow->id_fonction;
                }
            }
        }

        $values = [
            'titre' => 'Roles et permissions',
            'roles' => AccessRole::with('actions')->orderBy('name')->get(),
            'users' => $usersQuery->get(),
            'personnels' => Schema::hasTable('personnel') ? personnel::orderBy('nom')->get() : [],
            'fonctions' => Schema::hasTable('fonction') ? fonction::orderBy('nom')->get() : [],
            'personnel_fonctions' => $personnelFonctions,
            'search_term' => $search,
        ];

        if(session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }

        return view('admin.access.index', $values);
    }

    public function storeRole(Request $request)
    {
        $this->ensureAccessControlSchema();

        $this->validate($request, [
            'name' => 'required|max:100',
            'description' => 'max:255',
        ]);

        $exists = AccessRole::where('name', '=', $request->input('name'))->first();
        if($exists) {
            return redirect()->back()->withErrors(['name' => 'Ce role existe deja.']);
        }

        $role = new AccessRole();
        $role->name = $request->input('name');
        $role->description = $request->input('description');
        $role->save();

        session(['success' => true]);
        return redirect()->to(url('/access-control'));
    }

    public function editRolePermissions($id)
    {
        $this->ensureAccessControlSchema();

        $role = AccessRole::with('actions')->findOrFail($id);
        $selected = $role->actions->fetch('action_name')->toArray();

        $values = [
            'titre' => 'Permissions du role : '.$role->name,
            'role' => $role,
            'sections' => $this->permissionSections(),
            'selected_actions' => $selected,
        ];

        if(session('success')) {
            Session::forget('success');
            $values['success'] = true;
        }
        if(session('sync_count') !== null) {
            $values['sync_count'] = session('sync_count');
            Session::forget('sync_count');
        }

        return view('admin.access.permissions', $values);
    }

    public function syncActions($id)
    {
        $this->ensureAccessControlSchema();

        $role = AccessRole::findOrFail($id);
        $actions = $this->availableActions();

        session(array(
            'success' => true,
            'sync_count' => count($actions),
        ));

        return redirect()->to(url('/access-control/roles/'.$role->id.'/permissions'));
    }
    public function updateRolePermissions(Request $request, $id)
    {
        $this->ensureAccessControlSchema();

        $role = AccessRole::findOrFail($id);
        $actions = array_values(array_filter((array) $request->input('actions', array())));
        $now = date('Y-m-d H:i:s');

        AccessRoleAction::where('id_access_role', '=', $role->id)->delete();
        foreach($actions as $actionName) {
            $action = new AccessRoleAction();
            $action->id_access_role = $role->id;
            $action->action_name = $actionName;
            $action->created_at = $now;
            $action->updated_at = $now;
            $action->save();
        }

        session(['success' => true]);
        return redirect()->to(url('/access-control/roles/'.$role->id.'/permissions'));
    }

    public function updateUserRoles(Request $request, $id)
    {
        $this->ensureAccessControlSchema();

        $user = User::findOrFail($id);
        $roleIds = array_values(array_filter((array) $request->input('roles', array())));
        $idPersonnel = intval($request->input('id_personnel'));
        $idFonction = intval($request->input('id_fonction'));
        $now = date('Y-m-d H:i:s');

        if(Auth::user() && Auth::user()->id == $user->id && !$request->input('is_super_admin')) {
            return redirect()->back()->withErrors(['user' => 'Vous ne pouvez pas retirer votre propre acces super admin ici.']);
        }

        DB::table('access_role_user')->where('id_user', '=', $user->id)->delete();
        foreach($roleIds as $roleId) {
            DB::table('access_role_user')->insert([
                'id_access_role' => $roleId,
                'id_user' => $user->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $user->active = $request->input('active') ? 1 : 0;
        $user->id_personnel = $idPersonnel > 0 ? $idPersonnel : 0;
        if(Schema::hasColumn('users', 'is_super_admin')) {
            $user->is_super_admin = $request->input('is_super_admin') ? 1 : 0;
        }
        $user->save();

        if($idPersonnel > 0 && $idFonction > 0 && Schema::hasTable('fonction_personnel')) {
            $existingFonction = DB::table('fonction_personnel')
                ->where('id_personnel', '=', $idPersonnel)
                ->where('id_fonction', '=', $idFonction)
                ->where('annee_scolaire', '=', session('year'))
                ->first();

            if(!$existingFonction) {
                DB::table('fonction_personnel')->insert([
                    'id_fonction' => $idFonction,
                    'id_personnel' => $idPersonnel,
                    'annee_scolaire' => session('year'),
                ]);
            }
        }

        session(['success' => true]);
        return redirect()->to(url('/access-control'));
    }

    public function destroyRole($id)
    {
        $this->ensureAccessControlSchema();

        $role = AccessRole::findOrFail($id);
        AccessRoleAction::where('id_access_role', '=', $role->id)->delete();
        DB::table('access_role_user')->where('id_access_role', '=', $role->id)->delete();
        $role->delete();

        session(['success' => true]);
        return redirect()->to(url('/access-control'));
    }

    private function permissionSections()
    {
        $sections = [];
        $known = [];
        foreach($this->permissionCatalog() as $section => $items) {
            foreach($items as $item) {
                $known[$item['name']] = true;
                $sections[$section][] = $item;
            }
        }

        foreach($this->availableActions() as $actionName) {
            if(isset($known[$actionName])) {
                continue;
            }

            $section = $this->sectionName($actionName);
            $sections[$section][] = [
                'name' => $actionName,
                'label' => $this->actionLabel($actionName),
                'danger' => $this->isDangerousAction($actionName),
                'keywords' => $actionName,
            ];
        }

        ksort($sections);
        return $sections;
    }

    private function permissionCatalog()
    {
        return [
            'Acces complet' => [
                $this->permissionItem(Action::ACTION_ALL, 'Super administrateur', 'Tout voir, tout creer, tout modifier, tout supprimer'),
                $this->permissionItem(Action::ACTION_ALMOST_ALL, 'Administrateur delegue', 'Acces administratif large sans compte createur'),
            ],
            'Administration - Cycles' => [
                $this->permissionItem(Action::ACTION_ADMIN_CYCLE_READ, 'Cycle - Voir', 'Afficher liste cycle cycles'),
                $this->permissionItem(Action::ACTION_ADMIN_CYCLE_CREATE, 'Cycle - Creer', 'Ajouter enregistrer cycle cycles'),
                $this->permissionItem(Action::ACTION_ADMIN_CYCLE_UPDATE, 'Cycle - Modifier', 'Editer changer cycle cycles'),
                $this->permissionItem(Action::ACTION_ADMIN_CYCLE_DELETE, 'Cycle - Supprimer', 'Supprimer retirer cycle cycles'),
            ],
            'Administration - Filieres' => [
                $this->permissionItem(Action::ACTION_ADMIN_FILIERE_READ, 'Filiere - Voir', 'Afficher liste filiere filieres niveau niveaux'),
                $this->permissionItem(Action::ACTION_ADMIN_FILIERE_CREATE, 'Filiere - Creer', 'Ajouter enregistrer filiere niveau'),
                $this->permissionItem(Action::ACTION_ADMIN_FILIERE_UPDATE, 'Filiere - Modifier', 'Editer changer filiere niveau'),
                $this->permissionItem(Action::ACTION_ADMIN_FILIERE_DELETE, 'Filiere - Supprimer', 'Supprimer filiere niveau'),
            ],
            'Administration - Niveaux' => [
                $this->permissionItem(Action::ACTION_ADMIN_NNIVEAU_READ, 'Niveau - Voir', 'Afficher liste niveau nniveau'),
                $this->permissionItem(Action::ACTION_ADMIN_NNIVEAU_CREATE, 'Niveau - Creer', 'Ajouter enregistrer niveau nniveau'),
                $this->permissionItem(Action::ACTION_ADMIN_NNIVEAU_UPDATE, 'Niveau - Modifier', 'Editer changer niveau nniveau'),
                $this->permissionItem(Action::ACTION_ADMIN_NNIVEAU_DELETE, 'Niveau - Supprimer', 'Supprimer niveau nniveau'),
            ],
            'Administration - Specialites' => [
                $this->permissionItem(Action::ACTION_ADMIN_SPECIALITE_READ, 'Specialite - Voir', 'Afficher liste specialite classe'),
                $this->permissionItem(Action::ACTION_ADMIN_SPECIALITE_CREATE, 'Specialite - Creer', 'Ajouter enregistrer specialite classe'),
                $this->permissionItem(Action::ACTION_ADMIN_SPECIALITE_UPDATE, 'Specialite - Modifier', 'Editer changer specialite classe'),
                $this->permissionItem(Action::ACTION_ADMIN_SPECIALITE_DELETE, 'Specialite - Supprimer', 'Supprimer specialite classe'),
            ],
            'Administration - Tutelles' => [
                $this->permissionItem(Action::ACTION_ADMIN_TUTELLE_READ, 'Tutelle - Voir', 'Afficher liste tutelle tutelles academique'),
                $this->permissionItem(Action::ACTION_ADMIN_TUTELLE_CREATE, 'Tutelle - Creer', 'Ajouter enregistrer tutelle'),
                $this->permissionItem(Action::ACTION_ADMIN_TUTELLE_UPDATE, 'Tutelle - Modifier', 'Editer changer tutelle affecter specialites'),
                $this->permissionItem(Action::ACTION_ADMIN_TUTELLE_DELETE, 'Tutelle - Supprimer', 'Supprimer tutelle'),
            ],
            'Administration - Matieres' => [
                $this->permissionItem(Action::ACTION_ADMIN_MATIERE_READ, 'Matiere - Voir', 'Afficher liste matiere matieres'),
                $this->permissionItem(Action::ACTION_ADMIN_MATIERE_CREATE, 'Matiere - Creer', 'Ajouter enregistrer matiere'),
                $this->permissionItem(Action::ACTION_ADMIN_MATIERE_UPDATE, 'Matiere - Modifier', 'Editer changer matiere'),
                $this->permissionItem(Action::ACTION_ADMIN_MATIERE_DELETE, 'Matiere - Supprimer', 'Supprimer matiere'),
            ],
            'Administration - Programmes' => [
                $this->permissionItem(Action::ACTION_ADMIN_PROGRAMME_READ, 'Programme - Voir', 'Afficher programme specialite matiere'),
                $this->permissionItem(Action::ACTION_ADMIN_PROGRAMME_CREATE, 'Programme - Creer', 'Ajouter matiere au programme'),
                $this->permissionItem(Action::ACTION_ADMIN_PROGRAMME_UPDATE, 'Programme - Modifier', 'Modifier coefficient semestre groupe programme'),
                $this->permissionItem(Action::ACTION_ADMIN_PROGRAMME_DELETE, 'Programme - Supprimer', 'Supprimer matiere du programme'),
            ],
            'Administration - Sessions examens' => [
                $this->permissionItem(Action::ACTION_ADMIN_SESSION_EXAM_READ, 'Session examen - Voir', 'Afficher session examen matiere globale'),
                $this->permissionItem(Action::ACTION_ADMIN_SESSION_EXAM_CREATE, 'Session examen - Creer', 'Creer appliquer sessions examens toutes matieres'),
                $this->permissionItem(Action::ACTION_ADMIN_SESSION_EXAM_UPDATE, 'Session examen - Modifier', 'Modifier session examen matiere'),
                $this->permissionItem(Action::ACTION_ADMIN_SESSION_EXAM_DELETE, 'Session examen - Supprimer', 'Supprimer session examen'),
            ],
            'Administration - Journal' => [
                $this->permissionItem(Action::ACTION_ADMIN_AUDIT, 'Journal des operations - Voir', 'Audit journal operations historique activite utilisateur finance caisse banque'),
            ],
            'Inscriptions - Etudiants' => [
                $this->permissionItem(Action::ACTION_REGISTRATION, 'Inscription - Tableau de bord', 'Voir module inscription'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION, 'Inscription - Rechercher', 'Rechercher inscription etudiant'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_STUDENT_CU, 'Etudiant - Enregistrer', 'Creer enregistrer etudiant eleve'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE, 'Inscription - Inscrire un etudiant', 'Inscrire nouvel etudiant eleve'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_UPDATE, 'Inscription - Modifier', 'Modifier inscription etudiant'),
                $this->permissionItem(Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE, 'Inscription - Supprimer', 'Supprimer inscription etudiant'),
                $this->permissionItem(Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'Etudiant - Activer acces', 'Activer desactiver acces etudiant statut'),
            ],
            'Inscriptions - Listes et documents' => [
                $this->permissionItem(Action::ACTION_REGISTRATION_LIST, 'Listes - Afficher', 'Afficher liste listes inscriptions etudiants parents bourses acces'),
                $this->permissionItem(Action::ACTION_REGISTRATION_LIST_TUITION_SHEET, 'Fiche scolarite - Generer', 'Generer imprimer fiche scolarite'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE, 'Certificat de scolarite - Generer', 'Generer imprimer certificat scolarite'),
                $this->permissionItem(Action::ACTION_REGISTRATION_STUDENT_CARD, 'Carte etudiant - Imprimer', 'Generer imprimer carte etudiant'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SCAN_CARD, 'Carte etudiant - Scanner', 'Scanner carte etudiant'),
            ],
            'Inscriptions - Paiements' => [
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT, 'Paiement inscription - Voir', 'Afficher paiement inscription'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_CREATE, 'Paiement inscription - Enregistrer', 'Creer encaisser paiement inscription'),
                $this->permissionItem(Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE, 'Paiement inscription - Modifier montant', 'Modifier montant paiement inscription'),
                $this->permissionItem(Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT, 'Paiement inscription - Imprimer recu', 'Imprimer recu paiement inscription'),
            ],
            'Notes - Saisie' => [
                $this->permissionItem(Action::ACTION_STUDENT_NOTE, 'Notes - Voir', 'Afficher consulter notes'),
                $this->permissionItem(Action::ACTION_STUDENT_NOTE_CREATE, 'Notes - Creer', 'Saisir creer notes controles examens'),
                $this->permissionItem(Action::ACTION_STUDENT_NOTE_UPDATE, 'Notes - Modifier', 'Modifier corriger notes controles examens deliberer'),
                $this->permissionItem(Action::ACTION_STUDENT_NOTE_PRINT, 'Notes - Imprimer', 'Imprimer notes fiche note'),
            ],
            'Notes - PV releves attestations' => [
                $this->permissionItem(Action::ACTION_STUDENT_PV_GENERATE, 'PV - Generer', 'Generer proces verbal pv notes'),
                $this->permissionItem(Action::ACTION_STUDENT_PV_HISTORY, 'PV - Voir historique', 'Historique pv generations'),
                $this->permissionItem(Action::ACTION_STUDENT_PV_PRINT, 'PV - Imprimer', 'Imprimer visualiser pv'),
                $this->permissionItem(Action::ACTION_STUDENT_REPORT_NOTE, 'Releves - Voir', 'Afficher module releve notes'),
                $this->permissionItem(Action::ACTION_STUDENT_REPORT_NOTE_GENERATE, 'Releves - Generer', 'Generer releve notes soa ndere estic bts'),
                $this->permissionItem(Action::ACTION_STUDENT_REPORT_NOTE_HISTORY, 'Releves - Voir historique', 'Historique releve notes generations'),
                $this->permissionItem(Action::ACTION_STUDENT_REPORT_NOTE_PRINT, 'Releves - Imprimer', 'Imprimer releve notes'),
                $this->permissionItem(Action::ACTION_STUDENT_ATTESTATION_GENERATE, 'Attestation - Generer', 'Generer attestation reussite'),
                $this->permissionItem(Action::ACTION_STUDENT_ATTESTATION_HISTORY, 'Attestation - Voir historique', 'Historique attestation reussite'),
                $this->permissionItem(Action::ACTION_STUDENT_ATTESTATION_PRINT, 'Attestation - Imprimer', 'Imprimer attestation reussite'),
            ],
            'Personnel - General' => [
                $this->permissionItem(Action::ACTION_STAFF, 'Personnel - Tableau de bord', 'Voir module personnel ressources humaines'),
                $this->permissionItem(Action::ACTION_TEACHER, 'Enseignant - Acces', 'Acces enseignant professeur'),
            ],
            'Personnel - Agents' => [
                $this->permissionItem(Action::ACTION_STAFF_WORKER, 'Agents - Voir', 'Afficher liste personnel agents fonctions'),
                $this->permissionItem(Action::ACTION_STAFF_WORKER_CU, 'Agents - Creer / Modifier', 'Ajouter modifier personnel agents fonctions import'),
                $this->permissionItem(Action::ACTION_STAFF_WORKER_DELETE, 'Agents - Supprimer', 'Supprimer personnel agents fonctions'),
            ],
            'Personnel - Emploi du temps' => [
                $this->permissionItem(Action::ACTION_STAFF_TIMETABLE, 'Emploi du temps - Voir', 'Afficher emploi du temps enseignants cours plages horaires volumes decompte heures'),
                $this->permissionItem(Action::ACTION_STAFF_TIMETABLE_CU, 'Emploi du temps - Creer / Modifier', 'Ajouter modifier cours plages horaires emplois permanents heures biometrie'),
                $this->permissionItem(Action::ACTION_STAFF_TIMETABLE_DELETE, 'Emploi du temps - Supprimer', 'Supprimer cours plages horaires emplois permanents dispenses'),
            ],
            'Personnel - Discipline' => [
                $this->permissionItem(Action::ACTION_STAFF_DISCIPLINE, 'Discipline - Voir', 'Afficher discipline sanctions personnel'),
                $this->permissionItem(Action::ACTION_STAFF_DISCIPLINE_CU, 'Discipline - Creer / Modifier', 'Ajouter modifier discipline sanctions personnel'),
            ],
            'Personnel - Heures effectuees' => [
                $this->permissionItem(Action::ACTION_STAFF_HOUR_COUNT, 'Heures - Voir', 'Afficher heures effectuees personnel enseignants'),
                $this->permissionItem(Action::ACTION_STAFF_HOUR_COUNT_CU, 'Heures - Creer / Modifier', 'Ajouter modifier heures effectuees'),
                $this->permissionItem(Action::ACTION_STAFF_HOUR_COUNT_DELETE, 'Heures - Supprimer', 'Supprimer heures effectuees'),
            ],
            'Comptabilite' => [
                $this->permissionItem(Action::ACTION_ACCOUNTING, 'Comptabilite - Tableau de bord', 'Voir module comptabilite'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_WITHDRAWAL, 'Depenses - Voir', 'Afficher sorties decaissements retraits'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_WITHDRAWAL_CREATE, 'Depenses - Creer', 'Creer sortie depense retrait caisse'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_WITHDRAWAL_UPDATE, 'Depenses - Modifier', 'Modifier sortie depense retrait caisse'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_STAFF_PAYMENT, 'Salaires - Voir', 'Afficher paiement salaire personnel'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_STAFF_PAYMENT_CU, 'Salaires - Payer', 'Creer paiement salaire personnel'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_STAFF_PAYMENT_PRINT_RECEIPT, 'Salaires - Imprimer bulletin', 'Imprimer bulletin salaire'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_ENTRY_BALANCE, 'Bilan entrees - Voir', 'Afficher etat bilan entrees'),
                $this->permissionItem(Action::ACTION_ACCOUNTING_OUTPUT_BALANCE, 'Bilan sorties - Voir', 'Afficher etat bilan sorties'),
            ],
        ];
    }

    private function permissionItem($name, $label, $keywords)
    {
        return [
            'name' => $name,
            'label' => $label,
            'keywords' => $keywords.' '.$name,
            'danger' => $this->isDangerousAction($name),
        ];
    }

    private function ensureAccessControlSchema()
    {
        if(Schema::hasTable('users') && !Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function(Blueprint $table) {
                $table->boolean('is_super_admin')->default(false);
            });

            if(Schema::hasTable('actions')) {
                $adminIds = DB::table('actions')->where('action_name', '=', Action::ACTION_ALL)->lists('id_user');
                if(count($adminIds) > 0) {
                    DB::table('users')->whereIn('id', $adminIds)->update(['is_super_admin' => true]);
                }
            }
        }

        if(!Schema::hasTable('access_roles')) {
            Schema::create('access_roles', function(Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('access_role_actions')) {
            Schema::create('access_role_actions', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('id_access_role')->unsigned();
                $table->string('action_name');
                $table->timestamps();
                $table->unique(array('id_access_role', 'action_name'), 'access_role_action_unique');
            });
        }

        if(!Schema::hasTable('access_role_user')) {
            Schema::create('access_role_user', function(Blueprint $table) {
                $table->increments('id');
                $table->integer('id_access_role')->unsigned();
                $table->integer('id_user')->unsigned();
                $table->timestamps();
                $table->unique(array('id_access_role', 'id_user'), 'access_role_user_unique');
            });
        }
    }

    private function availableActions()
    {
        $reflection = new ReflectionClass(Action::class);
        $actions = array_values($reflection->getConstants());

        foreach(Route::getRoutes() as $route) {
            $routeAction = $route->getAction();
            if(isset($routeAction['action'])) {
                $routeActions = is_array($routeAction['action']) ? $routeAction['action'] : array($routeAction['action']);
                foreach($routeActions as $actionName) {
                    if(is_string($actionName) && strpos($actionName, 'ACTION_') === 0) {
                        $actions[] = $actionName;
                    }
                }
            }
        }

        $actions = array_values(array_unique($actions));
        sort($actions);
        return $actions;
    }

    private function sectionName($actionName)
    {
        $map = [
            'Administration' => ['ACTION_ADMIN'],
            'Inscriptions' => ['ACTION_REGISTRATION'],
            'Notes et discipline' => ['ACTION_STUDENT'],
            'Personnel' => ['ACTION_STAFF', 'ACTION_TEACHER'],
            'Comptabilite' => ['ACTION_ACCOUNTING'],
            'Acces complet' => ['ACTION_ALL', 'ACTION_ALMOST_ALL'],
        ];

        foreach($map as $section => $prefixes) {
            foreach($prefixes as $prefix) {
                if(strpos($actionName, $prefix) === 0) {
                    return $section;
                }
            }
        }

        return 'Autres actions';
    }

    private function actionLabel($actionName)
    {
        $label = str_replace('ACTION_', '', $actionName);
        $label = strtolower(str_replace('_', ' ', $label));
        return ucfirst($label);
    }

    private function isDangerousAction($actionName)
    {
        return strpos($actionName, 'DELETE') !== false
            || strpos($actionName, 'UPDATE') !== false
            || strpos($actionName, '_CU') !== false
            || $actionName == Action::ACTION_ALL
            || $actionName == Action::ACTION_ALMOST_ALL;
    }
}
