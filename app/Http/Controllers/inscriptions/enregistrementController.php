<?php namespace gesecole\Http\Controllers\inscriptions;

use gesecole\acces_eleve;
use gesecole\bourse;
use gesecole\eleve;
use gesecole\Http\Controllers\Controller;
use gesecole\inscription;
use gesecole\parametres;
use gesecole\payement;
use gesecole\Photo;
use gesecole\scolarite;
use gesecole\Vars;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;
use gesecole\parents;
use gesecole\Services\EnregistrementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;

class enregistrementController extends Controller {

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
        $values= array('titre'=>trans('inscription.title_search'));
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');

        }

        return view('inscriptions.search_edit_eleve',$values);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
    public function create()
	{

        $values= array('titre'=>'Enregistrement');
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');

        }
        if(session('error_parent')){
            Session::forget('error_parent');
            $values['error_parent']=trans('inscription.minimum_parent');
        }
        if(session('error_eleve')){
            Session::forget('error_eleve');
            $values['error_parent']=trans('inscription.deja_enreg');
        }
        return view('inscriptions.add_eleve',$values);
	}

    public function importPage()
    {
        $values = array(
            'titre' => 'Importation Excel des etudiants',
            'fields' => $this->studentImportFields()
        );

        if(session('import_success')) {
            $values['import_success'] = session('import_success');
            Session::forget('import_success');
        }

        return view('inscriptions.import_etudiants', $values);
    }

    public function downloadStudentImportTemplate()
    {
        $fields = $this->studentImportFields();
        $headers = array_keys($fields);
        $example = array();

        foreach($fields as $field => $meta) {
            $example[] = isset($meta['exemple']) ? $meta['exemple'] : '';
        }

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers, ';');
        fputcsv($stream, $example, ';');
        rewind($stream);
        $content = "\xEF\xBB\xBF".stream_get_contents($stream);
        fclose($stream);

        return Response::make($content, 200, array(
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_etudiants_parents.csv"',
        ));
    }

    public function importStudentsFromExcel(Request $req)
    {
        $this->validate($req, array(
            'fichier_etudiants' => 'required'
        ));

        $file = $req->file('fichier_etudiants');
        if(!$file || !$file->isValid()) {
            return redirect()->to(URL::previous())->withErrors(array('fichier_etudiants' => 'Le fichier est invalide.'));
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if(!in_array($extension, array('csv', 'txt', 'xlsx'))) {
            return redirect()->to(URL::previous())->withErrors(array('fichier_etudiants' => 'Utilisez le template telecharge depuis cette page, au format CSV ou XLSX simple.'));
        }

        $rows = $this->readStudentImportRows($file->getRealPath(), $extension);
        if(!$rows || count($rows) <= 1) {
            return redirect()->to(URL::previous())->withErrors(array('fichier_etudiants' => 'Impossible de lire le fichier ou fichier vide.'));
        }

        $created = 0;
        $skipped = 0;
        $lineNumber = 0;
        $headers = array();
        foreach($rows as $row) {
            $lineNumber++;
            if($lineNumber == 1) {
                $headers = $this->normalizeStudentImportHeaders($row);
                continue;
            }

            if(count(array_filter($row, 'strlen')) == 0) {
                continue;
            }

            $data = $this->combineStudentImportRow($headers, $row);
            $error = $this->validateStudentImportRow($data, $lineNumber);
            if($error) {
                return redirect()->to(URL::previous())->withErrors(array('fichier_etudiants' => $error));
            }

            $prenom = isset($data['prenom']) ? $data['prenom'] : '';
            $duplicate = eleve::whereNom($data['nom'])
                ->whereRaw('COALESCE(prenom, ?) = ?', array('', $prenom))
                ->where('date_naiss', '=', $this->normalizeStudentImportDate($data['date_naiss']))
                ->where('sexe', '=', strtoupper($data['sexe']))
                ->first();

            if($duplicate) {
                $skipped++;
                continue;
            }

            $e = new eleve();
            $this->fillStudentFromImportRow($e, $data);
            $current_user = \Auth::user();
            if($current_user) {
                $e->id_creator = $current_user->id;
                $e->id_last_editor = $current_user->id;
            }
            $e->save();
            $this->createParentsFromImportRow($e, $data);
            $created++;
        }

        session(array('import_success' => $created.' etudiant(s) importe(s) avec succes. '.$skipped.' doublon(s) ignore(s).'));

        return redirect()->action('inscriptions\enregistrementController@importPage');
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
            'date_naiss'=>'required|date',
            'lieu_naiss'=>'required',
            'sexe'=>'required',
            'nationalite'=>'required',
            'quartier'=>'required',
            'photo'=>'image|max:2048',

        ]);
        $e=new eleve();
        $p0=new parents();
        $p1=new parents();
        $p2=new parents();

        //élève

        $e->nom=$req->input('nom');
        $e->prenom=$req->input('prenom');
        $e->date_naiss=$req->input('date_naiss');
        $e->lieu_naiss=$req->input('lieu_naiss');
        $e->sexe=$req->input('sexe');
        $e->nationalite=$req->input('nationalite');
        $e->quartier=$req->input('quartier');
        $e->lien_tuteur=$req->input('lien_tuteur');
        $e->email=$req->input('email');
        $e->tel=$req->input('tel');
        $e->region_origine=$req->input('region_origine');
        $e->departement_origine=$req->input('departement_origine');
        $e->diplome_preinscription=$req->input('diplome_preinscription');
        $e->annee_obtention=$req->input('annee_obtention');
        $e->pays_obtention=$req->input('pays_obtention');
        $e->dernier_etablissement=$req->input('dernier_etablissement');
        $e->status_matrimonial=$req->input('status_matrimonial');
        $e->annee_sortie=$req->input('annee_sortie');
        $e->diplome_obtenu=$req->input('diplome_obtenu');
        $e->premiere_langue=$req->input('premiere_langue');
        $e->deuxieme_langue=$req->input('deuxieme_langue');
        $e->autre_langue=$req->input('autre_langue');
        $e->profession=$req->input('profession');
        $e->telephone_wathsapp_eleve=$req->input('telephone_wathsapp_eleve');
        $e->moyenne_generale=$req->input('moyenne_generale');
        $e->serie=$req->input('serie');
        $e->localisation_ecole=$req->input('localisation_ecole');
        $e->source_financement=$req->input('source_financement');
        $e->canal_decouvert=$req->input('canal_decouvert');
        $e->site_internet=$req->input('site_internet');
        $e->abonnement=$req->input('abonnement');
        $tp=$req->input('tel1_pere');
        $tm=$req->input('tel1_mere');
        $tt=$req->input('tel1_tuteur');
       // $ta=$request->input('max');
         $e->telephone_wathsapp_pere=$tp;
         $e->telephone_wathsapp_mere=$tm;
         $e->telephone_wathsapp_tuteur=$tt;

        // $e->telephone_wathsapp_pere=$req->input('tel1_pere');
        // $e->telephone_wathsapp_mere=$req->input('tel1_mere');
        // $e->telephone_wathsapp_tuteur=$req->input('tel1_tuteur');
        $e->tel=$req->input('tel');

        $e->abonnement_valide=$req->input('abonnement_valide');
        $e->chaine=$req->input('chaine');
        $e->numero_conseille=$req->input('numero_conseille');
        $eTest=eleve::whereNom($e->nom)->where('prenom','=',$e->prenom)->where('date_naiss','=',$e->date_naiss)->where('sexe','=',$e->sexe)->get()->first();
        //père//////////////////////////
        $p1->nom=$req->input('nom_pere');
        $p1->profession=$req->input('profession_pere');
        $p1->ville=$req->input('ville_pere');
        $p1->quartier=$req->input('quartier_pere');
        $p1->tel1=$req->input('tel1_pere');
        $p1->tel2=$req->input('tel2_pere');
        $p1->type=1;
        //mere//////////////////////////
        $p0->nom=$req->input('nom_mere');
        $p0->profession=$req->input('profession_mere');
        $p0->ville=$req->input('ville_mere');
        $p0->quartier=$req->input('quartier_mere');
        $p0->tel1=$req->input('tel1_mere');
        $p0->tel2=$req->input('tel2_mere');
        $p0->type=0;
        //tuteur////////////////////////
        $p2->nom=$req->input('nom_tuteur');
        $p2->profession=$req->input('profession_tuteur');
        $p2->ville=$req->input('ville_tuteur');
        $p2->quartier=$req->input('quartier_tuteur');
        $p2->tel1=$req->input('tel1_tuteur');
        $p2->tel2=$req->input('tel2_tuteur');
        $p2->type=2;

        if(($p0->nom==null) && ($p1->nom==null) && ($p2->nom==null) ){
            session(['error_parent'=>true]);
            //Input::flash();
            return redirect()->action('inscriptions\enregistrementController@create')->withInput();


        }elseif ($eTest!=null){
            session(['error_eleve'=>true]);
        }
        else{

            $current_user = \Auth::user();
            $e->id_creator = $current_user->id;
            $e->id_last_editor = $current_user->id;

                $e->save();
                if ($p0->nom != null) {
                    $p0->id_eleve = $e->id;
                    $p0->save();
                }
                if ($p1->nom != null) {
                    $p1->id_eleve = $e->id;
                    $p1->save();
                }
                if ($p2->nom != null) {
                    $p2->id_eleve = $e->id;
                    $p2->save();
                }
            $photo=$req->file('photo');
            if($photo!=null){
                $ext=$photo->getClientOriginalExtension();
                $savePath = photosController::getPhotosSaveDirPath() . '/' .$e->id.'-'.session('year').'.'.$ext;
                //Image::make($photo->getRealPath())->encode('jpg',80)->resize(300,300)->save($savePath);
                Image::make($photo->getRealPath())->resize(300,300)->save($savePath);

                $ph=new Photo();
                $ph->eleve_id=$e->id;
                $ph->annee_scolaire=\session('year');
                $ph->url = $savePath;
                $ph->save();


            }


                session(['success' => $e->id]);

        }
        return redirect()->action('inscriptions\enregistrementController@create');
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
        $e=eleve::find($id);
        $p=$e->parents;
        $values['eleve']=$e;
        $values['parents']=$p;
        return $values;
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$e=eleve::find($id);
		$p=$e->parents;
        $ins = inscription::with('classe', 'nniveau')
            ->where('id_eleve', '=', $e->id)
            ->orderBy('annee_scolaire')
            ->get();
        $current_inscription = inscription::where('id_eleve', '=', $e->id)
            ->where('annee_scolaire', '=', \session('year'))
            ->first();
		$photo=Photo::whereEleveId($id)->where('annee_scolaire','=',session('year'))->first();
        $values= array('titre'=>trans('inscription.edit_eleve'));
        if($photo!=null)
        {
            $pUrl=$photo->url;
            $values['photoUrl']=$pUrl;
        }
        $values['eleve']=$e;
        $values['parents']=$p;
        $values['inscription']=$current_inscription;
        $values['inscriptions']=$ins;

        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }


        return view('inscriptions.edit_eleve',$values);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $req,$id)
	{ $this->validate($req,[
        'nom'=>'required',
        'date_naiss'=>'required|date',
        'lieu_naiss'=>'required',
        'sexe'=>'required',
        'nationalite'=>'required',
        'quartier'=>'required',
        'photo'=>'image|max:2048',

    ]);
        $e=eleve::find($id);
        $e->nom=$req->input('nom');
        $e->date_naiss=$req->input('date_naiss');
        $e->lieu_naiss=$req->input('lieu_naiss');
        $e->sexe=$req->input('sexe');
        $e->nationalite=$req->input('nationalite');
        $e->quartier=$req->input('quartier');
        $e->lien_tuteur=$req->input('lien_tuteur');
        $e->parent_ens=$req->input('parents_ens');
        $e->prenom=$req->input('prenom');
        $e->email=$req->input('email');
        $e->tel=$req->input('tel');
        $e->region_origine=$req->input('region_origine');
        $e->departement_origine=$req->input('departement_origine');
        $e->diplome_preinscription=$req->input('diplome_preinscription');
        $e->annee_obtention=$req->input('annee_obtention');
        $e->pays_obtention=$req->input('pays_obtention');
        $e->dernier_etablissement=$req->input('dernier_etablissement');
        $e->annee_sortie=$req->input('annee_sortie');
        $e->diplome_obtenu=$req->input('diplome_obtenu');
        $e->premiere_langue=$req->input('premiere_langue');
        $e->deuxieme_langue=$req->input('deuxieme_langue');
        $e->autre_langue=$req->input('autre_langue');
        $e->profession=$req->input('profession');

        $e->telephone_wathsapp_eleve=$req->input('telephone_wathsapp_eleve');
        $e->moyenne_generale=$req->input('moyenne_generale');
        $e->serie=$req->input('serie');
        $e->localisation_ecole=$req->input('localisation_ecole');
        $e->source_financement=$req->input('source_financement');
        $e->canal_decouvert=$req->input('canal_decouvert');
        $e->site_internet=$req->input('site_internet');
        $e->abonnement=$req->input('abonnement');
        $e->telephone_wathsapp_pere=$req->input('telephone_wathsapp_pere');
        $e->telephone_wathsapp_mere=$req->input('telephone_wathsapp_mere');
        $e->telephone_wathsapp_tuteur=$req->input('telephone_wathsapp_tuteur');
       // $e->telephone_wathsapp_autre=$req->input('telephone_wathsapp_autre');
        $e->abonnement_valide=$req->input('abonnement_valide');
        $e->chaine=$req->input('chaine');
        $e->numero_conseille=$req->input('numero_conseille');

        $current_user = \Auth::user();
        $e->id_last_editor = $current_user->id;
        $e->save();

        $photo=$req->file('photo');
        if($photo!=null){

            $ext=$photo->getClientOriginalExtension();
            $savePath = photosController::getPhotosSaveDirPath() . '/' .$e->id.'-'.session('year').'.'.$ext;
            //Image::make($photo->getRealPath())->encode('jpg',80)->resize(300,300)->save($savePath);
            Image::make($photo->getRealPath())->resize(300,300)->save($savePath);

            $ph=Photo::firstOrNew(['eleve_id'=>$id,'annee_scolaire'=>\session('year')]);
            $ph->url = $savePath;
            $ph->save();


        }
        session(['success'=>true]);
        return redirect()->action('inscriptions\enregistrementController@edit',[$id]) ;
	}

	public function status_eleve(Request $request) {
	    $id = $request->input('id');
	    $show_access = $request->input('show_access');
        $idCampus = $this->currentCampusId();
        $values= array('titre'=>trans('admin.status_eleve'));

        $id=intval($id);
        $eleve = eleve::find($id);

	    if($eleve) {
	        if($show_access == 1) {
                $values['show_access'] = $show_access;
            }
            $acces = acces_eleve::where('annee_scolaire', '=', \session('year'))
                ->where('id_eleve', '=', $id);
            if(Schema::hasColumn('acces_eleves', 'id_campus')) {
                $acces->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
            }
            $acces = $acces->first();
            $values['eleve'] = $eleve;
            $values['acces'] = $acces;
            $values['access_decision'] = EnregistrementService::getStudentAccessStatus($id, $idCampus);


            $inscription = inscription::with('classe.niveau', 'nniveau.cycle')
                ->where('id_eleve', '=', $id)
                ->where('annee_scolaire', '=', \session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                ->first();

	        if ($inscription) {
	            $values['inscription'] = $inscription;
                $solde = payement::where('id_eleve', '=', $id)
                    ->where('type', '=', 0)
                    ->where('id_inscription', '=', 0)
                    ->where('annee_scolaire', '=', \session('year'))
                    ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                    ->sum('montant');

                $values['solde'] = $solde;


                $scolarite = scolarite::forInscription($inscription);
                $bourseQuery = bourse::where('id_eleve', '=', $id)
                    ->where('annee_scolaire', '=', session('year'));
                if (Schema::hasColumn('bourses', 'id_campus')) {
                    $bourseQuery->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
                }
                $bourse = $bourseQuery->first();
                if ($bourse) {
                    $values['montant_bourse'] = $bourse->montant;
                }

                $values['scolarite'] = $scolarite;
            }
        }

	    return view('inscriptions.status_eleve', $values);
    }


    public function set_status(Request $request) {
	    $status = $request->input('status');
	    $id = $request->input('id');
        $idCampus = $this->currentCampusId();
	    $date_debut = $request->input('date_debut');

	    $date_fin = $request->input('date_fin');
	    $montant = $request->input('montant');
	    $eleve = eleve::find($id);

	    if($eleve) {
            $inscription = inscription::where('id_eleve', '=', $id)
                ->where('annee_scolaire', '=', \session('year'))
                ->where('id_campus', '=', $idCampus)
                ->first();

            if(!$inscription) {
                return redirect()->to(URL::previous());
            }

            $current_user = \Auth::user();

            $acces = acces_eleve::where('annee_scolaire', '=', \session('year'))
                ->where('id_eleve', '=', $id);
            if(Schema::hasColumn('acces_eleves', 'id_campus')) {
                $acces->where('id_campus', '=', $idCampus);
            }
            $acces = $acces->first();

            if($acces == null) {
                $acces = new acces_eleve();
                $acces->annee_scolaire = \session('year');
                $acces->id_eleve = $id;
                if(Schema::hasColumn('acces_eleves', 'id_campus')) {
                    $acces->id_campus = $idCampus;
                }
                $acces->id_creator = $current_user->id;

                if($status == 1) {
                    $acces->edition_details = 'decision = '. Vars::DECISION_ALLOW;
                } else {
                    $acces->edition_details = 'decision = '. Vars::DECISION_DENY;
                }
            }
            $acces->id_last_editor = $current_user->id;

            if ($date_debut) {
                $acces->date_debut = $date_debut;
            }
            if ($date_fin) {
                $acces->date_fin = $date_fin;
            }

            if ($montant) {
                $acces->montant = $montant;
            }

            if($status == 1) {
                $acces->decision = Vars::DECISION_ALLOW;
            } else {
                $acces->decision = Vars::DECISION_DENY;
            }

            $acces->save();
        }

	    return redirect()->to(URL::previous());

    }

    public function set_multi_status(Request $request) {
        $status = $request->input('status');
        $ids = $request->input('ids');
        $current_user = \Auth::user();
        $idCampus = $this->currentCampusId();
        $date_debut = $request->input('date_debut');
	    $date_fin = $request->input('date_fin');
	    $montant = $request->input('montant');

        foreach ($ids as $id) {
            $eleve = eleve::find($id);
            if($eleve) {
                $inscription = inscription::where('id_eleve', '=', $id)
                    ->where('annee_scolaire', '=', \session('year'))
                    ->where('id_campus', '=', $idCampus)
                    ->first();

                if(!$inscription) {
                    continue;
                }

                $acces = acces_eleve::where('annee_scolaire', '=', \session('year'))
                    ->where('id_eleve', '=', $id);
                if(Schema::hasColumn('acces_eleves', 'id_campus')) {
                    $acces->where('id_campus', '=', $idCampus);
                }
                $acces = $acces->first();

                if($acces == null) {
                    $acces = new acces_eleve();
                    $acces->annee_scolaire = \session('year');
                    $acces->id_eleve = $id;
                    if(Schema::hasColumn('acces_eleves', 'id_campus')) {
                        $acces->id_campus = $idCampus;
                    }
                    $acces->id_creator = $current_user->id;
                    if($status == 1) {
                        $acces->edition_details = 'decision = '. Vars::DECISION_ALLOW;
                    } else {
                        $acces->edition_details = 'decision = '. Vars::DECISION_DENY;

                    }

                }
                $acces->id_last_editor = $current_user->id;
                if ($date_debut) {
                    $acces->date_debut = $date_debut;
                }
                if ($date_fin) {
                    $acces->date_fin = $date_fin;
                }
                if ($montant) {
                    $acces->montant = $montant;
                }

                if($status == 1) {
                    $acces->decision = Vars::DECISION_ALLOW;
                } else {
                    $acces->decision = Vars::DECISION_DENY;
                }

                $acces->save();
            }

        }



        return response()->json('OK');

    }

    public function get_status(Request $request) {
        $id = $request->input('id');
        $idCampus = $this->currentCampusId();

            $acces = acces_eleve::where('annee_scolaire', '=', \session('year'))
                ->where('id_eleve', '=', $id);
            if(Schema::hasColumn('acces_eleves', 'id_campus')) {
                $acces->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
            }
            $acces = $acces->first();


    }

    private function studentImportFields()
    {
        return array(
            'nom' => array('table' => 'eleve', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Nom de famille de l etudiant.', 'exemple' => 'KAMGA', 'valeurs' => 'Texte'),
            'prenom' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Prenom(s) de l etudiant.', 'exemple' => 'Loic Bernard', 'valeurs' => 'Texte'),
            'date_naiss' => array('table' => 'eleve', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Date de naissance.', 'exemple' => '2003-03-03', 'valeurs' => 'AAAA-MM-JJ, JJ/MM/AAAA ou date Excel'),
            'lieu_naiss' => array('table' => 'eleve', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Lieu de naissance.', 'exemple' => 'YAOUNDE', 'valeurs' => 'Texte'),
            'ne_vers' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Indication si la date de naissance est approximative.', 'exemple' => '', 'valeurs' => 'Texte'),
            'sexe' => array('table' => 'eleve', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Sexe de l etudiant.', 'exemple' => 'M', 'valeurs' => 'M ou F'),
            'nationalite' => array('table' => 'eleve', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Nationalite.', 'exemple' => 'Camerounaise', 'valeurs' => 'Texte'),
            'quartier' => array('table' => 'eleve', 'obligatoire' => 'Oui', 'defaut' => '-', 'description' => 'Residence ou quartier.', 'exemple' => 'Ngousso', 'valeurs' => 'Texte'),
            'parent_ens' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Indiquer si un parent est enseignant.', 'exemple' => '0', 'valeurs' => '0/1 ou texte selon usage interne'),
            'email' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Email de l etudiant.', 'exemple' => 'etudiant@example.com', 'valeurs' => 'Email'),
            'tel' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Telephone principal.', 'exemple' => '699000000', 'valeurs' => 'Texte'),
            'telephone_wathsapp_eleve' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Numero WhatsApp etudiant.', 'exemple' => '699000000', 'valeurs' => 'Texte'),
            'status_matrimonial' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Situation matrimoniale.', 'exemple' => 'Celibataire', 'valeurs' => 'Texte'),
            'region_origine' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Region d origine.', 'exemple' => 'Centre', 'valeurs' => 'Texte'),
            'departement_origine' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Departement d origine.', 'exemple' => 'Mfoundi', 'valeurs' => 'Texte'),
            'diplome_preinscription' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Diplome presente a la preinscription.', 'exemple' => 'BACC', 'valeurs' => 'Texte'),
            'serie' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Serie du diplome.', 'exemple' => 'D', 'valeurs' => 'Texte'),
            'moyenne_generale' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => '0', 'description' => 'Moyenne generale du diplome.', 'exemple' => '12.50', 'valeurs' => 'Nombre'),
            'annee_obtention' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Annee d obtention du diplome.', 'exemple' => '2024', 'valeurs' => 'Texte'),
            'pays_obtention' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Pays d obtention.', 'exemple' => 'Cameroun', 'valeurs' => 'Texte'),
            'dernier_etablissement' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Dernier etablissement frequente.', 'exemple' => 'Lycee Exemple', 'valeurs' => 'Texte'),
            'localisation_ecole' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Localisation du dernier etablissement.', 'exemple' => 'Yaounde', 'valeurs' => 'Texte'),
            'annee_sortie' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Annee de sortie.', 'exemple' => '2024', 'valeurs' => 'Texte'),
            'diplome_obtenu' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Diplome obtenu.', 'exemple' => 'BACC D', 'valeurs' => 'Texte'),
            'premiere_langue' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Premiere langue.', 'exemple' => 'Francais', 'valeurs' => 'Texte'),
            'deuxieme_langue' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Deuxieme langue.', 'exemple' => 'Anglais', 'valeurs' => 'Texte'),
            'autre_langue' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Autre langue.', 'exemple' => '', 'valeurs' => 'Texte'),
            'profession' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Profession actuelle si applicable.', 'exemple' => 'Etudiant', 'valeurs' => 'Texte'),
            'source_financement' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Source potentielle de financement.', 'exemple' => 'soutient_familial', 'valeurs' => 'bourse, pret, soutient_familial, autre'),
            'canal_decouvert' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Canal par lequel l etudiant a connu l institut.', 'exemple' => 'facebook', 'valeurs' => 'depliant, concour, college, google, connaissance, facebook, plaque, tele, radio, pannaux, conseille'),
            'site_internet' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'A deja visite le site internet.', 'exemple' => 'oui', 'valeurs' => 'oui ou non'),
            'abonnement' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Abonne a une page ou canal de communication.', 'exemple' => 'non', 'valeurs' => 'oui ou non'),
            'abonnement_valide' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Precision sur le canal d abonnement.', 'exemple' => '', 'valeurs' => 'Texte'),
            'chaine' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Chaine TV si canal TV.', 'exemple' => '', 'valeurs' => 'Texte'),
            'numero_conseille' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Telephone du conseille d orientation.', 'exemple' => '', 'valeurs' => 'Texte'),
            'lien_tuteur' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Lien avec le tuteur.', 'exemple' => 'Oncle', 'valeurs' => 'Texte'),
            'telephone_wathsapp_autre' => array('table' => 'eleve', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Autre numero WhatsApp utile au suivi.', 'exemple' => '', 'valeurs' => 'Texte'),
            'nom_pere' => array('table' => 'parent', 'obligatoire' => 'Au moins un parent', 'defaut' => 'Vide', 'description' => 'Nom du pere.', 'exemple' => 'KAMGA Pierre', 'valeurs' => 'Texte'),
            'profession_pere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Profession du pere.', 'exemple' => 'Commercant', 'valeurs' => 'Texte'),
            'ville_pere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Ville du pere.', 'exemple' => 'Yaounde', 'valeurs' => 'Texte'),
            'quartier_pere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Quartier du pere.', 'exemple' => 'Mvan', 'valeurs' => 'Texte'),
            'tel1_pere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'WhatsApp du pere.', 'exemple' => '677000000', 'valeurs' => 'Texte'),
            'tel2_pere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Autre numero du pere.', 'exemple' => '', 'valeurs' => 'Texte'),
            'nom_mere' => array('table' => 'parent', 'obligatoire' => 'Au moins un parent', 'defaut' => 'Vide', 'description' => 'Nom de la mere.', 'exemple' => 'NGONO Marie', 'valeurs' => 'Texte'),
            'profession_mere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Profession de la mere.', 'exemple' => 'Enseignante', 'valeurs' => 'Texte'),
            'ville_mere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Ville de la mere.', 'exemple' => 'Yaounde', 'valeurs' => 'Texte'),
            'quartier_mere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Quartier de la mere.', 'exemple' => 'Emana', 'valeurs' => 'Texte'),
            'tel1_mere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'WhatsApp de la mere.', 'exemple' => '676000000', 'valeurs' => 'Texte'),
            'tel2_mere' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Autre numero de la mere.', 'exemple' => '', 'valeurs' => 'Texte'),
            'nom_tuteur' => array('table' => 'parent', 'obligatoire' => 'Au moins un parent', 'defaut' => 'Vide', 'description' => 'Nom du tuteur.', 'exemple' => '', 'valeurs' => 'Texte'),
            'profession_tuteur' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Profession du tuteur.', 'exemple' => '', 'valeurs' => 'Texte'),
            'ville_tuteur' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Ville du tuteur.', 'exemple' => '', 'valeurs' => 'Texte'),
            'quartier_tuteur' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Quartier du tuteur.', 'exemple' => '', 'valeurs' => 'Texte'),
            'tel1_tuteur' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'WhatsApp du tuteur.', 'exemple' => '', 'valeurs' => 'Texte'),
            'tel2_tuteur' => array('table' => 'parent', 'obligatoire' => 'Non', 'defaut' => 'Vide', 'description' => 'Autre numero du tuteur.', 'exemple' => '', 'valeurs' => 'Texte'),
        );
    }

    private function normalizeStudentImportHeaders($headers)
    {
        $normalized = array();
        foreach($headers as $header) {
            $normalized[] = strtolower(trim($header, " \t\n\r\0\x0B\xEF\xBB\xBF"));
        }

        return $normalized;
    }

    private function readStudentImportRows($path, $extension)
    {
        if(in_array($extension, array('csv', 'txt'))) {
            return $this->readStudentCsvRows($path);
        }

        return $this->readStudentXlsxRows($path);
    }

    private function readStudentCsvRows($path)
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

    private function readStudentXlsxRows($path)
    {
        if(!class_exists('\ZipArchive')) {
            return null;
        }

        $zip = new \ZipArchive();
        if($zip->open($path) !== true) {
            return null;
        }

        $sharedStrings = $this->readStudentXlsxSharedStrings($zip);
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
                $index = $this->studentXlsxColumnIndex($cellRef);
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
                $rows[] = $this->normalizeStudentXlsxRowIndexes($row);
            }
        }

        return $rows;
    }

    private function readStudentXlsxSharedStrings(\ZipArchive $zip)
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

    private function studentXlsxColumnIndex($cellRef)
    {
        $letters = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
        $index = 0;
        for($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    private function normalizeStudentXlsxRowIndexes($row)
    {
        $normalized = array();
        $max = max(array_keys($row));
        for($i = 0; $i <= $max; $i++) {
            $normalized[] = isset($row[$i]) ? $row[$i] : '';
        }

        return $normalized;
    }

    private function combineStudentImportRow($headers, $row)
    {
        $data = array();
        foreach($headers as $index => $header) {
            if($header !== '') {
                $data[$header] = isset($row[$index]) ? trim($row[$index]) : '';
            }
        }

        return $data;
    }

    private function validateStudentImportRow($data, $lineNumber)
    {
        $required = array('nom', 'date_naiss', 'lieu_naiss', 'sexe', 'nationalite', 'quartier');
        foreach($required as $field) {
            if(!isset($data[$field]) || trim($data[$field]) === '') {
                return 'Ligne '.$lineNumber.' : le champ '.$field.' est obligatoire.';
            }
        }

        if(!$this->normalizeStudentImportDate($data['date_naiss'])) {
            return 'Ligne '.$lineNumber.' : date_naiss doit etre au format AAAA-MM-JJ ou JJ/MM/AAAA.';
        }

        $sexe = strtoupper($data['sexe']);
        if(!in_array($sexe, array('M', 'F'))) {
            return 'Ligne '.$lineNumber.' : sexe doit etre M ou F.';
        }

        if(isset($data['moyenne_generale']) && trim($data['moyenne_generale']) !== '' && !is_numeric(str_replace(',', '.', $data['moyenne_generale']))) {
            return 'Ligne '.$lineNumber.' : moyenne_generale doit etre numerique.';
        }

        if(!$this->hasAtLeastOneImportedParent($data)) {
            return 'Ligne '.$lineNumber.' : renseignez au moins nom_pere, nom_mere ou nom_tuteur.';
        }

        return null;
    }

    private function normalizeStudentImportDate($date)
    {
        $date = trim($date);
        $formats = array('Y-m-d', 'd/m/Y', 'd-m-Y');
        foreach($formats as $format) {
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

    private function fillStudentFromImportRow(eleve $e, $data)
    {
        $fields = array(
            'nom', 'prenom', 'lieu_naiss', 'nationalite', 'quartier', 'lien_tuteur',
            'email', 'tel', 'status_matrimonial', 'region_origine',
            'departement_origine', 'diplome_preinscription', 'annee_obtention',
            'pays_obtention', 'dernier_etablissement', 'annee_sortie', 'diplome_obtenu',
            'premiere_langue', 'deuxieme_langue', 'autre_langue', 'profession',
            'telephone_wathsapp_eleve', 'serie', 'localisation_ecole', 'source_financement',
            'canal_decouvert', 'site_internet', 'abonnement', 'abonnement_valide',
            'chaine', 'numero_conseille', 'ne_vers', 'parent_ens', 'telephone_wathsapp_autre'
        );

        foreach($fields as $field) {
            if(isset($data[$field])) {
                $e->$field = $data[$field];
            }
        }

        $e->date_naiss = $this->normalizeStudentImportDate($data['date_naiss']);
        $e->sexe = strtoupper($data['sexe']);
        $e->moyenne_generale = isset($data['moyenne_generale']) && trim($data['moyenne_generale']) !== ''
            ? str_replace(',', '.', $data['moyenne_generale'])
            : 0;
        $e->telephone_wathsapp_pere = isset($data['tel1_pere']) ? $data['tel1_pere'] : '';
        $e->telephone_wathsapp_mere = isset($data['tel1_mere']) ? $data['tel1_mere'] : '';
        $e->telephone_wathsapp_tuteur = isset($data['tel1_tuteur']) ? $data['tel1_tuteur'] : '';
    }

    private function hasAtLeastOneImportedParent($data)
    {
        return (isset($data['nom_pere']) && trim($data['nom_pere']) !== '')
            || (isset($data['nom_mere']) && trim($data['nom_mere']) !== '')
            || (isset($data['nom_tuteur']) && trim($data['nom_tuteur']) !== '');
    }

    private function createParentsFromImportRow(eleve $e, $data)
    {
        $this->createImportedParent($e, $data, 'pere', 1);
        $this->createImportedParent($e, $data, 'mere', 0);
        $this->createImportedParent($e, $data, 'tuteur', 2);
    }

    private function createImportedParent(eleve $e, $data, $suffix, $type)
    {
        $nameField = 'nom_'.$suffix;
        if(!isset($data[$nameField]) || trim($data[$nameField]) === '') {
            return;
        }

        $p = new parents();
        $p->id_eleve = $e->id;
        $p->type = $type;
        $p->nom = $data[$nameField];
        $p->profession = isset($data['profession_'.$suffix]) ? $data['profession_'.$suffix] : '';
        $p->ville = isset($data['ville_'.$suffix]) ? $data['ville_'.$suffix] : '';
        $p->quartier = isset($data['quartier_'.$suffix]) ? $data['quartier_'.$suffix] : '';
        $p->tel1 = isset($data['tel1_'.$suffix]) ? $data['tel1_'.$suffix] : '';
        $p->tel2 = isset($data['tel2_'.$suffix]) ? $data['tel2_'.$suffix] : '';
        $p->save();
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
            eleve::destroy([$id]);
        } catch (QueryException $fe) {
            return  redirect()->to(URL::previous())->withErrors(['constraint_violation' => trans('admin.cannot_delete')]);
        }
            return redirect()->to(\URL::previous());
	}

}
