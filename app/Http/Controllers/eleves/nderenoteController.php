<?php namespace gesecole\Http\Controllers\eleves;

use database\seeds\eleveTableSeeder;
use gesecole\classe;
use gesecole\classeMatiere;
use gesecole\cycle;
use gesecole\dispense;
use gesecole\eleve;
use gesecole\groupeMatieres;
use gesecole\niveau;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\NNiveau;
use gesecole\verrouNote;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use gesecole\matiere;
use gesecole\note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use gesecole\parametres;


class nderenoteController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */

    public function __construct()
    {
        session(['big_title'=>trans('eleve.big_title')]);
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
        $values= array('titre'=>'Notes');
        $c=classe::orderBy('nom')->get();
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }
        $cy=cycle::all();
        $n=niveau::all();
        $nn=NNiveau::all();
        $values['niveaux']=$n;
        $values['classes']=$c;
        $values['nniveaux']=$nn;
        $values['cycles']=$cy;

        return view('eleves/classe_note_ndere',$values);
    }
    public function index2()
    {
        $values= array('titre'=>'Notes');


        return view('eleves/notes_index_ndere',$values);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function matiere(Request $request)
    {
        $this->validate($request,[
            "nniveau"=>"required|numeric",
        ]);
        $id=$request->input('id');
        $id_cycle=$request->input('cycle');
        $nniveau=$request->input('nniveau');
        $values= array('titre'=>'Notes');
        $m=DB::select('SELECT m.* FROM matiere m ,classe_matiere cm WHERE m.id=cm.id_matiere AND cm.id_classe=? AND cm.id_cycle=? AND cm.id_nniveau=? ORDER BY m.nom',[$id,$id_cycle,$nniveau]);
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }
        if(session('erreur')){
            $values['erreur']=session('erreur');
            Session::forget('erreur');
        }
        $values['matieres']=$m;
        $values['id']=$id;
        $values['id_cycle']=$id_cycle;
        $values['id_nniveau']=$nniveau;

        return view('eleves/matiere_note_ndere',$values);
    }
    public function matiere_show(Request $request)
    {
        $this->validate($request,[
            "nniveau"=>"required|numeric",
        ]);
        $id=$request->input('id');
        $id_cycle=$request->input('cycle');
        $nniveau=$request->input('nniveau');
        $values= array('titre'=>'Notes');
        $m=DB::select('SELECT m.* FROM matiere m ,classe_matiere cm WHERE m.id=cm.id_matiere AND cm.id_classe=? AND cm.id_cycle=? AND cm.id_nniveau=? ORDER BY m.nom',[$id,$id_cycle,$nniveau]);
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }
        if(session('erreur')){
            $values['erreur']=session('erreur');
            Session::forget('erreur');
        }
        $values['matieres']=$m;
        $values['id']=$id;
        $values['id_cycle']=$id_cycle;
        $values['id_nniveau']=$nniveau;

        return view('eleves/matiere_note_show_ndere',$values);
    }

    public function create(Request $request,$id)
    {
        $this->validate($request,[
            'sequence'=>'required|numeric',
            'cycle'=>'required|numeric',
            'nniveau'=>'required|numeric',
        ]);


        $values= array('titre'=>'Notes');
        $sequence=$request->input('sequence');
        $nniveau=$request->input('nniveau');
        $id_m=$request->input('id');
        $id_cycle=$request->input('cycle');

        $v=verrouNote::whereAnneeScolaire(\session('year'))
            ->where('sequence','=',$sequence)
            ->get();

        if($v->count()>0){
            session(['erreur'=>trans('admin.mod_locked')]);
            return redirect()->to('/eleves/notes/matieres_ndere?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$nniveau);
        }

        $user=Auth::user();
        if($user->role==18.5){
            $d=dispense::whereAnneeScolaire(\session('year'))
                ->where('id_personnel','=',$user->id_personnel)
                ->where('id_matiere','=',$id_m)
                ->where('id_classe','=',$id)
                ->where('id_nniveau','=',$nniveau)
                ->first();
            if($d==null){
                $mat=matiere::find($id_m);
                session(['erreur'=>trans('admin.no_modification',['matiere'=>$mat->nom.'('.$mat->nom_anglais.')'])]);
                return redirect()->to('/eleves/notes/matieres_ndere?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$nniveau);
            }
        }

        $mat=matiere::find($id_m);
        $cm=classeMatiere::whereIdClasse($id)->where('id_matiere','=',$id_m)->where('id_cycle','=',$id_cycle)->where('id_nniveau','=',$nniveau)->first();

        $e=DB::select('SELECT e.id,e.nom,e.prenom FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom',[session('year'),$id,$id_cycle,$nniveau]);
        $n=note::whereAnneeScolaire(session('year'))
            ->where('id_matiere','=',$id_m)
            ->where('sequence','=',$sequence)
            ->get();

        $values['eleves']=$e;
        $values['notes']=$n;
        $values['max']=$cm->max;
        $values['matiere']=$mat;
        $values['id_cycle']=$id_cycle;
        $values['id_classe']=$id;
        $values['id_nniveau']=$nniveau;
        $values['sequence']=$sequence;
        return view('eleves/eleves_notes_ndere',$values);
    }

    public function print_note(Request $request,$id)
    {
        $this->validate($request,[
            'sequence'=>'required|numeric',
            'cycle'=>'required|numeric',
            'nniveau'=>'required|numeric',
        ]);

        $values= array('titre'=>'Notes');
        $sequence=$request->input('sequence');
        $id_m=$request->input('id');
        $nniveau=$request->input('nniveau');
        $id_cycle=$request->input('cycle');

        if (in_array((int) $sequence, array(7, 9)) && $request->input('ajax') != 1) {
            $params = $request->all();
            $params['ajax'] = 1;

            return view('eleves/pv_ajax_loader', array(
                'titre' => 'Chargement du PV',
                'pv_url' => url('/eleves/notes/print_note_ndere/'.$id).'?'.http_build_query($params),
                'back_url' => url('/eleves/notes/matieres/show_ndere').'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$nniveau
            ));
        }

        if($sequence==1 || $sequence==2) {
            $mat = matiere::find($id_m);
            $cm = classeMatiere::whereIdClasse($id)->where('id_matiere', '=', $id_m)->where('id_cycle', '=', $id_cycle)->where('id_nniveau','=',$nniveau)->first();

            $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau]);
            $j=0;
            foreach ($e as $el){
                $id_eleves[$j]=$el->id;
                $j++;
            }
            $n = note::whereAnneeScolaire(session('year'))
                ->where('id_matiere', '=', $id_m)
                ->where('sequence', '=', $sequence)
                ->whereIn('id_eleve',$id_eleves)
                ->get();


            $values['eleves'] = $e;
            $values['notes'] = $n;
            $values['max'] = $cm->max;
            $values['matiere'] = $mat;
            $values['id_cycle'] = $id_cycle;
            $values['classe'] = classe::find($id);
            $values['nniveau']=NNiveau::find($nniveau);
            $values['sequence'] = $sequence;

            return view('eleves/print_note_cc', $values);
        }

        if($sequence==3 || $sequence==4) {
            if($sequence==3)
                $cc=1;
            else
                $cc=2;
            $mat = matiere::find($id_m);
            //$cm = classeMatiere::whereIdClasse($id)->where('id_matiere', '=', $id_m)->where('id_cycle', '=', $id_cycle)->first();
            $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau]);
            $j=0;
            foreach ($e as $el){
                $id_eleves[$j]=$el->id;
                $j++;
            }
            $n = note::whereAnneeScolaire(session('year'))
                ->where('id_matiere', '=', $id_m)
                ->where('sequence', '=', $sequence)
                ->whereIn('id_eleve',$id_eleves)
                ->get();

            $tns=note::whereAnneeScolaire(\session('year'))->where('sequence','=',$cc)->where('id_matiere','=',$id_m)->whereIn('id_eleve',$id_eleves)->first();
            if($tns==null){
                session(['erreur'=>trans('admin.no_cc',['matiere'=>$mat->nom.'('.$mat->nom_anglais.')'])]);
                return redirect()->to('/eleves/notes/matieres/show_ndere?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$nniveau);
            }
            $n_cc = note::whereAnneeScolaire(session('year'))
                ->where('id_matiere', '=', $id_m)
                ->where('sequence', '=', $cc)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $values['eleves'] = $e;
            $values['notes'] = $n;
            $values['notes_cc'] = $n_cc;
            //$values['max'] = $cm->max;
            $values['matiere'] = $mat;
            $values['id_cycle'] = $id_cycle;
            $values['classe'] = classe::find($id);
            $values['nniveau']=NNiveau::find($nniveau);
            if($sequence==3)
                $values['semestre'] = 1;
            else
                $values['semestre'] = 2;
            $values['sequence'] = $sequence;

            return view('eleves/print_note_sn', $values);
        }
        if($sequence==5 || $sequence==6) {
            if($sequence==5) {
                $cc = 1;
                $sn = 3;
            }
            else {
                $cc = 2;
                $sn=4;
            }
            $mat = matiere::find($id_m);
            $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau]);
            $j=0;
            foreach ($e as $el){
                $id_eleves[$j]=$el->id;
                $j++;
            }
            $n = note::whereAnneeScolaire(session('year'))
                ->where('id_matiere', '=', $id_m)
                ->where('sequence', '=', $sequence)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_cc = note::whereAnneeScolaire(session('year'))
                ->where('id_matiere', '=', $id_m)
                ->where('sequence', '=', $cc)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $id_eleves2=$n->fetch('id_eleve')->toArray();
            $e=eleve::whereIn('id',$id_eleves2)->orderBy('nom')->orderBy('prenom')->get();


            $tns=note::whereAnneeScolaire(\session('year'))->where('sequence','=',$cc)->where('id_matiere','=',$id_m)->whereIn('id_eleve',$id_eleves)->first();
            $tnr=note::whereAnneeScolaire(\session('year'))->where('sequence','=',$sn)->where('id_matiere','=',$id_m)->whereIn('id_eleve',$id_eleves)->first();

            if($tns==null || $tnr==null){
                session(['erreur'=>trans('admin.no_note',['matiere'=>$mat->nom.'('.$mat->nom_anglais.')'])]);
                return redirect()->to('/eleves/notes/matieres/show_ndere?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$nniveau);
            }

            $values['eleves'] = $e;
            $values['notes'] = $n;
            $values['notes_cc'] = $n_cc;

            //$values['max'] = $cm->max;
            $values['matiere'] = $mat;
            $values['id_cycle'] = $id_cycle;
            $values['classe'] = classe::find($id);
            $values['nniveau']=NNiveau::find($nniveau);
            if($sequence==5)
                $values['semestre'] = 1;
            else
                $values['semestre'] = 2;
            $values['sequence'] = $sequence;

            return view('eleves/print_note_rat', $values);
        }
        // if($sequence==7 || $sequence==8) {
        if($sequence==7) {
            $semestre=1;
            $sn=3;
            $cc=1;
            $rattrap=5;
            $sequences_pv=array(1,3,5);

            $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau]);
            $j=0;
            $matieres=array();
            foreach ($e as $el){
                $id_eleves[$j]=$el->id;
                $j++;
            }

            $n_rattrap = note::whereAnneeScolaire(session('year'))
                ->where('sequence', '=', $rattrap)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_sn = note::whereAnneeScolaire(session('year'))
                ->where('sequence', '=', $sn)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_cc = note::whereAnneeScolaire(session('year'))
                ->where('sequence', '=', $cc)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $notes_sequences_pv=note::whereAnneeScolaire(\session('year'))
                ->whereIn('id_eleve',$id_eleves)
                ->whereIn('sequence',$sequences_pv)
                ->get();
            $matieres_pv=$notes_sequences_pv->fetch('id_matiere')->toArray();

            $sCoef=0;
            $cm = classeMatiere::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->where('id_cycle','=',$id_cycle)->get();
            foreach ($cm as $entry){
                $sCoef+=$entry->coef;
            }
            $matiere_list=$cm->fetch('id_matiere')->toArray();
