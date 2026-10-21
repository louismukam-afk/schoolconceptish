<?php namespace gesecole\Http\Controllers\inscriptions;

use Doctrine\Common\Collections\Expr\Value;
use gesecole\bourse;
use gesecole\cycle;
use gesecole\eleve;
use gesecole\Frais;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\classe;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parents;
use gesecole\payement;
use gesecole\scolarite;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use gesecole\inscription;
use gesecole\parametres;
use gesecole\Values;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class listesController extends Controller {
    const OP_TYPE_PAID = 1;
    const OP_TYPE_UNPAID = 0;

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
        $c=classe::orderBy('nom')->get();
        $c2=classe::with('niveau')->orderBy('id_niveau')
            ->orderBy('nom')
            ->get();
        $f = Frais::orderBy('nom')->get();
        $values= array('titre'=>trans('inscription.listes'));
        $values['classes']=$c;
        $values['frais']=$f;
        $values['classes_list']=$c2;
        $cy=cycle::all();
        $n=niveau::all();
        $nn=NNiveau::all();
        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['nniveaux']=$nn;


        return view('inscriptions.selection_liste',$values);
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
	public function show(Request $request)
	{
        $this->validate($request,[
            'id'=>'required',
        ]);

	    $id=$request->input('id');
        $type=$request->input('type');
        $option=$request->input('option');
        $idCampus = $this->currentCampusId();
        $scolariteStyle=$request->input('scolarite_style', '');
        $scolariteMatchWhere=$this->scolariteMatchWhere();
        $scolariteProfileWhere=$this->scolariteProfileWhere($scolariteStyle);

	    $nniveau=intval($request->input('nniveau', 0));
	    $p=parametres::whereNom('nom_etablissement')->first();
	    $e_name=$p->valeur;
	    $values['e_name']=$e_name;
	    $cycle=$request->input('cycle');
        if($nniveau) {
	        $cycle = NNiveau::find($nniveau)->id_cycle;
        }
        $idCampus = $this->currentCampusId();


	    if($option==null) {
            if ($id == 0) {
                $values['titre'] = trans('inscription.listes');

                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,e.id, e.nom ,e.prenom,e.date_naiss,e.diplome_preinscription,e.lieu_naiss,e.sexe,inscription.date_inscription,c.nom as nom_classe,c.code as code_classe,nn.nom as nniveau FROM eleve e,inscription,classe c,n_niveaus nn WHERE e.id=inscription.id_eleve and annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.id_classe=c.id  and inscription.desinscrire=0 and inscription.id_nniveau=nn.id AND (?=0 OR inscription.id_nniveau=?) ORDER BY code_classe,nom,prenom ', [session('year'), $this->currentCampusId(), $nniveau, $nniveau]);
                $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['show_classe'] = true;
                $values['titre_liste'] = trans('inscription.all_eleves') . ($nniveau ? ' - ' . NNiveau::find($nniveau)->nom : '');
                return view('inscriptions.listes', $values);
            } else {
                $values['titre'] = trans('inscription.listes');
                $classe = classe::find($id);
                $classe_name = $classe->getNom();
                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,e.id, e.nom ,e.prenom,e.date_naiss,e.diplome_preinscription,e.lieu_naiss,e.sexe,inscription.date_inscription,c.nom as nom_classe  FROM eleve e,inscription,classe c WHERE e.id=inscription.id_eleve and annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.id_classe=c.id and c.id=? AND (?=0 OR inscription.id_cycle=?)   AND inscription.desinscrire=0 AND (?=0 OR inscription.id_nniveau=?) ORDER BY nom,prenom ', [session('year'), $this->currentCampusId(), $id, $cycle, $cycle, $nniveau, $nniveau]);
               $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                @$values['titre_liste'] = trans('inscription.eleves_de') . ' ' . ($classe_name).' '.($nniveau ? NNiveau::find($nniveau)->nom : trans('admin.tous'));
                return view('inscriptions.listes', $values);
            }
        }else
        {
            $boursesQueryPart = '';
            if($option==1){
                $tranches = "s.tranche1";
                $tranche = "s.tranche1";
                $motif=trans('admin.tranche1');
                if (Values::SCHOOLFEES_SLICE_NUMBER <= 1) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==2){
                $tranches = "(s.tranche1+s.tranche2)";
                $tranche = "s.tranche2";
                $motif=trans('admin.tranche2');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 2) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==3){
                $tranches = "(s.tranche1+s.tranche2+s.tranche3)";
                $tranche = "s.tranche3";
                $motif=trans('admin.tranche3');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 3) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==4){
                $tranches = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4)";
                $tranche = "s.tranche4";
                $motif=trans('admin.tranche4');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 4) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==5){
                $tranches = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";
                $tranche = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";

                $motif=trans('admin.tranche5');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 5) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }
            else
            {
                $tranches = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";
                $tranche = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";
                $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                $values['last_installment'] = true;
                $motif=trans('admin.schoolfees');
            }



            if($type == static::OP_TYPE_PAID)
            {
                $opComp='>=';
                $values['paid'] = true;

                $t0=trans('admin.paid_eleve').' : '.$motif;
            }
            else
            {
                $opComp='<';
                $t0=trans('admin.unpaid_eleve').' : '.$motif;
            }

            if ($id == 0) {
                $values['titre'] = trans('inscription.listes');

                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,inscription.type_scolarite,inscription.date_inscription,e.id, e.nom ,e.prenom,e.date_naiss,e.lieu_naiss,e.diplome_preinscription,e.sexe,c.nom as nom_classe,c.code as code_classe,sol.solde,' . $tranche . ' AS tranche ,(' . $tranches . ' - sol.solde) AS reste, nn.nom as nniveau, b.montant as montant_bourse FROM n_niveaus nn, eleve e LEFT JOIN bourses as b ON (e.id = b.id_eleve AND b.annee_scolaire = ? AND ' . $this->campusVisibilitySql('b.id_campus') . '),inscription,classe c,niveau n,scolarite s, (SELECT SUM(CASE WHEN p.type = 0 AND p.id_inscription=0 THEN p.montant ELSE 0 END) as solde,e.id FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id  WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve   and inscription.desinscrire=0 and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.id_classe=c.id AND sol.id=e.id AND c.id_niveau=n.id AND ' . $scolariteMatchWhere . $scolariteProfileWhere . ' AND (sol.solde ' . $boursesQueryPart . ' - ' . $tranches . ')'.$opComp.'0 AND inscription.id_nniveau=nn.id AND (?=0 OR inscription.id_nniveau=?) ORDER BY code_classe,nom,prenom', [session('year'), $idCampus, session('year'), $idCampus, session('year'), $idCampus, $nniveau, $nniveau]);
                $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['titre_liste'] = $t0.' - '.trans('inscription.all_eleves') . ($nniveau ? ' - ' . NNiveau::find($nniveau)->nom : '');
                if($type != null) {
                    $values['type']=$type;
                }
                $values['show_classe'] = true;
                return view('inscriptions.listes', $values);
            } else {
                $values['titre'] = trans('inscription.listes');
                $classe = classe::find($id);
                $classe_name = $classe->getNom();
                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,e.id, inscription.type_scolarite,inscription.date_inscription, e.nom ,e.prenom,e.date_naiss,e.lieu_naiss,e.diplome_preinscription,e.sexe,c.nom as nom_classe,sol.solde,' . $tranche . ' AS tranche ,(' . $tranches . ' - sol.solde) AS reste, b.montant as montant_bourse  FROM eleve e LEFT JOIN bourses as b ON (e.id = b.id_eleve AND b.annee_scolaire = ? AND ' . $this->campusVisibilitySql('b.id_campus') . '),inscription,classe c,niveau n,scolarite s, (SELECT SUM(CASE WHEN p.type = 0 AND p.id_inscription=0 THEN p.montant ELSE 0 END) as solde,e.id FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.id_classe=c.id  AND inscription.desinscrire=0  AND sol.id=e.id AND c.id_niveau=n.id AND ' . $scolariteMatchWhere . $scolariteProfileWhere . '  AND (sol.solde + ' . $boursesQueryPart . ' - ' . $tranches . ')'.$opComp.'0 AND c.id=? AND (?=0 OR inscription.id_cycle=?) AND (?=0 OR inscription.id_nniveau=?) ORDER BY nom_classe,nom,prenom', [session('year'), $idCampus, session('year'), $idCampus, session('year'), $idCampus, $id, $cycle, $cycle, $nniveau, $nniveau]);
                $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['titre_liste'] = $t0.' - '. $classe_name.' '.($nniveau ? NNiveau::find($nniveau)->nom : trans('admin.tous'));
                if($type != null) {
                    $values['type']=$type;
                }
                return view('inscriptions.listes', $values);
            }


        }

	}




    //les listes des parents


    public function showParents(Request $request)
	{
        $this->validate($request,[
            'id'=>'required',
        ]);

	    $id=$request->input('id');
        $type=$request->input('type');
        $option=$request->input('option');
        $idCampus = $this->currentCampusId();

	    $nniveau=intval($request->input('nniveau', 0));
	    $p=parametres::whereNom('nom_etablissement')->first();
	    $e_name=$p->valeur;
	    $values['e_name']=$e_name;
	    $cycle=$request->input('cycle');
	    if($nniveau) {
	        $cycle = NNiveau::find($nniveau)->id_cycle;
        }


	    if($option==null) {
            if ($id == 0) {
                $values['titre'] = trans('inscription.listes');

                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.moyenne_generale,e.serie,e.localisation_ecole,e.source_financement,e.canal_decouvert,e.site_internet,e.abonnement,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,e.id, e.nom ,e.prenom,e.date_naiss,e.diplome_preinscription,e.lieu_naiss,e.sexe,inscription.date_inscription,c.nom as nom_classe,c.code as code_classe,nn.nom as nniveau FROM eleve e,inscription,classe c,n_niveaus nn WHERE e.id=inscription.id_eleve and annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.desinscrire=0 and inscription.id_classe=c.id and inscription.id_nniveau=nn.id AND (?=0 OR inscription.id_nniveau=?) ORDER BY code_classe,nom,prenom ', [session('year'), $idCampus, $nniveau, $nniveau]);
                $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['show_classe'] = true;
                $values['titre_liste'] = trans('inscription.all_eleves') . ($nniveau ? ' - ' . NNiveau::find($nniveau)->nom : '');
                return view('inscriptions.listes_parents', $values);
            } else {
                $values['titre'] = trans('inscription.listes');
                $classe = classe::find($id);
                $classe_name = $classe->getNom();
                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.moyenne_generale,e.serie,e.localisation_ecole,e.source_financement,e.canal_decouvert,e.site_internet,e.abonnement,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,e.id, e.nom ,e.prenom,e.date_naiss,e.diplome_preinscription,e.lieu_naiss,e.sexe,inscription.date_inscription,c.nom as nom_classe  FROM eleve e,inscription,classe c WHERE e.id=inscription.id_eleve and annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.desinscrire=0 and inscription.id_classe=c.id and c.id=? AND (?=0 OR inscription.id_cycle=?) AND (?=0 OR inscription.id_nniveau=?) ORDER BY nom,prenom ', [session('year'), $idCampus, $id, $nniveau, $cycle, $nniveau, $nniveau]);
               $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['titre_liste'] = trans('inscription.eleves_de') . ' ' . ($classe_name).' '.($nniveau ? NNiveau::find($nniveau)->nom : trans('admin.tous'));
                return view('inscriptions.listes_parents', $values);
            }
        }else
        {
            $boursesQueryPart = '';
            if($option==1){
                $tranches = "s.tranche1";
                $tranche = "s.tranche1";
                $motif=trans('admin.tranche1');
                if (Values::SCHOOLFEES_SLICE_NUMBER <= 1) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==2){
                $tranches = "(s.tranche1+s.tranche2)";
                $tranche = "s.tranche2";
                $motif=trans('admin.tranche2');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 2) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==3){
                $tranches = "(s.tranche1+s.tranche2+s.tranche3)";
                $tranche = "s.tranche3";
                $motif=trans('admin.tranche3');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 3) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==4){
                $tranches = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4)";
                $tranche = "s.tranche4";
                $motif=trans('admin.tranche4');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 4) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }else if($option==5){
                $tranches = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";
                $tranche = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";

                $motif=trans('admin.tranche5');

                if (Values::SCHOOLFEES_SLICE_NUMBER <= 5) {
                    $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                    $values['last_installment'] = true;
                }
            }
            else
            {
                $tranches = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";
                $tranche = "(s.tranche1+s.tranche2+s.tranche3+s.tranche4+s.tranche5)";
                $boursesQueryPart = '+ COALESCE(b.montant, 0)';
                $values['last_installment'] = true;
                $motif=trans('admin.schoolfees');
            }



            if($type == static::OP_TYPE_PAID)
            {
                $opComp='>=';
                $values['paid'] = true;

                $t0=trans('admin.paid_eleve').' : '.$motif;
            }
            else
            {
                $opComp='<';
                $t0=trans('admin.unpaid_eleve').' : '.$motif;
            }

            if ($id == 0) {
                $values['titre'] = trans('inscription.listes');

                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.moyenne_generale,e.serie,e.localisation_ecole,e.source_financement,e.canal_decouvert,e.site_internet,e.abonnement,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,inscription.type_scolarite,inscription.date_inscription,e.id, e.nom ,e.prenom,e.date_naiss,e.lieu_naiss,e.diplome_preinscription,e.sexe,c.nom as nom_classe,c.code as code_classe,sol.solde,' . $tranche . ' AS tranche ,(' . $tranches . ' - sol.solde) AS reste, nn.nom as nniveau, b.montant as montant_bourse FROM n_niveaus nn, eleve e LEFT JOIN bourses as b ON (e.id = b.id_eleve AND b.annee_scolaire = ? AND ' . $this->campusVisibilitySql('b.id_campus') . '),inscription,classe c,niveau n,scolarite s, (SELECT SUM(CASE WHEN p.type = 0 AND p.id_inscription=0 THEN p.montant ELSE 0 END) as solde,e.id FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id  WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve and inscription.desinscrire=0 and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.id_classe=c.id AND sol.id=e.id AND c.id_niveau=n.id AND ((inscription.id_scolarite IS NOT NULL AND s.id=inscription.id_scolarite) OR (inscription.id_scolarite IS NULL AND s.id_niveau=n.id AND s.id_cycle=inscription.id_cycle AND inscription.type_scolarite=s.type_scolarite)) AND (sol.solde ' . $boursesQueryPart . ' - ' . $tranches . ')'.$opComp.'0 AND inscription.id_nniveau=nn.id AND (?=0 OR inscription.id_nniveau=?) ORDER BY code_classe,nom,prenom', [session('year'), $idCampus, session('year'), $idCampus, session('year'), $idCampus, $nniveau, $nniveau]);
                $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['titre_liste'] = $t0.' - '.trans('inscription.all_eleves') . ($nniveau ? ' - ' . NNiveau::find($nniveau)->nom : '');
                if($type != null) {
                    $values['type']=$type;
                }
                $values['show_classe'] = true;
                return view('inscriptions.listes_parents', $values);
            } else {
                $values['titre'] = trans('inscription.listes');
                $classe = classe::find($id);
                $classe_name = $classe->getNom();
                $e = DB::select('SELECT e.*,e.telephone_wathsapp_eleve,e.moyenne_generale,e.serie,e.localisation_ecole,e.source_financement,e.canal_decouvert,e.site_internet,e.abonnement,e.telephone_wathsapp_pere,e.telephone_wathsapp_mere,e.telephone_wathsapp_tuteur,e.matricule,e.id, inscription.type_scolarite,inscription.date_inscription, e.nom ,e.prenom,e.date_naiss,e.lieu_naiss,e.diplome_preinscription,e.sexe,c.nom as nom_classe,sol.solde,' . $tranche . ' AS tranche ,(' . $tranches . ' - sol.solde) AS reste, b.montant as montant_bourse  FROM eleve e LEFT JOIN bourses as b ON (e.id = b.id_eleve AND b.annee_scolaire = ? AND ' . $this->campusVisibilitySql('b.id_campus') . '),inscription,classe c,niveau n,scolarite s, (SELECT SUM(CASE WHEN p.type = 0 AND p.id_inscription=0 THEN p.montant ELSE 0 END) as solde,e.id FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' and inscription.desinscrire=0 and inscription.id_classe=c.id AND sol.id=e.id AND c.id_niveau=n.id AND ((inscription.id_scolarite IS NOT NULL AND s.id=inscription.id_scolarite) OR (inscription.id_scolarite IS NULL AND s.id_niveau=n.id AND s.id_cycle=inscription.id_cycle AND inscription.type_scolarite=s.type_scolarite))  AND (sol.solde + ' . $boursesQueryPart . ' - ' . $tranches . ')'.$opComp.'0 AND c.id=? AND (?=0 OR inscription.id_cycle=?) AND (?=0 OR inscription.id_nniveau=?) ORDER BY nom_classe,nom,prenom', [session('year'), $idCampus, session('year'), $idCampus, session('year'), $idCampus, $id, $cycle, $cycle, $nniveau, $nniveau]);
                $p=parents::all();
                $values['parents']=$p;
                $values['eleves'] = $e;
                $values['titre_liste'] = $t0.' - '. $classe_name.' '.($nniveau ? NNiveau::find($nniveau)->nom : trans('admin.tous'));
                if($type != null) {
                    $values['type']=$type;
                }
                return view('inscriptions.listes_parents', $values);
            }


        }

	}


    public function showFrais(Request $request)
    {
        $this->validate($request,[
            'classe'=>'required',
            //'nniveau'=>'required',
            'type'=>'required',
            'frais'=>'required',
        ]);



        $classe=$request->input('classe');
        $type=$request->input('type');
        $nniveau = intval($request->input('nniveau', 0));
        $frais=$request->input('frais');
        $f=Frais::find($frais);
        $idCampus = $this->currentCampusId();
        $scolariteStyle=$request->input('scolarite_style', '');
        $scolariteMatchWhere=$this->scolariteMatchWhere();
        $scolariteProfileWhere=$this->scolariteProfileWhere($scolariteStyle);

        $values['titre'] = trans('inscription.listes');

        $added='';

        if ($type == static::OP_TYPE_PAID) {
            $opComp = '<=';
            $values['paid'] = true;
            $typeString = 'paid_eleve';
        } else {
            $opComp = '>';
            $typeString = 'unpaid_eleve';
        }

        if ($classe==0)
        {
            $added = trans('inscription.all_eleves') . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : '');

            if($frais != 0)

                $e = DB::select('SELECT e.*, nn.nom as nniveau,c.nom as nom_classe, c.code AS code_classe,f.montant as total, sol.solde as tranche,(f.montant-sol.solde) as reste, sol.nb_payement  FROM eleve e,inscription,classe c,frais f, n_niveaus nn, (SELECT SUM(CASE WHEN (p.type=?) THEN p.montant ELSE 0 END) as solde, e.id, COUNT(CASE WHEN (p.type=?) THEN 1 ELSE null END) as nb_payement FROM eleve e LEFT JOIN payement p ON (p.id_eleve=e.id) WHERE p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . '  AND inscription.desinscrire=0 AND sol.id = e.id and inscription.id_classe=c.id AND inscription.id_nniveau = nn.id AND (?=0 OR inscription.id_nniveau=?) AND f.id=? AND (((f.montant-sol.solde) ' . $opComp . ' 0 AND sol.nb_payement > 0) OR (sol.nb_payement <= 0 AND ' . $type . ' = 0))  ORDER BY nom_classe,nom,prenom ', [$frais, $frais, session('year'), $idCampus, session('year'), $idCampus, $nniveau, $nniveau, $frais]);
            else
                $e = DB::select('SELECT e.*, nn.nom as nniveau, c.nom as nom_classe, c.code AS code_classe, s.inscription as total, sol.solde as tranche,(s.inscription-sol.solde) as reste  FROM eleve e,inscription,classe c,niveau n,scolarite s, n_niveaus nn, (SELECT SUM(CASE WHEN (p.id_inscription<>0) THEN p.montant ELSE 0 END) as solde,e.id FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve  AND sol.id=e.id and inscription.id_classe=c.id and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . '  AND inscription.desinscrire=0 AND c.id_niveau=n.id AND ' . $scolariteMatchWhere . $scolariteProfileWhere . ' AND inscription.id_nniveau = nn.id AND (?=0 OR inscription.id_nniveau=?) AND (s.inscription - sol.solde) ' . $opComp . ' 0  ORDER BY nom_classe,nom,prenom ', [session('year'), $idCampus, session('year'), $idCampus, $nniveau, $nniveau]);
        }
        else
        {
            $added = classe::find($classe)->getNom() . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : ' - ' . trans('admin.tous'));

            if($frais!=0)

                $e = DB::select('SELECT e.*, nn.nom as nniveau, c.nom as nom_classe, c.code AS code_classe, f.montant as total, sol.solde as tranche,(f.montant-sol.solde) as reste, sol.nb_payement  FROM eleve e,inscription,classe c,frais f, n_niveaus nn, (SELECT SUM(CASE WHEN (p.type=?) THEN p.montant ELSE 0 END) as solde, e.id, COUNT(CASE WHEN (p.type=?) THEN 1 ELSE null END) as nb_payement FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . '   AND inscription.desinscrire=0 AND sol.id=e.id and inscription.id_classe=c.id AND inscription.id_nniveau = nn.id AND (?=0 OR nn.id=?)  AND f.id=? AND (((f.montant-sol.solde) ' . $opComp . ' 0 AND sol.nb_payement > 0) OR (sol.nb_payement <= 0 AND ' . $type . ' = 0)) AND c.id=? ORDER BY nom_classe,nom,prenom ', [$frais, $frais, session('year'), $idCampus, session('year'), $idCampus, $nniveau, $nniveau,$frais,$classe]);
            else
                $e = DB::select('SELECT e.*, nn.nom as nniveau, c.nom as nom_classe, c.code AS code_classe, s.inscription as total, sol.solde as tranche,(s.inscription-sol.solde) as reste  FROM eleve e,inscription,classe c,niveau n,scolarite s, n_niveaus nn, (SELECT SUM(CASE WHEN (p.id_inscription<>0) THEN p.montant ELSE 0 END) as solde,e.id FROM eleve e LEFT JOIN payement p ON p.id_eleve=e.id WHERE  p.annee_scolaire=? AND ' . $this->campusVisibilitySql('p.id_campus') . ' GROUP BY e.id) AS sol WHERE e.id=inscription.id_eleve and inscription.annee_scolaire=? and ' . $this->campusVisibilitySql('inscription.id_campus') . ' AND sol.id=e.id    AND inscription.desinscrire=0 and inscription.id_classe=c.id AND c.id=? AND c.id_niveau=n.id AND ' . $scolariteMatchWhere . $scolariteProfileWhere . ' AND inscription.id_nniveau = nn.id AND (?=0 OR nn.id=?) AND (s.inscription-sol.solde) ' . $opComp . ' 0  ORDER BY nom_classe,nom,prenom ', [session('year'), $idCampus, session('year'), $idCampus, $classe, $nniveau, $nniveau]);

        }

        if($frais != 0)
            $values['titre_liste'] = trans('admin.' . $typeString).' : '.$f->nom.' - '.$added;
        else
            $values['titre_liste'] = trans('admin.' . $typeString).' : '.trans('admin.inscription').' - '.$added;


        if($type != null) {
            $values['type']=$type;
        }
        $p=parents::all();
        $values['parents']=$p;
        $values['show_classe'] = true;
        $values['eleves'] = $e;
        return view('inscriptions.listes', $values);
    }


    public function liste_acces(Request $request)
    {
        $this->validate($request,[
            'classe'=>'required'
        ]);


        $classe = $request->input('classe');
        $nniveau = intval($request->input('nniveau', 0));

        $values['titre'] = trans('inscription.listes');

        $added='';
        $today=date('Y-m-d');
        $idCampus = $this->currentCampusId();

        if ($classe==0)
        {
            $added = trans('inscription.all_eleves');
            $query = eleve::join('acces_eleves', 'eleve.id', '=', 'acces_eleves.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('acces_eleves.annee_scolaire', '=', session('year'))
               // ->where('acces_eleves.decision', '=', 2)
                ->where('acces_eleves.date_fin','>', $today)

                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0);
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
                $added .= ' - ' . NNiveau::find($nniveau)->nom;
            }
            $e = $query
                ->select(['eleve.*','acces_eleves.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        } else
        {
            $added = classe::find($classe)->getNom() . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : ' - ' . trans('admin.tous'));
            $query = eleve::join('acces_eleves', 'eleve.id', '=', 'acces_eleves.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('acces_eleves.annee_scolaire', '=', session('year'))
                ->where('desinscrire','=',0)
               // ->where('acces_eleves.decision', '=', 2)
               ->where('acces_eleves.date_fin','>', $today)

                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('classe.id', '=', $classe);
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
            }
            $e = $query
                ->select(['eleve.*','acces_eleves.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }


        $values['titre_liste'] = trans('admin.liste_acces') . ' : '. $added;

        $p=parents::all();
        $values['parents']=$p;
        $values['show_classe'] = true;
        $values['show_montant'] = true;
        $values['eleves'] = $e;
        return view('inscriptions.listes_acces', $values);
    }


    public function liste_acces_autirise(Request $request)
    {
        $this->validate($request,[
            'classe'=>'required'
        ]);


        $classe = $request->input('classe');
        $nniveau = intval($request->input('nniveau', 0));

        $values['titre'] = trans('inscription.listes');

        $added='';
        $today=date('Y-m-d');
        $idCampus = $this->currentCampusId();

        if ($classe==0)
        {
            $added = trans('inscription.all_eleves');
            $query = eleve::join('acces_eleves', 'eleve.id', '=', 'acces_eleves.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('acces_eleves.annee_scolaire', '=', session('year'))
               // ->where('acces_eleves.decision', '=', 1)
               ->where('acces_eleves.date_fin','>', $today)

                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0);
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
                $added .= ' - ' . NNiveau::find($nniveau)->nom;
            }
            $e = $query
                ->select(['eleve.*','acces_eleves.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        } else
        {
            $added = classe::find($classe)->getNom() . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : ' - ' . trans('admin.tous'));
            
            $query = eleve::join('acces_eleves', 'eleve.id', '=', 'acces_eleves.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('acces_eleves.annee_scolaire', '=', session('year'))
               // ->where('acces_eleves.decision', '=', 1)
               ->where('acces_eleves.date_fin','>', $today)
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0)
                ->where('classe.id', '=', $classe);
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
            }
            $e = $query
                ->select(['eleve.*','acces_eleves.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }


        $values['titre_liste'] = trans('admin.liste_accesok') . ' : '. $added;

        $p=parents::all();
        $values['parents']=$p;
        $values['show_classe'] = true;
        $values['show_montant'] = true;
        $values['eleves'] = $e;
        return view('inscriptions.listes_accesok', $values);
    }


    public function demissionaire(Request $request)
    {
        $this->validate($request,[
            'classe'=>'required'
        ]);


        $classe = $request->input('classe');
        $nniveau = intval($request->input('nniveau', 0));

        if($classe != 0) {
            if($nniveau == null || strlen($nniveau) <= 0) {
                return redirect()->to(\URL::previous())->withErrors(['no_nniveau' => trans('admin.must_specify', ['value' => trans('admin.nniveau')])]);
            }
        }

        $values['titre'] = trans('inscription.listes');

        $added='';
        $idCampus = $this->currentCampusId();

        if ($classe==0)
        {
            $added = trans('inscription.all_eleves');
            $e = eleve::join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('acces_eleves.decision', '=', 1)
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0)
                ->select(['eleve.*','acces_eleves.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        } else
        {
            $added = classe::find($classe)->getNom() . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : ' - ' . trans('admin.tous'));
            $query = eleve::join('acces_eleves', 'eleve.id', '=', 'acces_eleves.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('acces_eleves.annee_scolaire', '=', session('year'))
                ->where('acces_eleves.decision', '=', 1)
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0)
                ->where('classe.id', '=', $classe);
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
            }
            $e = $query
                ->select(['eleve.*','acces_eleves.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }


        $values['titre_liste'] = trans('admin.liste_acces') . ' : '. $added;

        $p=parents::all();
        $values['parents']=$p;
        $values['show_classe'] = true;
        $values['show_montant'] = true;
        $values['eleves'] = $e;
        return view('inscriptions.listes_accesok', $values);
    }


    public function showDesinscrits(Request $request)
    {
        $this->validate($request, [
            'classe'=>'required'
        ]);

        $classe = $request->input('classe');
        $nniveau = intval($request->input('nniveau', 0));
        $idCampus = $this->currentCampusId();

        if($classe != 0 && $nniveau === null) {
            return redirect()->to(\URL::previous())->withErrors(['no_nniveau' => trans('admin.must_specify', ['value' => trans('admin.nniveau')])]);
        }

        $query = eleve::join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
            ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
            ->join('classe', 'classe.id', '=', 'inscription.id_classe')
            ->where('inscription.annee_scolaire', '=', session('year'))
            ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
            ->where('inscription.desinscrire', '=', 1);

        if($classe != 0) {
            $query->where('classe.id', '=', $classe);
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
            }
            $added = classe::find($classe)->getNom() . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : ' - ' . trans('admin.tous'));
        } else {
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
            }
            $added = trans('inscription.all_eleves') . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : '');
        }

        $e = $query->select(['eleve.*', 'classe.code as code_classe', 'classe.nom as nom_classe', 'n_niveaus.nom as nniveau', 'inscription.date_inscription'])
            ->orderBy('code_classe')
            ->orderBy('eleve.nom')
            ->orderBy('eleve.prenom')
            ->get();

        $values['titre'] = trans('inscription.listes');
        $values['titre_liste'] = 'Liste des etudiants desinscrits : ' . $added;
        $values['eleves'] = $e;
        $values['show_classe'] = true;
        $values['show_reinscrire'] = true;
        $values['parents'] = parents::all();

        return view('inscriptions.listes', $values);
    }



    public function showBourses(Request $request)
    {
        $this->validate($request,[
            'classe'=>'required'
        ]);


        $classe = $request->input('classe');
        $nniveau = $request->input('nniveau');

        if($classe != 0) {
            if($nniveau == null || strlen($nniveau) <= 0) {
                return redirect()->to(\URL::previous())->withErrors(['no_nniveau' => trans('admin.must_specify', ['value' => trans('admin.nniveau')])]);
            }
        }

        $values['titre'] = trans('inscription.listes');

        $added='';
        $idCampus = $this->currentCampusId();

        if ($classe==0)
        {
            $added = trans('inscription.all_eleves');
            $query = eleve::join('bourses', 'eleve.id', '=', 'bourses.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('bourses.annee_scolaire', '=', session('year'))
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0);
            if (Schema::hasColumn('bourses', 'id_campus')) {
                $query->whereRaw($this->campusVisibilitySql('bourses.id_campus'), array($idCampus));
            }
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
                $added .= ' - ' . NNiveau::find($nniveau)->nom;
            }
            $e = $query
                ->select(['eleve.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau', 'bourses.montant'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        } else
        {
            $added = classe::find($classe)->getNom() . ($nniveau != 0 ? ' - ' . NNiveau::find($nniveau)->nom : ' - ' . trans('admin.tous'));
            $query = eleve::join('bourses', 'eleve.id', '=', 'bourses.id_eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->join('n_niveaus', 'n_niveaus.id', '=', 'inscription.id_nniveau')
                ->join('classe', 'classe.id', '=', 'inscription.id_classe')
                ->where('bourses.annee_scolaire', '=', session('year'))
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('desinscrire','=',0)
                ->where('classe.id', '=', $classe);
            if (Schema::hasColumn('bourses', 'id_campus')) {
                $query->whereRaw($this->campusVisibilitySql('bourses.id_campus'), array($idCampus));
            }
            if($nniveau != 0) {
                $query->where('n_niveaus.id', '=', $nniveau);
            }
            $e = $query
                ->select(['eleve.*', 'classe.code as code_classe', 'n_niveaus.nom as nniveau', 'bourses.montant'])
                ->orderBy('code_classe')
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();
        }

        $values['titre_liste'] = trans('admin.boursiers') . ' : '. $added;

        $p=parents::all();
        $values['parents']=$p;
        $values['show_classe'] = true;
        $values['show_montant'] = true;
        $values['eleves'] = $e;
        return view('inscriptions.listes', $values);
    }

    public function fiche_scolarite(Request $request){
	    $this->validate($request,[
	        'id'=> 'required|numeric',
	        'nniveau'=> 'required|numeric',
        ]);
        $id_nniveau = $request->input('nniveau');
        $id_classe = $request->input('id');
        $nniveau = NNiveau::find($id_nniveau);
        $classe = classe::find($id_classe);
        $id_cycle = $nniveau->id_cycle;
        $idCampus = $this->currentCampusId();
        $frais_report = Frais::where('show_on_report', '=', true)
            ->orderBy('ordre')
            ->get();
        $frais_array = $frais_report->fetch('id')->toArray();

        $scolarite_ancien = scolarite::where('id_cycle', '=', $id_cycle)
                ->where('id_niveau', '=', $classe->id_niveau)
                ->where('type_scolarite', '=', 0)
                ->first();
        $scolarite_nouveau = scolarite::where('id_cycle', '=', $id_cycle)
            ->where('id_niveau', '=', $classe->id_niveau)
            ->where('type_scolarite', '=', 1)
            ->first();


            $eleves_inscrits = eleve::join('inscription', 'inscription.id_eleve', '=', 'eleve.id')
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('inscription.id_cycle', '=', $id_cycle)
                ->where('inscription.id_classe', '=', $id_classe)
                ->where('inscription.id_nniveau', '=', $id_nniveau)
                ->select(['eleve.id', 'matricule', 'nom', 'prenom','inscription.type_scolarite', 'inscription.id_scolarite', 'inscription.id as id_inscription'])
                ->orderBy('nom')
                ->orderBy('prenom')
                ->get();

        foreach ($eleves_inscrits as $e) {
            $inscription = payement::where('id_eleve', '=', $e->id)
                ->where('id_inscription', '=', $e->id_inscription)
                ->where('desinscrire','=',0)
                ->where('annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                ->first();

            $frais = payement::where('id_eleve', '=', $e->id)
                ->where('annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                ->whereIn('type', $frais_array)
                ->get();
            $payements = payement::where('id_eleve', '=', $e->id)
                ->where('annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                ->where('type', '=', 0)
                ->orderBy('date_payement')
                ->get();
            $payements_total = payement::where('id_eleve', '=', $e->id)
                ->where('annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                ->where('desinscrire', '=', 0)
                ->get();
            $inscriptionScolarite = new inscription();
            $inscriptionScolarite->id_eleve = $e->id;
            $inscriptionScolarite->id_classe = $id_classe;
            $inscriptionScolarite->id_cycle = $id_cycle;
            $inscriptionScolarite->id_nniveau = $id_nniveau;
            $inscriptionScolarite->annee_scolaire = session('year');
            $inscriptionScolarite->type_scolarite = $e->type_scolarite;
            if (isset($e->id_scolarite)) {
                $inscriptionScolarite->id_scolarite = $e->id_scolarite;
            }
            $scolarite_eleve=scolarite::forInscription($inscriptionScolarite, $classe);

            $bourseQuery = bourse::where('id_eleve', '=', $e->id)
                ->where('annee_scolaire', '=', session('year'));
            if (Schema::hasColumn('bourses', 'id_campus')) {
                $bourseQuery->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus));
            }
            $bourse = $bourseQuery->first();
            if ($bourse) {
                $e->montant_bourse = $bourse->montant;
            }

            $e->montant_inscription = $inscription->montant;
            $e->payements_frais = $frais;
            $e->payements = $payements;
            $e->total_paye = $payements_total->sum('montant') + ($e->montant_bourse ? $e->montant_bourse : 0);
            $e->scolarite=$scolarite_eleve;

        }

        return view('inscriptions.fiche_scolarite')
            ->with('eleves', $eleves_inscrits)
            ->with('titre_liste', trans('admin.fiche_scolarite_eleve') . ' / ' . trans('admin.year') . ' ' . session('year')  . ' / ' . trans('admin.classe') . ' ' . $classe->code . ' - ' . $nniveau->nom)
            ->with('frais', $frais_report)
            ->with('scolarite_ancien', $scolarite_ancien)
            ->with('scolarite_nouveau', $scolarite_nouveau)
            ->with('titre', trans('admin.fiche_scolarite_eleve'))
            ->with('page_title', trans('admin.fiche_scolarite_eleve'));
    }

    private function scolariteMatchWhere()
    {
        return "((inscription.id_scolarite IS NOT NULL AND s.id=inscription.id_scolarite) OR (inscription.id_scolarite IS NULL AND s.id_niveau=n.id AND s.id_cycle=inscription.id_cycle AND inscription.type_scolarite=s.type_scolarite AND (s.id_classe IS NULL OR s.id_classe=0 OR s.id_classe=inscription.id_classe) AND (s.id_nniveau IS NULL OR s.id_nniveau=0 OR s.id_nniveau=inscription.id_nniveau) AND (s.annee_scolaire IS NULL OR s.annee_scolaire='' OR s.annee_scolaire=inscription.annee_scolaire)))";
    }

    private function scolariteProfileWhere($style)
    {
        if(strcmp((string) $style, '0') === 0) {
            return " AND (s.annee_scolaire IS NULL OR s.annee_scolaire='') AND (s.id_classe IS NULL OR s.id_classe=0) AND (s.id_nniveau IS NULL OR s.id_nniveau=0)";
        }

        if(strcmp((string) $style, '1') === 0) {
            return " AND ((s.annee_scolaire IS NOT NULL AND s.annee_scolaire<>'') OR (s.id_classe IS NOT NULL AND s.id_classe<>0) OR (s.id_nniveau IS NOT NULL AND s.id_nniveau<>0))";
        }

        return "";
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
