<?php namespace gesecole\Http\Controllers\inscriptions;

use gesecole\bourse;
use gesecole\Banque;
use gesecole\Campus;
use gesecole\Caisse;
use gesecole\Frais;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\NNiveau;
use gesecole\payement;
use gesecole\parametres;
use gesecole\cycle;
use gesecole\niveau;
use gesecole\classe;
use gesecole\MouvementFinancier;
use gesecole\scolarite;
use gesecole\User;
use gesecole\Vars;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use gesecole\eleve;
use gesecole\inscription;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

class payementController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
     *
	 */
    public function __construct()
    {

        session(['big_title'=>trans('menu.gestion_inscriptions')]);
        $this->renewSchoolYear();
    }

	public function index()
	{
        $values= array('titre'=>trans('inscription.get_payement'));
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }

        return view('inscriptions/search_deposer',$values);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function search(Request $req)
    {
        $pattern=$req->input('search');
        $p=payement::whereNumero($pattern)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if($p==null) {
            $e = DB::table('eleve')
                ->join('inscription', 'eleve.id', '=', 'inscription.id_eleve')
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($this->currentCampusId()))
                ->whereRaw('(lower(eleve.nom) LIKE ? OR lower(eleve.prenom) LIKE ? OR lower(eleve.matricule) LIKE ?)', ['%'.strtolower($pattern).'%', '%'.strtolower($pattern).'%', '%'.strtolower($pattern).'%'])
                ->select('eleve.*')
                ->orderBy('nom')->orderBy('prenom')
                ->paginate(30);

            $values = array('titre' => trans('inscription.get_payement'));
            $values['eleves'] = $e;
            session(['search_result' => true]);
        }
        else{
            $values = array('titre' => trans('inscription.info_payement'));
            $values['payement'] = $p;
            $e=eleve::find($p->id_eleve);
            $values['eleve'] = $e;
            session(['info_payement' => true]);

        }
        return view('inscriptions/search_deposer',$values);
    }

	public function create($id_eleve)
	{
        $e=eleve::find($id_eleve);
        $values= array('titre'=>trans('inscription.get_payement'));
        $values['e']=$e;
        $values['campus']=Campus::where('id', '=', $this->currentCampusId())->get();
        $values['campus_actif']=$this->currentCampus();
        $values['caisses']=Caisse::where('type_caisse','=','entree')->orderBy('nom')->get();
        $values['banques']=Banque::orderBy('nom')->get();
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        if(session('custom_error')){
            $values['custom_error']=session('custom_error');
            Session::forget('custom_error');

        }

        return view('inscriptions/add_payement',$values);
	}

	public function create_frais($id_eleve)
	{
        $e=eleve::find($id_eleve);
        $frais = Frais::orderBy('nom')->get();
        $values= array('titre'=>trans('admin.paiement_frais'));
        $values['e']=$e;
        $values['frais']=$frais;
        $values['campus']=Campus::where('id', '=', $this->currentCampusId())->get();
        $values['campus_actif']=$this->currentCampus();
        $values['caisses']=Caisse::where('type_caisse','=','entree')->orderBy('nom')->get();
        $values['banques']=Banque::orderBy('nom')->get();
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        if(session('custom_error')){
            $values['custom_error']=session('custom_error');
            Session::forget('custom_error');

        }

        return view('inscriptions/add_payement_frais',$values);
	}

	public function index_liste($id_eleve){

        $values= array('titre'=>trans('inscription.liste_paiement'));
        $p=payement::whereIdEleve($id_eleve)->where('annee_scolaire','=',session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('date_payement')->get();
        $values['payements']=$p;
        $e=eleve::find($id_eleve);
        $name=$e->nom.' '.$e->prenom;
        $values['name']=$name;
        $values['id_eleve']=$id_eleve;
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }

        return view('inscriptions/liste_paiement',$values);
    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $req)
	{
        $this->validate($req,[
            'id_eleve'=>'required|numeric',
            'montant'=>'required|numeric',
            'motif'=>'required',
            'operation_source'=>'required',
        ]) ;
        $id_eleve=$req->input('id_eleve');
        $montant=$req->input('montant');
        $motif=$req->input('motif');
        $type=$req->input('type');
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

        if( $type && $type <> 0 && $type <> Vars::INSCRIPTION_TYPE){
            $f = Frais::find($type);
            $motif = $f->nom;
        }


        $annee_scolaire=session('year');
        $today=date('Y-m-d');

        $test=inscription::whereRaw("id_eleve=? and annee_scolaire=? and id_campus=?",[$id_eleve,$annee_scolaire,$id_campus])->get()->first();

        if($test==null){
            session(['custom_error'=>trans('inscription.pas_inscrit')]);
            return redirect()->action('inscriptions\payementController@create',['id_eleve'=>$id_eleve]);
        }
        $p=new payement();
        $p->annee_scolaire=$annee_scolaire;
        $p->motif=$motif;
        $p->lettre=$lettre;
        $p->mode_paye=$mode_paye;
        $p->ref_versement=$ref;
        $p->id_campus=$id_campus;
        $p->operation_source=$operation_source;
        $p->id_caisse=$id_caisse;
        $p->id_banque=$id_banque;
        $p->numero_versement=$numero_versement;
        $p->reference_operation=$reference_operation;
        $p->demande_operation=$demande_operation;
        if($type) {
            $p->type = $type;
        }
        $p->montant=$montant;
        $p->id_eleve=$id_eleve;
        $p->date_payement=$today;

        $current_user = \Auth::user();
        $p->edition_details = 'montant = ' . $montant;
        $p->id_creator = $current_user->id;
        $p->id_last_editor = $current_user->id;

        $p->save();
        $numero=sprintf('%08d',$p->id);
        $p->numero=$numero;
        $p->save();

        $this->recordFinancialEntry($p, $operation_source, $id_caisse, $id_banque, $montant, $numero_versement, $reference_operation, $demande_operation);

        session(['success'=>$p->id]);

        return redirect()->action('inscriptions\payementController@index') ;
	}

    private function recordFinancialEntry($payement, $source, $idCaisse, $idBanque, $montant, $numeroVersement, $reference, $demande)
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
        $m->id_inscription = $payement->id_inscription;
        $m->id_campus = $payement->id_campus;
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


     public function print_receipe($id){
        $p=payement::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if(!$p) { abort(404); }
        $isFrais = $p->type <> 0;

        if($isFrais) {
            $f=Frais::find($p->type);
            $values['frais']=$f;
        }

        $i=inscription::whereAnneeScolaire(session('year'))->where('id_eleve',$p->id_eleve)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        $cycle=cycle::find($i->id_cycle);
                $cy=$cycle->nom;
        $c=classe::find($i->id_classe);
        $n=niveau::find($c->id_niveau);
        $nn=NNiveau::find($i->id_nniveau);
        //$sc=$i->classe->niveau->scolarite->where('id_cycle','=',$cycle->id)->first();
        $sc=scolarite::forInscription($i, $c);

        if($isFrais){
            $fp=payement::whereIdEleve($p->id_eleve)->where('annee_scolaire','=',session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                ->where('type','=',$p->type)->get();
            if($fp)
                $solde=$fp->sum('montant');
            else
                $solde=0;

            $total=$f->montant;

        }
        else{
            if($sc == null) {
                $total = 0;
            }
            else {
                $bourseQuery = bourse::where('id_eleve', '=', $p->id_eleve)
                    ->where('annee_scolaire', '=', session('year'));
                if (Schema::hasColumn('bourses', 'id_campus')) {
                    $bourseQuery->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()));
                }
                $bourse = $bourseQuery->first();
                if ($bourse) {
                    $values['montant_bourse'] = $bourse->montant;
                }
                $total = $sc->total;
            }
            $ps=payement::whereIdEleve($p->id_eleve)->where('annee_scolaire','=',session('year'))->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
                //TO CHANGE

                ->where('id_inscription', '=', 0)
                ->where('type','=',0)
                ->get();

            $solde=0;
            $solde=$ps->sum('montant');

        }
        $current_user=User::where('id','=',$p->id_creator)
                       ->first();
       $u=$current_user->fullname;


        $pa=parametres::all();
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
        $values['total']=$total;
        $values['solde']=$solde;

        return view('inscriptions.imprimer_recu_p',$values);

    }

   /* public function print_receipe($id){
        $p=payement::where('id', '=', $id)->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->first();
        if(!$p) { abort(404); }

        if($p->type <> 0){
            $f=Frais::find($p->type);
            $values['frais']=$f;

        }

        $i=inscription::whereAnneeScolaire(session('year'))->where('id_eleve',$p->id_eleve,false)->first();
        $cycle=cycle::find($i->id_cycle);
                $cy=$cycle->nom;
        $c=classe::find($i->id_classe);
        $n=niveau::find($c->id_niveau);
        $nn=NNiveau::find($i->id_nniveau);
        //$sc=$i->classe->niveau->scolarite->where('id_cycle','=',$cycle->id)->first();
        $sc=scolarite::where('id_niveau','=',$n->id)->where('id_cycle','=',$cycle->id)->first();

        if($p->type <> 0){
            $fp=payement::whereIdEleve($p->id_eleve)->where('annee_scolaire','=',session('year'))
                ->where('type','=',$p->type)->get();
            if($fp)
                $solde=$fp->sum('montant');
            else
                $solde=0;

            $total=$f->montant;

        }
        else{
            if($sc == null)
                $total=0;
            else {
                $total=$sc->tranche1+$sc->tranche2+$sc->tranche3+$sc->tranche4+$sc->tranche5;

            }
            $ps=payement::whereIdEleve($p->id_eleve)->where('annee_scolaire','=',session('year'))
                //TO CHANGE

                ->where('id_inscription', '=', 0)
                ->where('type','=',0)
                ->get();

            $solde=0;
            $solde=$ps->sum('montant');

        }


        $pa=parametres::all();
        $pa=$pa->where('nom','nom_etablissement',false)->first();
        $nom_e=$pa->valeur;
        $values['cycle']=$cy;
        $values['classe']=$c;
        $values['niveau']=$n;
        $values['nniveau']=$nn;
        $values['payement']=$p;
        $values['inscription']=$i;
        $values['nom_etablissement']=$nom_e;
        $values['total']=$total;
        $values['solde']=$solde;

        return view('inscriptions/imprimer_recu_p',$values);

    }*/

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
	public function update(Request $request)
	{
         $this->validate($request,[
            'id'=>'required|numeric',
            'montant'=>'required|numeric',
        ]) ;
		$id=$request->input('id');
        $montant=$request->input('montant');
        $lettre = $request->input('lettre');

        $p=payement::find($id);
        $p->montant=$montant;
        $p->lettre = $lettre;


        $current_user = \Auth::user();
        $p->id_last_editor = $current_user->id;

        $p->save();


        return redirect()->to(\URL::previous());

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