//dump($matiere_list);
// die();

//$m=matiere::whereIn('id',$matiere_list)->whereIn('id',$matieres_pv)->get();

            $m=matiere::whereIn('matiere.id',$matiere_list)
                ->whereIn('matiere.id',$matieres_pv)
                ->Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
                ->where('classe_matiere.id_classe','=',$id)
                ->where('id_cycle','=',$id_cycle)
                ->where('classe_matiere.id_nniveau','=',$nniveau)
                ->select(['matiere.*','classe_matiere.semestre','coef','type','classe_matiere.id AS id_classe_matiere'])
                ->get();

            $lang=App::getLocale();
            $k=0;
            $gm=groupeMatieres::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->get();


            $m_cps=$gm->fetch('id_matiere_mere')->toArray();

            $calc=$gm->fetch('id_classe_matiere')->toArray();
            $m_cpst= classeMatiere::whereIn('id',$calc)->get()->fetch('id_matiere')->toArray();

            $composes= array();
            $composantes= array();
            $liste_mat= array();

            $cpstList= array();
            $minus_credit_cpst=false;
            foreach($e as $eleve){

                $l=0;
                $c=0;
                $cst=0;
                $sNote=0;
                $sCoef=0;
                foreach ($m as $matiere){

                    $cps=false;
                    $cpst=false;
                    $credit=$matiere->coef;

                    if($this->isIn($matiere->id,$m_cps)){
                        $cps=true;
                        $cpstList=$gm->where('id_matiere_mere',$matiere->id,false)->fetch('id_classe_matiere')->toArray();


                    }
                    if($this->isIn($matiere->id,$m_cpst)){
                        $cpst=true;


                    }

                    if($lang=='en')
                        $nom=$matiere->nom_anglais;
                    else
                        $nom=$matiere->nom;


//if($this->isIn($matiere->id,$m_cpst))
//  $cpst=true;



                    /*if ($cpst)
                    if(!$minus_credit_cpst) {
                    $sCoef -= $credit;
                    }*/

                    $credit=$cm->where('id_matiere',$matiere->id)
                        ->first()
                        ->coef;
                    $note_cc=$n_cc->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->first();
                    $note_sn=$n_sn->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->first();
                    $note_rattrap=$n_rattrap->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->first();


//                        $note_cc=$n_cc->where('id_matiere',$matiere->id,false)
//                            ->where('id_eleve',$eleve->id,false)
//                            ->where('sequence',1,false)
//                            ->first();
//                       /* $note_cc2=$n_cc->where('id_matiere',$matiere->id,false)
//                            ->where('id_eleve',$eleve->id,false)
//                            ->where('sequence',2,false)
//                            ->first();*/
//                        $note_sn=$n_sn->where('id_matiere',$matiere->id,false)
//                            ->where('id_eleve',$eleve->id,false)
//                            ->where('sequence',3,false)
//                            ->first();
//                        /*$note_sn2=$n_sn->where('id_matiere',$matiere->id,false)
//                            ->where('id_eleve',$eleve->id,false)
//                            ->where('sequence',4,false)
//                            ->first();*/
//                        $note_rattrap=$n_rattrap->where('id_matiere',$matiere->id,false)
//                            ->where('id_eleve',$eleve->id,false)
//                            ->where('sequence',5,false)
//                            ->first();
//                      /*  $note_rattrap2=$n_rattrap->where('id_matiere',$matiere->id,false)
//                            ->where('id_eleve',$eleve->id,false)
//                            ->where('sequence',6,false)
//                            ->first();*/
//                      //  $controle1=0;
                    $controle=0;
                    $normal=0;
//$normal2=0;
                    if($note_cc!=null || $note_sn!=null ){
                        @$note1=$note_cc->note+$note_sn->note;
                        if ($note_cc!=null) {
                            $controle= $note_cc->note;

                        }
                        else{
                            $controle='-';
                        }

                        if ($note_sn!=null) {
                            $normal=$note_sn->note;
                        }
                        else{
                            $normal='-';
                        }

                        if($note_rattrap!=null){
                            if($note1>=50){
                                $noteS1=$note1;
                                $normal1=$note_sn->note;
                            }else{
                                $noteS1=($note_cc?$note_cc->note:0)+$note_rattrap->note;
                                $normal=$note_rattrap->note;


                            }
                        }else{
                            $noteS1=$note1;
                        }
                    }else{
                        if($note_rattrap!=null){

                            $noteS1=$note_rattrap->note;
                            $normal1=$note_rattrap->note;
                            $controle='-';

                        }else{
                            $noteS1='-';
                        }

                    }

                    $note=0;
                    if(is_numeric($noteS1)){
                        $note=$noteS1;
                        $controle=$controle;
                        $normal=$normal;
                    }



                    if(is_numeric($note)){
//ajouts ISSBA
                        if( !$cps){
                            $sNote+=$note*$credit;
                            $sCoef+=$credit;
                        }
//if((!$cpst) && (!$cps))

                    }
//ajouts ISSBA
                    if( !$cps){
//if((!$cpst) && (!$cps)){
                        $matieres[$l]=new pv_matiere($matiere->id,$note,$credit,$controle,$normal,$matiere->nom);
                        if(!$minus_credit_cpst) {
                            $liste_mat[$l]=new matiere_groupe($matiere->id,$nom);
                        }
                    }
                    if(!$minus_credit_cpst) {
                        if($cpst){
                            $composantes[$cst]= new composante_pv($matiere->id,$note,$matiere->id_classe_matiere,$nom,$credit);
                            $cst++;
                        }
                        if($cps){
                            $composes[$c]= new compose_pv($matiere->id,$note,$credit,$cpstList,$nom,$credit,$matiere->type,$matiere->semestre,sizeof($cpstList));
                            $c++;

                        }

                        if((!$cpst) && (!$cps)){
                            $composantes[$cst]= new composante_pv($matiere->id,$note,$matiere->id_classe_matiere,$nom,$credit);
                            $cst++;


                            $composes[$c]= new compose_pv($matiere->id,$note,$credit,[$matiere->id_classe_matiere],$nom,$credit,$matiere->type,$matiere->semestre,1);
                            $c++;


                        }



                    }





                    $l++;
                }

                $composantes_ord=array();
                $co_n=0;

                foreach($composes as $co){
                    $co_note=0;
//$co_n=0;
                    $s_co_coef=0;
                    $lnoms='';
                    foreach ($composantes as $compt){

                        if($this->isIn($compt->id_classe_matiere,$co->cpsList)){
                            $composantes_ord[$co_n]=$compt;
                            $co_n++;
                        }
                    }
//
//                    /* if($co_n>0){
//                         $co_note=$co_note/$co_n;
//                     }*/
//                    if($s_co_coef>0){
//                        $co_note=$co_note/$s_co_coef;
//                    }
//                    // if(is_numeric($co->note))
//                    // $co_note=round(($co_note+$co->note)/2,2);
//
//
//
//                    if(sizeof($lnoms)>=1)
//                        $lnoms='';//'('.substr($lnoms,0,sizeof($lnoms)-2).')';
//
//                    $sNote+=$co_note*($co->credit);
//                    //$matieres[$l]=new pv_matiere($co->id_matiere,$co_note,$s_co_coef);
//                    if(!$minus_credit_cpst) {
//                        //$liste_mat[$l]=new matiere_groupe($co->id_matiere,$co->nom);
//                    }
//$l++;

                }


                $minus_credit_cpst=true;

                if ($sNote!=null)
                {
                    $proces_v[$k]=new PV($eleve->id,$matieres,$sNote/$sCoef);
                }
                else{
                    $proces_v[$k]=new PV($eleve->id,$matieres,0);

                }


                $k++;


            }



            $values['eleves'] = $e;
            $values['PV'] = $proces_v;
