<?php namespace gesecole\Http\Controllers\inscriptions;
use gesecole\classe;
use gesecole\Banque;
use gesecole\Campus;
use gesecole\Caisse;
use gesecole\cycle;
use gesecole\eleve;
use gesecole\Etudiant_user;
use gesecole\Frais;
use gesecole\Http\Controllers\startController;
use gesecole\inscription;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\MouvementFinancier;
use gesecole\moyenne_eleve;
use gesecole\payement;
use gesecole\User;
use gesecole\Vars;
use Illuminate\Support\Facades\DB;
use gesecole\Http\Requests;
use Illuminate\Support\Facades\Session;
use gesecole\Http\Controllers\Controller;
use gesecole\parametres;
use gesecole\scolarite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use URL;

class inscriptionController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('menu.gestion_inscriptions')]);
        $this->renewSchoolYear();
    }

public function index()
  {
        $nb_eleve=inscription::whereAnneeScolaire(\session('year'))
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->where('desinscrire', '=', 0)
            ->count();

        return view('inscriptions/index',["nb_eleves"=>$nb_eleve]);
  }

    public function effectifs(Request $request)
    {
        $annee = $request->input('annee_scolaire', session('year'));
        $anneeSansEspaces = str_replace(' ', '', $annee);
        $anneesRecherche = array_unique(array(
            $annee,
            $anneeSansEspaces,
            str_replace('-', ' - ', $anneeSansEspaces)
        ));
        $idCampus = $this->currentCampusId();
        $campus = $this->currentCampus();

        $rows = DB::table('inscription as i')
            ->leftJoin('cycle as cy', 'i.id_cycle', '=', 'cy.id')
            ->leftJoin('classe as cl', 'i.id_classe', '=', 'cl.id')
            ->leftJoin('niveau as n', 'cl.id_niveau', '=', 'n.id')
            ->leftJoin('n_niveaus as nn', 'i.id_nniveau', '=', 'nn.id')
            ->whereIn('i.annee_scolaire', $anneesRecherche)
            ->whereRaw($this->campusVisibilitySql('i.id_campus', $annee), array($idCampus))
            ->where(function ($query) {
                $query->whereNull('i.desinscrire')
                    ->orWhere('i.desinscrire', '=', 0);
            })
            ->select(
                'i.id_cycle',
                'cy.nom as cycle',
                'cl.id_niveau',
                'n.nom as filiere',
                'cl.id as id_classe',
                'cl.nom as specialite',
                'cl.code as code_specialite',
                'i.id_nniveau',
                'nn.nom as niveau',
                DB::raw('COUNT(*) as effectif')
            )
            ->groupBy(
                'i.id_cycle',
                'cy.nom',
                'cl.id_niveau',
                'n.nom',
                'cl.id',
                'cl.nom',
                'cl.code',
                'i.id_nniveau',
                'nn.nom'
            )
            ->orderBy('cy.nom')
            ->orderBy('n.nom')
            ->orderBy('cl.nom')
            ->orderBy('nn.nom')
            ->get();

        $effectifsParCycle = array();
        $total = 0;

        foreach ($rows as $row) {
            $cycle = $row->cycle ? $row->cycle : 'Sans cycle';
            if (!isset($effectifsParCycle[$cycle])) {
                $effectifsParCycle[$cycle] = array(
                    'total' => 0,
                    'rows' => array()
                );
            }

            $effectifsParCycle[$cycle]['rows'][] = $row;
            $effectifsParCycle[$cycle]['total'] += $row->effectif;
            $total += $row->effectif;
        }

        return view('inscriptions.effectifs', array(
            'titre' => 'Effectifs des inscriptions',
            'annee_scolaire' => $annee,
            'campus' => $campus,
            'effectifsParCycle' => $effectifsParCycle,
            'total' => $total
        ));
    }
	/*public function index()
	{
        $nb_eleve = eleve::inscrits()->count();
        return view('inscriptions.index',["nb_eleves" => $nb_eleve]);
	}
*/


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
    public function search()
    {
        $c=classe::all();
        $values= array('titre'=>trans('inscription.title_search_register'));
        $values['classes']=$c;
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }
        if(session('inscription_edition')){
            $values['inscription_edition']=session('inscription_edition');
            Session::forget('inscription_edition');
        }


        return view('inscriptions.add_inscription',$values);
    }

    public function find(Request $req)
    {
        $pattern=$req->input('search');
        $e=DB::table('eleve')->whereRaw('lower(nom) LIKE ?',['%'.strtolower($pattern).'%'] )

            ->orWhereRaw('lower(prenom) LIKE ?',['%'.strtolower($pattern).'%'])
            ->orWhereRaw('lower(matricule) LIKE ?',['%'.strtolower($pattern).'%'])
            ->orderBy('nom')->orderBy('prenom')
            ->paginate(30);
        //$e=DB::table('eleve')->whereRaw("nom LIKE '%?%' or prenom LIKE '%?%' or matricule LIKE '%?%'",[$pattern,$pattern,$pattern])->get();
        //$e=eleve::whereRaw("nom LIKE '%?%' or prenom LIKE '%?%' or matricule LIKE '%?%'",[$pattern,$pattern,$pattern]);
        $values= array('titre'=>trans('inscription.title_search_register'));
        $values['eleves']=$e;
        //session(['search_result'=>true]);

        return view('inscriptions.eleve_operations',$values);
    }

    public function find_inscription(Request $req)
    {
        $pattern=$req->input('search');
        $e=DB::table('eleve')
            ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
            ->where('inscription.annee_scolaire', '=', session('year'))
            ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($this->currentCampusId()))
            ->whereRaw('(lower(eleve.nom) LIKE ? OR lower(eleve.prenom) LIKE ? OR lower(eleve.matricule) LIKE ?)', ['%'.strtolower($pattern).'%', '%'.strtolower($pattern).'%', '%'.strtolower($pattern).'%'])
            ->select('eleve.*')
            ->orderBy('nom')->orderBy('prenom')
            ->paginate(30);
        //$e=DB::table('eleve')->whereRaw("nom LIKE '%?%' or prenom LIKE '%?%' or matricule LIKE '%?%'",[$pattern,$pattern,$pattern])->get();
        //$e=eleve::whereRaw("nom LIKE '%?%' or prenom LIKE '%?%' or matricule LIKE '%?%'",[$pattern,$pattern,$pattern]);
        $values= array('titre'=>trans('inscription.title_search_register'));
        $values['eleves']=$e;
        //session(['search_result'=>true]);

        return view('inscriptions.search_inscription',$values);
    }


    public function create($id_eleve)
	{
        $specialitesCampus = DB::table('campus_specialite')->where('id_campus', '=', $this->currentCampusId())->lists('id_classe');
        $classesQuery = classe::with('niveau')->orderBy('id_niveau')->orderBy('nom');
        if(count($specialitesCampus) > 0) {
            $classesQuery->whereIn('id', $specialitesCampus);
        }
        $c=$classesQuery->get();
        $cy=cycle::all();
        $e=eleve::find($id_eleve);
        $nniveau = NNiveau::orderBy('nom')->get();
        $niveaux = niveau::orderBy('nom')->get();
        $values= array('titre'=>trans('admin.inscription'));
        $values['classes']=$c;
        $values['cycles']=$cy;
        $values['nniveaux']=$nniveau;
        $values['niveaux']=$niveaux;
        $values['scolarites']=scolarite::all();
        $values['campus']=Campus::where('id', '=', $this->currentCampusId())->get();
        $values['campus_actif']=$this->currentCampus();
        $values['caisses']=Caisse::where('type_caisse','=','entree')->orderBy('nom')->get();
        $values['banques']=Banque::orderBy('nom')->get();
        $values['e']=$e;
        $values['new_record']=true;
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        if(session('custom_error')){
            $values['custom_error']=session('custom_error');
            Session::forget('custom_error');

        }

        return view('inscriptions.add_inscription',$values);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $req)
	{
       $this->validate($req,[
           'id_eleve'=>'required',
           'classe'=>'required',
           'cycle'=>'required|numeric',
           'nniveau'=>'required|numeric',
           'type_scolarite' => 'required|numeric',
           'operation_source' => 'required'
       ]) ;
       $id_eleve=$req->input('id_eleve');
        $id_classe=$req->input('classe');
        $id_cycle=$req->input('cycle');
        $id_nniveau=$req->input('nniveau');
        $type_scolarite=$req->input('type_scolarite');
        $id_scolarite=$req->input('id_scolarite');
        $scolarite_style=intval($req->input('scolarite_style', 1));
        $montant=$req->input('montant');
        $lettre = $req->input('lettre');
        $mode_paye = $req->input('mode_paye');
        $ref = $req->input('ref_versement');
        $id_campus = $this->currentCampusId();
        $operation_source = $req->input('operation_source');
        $id_caisse = $operation_source == 'caisse' ? $req->input('id_caisse') : null;
        $id_banque = $operation_source == 'banque' ? $req->input('id_banque') : null;
        $numero_versement = $req->input('numero_versement');
        $reference_operation = $req->input('reference_operation');
        $demande_operation = $req->input('demande_operation');
       $annee_scolaire=session('year');
       $today=date('Y-m-d');
       $capacite_classe=classe::find($id_classe)->capacite;
       $nb_eleve_classe=inscription::whereRaw('id_classe=? and id_nniveau=? and annee_scolaire=? and id_campus=?',[$id_classe,$id_nniveau,$annee_scolaire,$id_campus])->get()->count();
       //echo $nb_eleve_classe.'   '.$capacite_classe;
       $classe = classe::find($id_classe);

       if($scolarite_style === 0) {
           $scolarite = null;
           if($id_scolarite) {
               $selectedScolarite = scolarite::find($id_scolarite);
               if($selectedScolarite
                   && intval($selectedScolarite->id_cycle) == intval($id_cycle)
                   && intval($selectedScolarite->id_niveau) == intval($classe->id_niveau)
                   && intval($selectedScolarite->type_scolarite) == intval($type_scolarite)) {
                   $scolarite = $selectedScolarite;
               }
           }
           if(!$scolarite) {
               $scolarite = scolarite::matchingOldStyle($id_cycle, $classe->id_niveau, $type_scolarite)->first();
           }
       } else {
           $scolarite = $this->resolveScolariteForRegistration($id_scolarite, $id_cycle, $classe, $type_scolarite, $id_classe, $id_nniveau, $annee_scolaire);
       }

       if (!$scolarite) {
            return redirect(URL::previous())
                ->withErrors(['no_scolarite' => trans('admin.scolarite_not_set')])
                ->withInput();
       }

       if($montant === null || trim($montant) === '') {
           $montant = $scolarite->inscription;
       }

       if(!is_numeric($montant)) {
           return redirect(URL::previous())
               ->withErrors(['montant' => trans('validation.numeric', array('attribute' => trans('inscription.montant')))])
               ->withInput();
       }

       if($nb_eleve_classe>=$capacite_classe){
            session(['custom_error'=>trans('inscription.full_classe')]);
            return redirect()->action('inscriptions\inscriptionController@create',['id_eleve'=>$id_eleve]);
       }
       $test=inscription::whereRaw("id_eleve=?  and annee_scolaire=? and id_campus=?",[$id_eleve,$annee_scolaire,$id_campus])->get()->first();

       if($test!=null){
           session(['custom_error'=>trans('inscription.deja_inscrit')]);
           return redirect()->action('inscriptions\inscriptionController@create',['id_eleve'=>$id_eleve]);
       }
       $i=new inscription();
       $i->id_eleve=$id_eleve;
       $i->id_classe=$id_classe;
       $i->annee_scolaire=$annee_scolaire;
       $i->id_cycle=$id_cycle;
       $i->id_nniveau=$id_nniveau;
       $i->date_inscription=$today;
       $i->type_scolarite=$type_scolarite;
       if(Schema::hasColumn('inscription', 'id_scolarite')) {
           $i->id_scolarite=$scolarite->id;
       }
       $i->montant=$montant;
       $i->lettre = $lettre;
       $i->mode_paye = $mode_paye;
       $i->ref_versement = $ref;
       $i->id_campus = $id_campus;
       $i->operation_source = $operation_source;
       $i->id_caisse = $id_caisse;
       $i->id_banque = $id_banque;
       $i->numero_versement = $numero_versement;
       $i->reference_operation = $reference_operation;
       $i->demande_operation = $demande_operation;

        $current_user = \Auth::user();
        $i->id_creator = $current_user->id;
        $i->edition_details = 'classe =' . $id_classe . ', cycle = ' . $id_cycle . ', nniveau = ' . $id_nniveau . ', montant = ' . $montant;
        $i->id_last_editor = $current_user->id;

       if($i->save()) {
           $p = new payement();
           $p->id_eleve = $id_eleve;
           $p->annee_scolaire = $annee_scolaire;
           $p->date_payement = $today;
           $p->montant = $montant;
           $p->lettre = $lettre;
           $p->mode_paye = $mode_paye;
           $p->ref_versement = $ref;
           $p->id_campus = $id_campus;
           $p->operation_source = $operation_source;
           $p->id_caisse = $id_caisse;
           $p->id_banque = $id_banque;
           $p->numero_versement = $numero_versement;
           $p->reference_operation = $reference_operation;
           $p->demande_operation = $demande_operation;
           $p->id_inscription = $i->id;
           $p->motif = 'Inscription';
           $p->type = Vars::INSCRIPTION_TYPE;

           $current_user = \Auth::user();
           $p->edition_details = 'montant = ' . $montant;
           $p->id_creator = $current_user->id;
           $p->id_last_editor = $current_user->id;

           $p->save();
           $numero = sprintf('%08d', $p->id);
           $p->numero = $numero;
           $p->save();

           $this->recordFinancialEntry($p, $i, $operation_source, $id_caisse, $id_banque, $montant, $numero_versement, $reference_operation, $demande_operation);

           $e = eleve::find($id_eleve);
           if($e) {
               $this->ensureStudentMatriculeAndAccess($e, $i, classe::find($id_classe));
           }


           session(['success'=>$p->id]);

           return redirect()->action('inscriptions\inscriptionController@search') ;
       }else{
           session(['custom_error'=>trans('inscription.full_classe')]);
           return redirect()->action('inscriptions\inscriptionController@create',['id_eleve'=>$id_eleve]);
       }



	}

    public function importPage()
    {
        $values = array(
            'titre' => 'Importation Excel des inscriptions',
            'fields' => $this->inscriptionImportFields(),
            'cycles' => cycle::orderBy('nom')->get(),
            'niveaux' => niveau::orderBy('nom')->get(),
            'classes' => classe::with('niveau')->orderBy('id_niveau')->orderBy('nom')->get(),
            'nniveaux' => NNiveau::orderBy('nom')->get(),
            'caisses' => Caisse::where('type_caisse', '=', 'entree')->orderBy('nom')->get(),
            'banques' => Banque::orderBy('nom')->get(),
            'campus' => $this->currentCampus()
        );

        if(session('inscription_import_success')) {
            $values['import_success'] = session('inscription_import_success');
            Session::forget('inscription_import_success');
        }

        return view('inscriptions.import_inscriptions', $values);
    }

    public function downloadInscriptionImportTemplate()
    {
        $fields = $this->inscriptionImportFields();
        $headers = array_keys($fields);
        $example = array();

        foreach($fields as $field => $meta) {
            $example[] = $meta['exemple'];
        }

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers, ';');
        fputcsv($handle, $example, ';');
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return Response::make("\xEF\xBB\xBF".$content, 200, array(
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_inscriptions.csv"',
        ));
    }

    public function importInscriptionsFromExcel(Request $req)
    {
        $this->validate($req, array(
            'fichier_inscriptions' => 'required'
        ));

        $file = $req->file('fichier_inscriptions');
        if(!$file || !$file->isValid()) {
            return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Fichier invalide.'));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if(!in_array($extension, array('csv', 'txt', 'xlsx'))) {
            return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Format non accepte. Utiliser CSV ou XLSX.'));
        }

        $rows = $this->readInscriptionImportRows($file->getRealPath(), $extension);
        if(!$rows || count($rows) < 2) {
            return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Le fichier est vide ou illisible.'));
        }

        $headers = $this->normalizeInscriptionImportHeaders(array_shift($rows));
        $expectedHeaders = array_keys($this->inscriptionImportFields());
        foreach($expectedHeaders as $header) {
            if(!in_array($header, $headers)) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Colonne manquante : '.$header.'. Ne pas renommer les entetes du template.'));
            }
        }

        $created = 0;
        $ignored = 0;
        $currentUser = \Auth::user();

        foreach($rows as $index => $row) {
            if(count(array_filter($row, function($value) { return trim($value) !== ''; })) == 0) {
                continue;
            }

            $lineNumber = $index + 2;
            $data = $this->combineInscriptionImportRow($headers, $row);
            if(isset($data['operation_source'])) {
                $data['operation_source'] = strtolower($data['operation_source']);
            }
            $validationError = $this->validateInscriptionImportRow($data, $lineNumber);
            if($validationError) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => $validationError));
            }

            $student = $this->findStudentForInscriptionImport($data);
            if(!$student) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Ligne '.$lineNumber.' : etudiant introuvable. Renseigner id_eleve ou matricule existant.'));
            }

            $idCampus = isset($data['id_campus']) && trim($data['id_campus']) !== '' ? intval($data['id_campus']) : $this->currentCampusId();
            if($idCampus != $this->currentCampusId()) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Ligne '.$lineNumber.' : le campus doit correspondre au campus actif.'));
            }

            $anneeScolaire = isset($data['annee_scolaire']) && trim($data['annee_scolaire']) !== '' ? trim($data['annee_scolaire']) : session('year');
            $existing = inscription::where('id_eleve', '=', $student->id)
                ->where('annee_scolaire', '=', $anneeScolaire)
                ->where('id_campus', '=', $idCampus)
                ->first();

            if($existing) {
                $ignored++;
                continue;
            }

            $idClasse = intval($data['id_classe']);
            $idNiveau = isset($data['id_niveau']) && trim($data['id_niveau']) !== '' ? intval($data['id_niveau']) : null;
            $idCycle = intval($data['id_cycle']);
            $idNniveau = intval($data['id_nniveau']);
            $typeScolarite = intval($data['type_scolarite']);
            $classe = classe::find($idClasse);

            if(!$classe || !$classe->niveau || !cycle::find($idCycle) || !NNiveau::find($idNniveau)) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Ligne '.$lineNumber.' : cycle, specialite ou niveau academique introuvable.'));
            }

            if($idNiveau && $classe->id_niveau != $idNiveau) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Ligne '.$lineNumber.' : id_niveau ne correspond pas a la filiere de la specialite choisie.'));
            }

            $scolarite = scolarite::where('id_cycle', '=', $idCycle)
                ->where('id_niveau', '=', $classe->niveau->id)
                ->where('type_scolarite', '=', $typeScolarite)
                ->first();

            if (isset($data['id_scolarite']) && trim($data['id_scolarite']) !== '') {
                $scolarite = $this->resolveScolariteForRegistration(intval($data['id_scolarite']), $idCycle, $classe, $typeScolarite, $idClasse, $idNniveau, $anneeScolaire);
            } else {
                $scolarite = $this->resolveScolariteForRegistration(null, $idCycle, $classe, $typeScolarite, $idClasse, $idNniveau, $anneeScolaire);
            }

            if(!$scolarite) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Ligne '.$lineNumber.' : scolarite non configuree pour ce cycle, cette filiere et ce type de scolarite.'));
            }

            $capacite = intval($classe->capacite);
            $nbEleves = inscription::where('id_classe', '=', $idClasse)
                ->where('id_nniveau', '=', $idNniveau)
                ->where('annee_scolaire', '=', $anneeScolaire)
                ->where('id_campus', '=', $idCampus)
                ->count();

            if($capacite > 0 && $nbEleves >= $capacite) {
                return redirect()->back()->withErrors(array('fichier_inscriptions' => 'Ligne '.$lineNumber.' : capacite atteinte pour cette specialite et ce niveau.'));
            }

            $inscription = new inscription();
            $inscription->id_eleve = $student->id;
            $inscription->id_classe = $idClasse;
            $inscription->annee_scolaire = $anneeScolaire;
            $inscription->id_cycle = $idCycle;
            $inscription->id_nniveau = $idNniveau;
            $inscription->date_inscription = $this->normalizeInscriptionImportDate($data['date_inscription']);
            $inscription->type_scolarite = $typeScolarite;
            if(Schema::hasColumn('inscription', 'id_scolarite')) {
                $inscription->id_scolarite = $scolarite->id;
            }
            $inscription->montant = str_replace(',', '.', $data['montant']);
            $inscription->lettre = isset($data['lettre']) ? $data['lettre'] : '';
            $inscription->mode_paye = isset($data['mode_paye']) ? $data['mode_paye'] : '';
            $inscription->ref_versement = isset($data['ref_versement']) ? $data['ref_versement'] : '';
            $inscription->id_campus = $idCampus;
            $inscription->operation_source = $data['operation_source'];
            $inscription->id_caisse = $data['operation_source'] == 'caisse' && isset($data['id_caisse']) && trim($data['id_caisse']) !== '' ? intval($data['id_caisse']) : null;
            $inscription->id_banque = $data['operation_source'] == 'banque' && isset($data['id_banque']) && trim($data['id_banque']) !== '' ? intval($data['id_banque']) : null;
            $inscription->numero_versement = isset($data['numero_versement']) ? $data['numero_versement'] : '';
            $inscription->reference_operation = isset($data['reference_operation']) ? $data['reference_operation'] : '';
            $inscription->demande_operation = isset($data['demande_operation']) ? $data['demande_operation'] : '';
            $inscription->id_creator = $currentUser ? $currentUser->id : 0;
            $inscription->id_last_editor = $currentUser ? $currentUser->id : 0;
            $inscription->edition_details = 'import excel inscription';
            $inscription->save();

            $payement = new payement();
            $payement->id_eleve = $student->id;
            $payement->annee_scolaire = $anneeScolaire;
            $payement->date_payement = $inscription->date_inscription;
            $payement->montant = $inscription->montant;
            $payement->lettre = $inscription->lettre;
            $payement->mode_paye = $inscription->mode_paye;
            $payement->ref_versement = $inscription->ref_versement;
            $payement->id_campus = $idCampus;
            $payement->operation_source = $inscription->operation_source;
            $payement->id_caisse = $inscription->id_caisse;
            $payement->id_banque = $inscription->id_banque;
            $payement->numero_versement = $inscription->numero_versement;
            $payement->reference_operation = $inscription->reference_operation;
            $payement->demande_operation = $inscription->demande_operation;
            $payement->id_inscription = $inscription->id;
            $payement->motif = 'Inscription';
            $payement->type = Vars::INSCRIPTION_TYPE;
            $payement->edition_details = 'import excel inscription';
            $payement->id_creator = $currentUser ? $currentUser->id : 0;
            $payement->id_last_editor = $currentUser ? $currentUser->id : 0;
            $payement->save();
            $payement->numero = sprintf('%08d', $payement->id);
            $payement->save();

            $this->recordFinancialEntry($payement, $inscription, $inscription->operation_source, $inscription->id_caisse, $inscription->id_banque, $inscription->montant, $inscription->numero_versement, $inscription->reference_operation, $inscription->demande_operation);
            $this->ensureStudentMatriculeAndAccess($student, $inscription, $classe);
            $created++;
        }

        session(array('inscription_import_success' => $created.' inscription(s) importee(s). '.$ignored.' ligne(s) ignoree(s) car deja inscrite(s).'));

        return redirect()->route('inscriptions_import');
    }

    private function inscriptionImportFields()
    {
        return array(
            'id_eleve' => array('table' => 'inscription', 'obligatoire' => 'Oui si matricule vide', 'defaut' => '-', 'description' => 'Identifiant interne de l etudiant.', 'exemple' => '2004', 'valeurs' => 'Nombre existant dans eleve.id'),
            'matricule' => array('table' => 'eleve', 'obligatoire' => 'Oui si id_eleve vide', 'defaut' => 'Lecture seule, jamais modifie par l import', 'description' => 'Matricule existant utilise uniquement pour retrouver l etudiant.', 'exemple' => '25AZ-SIN3563', 'valeurs' => 'Texte existant dans eleve.matricule'),
            'id_cycle' => array('table' => 'inscription', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Cycle de formation.', 'exemple' => '1', 'valeurs' => 'Identifiant existant dans cycle.id'),
            'id_niveau' => array('table' => 'niveau/classe', 'obligatoire' => 'Non', 'defaut' => 'Controle automatique par id_classe', 'description' => 'Filiere rattachee a la specialite.', 'exemple' => '2', 'valeurs' => 'Identifiant existant dans niveau.id'),
            'id_classe' => array('table' => 'inscription', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Specialite/classe.', 'exemple' => '3', 'valeurs' => 'Identifiant existant dans classe.id'),
            'id_nniveau' => array('table' => 'inscription', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Niveau academique.', 'exemple' => '1', 'valeurs' => 'Identifiant existant dans n_niveaus.id'),
            'type_scolarite' => array('table' => 'inscription', 'obligatoire' => 'Oui', 'defaut' => '0', 'description' => 'Type de scolarite utilise pour retrouver le tarif.', 'exemple' => '0', 'valeurs' => 'Nombre configure dans scolarite.type_scolarite'),
            'id_scolarite' => array('table' => 'inscription', 'obligatoire' => 'Non', 'defaut' => 'Profil trouve automatiquement', 'description' => 'Profil de scolarite precis a affecter a cette inscription.', 'exemple' => '', 'valeurs' => 'Identifiant existant dans scolarite.id'),
            'montant' => array('table' => 'inscription/payement', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Montant paye a l inscription.', 'exemple' => '50000', 'valeurs' => 'Nombre'),
            'operation_source' => array('table' => 'inscription/payement', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Source du paiement.', 'exemple' => 'caisse', 'valeurs' => 'caisse ou banque'),
            'id_caisse' => array('table' => 'inscription/payement', 'obligatoire' => 'Oui si operation_source=caisse', 'defaut' => 'Vide', 'description' => 'Caisse qui recoit le paiement.', 'exemple' => '1', 'valeurs' => 'Identifiant existant dans caisse.id'),
            'id_banque' => array('table' => 'inscription/payement', 'obligatoire' => 'Oui si operation_source=banque', 'defaut' => 'Vide', 'description' => 'Banque qui recoit le paiement.', 'exemple' => '', 'valeurs' => 'Identifiant existant dans banque.id'),
            'date_inscription' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Date du jour', 'description' => 'Date de l inscription.', 'exemple' => date('Y-m-d'), 'valeurs' => 'AAAA-MM-JJ, JJ/MM/AAAA ou date Excel'),
            'annee_scolaire' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Annee active', 'description' => 'Annee scolaire concernee.', 'exemple' => session('year'), 'valeurs' => 'Texte, exemple 2025/2026'),
            'id_campus' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Campus actif', 'description' => 'Campus concerne.', 'exemple' => $this->currentCampusId(), 'valeurs' => 'Doit correspondre au campus actif'),
            'lettre' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Montant en lettres.', 'exemple' => 'Cinquante mille', 'valeurs' => 'Texte'),
            'mode_paye' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Mode de paiement.', 'exemple' => 'espece', 'valeurs' => 'Texte'),
            'ref_versement' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Reference de versement.', 'exemple' => '', 'valeurs' => 'Texte'),
            'numero_versement' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Numero de versement.', 'exemple' => '', 'valeurs' => 'Texte'),
            'reference_operation' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Reference de l operation bancaire ou caisse.', 'exemple' => '', 'valeurs' => 'Texte'),
            'demande_operation' => array('table' => 'inscription/payement', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Nom du demandeur ou commentaire operation.', 'exemple' => '', 'valeurs' => 'Texte'),
        );
    }

    private function normalizeInscriptionImportHeaders($headers)
    {
        $normalized = array();
        foreach($headers as $header) {
            $normalized[] = strtolower(trim($header, " \t\n\r\0\x0B\xEF\xBB\xBF"));
        }

        return $normalized;
    }

    private function readInscriptionImportRows($path, $extension)
    {
        if(in_array($extension, array('csv', 'txt'))) {
            return $this->readInscriptionCsvRows($path);
        }

        return $this->readInscriptionXlsxRows($path);
    }

    private function readInscriptionCsvRows($path)
    {
        $separator = ';';
        $handle = fopen($path, 'r');
        if(!$handle) {
            return null;
        }

        $rows = array();
        $first = fgetcsv($handle, 0, $separator);
        if($first !== false && count($first) <= 1) {
            rewind($handle);
            $commaFirst = fgetcsv($handle, 0, ',');
            if($commaFirst !== false && count($commaFirst) > count($first)) {
                $separator = ',';
                $first = $commaFirst;
            }
        }

        if($first !== false) {
            $rows[] = $first;
        }

        while(($row = fgetcsv($handle, 0, $separator)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        return $rows;
    }

    private function readInscriptionXlsxRows($path)
    {
        if(!class_exists('\ZipArchive')) {
            return null;
        }

        $zip = new \ZipArchive();
        if($zip->open($path) !== true) {
            return null;
        }

        $sharedStrings = $this->readInscriptionXlsxSharedStrings($zip);
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if(!$sheetContent) {
            return null;
        }

        $sheet = simplexml_load_string($sheetContent);
        if(!$sheet || !isset($sheet->sheetData)) {
            return null;
        }

        $rows = array();
        foreach($sheet->sheetData->row as $sheetRow) {
            $row = array();
            foreach($sheetRow->c as $cell) {
                $attributes = $cell->attributes();
                $cellRef = isset($attributes['r']) ? (string) $attributes['r'] : '';
                $index = $this->inscriptionXlsxColumnIndex($cellRef);
                $type = isset($attributes['t']) ? (string) $attributes['t'] : '';
                $value = '';

                if($type == 's') {
                    $sharedIndex = (int) $cell->v;
                    $value = isset($sharedStrings[$sharedIndex]) ? $sharedStrings[$sharedIndex] : '';
                } elseif($type == 'inlineStr' && isset($cell->is->t)) {
                    $value = (string) $cell->is->t;
                } elseif(isset($cell->v)) {
                    $value = (string) $cell->v;
                }

                $row[$index] = trim($value);
            }

            if(count($row) > 0) {
                ksort($row);
                $rows[] = $this->normalizeInscriptionXlsxRowIndexes($row);
            }
        }

        return $rows;
    }

    private function readInscriptionXlsxSharedStrings(\ZipArchive $zip)
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');
        if(!$content) {
            return array();
        }

        $xml = simplexml_load_string($content);
        if(!$xml) {
            return array();
        }

        $strings = array();
        foreach($xml->si as $si) {
            if(isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $text = '';
            if(isset($si->r)) {
                foreach($si->r as $run) {
                    if(isset($run->t)) {
                        $text .= (string) $run->t;
                    }
                }
            }
            $strings[] = $text;
        }

        return $strings;
    }

    private function inscriptionXlsxColumnIndex($cellRef)
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
        $index = 0;
        for($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function normalizeInscriptionXlsxRowIndexes($row)
    {
        $normalized = array();
        $max = max(array_keys($row));
        for($i = 0; $i <= $max; $i++) {
            $normalized[] = isset($row[$i]) ? $row[$i] : '';
        }

        return $normalized;
    }

    private function combineInscriptionImportRow($headers, $row)
    {
        $data = array();
        foreach($headers as $index => $header) {
            if($header !== '') {
                $data[$header] = isset($row[$index]) ? trim($row[$index]) : '';
            }
        }

        return $data;
    }

    private function validateInscriptionImportRow($data, $lineNumber)
    {
        if((!isset($data['id_eleve']) || trim($data['id_eleve']) === '') && (!isset($data['matricule']) || trim($data['matricule']) === '')) {
            return 'Ligne '.$lineNumber.' : renseigner id_eleve ou matricule.';
        }

        foreach(array('id_cycle', 'id_classe', 'id_nniveau', 'type_scolarite', 'montant', 'operation_source') as $field) {
            if(!isset($data[$field]) || trim($data[$field]) === '') {
                return 'Ligne '.$lineNumber.' : le champ '.$field.' est obligatoire.';
            }
        }

        foreach(array('id_cycle', 'id_classe', 'id_nniveau', 'type_scolarite') as $field) {
            if(!is_numeric($data[$field])) {
                return 'Ligne '.$lineNumber.' : '.$field.' doit etre numerique.';
            }
        }

        if(isset($data['id_scolarite']) && trim($data['id_scolarite']) !== '' && !is_numeric($data['id_scolarite'])) {
            return 'Ligne '.$lineNumber.' : id_scolarite doit etre numerique.';
        }

        if(!is_numeric(str_replace(',', '.', $data['montant']))) {
            return 'Ligne '.$lineNumber.' : montant doit etre numerique.';
        }

        if(!in_array($data['operation_source'], array('caisse', 'banque'))) {
            return 'Ligne '.$lineNumber.' : operation_source doit etre caisse ou banque.';
        }

        if($data['operation_source'] == 'caisse' && (!isset($data['id_caisse']) || trim($data['id_caisse']) === '')) {
            return 'Ligne '.$lineNumber.' : id_caisse est obligatoire quand operation_source=caisse.';
        }

        if($data['operation_source'] == 'banque' && (!isset($data['id_banque']) || trim($data['id_banque']) === '')) {
            return 'Ligne '.$lineNumber.' : id_banque est obligatoire quand operation_source=banque.';
        }

        if(isset($data['date_inscription']) && trim($data['date_inscription']) !== '' && !$this->normalizeInscriptionImportDate($data['date_inscription'])) {
            return 'Ligne '.$lineNumber.' : date_inscription doit etre au format AAAA-MM-JJ ou JJ/MM/AAAA.';
        }

        return null;
    }

    private function normalizeInscriptionImportDate($date)
    {
        $date = trim($date);
        if($date === '') {
            return date('Y-m-d');
        }

        foreach(array('Y-m-d', 'd/m/Y', 'd-m-Y') as $format) {
            $parsed = \DateTime::createFromFormat($format, $date);
            if($parsed && $parsed->format($format) === $date) {
                return $parsed->format('Y-m-d');
            }
        }

        if(is_numeric($date)) {
            $timestamp = ((int) $date - 25569) * 86400;
            if($timestamp > 0) {
                return gmdate('Y-m-d', $timestamp);
            }
        }

        return null;
    }

    private function findStudentForInscriptionImport($data)
    {
        if(isset($data['id_eleve']) && trim($data['id_eleve']) !== '') {
            return eleve::find(intval($data['id_eleve']));
        }

        return eleve::where('matricule', '=', trim($data['matricule']))->first();
    }

    private function ensureStudentMatriculeAndAccess(eleve $student, inscription $inscription, $classe)
    {
        if(strlen($student->matricule) <= 0 && $classe) {
            $param = parametres::all();
            $pa = $param->where('nom', 'initiales_etablissement')->first();
            $front = substr(session('year'), 2, 2);
            $number = sprintf('%04d', $student->id);
            $student->matricule = $front . ($pa ? $pa->valeur : '') . '-' . $classe->code . $number;
            $student->save();
        }

        $existingAccess = Etudiant_user::where('id_eleve', '=', $student->id)->first();
        if($existingAccess == null) {
            $access = new Etudiant_user();
            $access->username = $student->matricule;
            $access->password = $student->matricule;
            $access->id_inscription = $inscription->id;
            $access->id_eleve = $student->id;
            $access->save();
        }
    }

    private function recordFinancialEntry($payement, $inscription, $source, $idCaisse, $idBanque, $montant, $numeroVersement, $reference, $demande)
    {
        $m = new MouvementFinancier();
        $m->type_operation = 'entree';
        $m->source = $source;
        $m->id_caisse = $idCaisse;
        $m->id_banque = $idBanque;
        $m->montant = $montant;
        $m->date_operation = date('Y-m-d');
        $m->motif = $payement->motif;
        $m->numero_versement = $numeroVersement;
        $m->reference = $reference;
        $m->demande = $demande;
        $m->id_payement = $payement->id;
        $m->id_inscription = $inscription->id;
        $m->id_campus = $inscription->id_campus;
        $m->id_user = \Auth::user() ? \Auth::user()->id : null;
        $m->id_creator = \Auth::user() ? \Auth::user()->id : null;
        $m->id_last_editor = \Auth::user() ? \Auth::user()->id : null;
        $m->save();

        if ($source == 'caisse' && $idCaisse) {
            $caisse = Caisse::find($idCaisse);
            if ($caisse) {
                $caisse->solde = (float) $caisse->solde + (float) $montant;
                $caisse->save();
            }
        }
        if ($source == 'banque' && $idBanque) {
            $banque = Banque::find($idBanque);
            if ($banque) {
                $banque->solde = (float) $banque->solde + (float) $montant;
                $banque->save();
            }
        }
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		return view('inscriptions.recu_inscription',['titre'=>'Recu']);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id_eleve)
	{
        $specialitesCampus = DB::table('campus_specialite')->where('id_campus', '=', $this->currentCampusId())->lists('id_classe');
        $classesQuery = classe::with('niveau')->orderBy('id_niveau')->orderBy('nom');
        if(count($specialitesCampus) > 0) {
            $classesQuery->whereIn('id', $specialitesCampus);
        }
        $c=$classesQuery->get();
        $cy=cycle::all();
        $e=eleve::find($id_eleve);
        $nniveau=NNiveau::all();
        $niveaux=niveau::orderBy('nom')->get();
        $i=inscription::whereAnneeScolaire(\session('year'))
            ->where('id_eleve','=',$id_eleve)
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->first();
        $values= array('titre'=>trans('inscription.title_search_register'));
        $values['classes']=$c;
        $values['cycles']=$cy;
        $values['inscription']=$i;
        $values['e']=$e;
        $values['nniveaux']=$nniveau;
        $values['niveaux']=$niveaux;
        $values['campus']=Campus::where('id', '=', $this->currentCampusId())->get();
        $values['campus_actif']=$this->currentCampus();
        $values['old_record']=true;
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        if(session('custom_error')){
            $values['custom_error']=session('custom_error');
            Session::forget('custom_error');

        }

        return view('inscriptions.add_inscription',$values);
	}

	public function print_receipe($id){
	    $p=payement::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if(!$p) { abort(404); }
        $i=inscription::whereAnneeScolaire(session('year'))->where('id_eleve',$p->id_eleve)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        $cy=cycle::find($i->id_cycle)->nom;
        $c=classe::find($i->id_classe);
        $n=niveau::find($c->id_niveau);
        $nn=NNiveau::find($i->id_nniveau);
	    $pa=parametres::all();
       // $current_user = \Auth::user();
       $current_user=User::where('id','=',$p->id_creator)
                       ->first();
       $u=$current_user->fullname;
       /* dump($u);
        die();*/


	    $pa=$pa->where('nom','nom_etablissement')->first();
	    $nom_e=$pa->valeur;
        $values['cycle']=$cy;
        $values['classe']=$c;
        $values['niveau']=$n;
        $values['nniveau']=$nn;
        $values['payement']=$p;
        $values['inscription']=$i;
        $values['user']=$u;
        $values['nom_etablissement']=$nom_e;

        return view('inscriptions.imprimer_recu',$values);

    }

    public function certificate($id){
	    $e=eleve::find($id);
        $i=inscription::whereAnneeScolaire(session('year'))->where('id_eleve','=',$id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        $values['inscription']=$i;
        if($i!=null){
            $cy=cycle::find($i->id_cycle);
            $c=classe::find($i->id_classe);
            $n=niveau::find($c->id_niveau);
            $nn=nniveau::find($i->id_nniveau);
            $pa=parametres::all();
            $pa1=$pa->where('nom','nom_etablissement')->first();
            $pa2=$pa->where('nom','nom_directeur')->first();
            $nom_e=$pa1->valeur;
            $nom_d=$pa2->valeur;
            $values['cycle']=$cy;
            $values['classe']=$c;
            $values['eleve']=$e;
            $values['niveau']=$n;
            $values['nniveau']=$nn;
            $values['nom_etablissement']=$nom_e;
            $values['nom_directeur']=$nom_d;
            $values['campus']=$this->currentCampus();

        }


        return view('inscriptions.print_certificate',$values);

    }

    public function certificateSelection()
    {
        $specialitesCampus = DB::table('campus_specialite')->where('id_campus', '=', $this->currentCampusId())->lists('id_classe');
        $classesQuery = classe::with('niveau')->orderBy('id_niveau')->orderBy('nom');
        if(count($specialitesCampus) > 0) {
            $classesQuery->whereIn('id', $specialitesCampus);
        }

        $values = array(
            'titre' => trans('admin.certificat_scolarite'),
            'cycles' => cycle::orderBy('nom')->get(),
            'niveaux' => niveau::orderBy('nom')->get(),
            'classes' => $classesQuery->get(),
            'nniveaux' => NNiveau::orderBy('nom')->get(),
            'campus' => $this->currentCampus()
        );

        return view('inscriptions.selection_certificates', $values);
    }

    public function certificatesByClasse(Request $request)
    {
        $this->validate($request, array(
            'cycle' => 'required|numeric',
            'classe' => 'required|numeric',
            'nniveau' => 'required|numeric',
        ));

        $idCycle = $request->input('cycle');
        $idClasse = $request->input('classe');
        $idNniveau = $request->input('nniveau');

        $classe = classe::find($idClasse);
        $cycle = cycle::find($idCycle);
        $nniveau = NNiveau::find($idNniveau);

        if(!$classe || !$cycle || !$nniveau) {
            abort(404);
        }

        $specialitesCampus = DB::table('campus_specialite')->where('id_campus', '=', $this->currentCampusId())->lists('id_classe');
        $specialitesCampus = is_array($specialitesCampus) ? $specialitesCampus : $specialitesCampus->toArray();
        if(count($specialitesCampus) > 0 && !in_array($classe->id, $specialitesCampus)) {
            abort(404);
        }

        $niveau = niveau::find($classe->id_niveau);
        $inscriptions = inscription::with('eleve')
            ->whereAnneeScolaire(session('year'))
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->where('id_cycle', '=', $idCycle)
            ->where('id_classe', '=', $idClasse)
            ->where('id_nniveau', '=', $idNniveau)
            ->where('desinscrire', '=', 0)
            ->join('eleve', 'inscription.id_eleve', '=', 'eleve.id')
            ->orderBy('eleve.nom')
            ->orderBy('eleve.prenom')
            ->select('inscription.*')
            ->get();

        $pa = parametres::all();
        $pa1 = $pa->where('nom','nom_etablissement')->first();
        $pa2 = $pa->where('nom','nom_directeur')->first();

        return view('inscriptions.print_certificates', array(
            'titre' => trans('admin.certificat_scolarite'),
            'inscriptions' => $inscriptions,
            'cycle' => $cycle,
            'classe' => $classe,
            'niveau' => $niveau,
            'nniveau' => $nniveau,
            'nom_etablissement' => $pa1 ? $pa1->valeur : '',
            'nom_directeur' => $pa2 ? $pa2->valeur : '',
            'campus' => $this->currentCampus()
        ));
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
            'id_eleve'=>'required',
            'classe'=>'required',
            'cycle'=>'required|numeric',
            'nniveau'=>'required|numeric',
            'type_scolarite'=>'required|numeric',
        ]) ;
        $update=true;
        $id_eleve=$req->input('id_eleve');
        $id_classe=$req->input('classe');
        $id_cycle=$req->input('cycle');
        $id_nniveau=$req->input('nniveau');
        $type_scolarite=$req->input('type_scolarite');
        $id_scolarite=$req->input('id_scolarite');
        $annee_scolaire=session('year');

        $i=inscription::whereAnneeScolaire(\session('year'))
            ->where('id_eleve','=',$id_eleve)
            ->where('id_campus', '=', $this->currentCampusId())
            ->first();
        $classe=classe::find($id_classe);
        $capacite_classe=$classe->capacite;
        $nb_eleve_classe=inscription::whereRaw('id_classe=? and id_nniveau=? and annee_scolaire=? and id_campus=? and id_eleve<>?',[$id_classe,$id_nniveau,$annee_scolaire,$this->currentCampusId(),$id_eleve])->get()->count();

        if($capacite_classe > 0 && $nb_eleve_classe>=$capacite_classe && ($i->id_classe!=$id_classe || $i->id_nniveau!=$id_nniveau)){
            session(['custom_error'=>trans('inscription.full_classe')]);
            return redirect()->action('inscriptions\inscriptionController@edit',['id_eleve'=>$id_eleve]);
        }

        $scolarite = $this->resolveScolariteForRegistration($id_scolarite, $id_cycle, $classe, $type_scolarite, $id_classe, $id_nniveau, $annee_scolaire);

        if (!$scolarite) {
            return redirect(\URL::previous())
                ->withErrors(['no_scolarite' => trans('admin.scolarite_not_set')])
                ->withInput();
        }

        $i->id_classe=$id_classe;
        $i->id_cycle=$id_cycle;
        $i->id_nniveau=$id_nniveau;
        $i->type_scolarite=$type_scolarite;
        if(Schema::hasColumn('inscription', 'id_scolarite')) {
            $i->id_scolarite=$scolarite->id;
        }

        $current_user = \Auth::user();
        $i->id_last_editor = $current_user->id;

        if($i->save()) {


            $e = eleve::find($id_eleve);
            if($e) {
                $this->ensureStudentMatriculeAndAccess($e, $i, classe::find($id_classe));
            }


            session(['success'=>true]);
            session(['inscription_edition'=>true]);

            return redirect()->action('inscriptions\listesController@index') ;
        }else{
            session(['custom_error'=>trans('inscription.full_classe')]);
            return redirect()->action('inscriptions\inscriptionController@edit',['id_eleve'=>$id_eleve]);
        }
	}

	public function index_scan_carte(){
        $values= array('titre'=> trans('admin.scan_carte'));

        return view('inscriptions.scan_carte', $values);

    }

    public function process_scan(Request $request){
	    $scanned = $request->input('scanned');
	    //$id_eleve = openssl_decrypt($scanned, startController::SIMPLE_CRYPT_ALGO, startController::PASSPHRASE, 0, startController::VECTOR);
        $idEleve = intval($scanned);
        $eleve = eleve::find($idEleve);
        if($eleve) {
            $inscription = inscription::where('id_eleve', '=', $idEleve)
                ->where('annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                ->first();

            if($inscription) {
                return response()->json($idEleve);
            }
        } else {
            return response()->json(0);

        }

        return response()->json(0);

    }

    public function historiqueEtudiant(Request $request)
    {
        $pattern = trim($request->input('search'));
        $eleves = null;

        if ($pattern !== '') {
            $eleves = eleve::whereRaw('lower(nom) LIKE ?', array('%'.strtolower($pattern).'%'))
                ->orWhereRaw('lower(prenom) LIKE ?', array('%'.strtolower($pattern).'%'))
                ->orWhereRaw('lower(matricule) LIKE ?', array('%'.strtolower($pattern).'%'))
                ->orderBy('nom')
                ->orderBy('prenom')
                ->paginate(30);
        }

        return view('inscriptions.historique_etudiant_search', array(
            'titre' => 'Historique etudiant',
            'eleves' => $eleves,
            'search' => $pattern
        ));
    }

    public function historiqueEtudiantShow($id)
    {
        $eleve = eleve::with('parents')->find($id);
        if (!$eleve) {
            abort(404);
        }

        $inscriptions = inscription::with('classe', 'classe.niveau', 'nniveau', 'cycle', 'campus')
            ->where('id_eleve', '=', $eleve->id)
            ->orderBy('annee_scolaire', 'asc')
            ->orderBy('date_inscription', 'asc')
            ->get();

        $parcours = array();
        foreach ($inscriptions as $inscriptionItem) {
            $resultat = $this->historiqueResultat($inscriptionItem);
            $discipline = $this->historiqueDiscipline($inscriptionItem);
            $finances = $this->historiqueSituationFinanciere($inscriptionItem);

            $parcours[] = array(
                'inscription' => $inscriptionItem,
                'resultat' => $resultat,
                'discipline' => $discipline,
                'paiements' => $finances['total_paye'],
                'finances' => $finances
            );
        }

        return view('inscriptions.historique_etudiant_show', array(
            'titre' => 'Historique etudiant',
            'eleve' => $eleve,
            'parents' => $this->parentsByType($eleve->parents),
            'parcours' => $parcours,
            'campus_actif' => $this->currentCampus()
        ));
    }

    private function historiqueSituationFinanciere($inscriptionItem)
    {
        $scolarite = $this->historiqueScolaritePrevue($inscriptionItem);
        $montantInscription = $scolarite ? (float)$scolarite->inscription : 0;
        $montantScolarite = $scolarite ? (float)$scolarite->total : 0;
        $basePayments = payement::where('id_eleve', '=', $inscriptionItem->id_eleve)
            ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire);

        if ($inscriptionItem->id_campus) {
            $basePayments->whereRaw($this->campusVisibilitySql('id_campus', $inscriptionItem->annee_scolaire), array($inscriptionItem->id_campus));
        }

        $payments = $basePayments->get();
        $paidInscription = 0;
        $paidScolarite = 0;
        $paidFraisByType = array();

        foreach ($payments as $payment) {
            $type = intval($payment->type);
            $montant = (float)$payment->montant;

            if ($type === Vars::INSCRIPTION_TYPE || intval($payment->id_inscription) === intval($inscriptionItem->id)) {
                $paidInscription += $montant;
            } elseif ($type === 0) {
                $paidScolarite += $montant;
            } else {
                if (!isset($paidFraisByType[$type])) {
                    $paidFraisByType[$type] = 0;
                }
                $paidFraisByType[$type] += $montant;
            }
        }

        $details = array(
            array(
                'type' => 'Inscription',
                'prevu' => $montantInscription,
                'paye' => $paidInscription,
                'reste' => max($montantInscription - $paidInscription, 0),
            ),
            array(
                'type' => 'Scolarite',
                'prevu' => $montantScolarite,
                'paye' => $paidScolarite,
                'reste' => max($montantScolarite - $paidScolarite, 0),
            ),
        );

        $montantFrais = 0;
        $paidFrais = 0;
        $fraisIds = array_keys($paidFraisByType);
        $fraisPrevus = count($fraisIds) > 0 ? Frais::whereIn('id', $fraisIds)->orderBy('nom')->get() : array();

        foreach ($fraisPrevus as $frais) {
            $prevu = (float)$frais->montant;
            $paye = isset($paidFraisByType[$frais->id]) ? (float)$paidFraisByType[$frais->id] : 0;
            $montantFrais += $prevu;
            $paidFrais += $paye;
            unset($paidFraisByType[$frais->id]);

            $details[] = array(
                'type' => 'Frais - '.$frais->nom,
                'prevu' => $prevu,
                'paye' => $paye,
                'reste' => max($prevu - $paye, 0),
            );
        }

        foreach ($paidFraisByType as $type => $paye) {
            if ($paye <= 0) {
                continue;
            }
            $paidFrais += (float)$paye;
            $details[] = array(
                'type' => 'Frais non configure #'.$type,
                'prevu' => 0,
                'paye' => (float)$paye,
                'reste' => 0,
            );
        }

        return array(
            'details' => $details,
            'total_prevu' => $montantInscription + $montantScolarite + $montantFrais,
            'total_paye' => $paidInscription + $paidScolarite + $paidFrais,
            'total_reste' => max(($montantInscription + $montantScolarite + $montantFrais) - ($paidInscription + $paidScolarite + $paidFrais), 0),
        );
    }

    private function historiqueScolaritePrevue($inscriptionItem)
    {
        $scolarite = scolarite::forInscription($inscriptionItem);
        if ($scolarite) {
            return $scolarite;
        }

        $idNiveau = ($inscriptionItem->classe && $inscriptionItem->classe->niveau) ? $inscriptionItem->classe->niveau->id : null;
        if (!$idNiveau) {
            return null;
        }

        $query = scolarite::where('id_cycle', '=', $inscriptionItem->id_cycle)
            ->where('id_niveau', '=', $idNiveau)
            ->where('type_scolarite', '=', $inscriptionItem->type_scolarite);

        if (DB::getSchemaBuilder()->hasColumn('scolarite', 'annee_scolaire')) {
            $query->where(function ($q) use ($inscriptionItem) {
                $q->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                    ->orWhereNull('annee_scolaire')
                    ->orWhere('annee_scolaire', '=', '');
            });
        }

        return $query->orderBy('id', 'desc')->first();
    }

    private function resolveScolariteForRegistration($idScolarite, $idCycle, $classe, $typeScolarite, $idClasse, $idNniveau, $anneeScolaire)
    {
        if ($idScolarite) {
            $scolarite = scolarite::find($idScolarite);
            if ($scolarite
                && intval($scolarite->id_cycle) == intval($idCycle)
                && intval($scolarite->id_niveau) == intval($classe->id_niveau)
                && intval($scolarite->type_scolarite) == intval($typeScolarite)) {
                return $scolarite;
            }
        }

        $scolarite = scolarite::matching($idCycle, $classe->id_niveau, $typeScolarite, $idClasse, $idNniveau, $anneeScolaire)->first();
        if(!$scolarite && $anneeScolaire !== null) {
            $scolarite = scolarite::matching($idCycle, $classe->id_niveau, $typeScolarite, $idClasse, $idNniveau, null)->first();
        }

        return $scolarite;
    }

    private function historiqueResultat($inscriptionItem)
    {
        $moyenne = moyenne_eleve::where('id_eleve', '=', $inscriptionItem->id_eleve)
            ->where('id_classe', '=', $inscriptionItem->id_classe)
            ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
            ->orderBy('sequence', 'desc')
            ->first();

        if (!$moyenne) {
            return $this->historiqueResultatDepuisAnciennesNotes($inscriptionItem);
        }

        $rang = moyenne_eleve::where('id_classe', '=', $inscriptionItem->id_classe)
            ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
            ->where('sequence', '=', $moyenne->sequence)
            ->where('moyenne', '>', $moyenne->moyenne)
            ->count() + 1;

        $effectif = moyenne_eleve::where('id_classe', '=', $inscriptionItem->id_classe)
            ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
            ->where('sequence', '=', $moyenne->sequence)
            ->count();

        return array(
            'moyenne' => round($moyenne->moyenne, 2),
            'sequence' => $moyenne->sequence,
            'rang' => $rang,
            'effectif' => $effectif,
            'decision' => $moyenne->moyenne >= 10 ? 'Admis' : 'Ajourne',
            'source' => 'Moyenne enregistree'
        );
    }

    private function historiqueResultatDepuisAnciennesNotes($inscriptionItem)
    {
        $moyenne = $this->calculerMoyenneAnciennesNotes($inscriptionItem->id_eleve, $inscriptionItem);
        if ($moyenne === null) {
            return array(
                'moyenne' => null,
                'sequence' => null,
                'rang' => null,
                'effectif' => null,
                'decision' => 'Non renseignee',
                'source' => 'Aucune note'
            );
        }

        $studentQuery = inscription::where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
            ->where('id_classe', '=', $inscriptionItem->id_classe)
            ->where('id_cycle', '=', $inscriptionItem->id_cycle)
            ->where('id_nniveau', '=', $inscriptionItem->id_nniveau)
            ->where('desinscrire', '=', 0);

        if ($inscriptionItem->id_campus) {
            $studentQuery->whereRaw($this->campusVisibilitySql('id_campus', $inscriptionItem->annee_scolaire), array($inscriptionItem->id_campus));
        }

        $studentIds = $studentQuery->lists('id_eleve');
        $effectif = 0;
        $rang = 1;
        foreach ($studentIds as $studentId) {
            $studentAverage = $this->calculerMoyenneAnciennesNotes($studentId, $inscriptionItem);
            if ($studentAverage !== null) {
                $effectif++;
                if ($studentAverage > $moyenne) {
                    $rang++;
                }
            }
        }

        return array(
            'moyenne' => round($moyenne, 2),
            'sequence' => null,
            'rang' => $rang,
            'effectif' => $effectif,
            'decision' => $moyenne >= 10 ? 'Admis' : 'Ajourne',
            'source' => 'Anciennes notes'
        );
    }

    private function calculerMoyenneAnciennesNotes($idEleve, $inscriptionItem)
    {
        $matieres = DB::table('classe_matiere')
            ->where('id_classe', '=', $inscriptionItem->id_classe)
            ->where('id_cycle', '=', $inscriptionItem->id_cycle)
            ->where('id_nniveau', '=', $inscriptionItem->id_nniveau)
            ->get();

        if (count($matieres) <= 0) {
            return null;
        }

        $matiereIds = array();
        foreach ($matieres as $matiere) {
            $matiereIds[] = $matiere->id_matiere;
        }

        $noteQuery = DB::table('note')
            ->where('id_eleve', '=', $idEleve)
            ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
            ->whereIn('id_matiere', $matiereIds);

        if (DB::getSchemaBuilder()->hasColumn('note', 'id_campus') && $inscriptionItem->id_campus) {
            $noteQuery->where(function ($query) use ($inscriptionItem) {
                $query->where('id_campus', '=', $inscriptionItem->id_campus)
                    ->orWhereNull('id_campus');
            });
        }

        $notes = $noteQuery->get();
        if (count($notes) <= 0) {
            return null;
        }

        $notesByMatiere = array();
        foreach ($notes as $note) {
            if (!isset($notesByMatiere[$note->id_matiere])) {
                $notesByMatiere[$note->id_matiere] = array();
            }
            $notesByMatiere[$note->id_matiere][$note->sequence] = (float)$note->note;
        }

        $totalPoints = 0;
        $totalCoef = 0;
        foreach ($matieres as $matiere) {
            if (!isset($notesByMatiere[$matiere->id_matiere])) {
                continue;
            }

            $semestre = ((int)$matiere->semestre === 2) ? 2 : 1;
            $ccSeq = $semestre === 1 ? 1 : 2;
            $snSeq = $semestre === 1 ? 3 : 4;
            $ratSeq = $semestre === 1 ? 5 : 6;
            $tpeSeq = $semestre === 1 ? 7 : 8;

            $matiereNotes = $notesByMatiere[$matiere->id_matiere];
            $cc = isset($matiereNotes[$ccSeq]) ? $matiereNotes[$ccSeq] : null;
            $sn = isset($matiereNotes[$snSeq]) ? $matiereNotes[$snSeq] : null;
            $rat = isset($matiereNotes[$ratSeq]) ? $matiereNotes[$ratSeq] : null;
            $tpe = isset($matiereNotes[$tpeSeq]) ? $matiereNotes[$tpeSeq] : null;

            if ($cc === null && $sn === null && $rat === null && $tpe === null) {
                continue;
            }

            $useRattrapage = $rat !== null && ($sn === null || $rat > $sn);
            $final = $useRattrapage ? $rat : $sn;
            $total100 = ($cc !== null ? $cc : 0) + ($final !== null ? $final : 0) + ($tpe !== null ? $tpe : 0);
            $total100 = $total100 > 100 ? 100 : $total100;
            $coef = $matiere->coef ? (float)$matiere->coef : 1;

            $totalPoints += ($total100 / 5) * $coef;
            $totalCoef += $coef;
        }

        if ($totalCoef <= 0) {
            return null;
        }

        return $totalPoints / $totalCoef;
    }

    private function historiqueDiscipline($inscriptionItem)
    {
        $legacy = array('absences' => 0, 'heures' => 0, 'retards' => 0, 'justifiees' => 0, 'non_justifiees' => 0, 'exclusions' => 0);
        $details = array();
        if (DB::getSchemaBuilder()->hasTable('discipline')) {
            $legacy['absences'] = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->count();
            $legacy['heures'] = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->sum('nb_heures');
            $legacy['retards'] = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->sum('retard');
            $legacy['justifiees'] = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->where('just', '=', 1)
                ->count();
            $legacy['non_justifiees'] = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->where('just', '=', 0)
                ->count();
            $legacy['exclusions'] = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->sum('exclus_def');

            $legacyDetails = DB::table('discipline')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->orderBy('ddate', 'desc')
                ->get();
            foreach ($legacyDetails as $item) {
                $details[] = array(
                    'date' => $item->ddate,
                    'type' => $item->retard ? 'Retard' : 'Absence',
                    'heures' => $item->nb_heures,
                    'statut' => $item->just ? 'Justifiee' : 'Non justifiee',
                    'description' => $item->description ? $item->description : $item->motif_justification
                );
            }
        }

        $new = array('absences' => 0, 'heures' => 0);
        if (DB::getSchemaBuilder()->hasTable('discipline__new__etds')) {
            $disciplineQuery = DB::table('discipline__new__etds')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->where('statuts_discipline', '=', 1);

            if (DB::getSchemaBuilder()->hasColumn('discipline__new__etds', 'id_inscription')) {
                $disciplineQuery->where(function ($query) use ($inscriptionItem) {
                    $query->where('id_inscription', '=', $inscriptionItem->id)
                        ->orWhere('id_inscription', '=', 0);
                });
            }

            $new['absences'] = $disciplineQuery->count();

            $hoursQuery = DB::table('discipline__new__etds')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->where('statuts_discipline', '=', 1);
            if (DB::getSchemaBuilder()->hasColumn('discipline__new__etds', 'id_inscription')) {
                $hoursQuery->where(function ($query) use ($inscriptionItem) {
                    $query->where('id_inscription', '=', $inscriptionItem->id)
                        ->orWhere('id_inscription', '=', 0);
                });
            }
            $new['heures'] = $hoursQuery->sum('nb_heures');

            $newDetailsQuery = DB::table('discipline__new__etds')
                ->where('id_eleve', '=', $inscriptionItem->id_eleve)
                ->where('annee_scolaire', '=', $inscriptionItem->annee_scolaire)
                ->where('statuts_discipline', '=', 1);
            if (DB::getSchemaBuilder()->hasColumn('discipline__new__etds', 'id_inscription')) {
                $newDetailsQuery->where(function ($query) use ($inscriptionItem) {
                    $query->where('id_inscription', '=', $inscriptionItem->id)
                        ->orWhere('id_inscription', '=', 0);
                });
            }

            $newDetails = $newDetailsQuery->orderBy('date_discipline', 'desc')->get();
            foreach ($newDetails as $item) {
                $details[] = array(
                    'date' => $item->date_discipline,
                    'type' => 'Absence',
                    'heures' => $item->nb_heures,
                    'statut' => 'Enregistree',
                    'description' => trim($item->heure_debut.' - '.$item->heure_fin)
                );
            }
        }

        return array(
            'absences' => $legacy['absences'] + $new['absences'],
            'heures' => $legacy['heures'] + $new['heures'],
            'retards' => $legacy['retards'],
            'justifiees' => $legacy['justifiees'],
            'non_justifiees' => $legacy['non_justifiees'],
            'exclusions' => $legacy['exclusions'],
            'details' => $details
        );
    }

    private function parentsByType($parents)
    {
        $items = array('mere' => null, 'pere' => null, 'tuteur' => null, 'autres' => array());
        foreach ($parents as $parent) {
            if ((int)$parent->type === 0) {
                $items['mere'] = $parent;
            } elseif ((int)$parent->type === 1) {
                $items['pere'] = $parent;
            } elseif ((int)$parent->type === 2) {
                $items['tuteur'] = $parent;
            } else {
                $items['autres'][] = $parent;
            }
        }
        return $items;
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	/* public function destroy(Request $request)
	{
        $this->validate($request,[
            'id_eleve'=>'required',
        ]) ;
        $id_eleve=$request->input('id_eleve');
        

		$i=inscription::whereAnneeScolaire(\session('year'))
            ->where('id_eleve','=',$id_eleve)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
	     	$i->desincrire=1;
	     	$i->save();
        return redirect()->action('inscriptions\listesController@index') ;

	} */
    public function destroy($id_eleve)
	{

    
		$i=inscription::whereAnneeScolaire(\session('year'))
            ->where('id_eleve','=',$id_eleve)
            ->where('id_campus', '=', $this->currentCampusId())
            ->first();

        if(!$i) {
            return redirect()->action('inscriptions\listesController@index')
                ->withErrors(['inscription' => 'Aucune inscription trouvee pour cet etudiant dans l\'annee et le campus actifs.']);
        }

	     	$i->desinscrire=1;
	     	$i->save();
        return redirect()->action('inscriptions\listesController@index') ;

	}

    public function restore($id_eleve)
    {
        $i=inscription::whereAnneeScolaire(\session('year'))
            ->where('id_eleve','=',$id_eleve)
            ->where('id_campus', '=', $this->currentCampusId())
            ->first();

        if($i) {
            $i->desinscrire=0;
            $i->save();
        }

        return redirect()->action('inscriptions\listesController@index') ;
    }

}
