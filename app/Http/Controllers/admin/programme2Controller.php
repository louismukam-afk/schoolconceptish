<?php namespace gesecole\Http\Controllers\admin;

use gesecole\classe;
use gesecole\classeMatiere;
use gesecole\Http\Controllers\Controller;
use gesecole\cycle;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parametres;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class programmeController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {
        session(['big_title'=>trans('admin.dashboard')]);
        $year=session('year');
        if($year==null){
            $p=parametres::whereNom('annee_scolaire')->get()->first();

            if(count($p)<=0){
                $p=new parametres();
                $p->nom='annee_scolaire';
                $p->valeur='2017-2018';
                $p->save();
                session(['year'=>$p->valeur]);
            }else{
                session(['year'=>$p->valeur]);
            }
        }
    }
    public function index()
    {
        $titre=trans('admin.selectionner_classe');
        $m=classe::orderBy('nom')->get();
        $cy=cycle::all();
        $n=niveau::all();
        $nn=NNiveau::all();
        $values= array('titre'=>$titre,'classes'=>$m);
        $values['cycles']=$cy;
        $values['niveaux']=$n;
        $values['nniveaux']=$nn;


        if(session('success')){
            Session::forget('success');
            $values['success']=true;

        }
        return view('admin/selection_classe_programme',$values);
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request)
	{
        $this->validate($request,[
            "nniveau"=>"required|numeric",
        ]);
	    $id=$request->input('id');
	    $c=classe::find($id);
	    $cycle= $request->input('cycle');
	  
	    $nniveau=$request->input('nniveau');
        $titre=trans('admin.programme');
        $p=DB::select('SELECT cm.id,cm.id_matiere,cm.type,cm.id_cycle,cm.semestre,n.nom as nom_code,m.nom_anglais,m.nom as nom_matiere,coef,max FROM classe_matiere cm, matiere m ,classe c, codages n WHERE cm.id_classe=c.id AND cm.id_matiere=m.id  And cm.id_code=n.id  And c.id=?  AND cm.id_cycle=? AND cm.id_nniveau=?',[$id,$cycle,$nniveau]);
        $m=DB::select('select m.id,m.nom,m.nom_anglais FROM matiere m WHERE  m.id NOT IN (SELECT cm.id_matiere FROM classe_matiere cm WHERE cm.id_classe=?  AND cm.id_cycle=? AND cm.id_nniveau=?)',[$id,$cycle,$nniveau]);
      
        $n=DB::select('select n.id,n.nom FROM codages n  WHERE  n.id NOT IN (SELECT cm.id_code FROM classe_matiere cm WHERE cm.id_classe=?  AND cm.id_cycle=? AND cm.id_nniveau=?)',[$id,$cycle,$nniveau]);


        $values= array('titre'=>$titre,'programmes'=>$p);
        $values['classe']=$c;
        $values['matieres']=$m;
        $values['codages']=$n;
        $values['id_cycle']=$cycle;
        $values['id_nniveau']=$nniveau;
        if(session('success')){
            Session::forget('success');
            $values['success']=true;

        }

        return view('admin/addProgram',$values);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
	    $this->validate($request,[
	       'id'=>'required|numeric',
            'id_cycle'=>'required|numeric',
            'id_nniveau'=>'required|numeric'


        ]);
	    $id=$request->input('id');
        $id_cycle=$request->input('id_cycle');
        $nniveau=$request->input('id_nniveau');
        $matiereList=$request->input('matiere');
        $codageList=$request->input('codage');
        $coefList=$request->input('coef');
        $maxList=$request->input('max');
        $semestreList=$request->input('semestre');
        $typeList=$request->input('type');
        $i=0;
        foreach ($matiereList as $mat){

            $cm=new classeMatiere();
            $cm->id_classe=$id;
            $cm->id_cycle=$id_cycle;
            $cm->id_matiere=$mat;
            $cm->id_code=$codageList[$i];
            $cm->id_nniveau=$nniveau;
            $cm->coef=$coefList[$i];
            $cm->max=$maxList[$i];
            $cm->semestre=$semestreList[$i];
            $cm->type=$typeList[$i];
            $cm->save();

            $i++;
        }




        session(['success'=>true]);

        //$request->request->add(['cycle'=>$id_cycle]);
        return redirect()->to('/admin/programmes/selection-matieres/?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$nniveau);
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
            'id_cycle'=>'required|numeric',
            'id_nniveau'=>'required|numeric',
        ]);
        $id=$request->input('id');
        $id_cycle=$request->input('id_cycle');
        $nniveau=$request->input('id_nniveau');

        $coef=$request->input('coef');
        $max=$request->input('max');
        $semestre=$request->input('semestre');
        $type=$request->input('type');

        $cm=classeMatiere::find($id);
        $cm->coef=$coef;
        $cm->max=$max;

        $cm->semestre=$semestre;
        $cm->type=$type;
        $cm->save();

        session(['success'=>true]);

        //$request->request->add(['cycle'=>$id_cycle]);
        return redirect()->to('/admin/programmes/selection-matieres/?id='.$cm->id_classe.'&cycle='.$id_cycle.'&nniveau='.$nniveau);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(Request $request,$id)
	{
	    $id_classe=$request->input('id');
        $id_cycle=$request->input('cycle');
        $nniveau=$request->input('nniveau');
		$cm=classeMatiere::find($id);
		$cm->delete();
        session(['success'=>true]);
        return redirect()->to('/admin/programmes/selection-matieres/?id='.$id_classe.'&nniveau='.$nniveau.'&cycle='.$id_cycle);
	}

}
