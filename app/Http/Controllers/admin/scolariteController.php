<?php namespace gesecole\Http\Controllers\admin;

use gesecole\classe;
use gesecole\cycle;
use gesecole\Http\Controllers\Controller;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parametres;
use Illuminate\Support\Facades\Session;
use gesecole\scolarite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class scolariteController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.dashboard')]);
        $this->renewSchoolYear();
    }

	public function index(Request $request)
	{
        $style = $request->input('style');
        if($style !== null && !in_array($style, array('0', '1', 0, 1), true)) {
            $style = null;
        }
        $styleMode = $style === null ? null : intval($style);

	    $s0=scolarite::all();
	    $arr=[];
	    foreach ($s0 as $sco){
	        array_push($arr,$sco->id_niveau);
        }

        $sQuery=scolarite::orderBy('id_cycle')->orderBy('id_niveau');
        if($styleMode === 0) {
            if(Schema::hasColumn('scolarite', 'annee_scolaire')) {
                $sQuery->where(function ($q) {
                    $q->whereNull('annee_scolaire')->orWhere('annee_scolaire', '=', '');
                });
            }
            if(Schema::hasColumn('scolarite', 'id_classe')) {
                $sQuery->where(function ($q) {
                    $q->whereNull('id_classe')->orWhere('id_classe', '=', 0);
                });
            }
            if(Schema::hasColumn('scolarite', 'id_nniveau')) {
                $sQuery->where(function ($q) {
                    $q->whereNull('id_nniveau')->orWhere('id_nniveau', '=', 0);
                });
            }
        }
        if($styleMode === 1 && Schema::hasColumn('scolarite', 'annee_scolaire')) {
            $sQuery->whereNotNull('annee_scolaire')->where('annee_scolaire', '<>', '');
        }
        if(Schema::hasColumn('scolarite', 'annee_scolaire')) {
            $sQuery->orderBy('annee_scolaire', 'desc');
        }
        if(Schema::hasColumn('scolarite', 'id_classe')) {
            $sQuery->orderBy('id_classe');
        }
        $s=$sQuery->paginate(15);
        if($style !== null) {
            $s->appends(array('style' => $style));
        }
        $c=niveau::all();
        $classes=classe::with('niveau')->orderBy('id_niveau')->orderBy('nom')->get();
        $cy=cycle::all();
        $nniveaux=NNiveau::orderBy('nom')->get();
        $values= array('titre'=>trans('admin.schoolfees'),'scolarite'=>$s,'niveaux'=>$c,'classes'=>$classes,'cycles'=>$cy,'nniveaux'=>$nniveaux,'style'=>$style);
        if(session('success')){
            Session::forget('success');
            $values['success']=true;
        }
        return view('admin.scolarite',$values);
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
	public function store(Request $req)
	{
        $styleMode = intval($req->input('scolarite_style', 1));

        $this->validate($req,[
            'classe'=>'required|numeric',
            'cycle'=>'required|numeric',
            'inscription'=>'required|numeric',
            'tranche1'=>'required|numeric',
            'tranche2'=>'required|numeric',
            'tranche3'=>'numeric',
            'tranche4'=>'numeric',
            'tranche5'=>'numeric',
            'type_scolarite'=>'numeric',
        ]);

        if($styleMode === 1 && trim($req->input('annee_scolaire')) === '') {
            return redirect(\URL::previous())->withErrors(array('annee_scolaire' => trans('validation.required', array('attribute' => trans('admin.year')))))->withInput();
        }

        $classe=$req->input('classe');
        $cycle=$req->input('cycle');
        $specialite=$styleMode === 1 ? $req->input('specialite') : null;
        $nniveau=$styleMode === 1 ? $req->input('nniveau') : null;
        $annee_scolaire=$styleMode === 1 ? $req->input('annee_scolaire') : null;
        $libelle=$styleMode === 1 ? $req->input('libelle') : null;
        $inscription=$req->input('inscription',0);
        $tranche1=$req->input('tranche1',0);
        $tranche2=$req->input('tranche2');
        $tranche3=$req->input('tranche3');
        $tranche4=$req->input('tranche4');
        $tranche5=$req->input('tranche5');
        $type_scolarite=$req->input('type_scolarite');

        $current_user = \Auth::user();

        $s = null;
        if($styleMode === 0) {
            $existingQuery = scolarite::where('id_niveau', '=', $classe)
                ->where('id_cycle', '=', $cycle)
                ->where('type_scolarite', '=', $type_scolarite);

            if(Schema::hasColumn('scolarite', 'id_classe')) {
                $existingQuery->where(function($q) {
                    $q->whereNull('id_classe')->orWhere('id_classe', '=', 0);
                });
            }
            if(Schema::hasColumn('scolarite', 'id_nniveau')) {
                $existingQuery->where(function($q) {
                    $q->whereNull('id_nniveau')->orWhere('id_nniveau', '=', 0);
                });
            }
            if(Schema::hasColumn('scolarite', 'annee_scolaire')) {
                $existingQuery->where(function($q) {
                    $q->whereNull('annee_scolaire')->orWhere('annee_scolaire', '=', '');
                });
            }

            $s = $existingQuery->orderBy('id', 'desc')->first();
        }

        if(!$s) {
            $s=new scolarite();
        }
        $s->id_niveau=$classe;
        $s->id_cycle=$cycle;
        if(Schema::hasColumn('scolarite', 'id_classe')) {
            $s->id_classe=$specialite ? $specialite : null;
        }
        if(Schema::hasColumn('scolarite', 'id_nniveau')) {
            $s->id_nniveau=$nniveau ? $nniveau : null;
        }
        if(Schema::hasColumn('scolarite', 'annee_scolaire')) {
            $s->annee_scolaire=$annee_scolaire;
        }
        if(Schema::hasColumn('scolarite', 'libelle')) {
            $s->libelle=$libelle;
        }
        $s->inscription=$inscription;
        $s->tranche1=floatval($tranche1);
        $s->tranche2=floatval($tranche2);
        $s->tranche3=floatval($tranche3);
        $s->tranche4=floatval($tranche4);
        $s->tranche5=floatval($tranche5);
        $s->type_scolarite=($type_scolarite);
        $s->total=$tranche1+$tranche2+$tranche3+$tranche4+$tranche5;

        $s->id_creator = $current_user ? $current_user->id : 0;
        $s->id_last_editor = $current_user ? $current_user->id : 0;
        $s->edition_details = 'total : ' . $s->total. ', inscription : ' . $inscription;

        $s->save();


        session(['success'=>true]);
        return redirect()->to(url('/admin/scolarites?style='.$styleMode));
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
        $styleMode = intval($req->input('scolarite_style', 1));

        $this->validate($req,[
            'id'=>'required|numeric',
            'classes'=>'required|numeric',
            'inscription'=>'required|numeric',
            'cycle'=>'required|numeric',
            'tranche1'=>'required|numeric',
            'tranche2'=>'required|numeric',
            'tranche3'=>'numeric',
            'tranche4'=>'numeric',
            'tranche5'=>'numeric',
            'type_scolarite'=>'numeric',
        ]);

        if($styleMode === 1 && trim($req->input('annee_scolaire')) === '') {
            return redirect(\URL::previous())->withErrors(array('annee_scolaire' => trans('validation.required', array('attribute' => trans('admin.year')))))->withInput();
        }

        $id=$req->input('id');
        $classe=$req->input('classes');
        $cycle=$req->input('cycle');
        $specialite=$styleMode === 1 ? $req->input('specialite') : null;
        $nniveau=$styleMode === 1 ? $req->input('nniveau') : null;
        $annee_scolaire=$styleMode === 1 ? $req->input('annee_scolaire') : null;
        $libelle=$styleMode === 1 ? $req->input('libelle') : null;
        $inscription=$req->input('inscription');
        $tranche1=$req->input('tranche1');
        $tranche2=$req->input('tranche2');
        $tranche3=$req->input('tranche3');
        $tranche4=$req->input('tranche4');
        $tranche5=$req->input('tranche5');
        $type_scolarite=$req->input('type_scolarite');

        $s=scolarite::find($id);
        $s->id_niveau=$classe;
        $s->id_cycle=$cycle;
        if(Schema::hasColumn('scolarite', 'id_classe')) {
            $s->id_classe=$specialite ? $specialite : null;
        }
        if(Schema::hasColumn('scolarite', 'id_nniveau')) {
            $s->id_nniveau=$nniveau ? $nniveau : null;
        }
        if(Schema::hasColumn('scolarite', 'annee_scolaire')) {
            $s->annee_scolaire=$annee_scolaire;
        }
        if(Schema::hasColumn('scolarite', 'libelle')) {
            $s->libelle=$libelle;
        }
        $s->inscription=$inscription;
        $s->type_scolarite=$type_scolarite;
        $s->tranche1=floatval($tranche1);
        $s->tranche2=floatval($tranche2);
        $s->tranche3=floatval($tranche3);
        $s->tranche4=floatval($tranche4);
        $s->tranche5=floatval($tranche5);
        $s->total=$tranche1+$tranche2+$tranche3+$tranche4+$tranche5;

        $current_user = \Auth::user();
        $s->id_last_editor = $current_user ? $current_user->id : 0;

        $s->save();
        session(['success'=>true]);
        return redirect()->to(url('/admin/scolarites?style='.$styleMode));
	}


	public function getScolarite(Request $request)
    {
        $cycle=$request->input('cycle');
        $niveau=$request->input('niveau');
        $classe=$request->input('classe');
        $nniveau=$request->input('nniveau');
        $annee_scolaire=$request->input('annee_scolaire', session('year'));
        $type_scolarite=$request->input('type_scolarite');
        $scolarite_style=intval($request->input('scolarite_style', 1));
        $selectedClasse=$classe ? classe::find($classe) : null;

        if($selectedClasse) {
            $niveau=$selectedClasse->id_niveau;
        }

        if($type_scolarite === null) {
            $type_scolarite = 0;
        }

        if($cycle==null || $niveau==null){
            $s=new scolarite();
            $s->inscription=0;
            $s->type_scolarite = 0;
            $s->options = array();
            return response()->json($s);
        }

        if($scolarite_style === 0) {
            $options=scolarite::matchingOldStyle($cycle, $niveau, $type_scolarite)->get();
        }
        else {
            $options=scolarite::matching($cycle, $niveau, $type_scolarite, $classe, $nniveau, $annee_scolaire)->get();
            if(count($options) == 0) {
                $options=scolarite::matching($cycle, $niveau, $type_scolarite, $classe, $nniveau, null)->get();
            }
        }
        $s=$options->first();
        if($s==null){
            $s=new scolarite();
            $s->inscription=0;
            $s->type_scolarite = 0;
            $s->options = array();
            return response()->json($s);
        }

        $s->options = $options;
        return response()->json($s);
    }
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $c=scolarite::find($id);
        $c->delete();
        session(['success'=>true]);
        return redirect()->action('admin\scolariteController@index');
	}

}