//$values['matieres'] = $m;
//$values['matieres'] = $liste_mat;
            $values['groupe_matieres'] = $composes;
            $values['matieres'] = $composantes_ord;
            $values['nb_matieres'] =sizeof($composantes_ord);
            $values['classe'] = classe::find($id);
            $values['semestre'] = $semestre;
            $values['nniveau']=NNiveau::find($nniveau);



            return view('eleves/print_pv_ndere', $values);

        }






        if($sequence==9) {
            $semestre=array(1,2);
            $sn=array(3,4);
            $cc=array(1,2);
            $rattrap=array(5,6);
            $sequences_pv=array(1,2,3,4,5,6);


            $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau]);
            $j=0;
            $matieres=array();
            foreach ($e as $el){
                $id_eleves[$j]=$el->id;
                $j++;
            }

            $n_rattrap = note::whereAnneeScolaire(session('year'))
                ->whereIn('sequence',$rattrap)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_sn = note::whereAnneeScolaire(session('year'))
                ->whereIn('sequence',$sn)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_cc = note::whereAnneeScolaire(session('year'))
                ->whereIn('sequence',$cc)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $notes_sequences_pv=note::whereAnneeScolaire(\session('year'))
                ->whereIn('id_eleve',$id_eleves)
                ->whereIn('sequence',$sequences_pv)
                ->get();


            $matieres_pv=$notes_sequences_pv->fetch('id_matiere')->toArray();
            $sCoef=0;
            $cm = classeMatiere::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->where('id_cycle','=',$id_cycle)->get();
            foreach ($cm as $entry){
                $sCoef+=$entry->coef;
            }
            $matiere_list=$cm->fetch('id_matiere')->toArray();

            //$m=matiere::whereIn('id',$matiere_list)->whereIn('id',$matieres_pv)->get();
            $m=matiere::whereIn('matiere.id',$matiere_list)
                ->Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
                //->Join('groupe_matieres','classe_matiere.id','=','groupe_matieres.id_classe_matiere')
                ->where('classe_matiere.id_classe','=',$id)
                // ->where('groupe_matieres.id_classe','=',$id)
                // ->where('groupe_matieres.id_nniveau','=',$nniveau)
                ->where('id_cycle','=',$id_cycle)
                ->where('classe_matiere.id_nniveau','=',$nniveau)
                ->select(['matiere.*','classe_matiere.semestre','coef','type','classe_matiere.id AS id_classe_matiere'])
                //->orderBy('groupe_matieres.id')
                ->get();
            /*   $matiere_mere=matiere::whereIn('matiere.id',$gm->fetch('id_matiere_mere')->toArray())
                   ->Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
                   ->Join('groupe_matieres','matiere.id','=','groupe_matieres.id_matiere_mere')
                   ->where('classe_matiere.id_classe','=',$id)
                   ->where('groupe_matieres.id_classe','=',$id)
                   ->where('groupe_matieres.id_nniveau','=',$nniveau)
                   ->where('id_cycle','=',$id_cycle)
                   ->where('classe_matiere.id_nniveau','=',$nniveau)
                   ->orderBy('groupe_matieres.id')
                   ->get(['matiere.*','classe_matiere.semestre','type','id_matiere_mere','classe_matiere.id AS id_classe_matieres']);
               dump($m);
               dump($matiere_mere);
               die();*/


            $lang=App::getLocale();
            $k=0;
            $gm=groupeMatieres::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->get();


            $m_cps=$gm->fetch('id_matiere_mere')->toArray();


            $calc=$gm->fetch('id_classe_matiere')->toArray();
            $m_cpst= classeMatiere::whereIn('id',$calc)->get()->fetch('id_matiere')->toArray();

            $composes= array();
            $composantes= array();
            $liste_mat= array();

            $cpstList= array();
            $minus_credit_cpst=false;
            foreach($e as $eleve){

                $l=0;
                $c=0;
                $cst=0;
                $sNote=0;
                $sCoef=0;
                foreach ($m as $matiere){

                    $cps=false;
                    $cpst=false;
                    $credit=$matiere->coef;

                    if($this->isIn($matiere->id,$m_cps)){
                        $cps=true;
                        $cpstList=$gm->where('id_matiere_mere',$matiere->id,false)->fetch('id_classe_matiere')->toArray();


                    }
                    if($this->isIn($matiere->id,$m_cpst)){
                        $cpst=true;


                    }

                    if($lang=='en')
                        $nom=$matiere->nom_anglais;
                    else
                        $nom=$matiere->nom;


                    //if($this->isIn($matiere->id,$m_cpst))
                    //  $cpst=true;



                    /*if ($cpst)
                        if(!$minus_credit_cpst) {
                            $sCoef -= $credit;
                        }*/


                    $note_cc1=$n_cc->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',1,false)
                        ->first();
                    $note_cc2=$n_cc->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',2,false)
                        ->first();
                    $note_sn1=$n_sn->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',3,false)
                        ->first();
                    $note_sn2=$n_sn->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',4,false)
                        ->first();
                    $note_rattrap1=$n_rattrap->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',5,false)
                        ->first();
                    $note_rattrap2=$n_rattrap->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',6,false)
                        ->first();
                    $controle1=0;
                    $controle2=0;
                    $normal1=0;
                    $normal2=0;
                    if($note_cc1!=null || $note_sn1!=null ){
                        @$note1=$note_cc1->note+$note_sn1->note;
                        if ($note_cc1!=null) {
                            $controle1= $note_cc1->note;

                        }
                        else{
                            $controle1='-';
                        }

                        if ($note_sn1!=null) {
                            $normal1=$note_sn1->note;
                        }
                        else{
                            $normal1='-';
                        }

                        if($note_rattrap1!=null){
                            if($note1>=50){
                                $noteS1=$note1;
                                $normal1=$note_sn1->note;
                            }else{
                                $noteS1=($note_cc1?$note_cc1->note:0)+$note_rattrap1->note;
                                $normal1=$note_rattrap1->note;


                            }
                        }else{
                            $noteS1=$note1;
                        }
                    }else{
                        if($note_rattrap1!=null){

                            $noteS1=$note_rattrap1->note;
                            $normal1=$note_rattrap1->note;
                            $controle1='-';

                        }else{
                            $noteS1='-';
                        }

                    }
                    if($note_cc2!=null || $note_sn2!=null ){
                        @$note1=$note_cc2->note+$note_sn2->note;

                        if ($note_cc2!=null) {
                            $controle2 = $note_cc2->note;
                        }
                        else{
                            $controle2='-';
                        }
                        if ($note_sn2!=null) {
                            $normal2=$note_sn2->note;
                        }
                        else{
                            $normal2='-';
                        }

                        if($note_rattrap2!=null){
                            if($note1>=50){
                                $noteS2=$note1;
                                $normal2=$note_sn2->note;
                            }else{
                                $noteS2=($note_cc2?$note_cc2->note:0)+$note_rattrap2->note;
                                $normal2=$note_rattrap2->note;
                            }
                        }else{
                            $noteS2=$note1;
                        }
                    }else{
                        if($note_rattrap2!=null){
                            $noteS2=$note_rattrap2->note;
                            $normal2=$note_rattrap2->note;
                            $controle2='-';
                        }else{
                            $noteS2='-';
                        }

                    }
                    $note=0;
                    if(is_numeric($noteS1) && is_numeric($noteS2)){
                        $note=($noteS1+$noteS2)/2;
                        $controle=($controle1+$controle2)/2;
                        $normal=($normal1+$normal2)/2;
                    }elseif (!is_numeric($noteS1)){
                        $note=$noteS2;
                        $controle=$controle2;
                        $normal=$normal2;


                    }elseif (!is_numeric($noteS2)){
                        $note=$noteS1;
                        $controle=$controle1;
                        $normal=$normal1;
                    }



                    if(is_numeric($note)){
                        //ajouts ISSBA
                        if( !$cps){
                            $sNote+=$note*$credit;
                            $sCoef+=$credit;
                        }
                        //if((!$cpst) && (!$cps))

                    }
                    //ajouts ISSBA
                    if( !$cps){
                        //if((!$cpst) && (!$cps)){
                        $matieres[$l]=new pv_matiere($matiere->id,$note,$credit,$controle,$normal,$matiere->nom);

                        if(!$minus_credit_cpst) {
                            $liste_mat[$l]=new matiere_groupe($matiere->id,$nom);

                        }

                    }
                    if(!$minus_credit_cpst) {
                        if($cpst){
                            $composantes[$cst]= new composante_pv($matiere->id,$note,$matiere->id_classe_matiere,$nom,$credit);
                            $cst++;


                        }

                        if($cps){
                            $composes[$c]= new compose_pv($matiere->id,$note,$credit,$cpstList,$nom,$credit,$matiere->type,$matiere->semestre,sizeof($cpstList));
                            $c++;

                        }

                        if((!$cpst) && (!$cps)){
                            $composantes[$cst]= new composante_pv($matiere->id,$note,$matiere->id_classe_matiere,$nom,$credit);
                            $cst++;


                            $composes[$c]= new compose_pv($matiere->id,$note,$credit,[$matiere->id_classe_matiere],$nom,$credit,$matiere->type,$matiere->semestre,1);
                            $c++;


                        }



                    }





                    $l++;
                }

                $composantes_ord=array();
                $co_n=0;

                foreach($composes as $co){
                    $co_note=0;
                    //$co_n=0;
                    $s_co_coef=0;
                    $lnoms='';
                    foreach ($composantes as $compt){

                        if($this->isIn($compt->id_classe_matiere,$co->cpsList)){
                            $composantes_ord[$co_n]=$compt;
                            $co_n++;
                        }
                    }
//
//                    /* if($co_n>0){
//                         $co_note=$co_note/$co_n;
//                     }*/
//                    if($s_co_coef>0){
                    //                        $co_note=$co_note/$s_co_coef;
//                    }
//                    // if(is_numeric($co->note))
//                    // $co_note=round(($co_note+$co->note)/2,2);
//
//
//
//                    if(sizeof($lnoms)>=1)
//                        $lnoms='';//'('.substr($lnoms,0,sizeof($lnoms)-2).')';
//
//                    $sNote+=$co_note*($co->credit);
//                    //$matieres[$l]=new pv_matiere($co->id_matiere,$co_note,$s_co_coef);
//                    if(!$minus_credit_cpst) {
//                        //$liste_mat[$l]=new matiere_groupe($co->id_matiere,$co->nom);
//                    }
                    //$l++;

                }


                $minus_credit_cpst=true;

                if ($sNote!=null)
                {
                    $proces_v[$k]=new PV($eleve->id,$matieres,$sNote/$sCoef);

                }
                else{
                    $proces_v[$k]=new PV($eleve->id,$matieres,0);

                }


                $k++;
//                dump($proces_v);
//                die();


            }



            $values['eleves'] = $e;
            $values['PV'] = $proces_v;
            //$values['matieres'] = $m;
            //$values['matieres'] = $liste_mat;
            $values['groupe_matieres'] = $composes;
            $values['matieres'] = $composantes_ord;
            $values['nb_matieres'] =sizeof($composantes_ord);
            $values['classe'] = classe::find($id);
            $values['semestre'] = $semestre;
            $values['nniveau']=NNiveau::find($nniveau);

            //dump($values);
            //die();
            return view('eleves/print_bilan_ndere', $values);
        }
    }






    public function print_rattrapage(Request $request,$id)
    {
        $this->validate($request,[
            'sequence'=>'required|numeric',
            'cycle'=>'required|numeric',
            'nniveau'=>'required|numeric',
        ]);

        $values= array('titre'=>'Notes');
        $sequence=$request->input('sequence');
        $id_m=$request->input('id');
        $nniveau=$request->input('nniveau');
        $id_cycle=$request->input('cycle');

        if(10) {
            $semestre=array(1,2);
            $sn=array(3,4);
            $cc=array(1,2);
            $rattrap=array(5,6);
            $sequences_pv=array(1,2,3,4,5,6);


            $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau]);
            $j=0;
            $matieres=array();
            foreach ($e as $el){
                $id_eleves[$j]=$el->id;
                $j++;
            }

            $n_rattrap = note::whereAnneeScolaire(session('year'))
                ->whereIn('sequence',$rattrap)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_sn = note::whereAnneeScolaire(session('year'))
                ->whereIn('sequence',$sn)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $n_cc = note::whereAnneeScolaire(session('year'))
                ->whereIn('sequence',$cc)
                ->whereIn('id_eleve',$id_eleves)
                ->get();
            $notes_sequences_pv=note::whereAnneeScolaire(\session('year'))
                ->whereIn('id_eleve',$id_eleves)
                ->whereIn('sequence',$sequences_pv)
                ->get();


            $matieres_pv=$notes_sequences_pv->fetch('id_matiere')->toArray();
            $sCoef=0;
            $cm = classeMatiere::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->where('id_cycle','=',$id_cycle)->get();
            foreach ($cm as $entry){
                $sCoef+=$entry->coef;
            }
            $matiere_list=$cm->fetch('id_matiere')->toArray();
            //$m=matiere::whereIn('id',$matiere_list)->whereIn('id',$matieres_pv)->get();
            $m=matiere::whereIn('matiere.id',$matiere_list)
                ->Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
                ->where('id_classe','=',$id)
                ->where('id_cycle','=',$id_cycle)
                ->where('classe_matiere.id_nniveau','=',$nniveau)
                ->select(['matiere.*','semestre','type','classe_matiere.id AS id_classe_matiere'])
                ->get();
            $lang=App::getLocale();
            $k=0;

            $gm=groupeMatieres::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->get();
            $m_cps=$gm->fetch('id_matiere_mere')->toArray();

            $calc=$gm->fetch('id_classe_matiere')->toArray();
            $m_cpst= classeMatiere::whereIn('id',$calc)->get()->fetch('id_matiere')->toArray();

            $composes= array();
            $composantes= array();
            $liste_mat= array();

            $cpstList= array();
            $minus_credit_cpst=false;
            foreach($e as $eleve){

                $l=0;
                $c=0;
                $cst=0;
                $sNote=0;
                $sCoef=0;
                foreach ($m as $matiere){

                    $cps=false;
                    $cpst=false;

                    $credit=$cm->where('id_matiere',$matiere->id,false)
                        ->first()
                        ->coef;

                    if($this->isIn($matiere->id,$m_cps)){
                        $cps=true;
                        $cpstList=$gm->where('id_matiere_mere',$matiere->id,false)->fetch('id_classe_matiere')->toArray();


                    }

                    if($lang=='en')
                        $nom=$matiere->nom_anglais;
                    else
                        $nom=$matiere->nom;


                    //if($this->isIn($matiere->id,$m_cpst))
                    //  $cpst=true;



                    /*if ($cpst)
                        if(!$minus_credit_cpst) {
                            $sCoef -= $credit;
                        }*/


                    $note_cc1=$n_cc->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',1,false)
                        ->first();
                    $note_cc2=$n_cc->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',2,false)
                        ->first();
                    $note_sn1=$n_sn->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',3,false)
                        ->first();
                    $note_sn2=$n_sn->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',4,false)
                        ->first();
                    $note_rattrap1=$n_rattrap->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',5,false)
                        ->first();


                    $note_rattrap2=$n_rattrap->where('id_matiere',$matiere->id,false)
                        ->where('id_eleve',$eleve->id,false)
                        ->where('sequence',6,false)
                        ->first();
                    $controle1=0;
                    $controle2=0;
                    $normal1=0;
                    $normal2=0;
                    $noteS1=0;
                    $noteS2=0;
                    if($note_cc1!=null || $note_rattrap1!=null ){
                        @$note1=$note_cc1->note+$note_rattrap1->note;

                        if ($note_cc1!=null) {
                            $controle1= $note_cc1->note;

                        }
                        else{
                            $controle1='-';
                        }

                        if ($note_rattrap1!=null) {
                            $normal1=$note_rattrap1->note;
                        }
                        else{
                            $normal1='-';
                        }
                        $noteS1=$note1;
                    }
                    if($note_cc2!=null || $note_rattrap2!=null ){
                        @$note2=$note_cc2->note+$note_rattrap2->note;

                        if ($note_cc2!=null) {
                            $controle2 = $note_cc2->note;
                        }
                        else{
                            $controle2='-';
                        }
                        if ($note_rattrap2!=null) {
                            $normal2=$note_rattrap2->note;
                        }
                        else{
                            $normal2='-';
                        }
                        $noteS2=$note2;
                    }
                    $note=0;
                    if(is_numeric($noteS1) && is_numeric($noteS2)){
                        $note=($noteS1+$noteS2)/2;
                        $controle=($controle1+$controle2)/2;
                        $normal=($normal1+$normal2)/2;
                    }elseif (!is_numeric($noteS2)){
                        $note=$noteS2;
                        $controle=$controle2;
                        $normal=$normal2;


                    }elseif (!is_numeric($noteS1)){
                        $note=$noteS1;
                        $controle=$controle1;
                        $normal=$normal1;
                    }



                    if(is_numeric($note)){
                        //ajouts ISSBA
                        if( !$cps){
                            $sNote+=$note*$credit;
                            $sCoef+=$credit;
                        }
                        //if((!$cpst) && (!$cps))

                    }
                    //ajouts ISSBA
                    if( !$cps){
                        //if((!$cpst) && (!$cps)){
                        $matieres[$l]=new pv_matiere($matiere->id,$note,$credit,$normal,$controle,$matiere->nom);
                        if(!$minus_credit_cpst) {
                            $liste_mat[$l]=new matiere_groupe($matiere->id,$nom);
                        }
                    }
                    /*if($cpst){
                        $composantes[$cst]= new composante($matiere->id,$note,$matiere->id_classe_matiere,$nom,$credit);
                        $cst++;
                    }*/
                    /*if($cps){
                        $composes[$c]= new compose($matiere->id,$note,$credit,$cpstList,$nom,$credit,$matiere->type,$matiere->semestre);
                        $c++;
                    }*/




                    $l++;
                }


                foreach($composes as $co){
                    $co_note=0;
                    $co_n=0;
                    $s_co_coef=0;
                    $lnoms='';
                    foreach ($composantes as $compt){

                        if($this->isIn($compt->id_classe_matiere,$co->cpsList)){
                            $co_note+=$compt->note*$compt->credit;
                            //$lnoms.=$compt->nom.' ,';
                            $s_co_coef+=$compt->credit;
                            $co_n++;
                        }
                    }

                    /* if($co_n>0){
                         $co_note=$co_note/$co_n;
                     }*/
                    if($s_co_coef>0){
                        $co_note=$co_note/$s_co_coef;
                    }
                    // if(is_numeric($co->note))
                    // $co_note=round(($co_note+$co->note)/2,2);



                    if(sizeof($lnoms)>=1)
                        $lnoms='';//'('.substr($lnoms,0,sizeof($lnoms)-2).')';

                    $sNote+=$co_note*($co->credit);
                    //$matieres[$l]=new pv_matiere($co->id_matiere,$co_note,$s_co_coef);
                    if(!$minus_credit_cpst) {
                        //$liste_mat[$l]=new matiere_groupe($co->id_matiere,$co->nom);
                    }
                    $l++;

                }
                $minus_credit_cpst=true;

                if ($sNote!=null)
                {
                    $proces_v[$k]=new PV($eleve->id,$matieres,$sNote/$sCoef);
                }
                else{
                    $proces_v[$k]=new PV($eleve->id,$matieres,'-');

                }


                $k++;


            }



            $values['eleves'] = $e;
            $values['PV'] = $proces_v;
            //$values['matieres'] = $m;
            $values['matieres'] = $liste_mat;
            $values['nb_matieres'] =sizeof($liste_mat);
            $values['classe'] = classe::find($id);
            $values['semestre'] = $semestre;
            $values['nniveau']=NNiveau::find($nniveau);


            return view('eleves/print_bilan_ndere', $values);
        }
    }



    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'id_matiere'=>'required|numeric',
            'sequence'=>'required|numeric',
            'id_classe'=>'required|numeric',
            'nniveau'=>'required|numeric',
        ]);
        $id_cycle=$request->input('cycle');
        $id_nniveau=$request->input('nniveau');
        $id_matiere=$request->input('id_matiere');
        $sequence=$request->input('sequence');
        $id_classe=$request->input('id_classe');

        $eleveList=$request->input('eleve');
        $noteList=$request->input('note');
        $i=0;
        if($eleveList==null){
            session(['success'=>true]);

            return redirect()->to('/eleves/notes/matieres_ndere?id='.$id_classe.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau);
        }
        foreach ($eleveList as $e){
            $n=note::firstOrNew(['annee_scolaire'=>session('year'),'id_matiere'=>$id_matiere,'sequence'=>$sequence,'id_eleve'=>$e]);

            $n->id_matiere=$id_matiere;
            $n->sequence=$sequence;
            $n->id_eleve=$e;
            $n->annee_scolaire=session('year');
            $n->note=$noteList[$i];
            $n->save();

            $i++;
        }

        session(['success'=>true]);

        return redirect()->to('/eleves/notes/matieres_ndere?id='.$id_classe.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show()
    {
        $values= array('titre'=>'Notes');
        $c=classe::orderBy('nom')->get();
        if(session('success')){
            $values['success']=session('success');
            Session::forget('success');
        }
        $cy=cycle::all();
        $n=niveau::all();
        $nn=NNiveau::all();

        $values['niveaux']=$n;
        $values['classes']=$c;
        $values['cycles']=$cy;
        $values['nniveaux']=$nn
        ;

        return view('eleves/classe_note_show_ndere',$values);
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

    function isIn($val,$array)
    {
        $n=sizeof($array);
        for($i=0;$i<$n;$i++){
            if($val==$array[$i])
                return true;
        }
        return false;
    }

    function getGrade($note){
        if($note>=80){
            $grade[0]='A';
            $grade[1]=4;
            return $grade;
        }
        if($note>=75){
            $grade[0]='A-';
            $grade[1]=3.7;
            return $grade;
        }
        if($note>=70){
            $grade[0]='B+';
            $grade[1]=3.3;
            return $grade;
        }
        if($note>=65){
            $grade[0]='B';
            $grade[1]=3;
            return $grade;
        }
        if($note>=60){
            $grade[0]='B-';
            $grade[1]=2.7;
            return $grade;
        }
        if($note>=55){
            $grade[0]='C+';
            $grade[1]=2.3;
            return $grade;
        }
        if($note>=50){
            $grade[0]='C';
            $grade[1]=2;
            return $grade;
        }

        if($note>=45){
            $grade[0]='C-';
            $grade[1]=1.7;
            return $grade;
        }
        if($note>=40){
            $grade[0]='D+';
            $grade[1]=1.3;
            return $grade;
        }
        if($note>=35){
            $grade[0]='D';
            $grade[1]=1;
            return $grade;
        }
        if($note>=30){
            $grade[0]='E';
            $grade[1]=0;
            return $grade;
        }
        $grade[0]='F';
        $grade[1]=0;
        return $grade;

    }

}
class PV{
    public $id_eleve;
    public $matieres;
    public $mgp;
    public  function __construct($id_e,$ma,$mg)
    {
        $this->id_eleve=$id_e;
        $this->matieres=$ma;
        $this->mgp=$mg;

    }
}

class pv_matiere{
    public $id_matiere;
    public $note;
    public $credit;
    public $controle;
    public $normal;
    public $nom_matiere;

    public  function __construct($id_m,$not,$cr,$con,$nor,$n_m)
    {
        $this->id_matiere=$id_m;
        $this->note=$not;
        $this->credit=$cr;
        $this->controle=$con;
        $this->normal=$nor;
        $this->nom_matiere=$n_m;
    }


}

class matiere_groupe
{
    public $id;
    public $nom;

    /**
     * matiere_groupe constructor.
     * @param $id
     * @param $nom
     */
    public function __construct($id, $nom)
    {
        $this->id = $id;
        $this->nom = $nom;
    }


}

class composante_pv{
    public $id;
    public $note;
    public $id_classe_matiere;
    public $nom;
    public $credit;
    public $groupe_matiere;

    public function __construct($id,$n,$icm,$no,$credit)
    {
        $this->id=$id;
        $this->note=$n;
        $this->id_classe_matiere=$icm;
        $this->nom=$no;
        $this->credit=$credit;
    }
}
class compose_pv{
    public $id;
    public $note;
    public $coef;
    public $cpsList;
    public $nom;
    public $credit;
    public $type;
    public $semestre;
    public $nbComposes;


    public function __construct($id,$n,$c,$cl,$no,$cr,$ty,$se,$nbC)
    {
        $this->id=$id;
        $this->note=$n;
        $this->coef=$c;
        $this->cpsList=$cl;
        $this->nom=$no;
        $this->credit=$cr;
        $this->type=$ty;
        $this->semestre=$se;
        $this->nbComposes=$nbC;
    }
}
