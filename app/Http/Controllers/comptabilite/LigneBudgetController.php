<?php

namespace gesecole\Http\Controllers\comptabilite;

use gesecole\CategorieBudgetaire;
use gesecole\decaissement;
use gesecole\DonneeLigneBudgetaire;
use gesecole\LigneBudgetaire;
use gesecole\personnel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use gesecole\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use stdClass;

class LigneBudgetController extends Controller
{
    public function __construct()
    {
        session(['big_title'=>trans('admin.comptabilite')]);
        $this->renewSchoolYear();
    }

    public function index()
    {
        $budgetLineQuery=LigneBudgetaire::orderBy('date_fin', 'desc');
        if (Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            $budgetLineQuery->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()));
        }
        $budget_line=$budgetLineQuery->paginate(30);
        $values= array('titre'=>trans('admin.ligneB'),'ligne_budget'=>$budget_line);
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        return view('comptabilite.ligne_budgetaire',$values);

    }

    public function getLineData($id) {
        $ligne = $this->findCampusBudgetLine($id, 'donneesLigneBudgetaire.categorieBudgetaire', true);
        if (!$ligne) {
            return response()->json(array());
        }
        $dataArray = [];
        foreach ($ligne->donneesLigneBudgetaire as $entry) {
            $c = new stdClass();
            $c->categoryName = $entry->categorieBudgetaire->libelle;
            $c->categoryId = $entry->categorieBudgetaire->id;
            $c->amount = $entry->montant;

            array_push($dataArray, $c);
        }

        return response()->json($dataArray);
    }

    public function report($id) {
        $line = $this->findCampusBudgetLine($id, null, true);
        if (!$line) {
            abort(404);
        }
        $campusId = $this->currentCampusId();
        $lineData = DB::table('donnee_ligne_budgetaires as dlb')
            ->join('categorie_budgetaires as cb', 'dlb.id_categorie_budgetaire', '=', 'cb.id')
            ->leftJoin('decaissement as d', function($join) {
                $join->on('d.id_categorie_budgetaire', '=', 'dlb.id_categorie_budgetaire');
                $join->on('d.id_ligne_budgetaire', '=', 'dlb.id_ligne_budgetaire');
            })
            ->where('dlb.id_ligne_budgetaire', '=', $id)
            ->groupBy(['cb.id', 'cb.libelle', 'cb.numero_compte', 'dlb.id', 'dlb.montant'])
            ->selectRaw('dlb.id, cb.id as id_categorie, cb.libelle, cb.numero_compte, COALESCE(SUM(CASE WHEN '.$this->campusVisibilitySql('d.id_campus').' THEN d.montant ELSE 0 END), 0) as realized_amount, dlb.montant as total_amount', array($campusId))
            ->orderBy('cb.libelle')
            ->get();

            $values = array(
                'titre' => trans('admin.report') . ' ' . trans('admin.ligne_budget'),
                'line' => $line,
                'lineData' => $lineData,
                'campus' => $this->currentCampus(),
            );

            return view('comptabilite.line_report', $values);
    }

    public function categorie_report($dataId)
    {
        $data = DonneeLigneBudgetaire::with(['ligneBudgetaire', 'categorieBudgetaire'])->find($dataId);
        if (!$data || !$this->budgetLineBelongsToCurrentCampus($data->ligneBudgetaire, true)) {
            abort(404);
        }
        $decaissement = decaissement::with('personnel')
            ->whereIdLigneBudgetaire($data->id_ligne_budgetaire)
            ->whereIdCategorieBudgetaire($data->id_categorie_budgetaire)
            ->whereRaw($this->campusVisibilitySql('id_campus'), array($this->currentCampusId()))
            ->orderBy('created_at', 'desc')
            ->get();


            $values = array(
                'titre' => trans('admin.report') . ' ' . trans('admin.categorie_budget'),
                'data' => $data,
                'decaissement' => $decaissement,
                'campus' => $this->currentCampus(),
            );
        return view('comptabilite.categorie_report', $values);

    }

    public function edit_data($id)
    {
        $budget_line = $this->findCampusBudgetLine($id, 'donneesLigneBudgetaire');
        if (!$budget_line) {
            abort(404);
        }
        $allocated = $budget_line->donneesLigneBudgetaire->sum('montant');
        $budget_categories = CategorieBudgetaire::orderBy('libelle')->get();
        $values= array(
            'titre' => trans('admin.budget_line_data'),
            'budget_line' => $budget_line,
            'budget_categories' => $budget_categories,
            'allocated' => $allocated
        );
        
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        return view('comptabilite.donnees_ligne_budgetaire',$values);

    }
    public function print_line($id)
    {
        $line = $this->findCampusBudgetLine($id, 'donneesLigneBudgetaire.categorieBudgetaire', true);
        if (!$line) {
            abort(404);
        }

        $values = array(
            'titre' => trans('admin.report') . ' ' . trans('admin.ligne_budget'),
            'line' => $line,
            'campus' => $this->currentCampus(),
        );
        return view('comptabilite.print_ligne_budgetaire',$values);
    }

    public function update_data(Request $request, $id)
    {

        $categoryList = $request->input('categorie');
        $montantList = $request->input('montant');

        $line = $this->findCampusBudgetLine($id, 'donneesLigneBudgetaire');
        if (!$line) {
            abort(404);
        }

        $data_ids_array = array();
        foreach ($line->donneesLigneBudgetaire as $entry) {
            $data_ids_array[] = $entry->id;
        }
        // Delete the old Data
        if (sizeof($data_ids_array)) {
            DonneeLigneBudgetaire::destroy($data_ids_array);
        }

        if($categoryList == null){
            session(['success'=>true]);
            return redirect()->action('comptabilite\LigneBudgetController@index');
        }

        $i = 0;


        foreach ($categoryList as $c) {
            $donnee = new DonneeLigneBudgetaire();
            $donnee->id_ligne_budgetaire = $id;
            $donnee->id_categorie_budgetaire = $c;

            $donnee->montant = $montantList[$i];
            $donnee->save();

            $i++;
        }

        session(['success'=>true]);
        return redirect()->action('comptabilite\LigneBudgetController@index');
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
            "libelle_ligne" => "required",
            "date_debut" => "required",
            "date_fin" => "required",
        ]);

        $libelle =$request->input('libelle_ligne');
        $date_debut=$request->input('date_debut');
        $date_fin=$request->input('date_fin');
        $total=$request->input('total');
        
        $budget_line=new LigneBudgetaire();
        $budget_line->libelle_ligne=$libelle;
        $budget_line->date_debut=$date_debut;
        $budget_line->date_fin=$date_fin;

        if ($total) {
            $budget_line->total=$total;
        }
        if (Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            $budget_line->id_campus=$this->currentCampusId();
        }

        $budget_line->save();
        session(['success'=>true]);
        return redirect()->action('comptabilite\LigneBudgetController@index');
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
    public function update(Request $req)
    {
        $this->validate($req,[
            'id'=>'required|numeric',
            'libelle_ligne'=>'required',
            'date_debut'=>'required',
            'date_fin'=>'',
        ]);
        $id=$req->input('id');
        $libelle=$req->input('libelle_ligne');
        $date_debut=$req->input('date_debut');
        $date_fin=$req->input('date_fin');
        $total=$req->input('total');
        $budget_line=$this->findCampusBudgetLine($id);
        if (!$budget_line) {
            abort(404);
        }
        $budget_line->libelle_ligne=$libelle;
        $budget_line->date_debut=$date_debut;
        $budget_line->date_fin=$date_fin;

        if ($total) {
            $budget_line->total=$total;
        }

        $budget_line->save();
        session(['success'=>true]);
        return redirect()->action('comptabilite\LigneBudgetController@index');
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
            $budget_line=$this->findCampusBudgetLine($id);
            if (!$budget_line) {
                abort(404);
            }
            $budget_line->delete();
            session(['success'=>true]);
        } catch (QueryException $fe) {
            return  redirect()->to(URL::previous())->withErrors(['constraint_violation' => trans('admin.cannot_delete')]);
        }
        return redirect()->action('comptabilite\LigneBudgetController@index');
    }

    private function findCampusBudgetLine($id, $with = null, $includeHistorical = false)
    {
        $query = LigneBudgetaire::query();
        if ($with) {
            $query->with($with);
        }
        if (Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            if ($includeHistorical) {
                $query->whereRaw($this->campusVisibilitySql(), array($this->currentCampusId()));
            } else {
                $query->where('id_campus', '=', $this->currentCampusId());
            }
        }
        return $query->where('id', '=', $id)->first();
    }

    private function budgetLineBelongsToCurrentCampus($line, $includeHistorical = false)
    {
        if (!$line || !Schema::hasColumn('ligne_budgetaires', 'id_campus')) {
            return (bool) $line;
        }
        return (int) $line->id_campus === (int) $this->currentCampusId()
            || ($includeHistorical && $line->id_campus === null && $this->isHistoricalSchoolYear());
    }
}
