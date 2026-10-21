<?php namespace gesecole\Http\Controllers\inscriptions;

use gesecole\classe;
use gesecole\eleve;
use gesecole\Http\Controllers\admin\nniveauController;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\inscription;
use gesecole\NNiveau;
use gesecole\parametres;
use gesecole\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;

class photosController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
    public function __construct()
    {

        session(['big_title'=>trans('menu.gestion_inscriptions')]);
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
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(Request $request)
	{
        $this->validate($request,[
            'id'=>'required|numeric',
            'cycle'=>'required|numeric',
            'nniveau'=>'required|numeric',
        ]);


        $values= array('titre'=>'Photos');

        $id_nniveau=$request->input('nniveau');
        $id_classe=$request->input('id');
        $id_cycle=$request->input('cycle');




        $e=DB::select('SELECT e.id,e.nom,e.prenom FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom',[session('year'),$id_classe,$id_cycle,$id_nniveau]);
        $i=0;
        $e_list=array();
        foreach ($e as $eleve){
            $e_list[$i]=$eleve->id;
            $i++;
        }
        $p=Photo::whereAnneeScolaire(session('year'))->whereIn('eleve_id',$e_list)->get();

        $values['eleves']=$e;

        $values['id_cycle']=$id_cycle;
        $values['id_classe']=$id_classe;
        $values['id_nniveau']=$id_nniveau;
        $values['photos']=$p;

        return view('inscriptions/photos',$values);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
	    $messages=[];
	    $rules=[
            'eleve'=>'required',

        ];
        $nbr = count($request->file('photo')) - 1;
        foreach(range(0, $nbr) as $index) {
            $rules['photo.' . $index] = 'image|max:4096';
            $messages['photo.' . $index.'.max'] = trans('admin.p_size_limit',['num'=>$index+1,'lim'=>4]);

        }



        $this->validate($request,$rules,$messages);


        $eleveList=$request->input('eleve');
        $photoList=$request->file('photo');

        $i=0;
        if($eleveList==null){
            session(['success'=>true]);

            return redirect()->to('/select/classe?url='.URL::to('/inscriptions/photos'));
        }
        foreach ($eleveList as $e){
            if($photoList[$i]!=null)
            {
                $p=$photoList[$i];
                $ph=Photo::firstOrNew(['annee_scolaire'=>session('year'),'eleve_id'=>$e]);
                Image::make($p->getRealPath())->resize(300,300)->encode('jpg',80)->save('img/photos/'.$e.'-'.session('year').'.jpg');
                $ph->url='img/photos/'.$e.'-'.session('year').'.jpg';
                $ph->save();

            }
            $i++;
        }

        session(['success'=>true]);

        return redirect()->to('/select/classe?url='.URL::to('/inscriptions/photos'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function show_carte(Request $request)
    {
        $divisor=10;
        $this->validate($request,[
            'id'=>'required|numeric',
            'cycle'=>'required|numeric',
            'nniveau'=>'required|numeric',
        ]);
        $values= array('titre'=>trans_choice('admin.carte_e',2));

        $id_nniveau=$request->input('nniveau');
        $id_classe=$request->input('id');
        $id_cycle=$request->input('cycle');
        $nn=NNiveau::find($id_nniveau);
        $classe=classe::find($id_classe);
        $e=inscription::where('inscription.annee_scolaire','=',session('year'))
            ->where('id_cycle','=',$id_cycle)
            ->where('id_classe','=',$id_classe)
            ->where('id_nniveau','=',$id_nniveau)
            ->join('eleve','eleve.id','=','inscription.id_eleve')

            ->select(['eleve.*'])
            ->orderBy('nom')
        ->get();
        $id_e=$e->fetch('id')->toArray();
        $po=Photo::whereAnneeScolaire(session('year'))
            ->whereIn('eleve_id',$id_e)
            ->get()
            ;
        $pa=parametres::all();
        $nom_e=$pa->where('nom','nom_etablissement',false)->first()->valeur;
        $n=0;
       $div=($e->count()/$divisor);
       if($div>intval($div))
       {
           $n=intval($div)+1;
       }
       else
       {
           $n=intval($div);
       }


        $values['eleves']=$e;
        $values['nom_e']=$nom_e;
        $values['n']=$n;
        $values['photos']=$po;
        $values['classe']=$classe;
        $values['niveau']=$classe->niveau;
        $values['nniveau']=NNiveau::find($id_nniveau);







        return view('inscriptions/cartes',$values);


    }

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
