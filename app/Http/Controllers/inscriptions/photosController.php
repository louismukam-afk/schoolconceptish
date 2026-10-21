<?php namespace gesecole\Http\Controllers\inscriptions;

use gesecole\classe;
use gesecole\eleve;
use gesecole\Http\Controllers\admin\nniveauController;
use gesecole\Http\Controllers\startController;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;

use gesecole\inscription;
use gesecole\NNiveau;
use gesecole\parametres;
use gesecole\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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
        $this->renewSchoolYear();
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




        $idCampus = $this->currentCampusId();

        $e=DB::select('SELECT e.id,e.nom,e.prenom FROM eleve e,inscription i WHERE i.annee_scolaire=? AND ' . $this->campusVisibilitySql('i.id_campus') . ' AND i.desinscrire=0 AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom',[session('year'),$idCampus,$id_classe,$id_cycle,$id_nniveau]);
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

            return redirect()->to(url('/select/classe?url='.URL::to('/inscriptions/photos')));
        }
        $idCampus = $this->currentCampusId();
        $allowedEleves = inscription::where('annee_scolaire', '=', session('year'))
            ->where('id_campus', '=', $idCampus)
            ->where('desinscrire', '=', 0)
            ->whereIn('id_eleve', $eleveList)
            ->lists('id_eleve')
            ->toArray();

        foreach ($eleveList as $e){
            if(!in_array($e, $allowedEleves)) {
                $i++;
                continue;
            }
            if($photoList[$i]!=null)
            {
                $p=$photoList[$i];
                $ext=$p->getClientOriginalExtension();
                $ph=Photo::firstOrNew(['annee_scolaire'=>session('year'),'eleve_id'=>$e]);

                $savePath = self::getPhotosSaveDirPath() . '/' .$e.'-'.session('year').'.'.$ext;
                Image::make($p->getRealPath())->resize(300,300)->save($savePath);
                $ph->url=$savePath;
                $ph->save();

            }
            $i++;
        }

        session(['success'=>true]);

        return redirect()->to(url('/select/classe?url='.URL::to('/inscriptions/photos')));
	}


	public function search_carte_eleve(){
        $values= array('titre'=>trans('inscription.title_search'));
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');

        }
	    return view('inscriptions.search_carte_eleve',$values);
    }

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function show_carte(Request $request)
    {
        $eleve=$request->input('eleve');
        $divisor=10;
        $values= array('titre'=>trans_choice('admin.carte_e',2));

        $nom_e=parametres::where('nom','=','nom_etablissement')->first()->valeur;

        //$nom_abb=parametres::where('nom','=','nom_abbrege')->first()->valeur;

        if($eleve!=null)
        {
            $idCampus = $this->currentCampusId();
            $i=inscription::whereAnneeScolaire(session('year'))
                ->whereRaw($this->campusVisibilitySql('id_campus'), array($idCampus))
                ->where('desinscrire', '=', 0)
                ->where('id_eleve','=',$eleve)
                ->first();
            if($i==null)
            {
                $values['inscription']=0;
                $values['eleves']=null;
                $values['nom_e']=null;
                $values['n']=null;
                $values['photos']=null;
                $values['classe']=null;
                $values['niveau']=null;
                $values['nniveau']=null;
                return view('inscriptions/cartes',$values);

            }



            $id_nniveau=$i->id_nniveau;
            $id_classe=$i->id_classe;
            $id_cycle=$i->id_cycle;
            $nn=NNiveau::find($id_nniveau);
            $classe=classe::find($id_classe);
           $e=eleve::where('id','=',$eleve)->get();

            $id_e=$e->fetch('id')->toArray();
            $po=Photo::whereAnneeScolaire(session('year'))
                ->whereIn('eleve_id',$id_e)
                ->get()
            ;
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
          //  $values['nom_abb']=$nom_abb;
            $values['n']=$n;
            $values['photos']=$po;
            $values['classe']=$classe;
            $values['niveau']=$classe->niveau;
            $values['nniveau']=$nn;

            return view('inscriptions/cartes',$values);

        }
        else{
            $this->validate($request,[
                'id'=>'required|numeric',
                'cycle'=>'required|numeric',
                'nniveau'=>'required|numeric',
            ]);

            $id_nniveau=$request->input('nniveau');
            $id_classe=$request->input('id');
            $id_cycle=$request->input('cycle');
            $nn=NNiveau::find($id_nniveau);
            $classe=classe::find($id_classe);
            $idCampus = $this->currentCampusId();
            $e=inscription::where('inscription.annee_scolaire','=',session('year'))
                ->whereRaw($this->campusVisibilitySql('inscription.id_campus'), array($idCampus))
                ->where('inscription.desinscrire','=',0)
                ->where('inscription.id_cycle','=',$id_cycle)
                ->where('inscription.id_classe','=',$id_classe)
                ->where('inscription.id_nniveau','=',$id_nniveau)
                ->join('eleve','eleve.id','=','inscription.id_eleve')

                ->select(['eleve.*'])
                ->orderBy('nom')
                ->get();
            $id_e=$e->fetch('id')->toArray();
            $po=Photo::whereAnneeScolaire(session('year'))
                ->whereIn('eleve_id',$id_e)
                ->get()
            ;
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
           // $values['nom_abb']=$nom_abb;
            $values['n']=$n;
            $values['photos']=$po;
            $values['classe']=$classe;
            $values['niveau']=$classe->niveau;
            $values['nniveau']=$nn;







            return view('inscriptions/cartes',$values);

        }


    }

    public static function getPhotosSaveDirPath() {
        $dir = 'img/photos/'. session('year');
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
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
