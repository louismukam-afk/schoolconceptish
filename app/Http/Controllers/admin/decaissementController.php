<?php namespace gesecole\Http\Controllers\admin;

use gesecole\Caisse;
use gesecole\decaissement;
use gesecole\Http\Controllers\Controller;

use gesecole\LigneBudgetaire;
use gesecole\MouvementFinancier;
use gesecole\personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class decaissementController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.comptabilite')]);
        $this->renewSchoolYear();
    }

	public function index(Request $req)
	{
        $values= array('titre'=>trans('admin.sortie_caisse'));

        if(session('success')){

            $values['success']=session('success');
            Session::forget('success');
        }
        $pattern=$req->input('search');
            $d=decaissement::with(['personnel', 'ligne_budgetaire', 'categorie_budgetaire', 'caisse'])
                ->leftJoin('personnel', 'decaissement.id_personnel', '=', 'personnel.id')
                ->whereRaw(' (lower(personnel.nom) LIKE ? OR lower(decaissement.nom_beneficiaire) LIKE ?) ', ['%' . strtolower($pattern) . '%', '%' . strtolower($pattern) . '%'])
                ->where('decaissement.annee_scolaire','=',\session('year'))
                ->whereRaw($this->campusVisibilitySql('decaissement.id_campus'), array($this->currentCampusId()))
                ->select(['decaissement.*', 'personnel.nom as nom_personnel'])
                ->orderBy('decaissement.created_at','desc')
                ->paginate(30);

        $ligneQuery=LigneBudgetaire::with('donneesLigneBudgetaire.categorieBudgetaire')->orderBy('date_fin', 'desc');
        if (Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            $ligneQuery->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()));
        }
        $li=$ligneQuery->get();
        $p=personnel::whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))->orderBy('nom')->get();
        $values['personnel']=$p;
        $values['ligne']=$li;
        $values['caisses']=$this->availableUserOutputCaisses();
        $values['decaissement']=$d;

        return view('admin.search_decaissement',$values);
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
		$this->validate($request,[
            'nom_beneficiaire'=>'',
            'caisse'=>'required|numeric',
            'montant'=>'required|numeric|min:1',
            'motif'=>'required',
            'ligne_budgetaire'=>'required',
            'categorie_budgetaire'=>'required'
        ]);

        if($request->input('personnel') && !is_numeric($request->input('personnel'))) {
            return redirect()->to(URL::previous())->withErrors(['personnel' => 'Le personnel choisi est invalide.']);
        }
        if(!$request->input('personnel') && trim($request->input('nom_beneficiaire')) == '') {
            return redirect()->to(URL::previous())->withErrors(['personnel' => 'Choisissez un personnel ou saisissez le nom de la personne qui fait la depense.']);
        }

        $caisse=$this->findUserOutputCaisse($request->input('caisse'));
        if(!$caisse) {
            return redirect()->to(URL::previous())->withErrors(['caisse' => 'La caisse selectionnee n est pas autorisee pour cet utilisateur.']);
        }
        $montant=(float) $request->input('montant');
        if((float) $caisse->solde < $montant) {
            return redirect()->to(URL::previous())->withErrors(['caisse' => 'Solde insuffisant dans la caisse selectionnee. Solde disponible: '.number_format($caisse->solde, 0, '.', ' ').' '.trans('inscription.devise')]);
        }
		
        $current_user = \Auth::user();

        DB::transaction(function() use ($request, $caisse, $montant, $current_user, &$d) {
            $d=new decaissement();
            $d->id_personnel=$request->input('personnel') ? $request->input('personnel') : 0;
            $d->nom_beneficiaire=trim($request->input('nom_beneficiaire'));
            $d->id_caisse=$caisse->id;
            $d->montant=$montant;
            $d->motif=$request->input('motif');
            $d->id_ligne_budgetaire=$request->input('ligne_budgetaire');
            $d->id_categorie_budgetaire=$request->input('categorie_budgetaire');
            $d->date=date('Y-m-d');
            $d->annee_scolaire=\session('year');
            $d->id_campus=$this->currentCampusId();
            $d->id_creator = $current_user->id;
            $d->id_last_editor = $current_user->id;
            $d->save();

            $caisse->solde=(float) $caisse->solde - $montant;
            $caisse->id_last_editor=$current_user->id;
            $caisse->save();

            $this->saveWithdrawalMovement($d, $caisse, $current_user);
        });

        session(['success'=>true]);
        return redirect()->action('admin\decaissementController@index');
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
	 * Update an existing resource in storage.
	 *
	 * @return Response
	 */
	public function update(Request $request)
	{
		$this->validate($request,[
            'id' => 'required|numeric',
            'nom_beneficiaire'=>'',
            'caisse'=>'required|numeric',
            'motif'=>'required',
            'ligne_budgetaire'=>'required',
            'categorie_budgetaire'=>'required'
        ]);

        if($request->input('personnel') && !is_numeric($request->input('personnel'))) {
            return redirect()->to(URL::previous())->withErrors(['personnel' => 'Le personnel choisi est invalide.']);
        }
        if(!$request->input('personnel') && trim($request->input('nom_beneficiaire')) == '') {
            return redirect()->to(URL::previous())->withErrors(['personnel' => 'Choisissez un personnel ou saisissez le nom de la personne qui fait la depense.']);
        }
        $newCaisse=$this->findUserOutputCaisse($request->input('caisse'));
        if(!$newCaisse) {
            return redirect()->to(URL::previous())->withErrors(['caisse' => 'La caisse selectionnee n est pas autorisee pour cet utilisateur.']);
        }
		
		$d=decaissement::where('decaissement.id', '=', $request->input('id'))
            ->where('decaissement.id_campus', '=', $this->currentCampusId())
            ->first();

        if(!$d) {
            return redirect()->action('admin\decaissementController@index');
        }

        $current_user = \Auth::user();
        $oldCaisse = $d->id_caisse ? Caisse::find($d->id_caisse) : null;
        $mustMoveCaisse = !$oldCaisse || $oldCaisse->id != $newCaisse->id;

        if($mustMoveCaisse && (float) $newCaisse->solde < (float) $d->montant) {
            return redirect()->to(URL::previous())->withErrors(['caisse' => 'Solde insuffisant dans la nouvelle caisse. Solde disponible: '.number_format($newCaisse->solde, 0, '.', ' ').' '.trans('inscription.devise')]);
        }

        DB::transaction(function() use ($request, $newCaisse, $oldCaisse, $mustMoveCaisse, $current_user, $d) {
            if($mustMoveCaisse) {
                if($oldCaisse) {
                    $oldCaisse->solde=(float) $oldCaisse->solde + (float) $d->montant;
                    $oldCaisse->id_last_editor=$current_user->id;
                    $oldCaisse->save();
                }

                $newCaisse->solde=(float) $newCaisse->solde - (float) $d->montant;
                $newCaisse->id_last_editor=$current_user->id;
                $newCaisse->save();
            }

            $d->id_personnel=$request->input('personnel') ? $request->input('personnel') : 0;
            $d->nom_beneficiaire=trim($request->input('nom_beneficiaire'));
            $d->id_caisse=$newCaisse->id;
            $d->motif=$request->input('motif');
            $d->id_ligne_budgetaire=$request->input('ligne_budgetaire');
            $d->id_categorie_budgetaire=$request->input('categorie_budgetaire');
            $d->id_last_editor = $current_user->id;
            $d->save();

            $this->updateWithdrawalMovement($d, $newCaisse, $current_user);
        });

        session(['success'=>true]);
        return redirect()->action('admin\decaissementController@index');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update_amount(Request $request)
	{
		$this->validate($request, [
		    'id' => 'required|numeric',
		    'montant' => 'required|numeric|min:1',
        ]);
		$id = $request->input('id');
		$montant = $request->input('montant');
		$d=decaissement::where('decaissement.id', '=', $id)
            ->where('decaissement.id_campus', '=', $this->currentCampusId())
            ->first();
		if($d) {
            $current_user = \Auth::user();
            $caisse = $this->findUserOutputCaisse($d->id_caisse);
            if(!$caisse) {
                return redirect()->to(URL::previous())->withErrors(['caisse' => 'La caisse de cette sortie n est pas autorisee pour cet utilisateur.']);
            }
            $difference = (float) $montant - (float) $d->montant;
            if($difference > 0 && (float) $caisse->solde < $difference) {
                return redirect()->to(URL::previous())->withErrors(['caisse' => 'Solde insuffisant dans la caisse selectionnee. Solde disponible: '.number_format($caisse->solde, 0, '.', ' ').' '.trans('inscription.devise')]);
            }

            DB::transaction(function() use ($d, $montant, $difference, $caisse, $current_user) {
                $caisse->solde=(float) $caisse->solde - $difference;
                $caisse->id_last_editor=$current_user->id;
                $caisse->save();

                $d->montant = $montant;
                $d->id_last_editor = $current_user->id;
                $d->save();

                $this->updateWithdrawalMovement($d, $caisse, $current_user);
            });
        }
        session(['success'=>true]);
		return redirect()->to(URL::previous());
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

    private function availableUserOutputCaisses()
    {
        $user = \Auth::user();
        $query = Caisse::where('type_caisse', '=', 'sortie')->orderBy('nom');

        if(Schema::hasTable('caisse_user') && $user) {
            $ids = DB::table('caisse_user')->where('id_user', '=', $user->id)->lists('id_caisse');
            if(count($ids) <= 0) {
                return Caisse::whereRaw('1=0')->get();
            }
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }

    private function findUserOutputCaisse($id)
    {
        if(!$id) {
            return null;
        }
        $caisses = $this->availableUserOutputCaisses();
        foreach($caisses as $caisse) {
            if((int) $caisse->id === (int) $id) {
                return $caisse;
            }
        }
        return null;
    }

    private function saveWithdrawalMovement(decaissement $d, Caisse $caisse, $currentUser)
    {
        $m = new MouvementFinancier();
        $m->type_operation = 'sortie';
        $m->source = 'caisse';
        $m->id_caisse = $caisse->id;
        $m->id_campus = $this->currentCampusId();
        $m->montant = $d->montant;
        $m->date_operation = $d->date;
        $m->motif = $d->motif;
        $m->reference = 'decaissement-'.$d->id;
        $m->id_user = $currentUser ? $currentUser->id : null;
        $m->id_creator = $currentUser ? $currentUser->id : null;
        $m->id_last_editor = $currentUser ? $currentUser->id : null;
        $m->save();
    }

    private function updateWithdrawalMovement(decaissement $d, Caisse $caisse, $currentUser)
    {
        $m = MouvementFinancier::where('reference', '=', 'decaissement-'.$d->id)->first();
        if(!$m) {
            $this->saveWithdrawalMovement($d, $caisse, $currentUser);
            return;
        }

        $m->type_operation = 'sortie';
        $m->source = 'caisse';
        $m->id_caisse = $caisse->id;
        $m->id_campus = $this->currentCampusId();
        $m->montant = $d->montant;
        $m->date_operation = $d->date;
        $m->motif = $d->motif;
        $m->id_user = $currentUser ? $currentUser->id : null;
        $m->id_last_editor = $currentUser ? $currentUser->id : null;
        $m->save();
    }

}

