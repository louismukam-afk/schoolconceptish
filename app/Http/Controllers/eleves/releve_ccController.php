<?php namespace gesecole\Http\Controllers\eleves;

use gesecole\eleve;
use gesecole\groupeMatieres;
use gesecole\Http\Requests;
use gesecole\Http\Controllers\Controller;
use gesecole\NNiveau;
use Illuminate\Support\Facades\Session;
use gesecole\parametres;
use gesecole\classe;
use gesecole\niveau;
use gesecole\cycle;
use Illuminate\Support\Facades\DB;
use gesecole\note;
use gesecole\classeMatiere;
use gesecole\matiere;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Array_;

class releve_ccController extends Controller {

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
        $values= array('titre'=>trans('admin.releve_notes'));
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

        return view('eleves/classe_releve_cc',$values);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $id=$request->input('id');
        $this->validate($request,[
            "cycle"=>"required|numeric",
            "nniveau"=>"required|numeric",
        ]);
        $proces_v=[];
        $id_cycle=$request->input('cycle');
        $nniveau=$request->input('nniveau');
        $values= array('titre'=>trans('admin.releve_notes'));
        $id_eleves=array();
        $semestre=array(1);
        $sn=array(3);
        $cc=array(1);
       // $cc=1;
        $rattrap=array(5);
     //
        $sequences_pv=array(1,2,3);
        //$semestre_matiere = array(1,3,4,5,6);


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
      /*  $m=matiere::Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
            //->Join('groupe_matieres','classe_matiere.id','=','groupe_matieres.id_classe_matiere')
            ->where('classe_matiere.id_classe','=',$id)
            // ->where('groupe_matieres.id_classe','=',$id)
            // ->where('groupe_matieres.id_nniveau','=',$nniveau)
            ->where('id_cycle','=',$id_cycle)
            ->where('classe_matiere.id_nniveau','=',$nniveau)
          //  ->whereIn('classe_matiere.semestre')
            ->select(['matiere.*','classe_matiere.semestre','coef','type','classe_matiere.id AS id_classe_matiere'])
            //->orderBy('groupe_matieres.id')
            ->get();*/

        //$m=matiere::whereIn('id',$matiere_list)->whereIn('id',$matieres_pv)->get();
       $m=matiere::whereIn('matiere.id',$matiere_list)
            ->Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
           // ->join('codages','codages.id','=','classe_matiere.id_code'   )
            ->where('id_classe','=',$id)
            ->where('id_cycle','=',$id_cycle)
            ->where('classe_matiere.id_nniveau','=',$nniveau)
            ->select(['matiere.*','id_code','semestre','type','classe_matiere.id AS id_classe_matiere'])

            ->get();

        $k=0;
        $gm=groupeMatieres::whereIdClasse($id)->where('id_nniveau','=',$nniveau)->get();
        $m_cps=$gm->fetch('id_matiere_mere')->toArray();

        $calc=$gm->fetch('id_classe_matiere')->toArray();
        $m_cpst= classeMatiere::whereIn('id',$calc)->get()->fetch('id_matiere')->toArray();

        $composes= array();
        $composantes= array();
        $cpstList= array();
        $minus_credit_cpst=false;

        foreach($e as $eleve) {

            $l = 0;
            $c = 0;
            $cst = 0;
            $sNote = 0;
            foreach ($m as $matiere) {
                $cps = false;
                $cpst = false;

                if ($this->isIn($matiere->id, $m_cps)) {
                    $cps = true;
                    $cpstList = $gm->where('id_matiere_mere', $matiere->id, false)->fetch('id_classe_matiere')->toArray();


                }
                $nom = \App::getLocale() == 'en' ? $matiere->nom_anglais : $matiere->nom;
                if ($this->isIn($matiere->id, $m_cpst))
                    $cpst = true;

                $credit = $cm->where('id_matiere', $matiere->id)
                    ->first()
                    ->coef;
                if ($cpst)
                    if (!$minus_credit_cpst) {
                        $sCoef -= $credit;
                    }

               $note_cc1 = $n_cc->where('id_matiere', $matiere->id, false)
                    ->where('id_eleve', $eleve->id, false)
                    ->where('sequence', 1, false)
                    ->first();

               /*dump($m1);
                die();*/



                $note_cc2 = $n_cc->where('id_matiere', $matiere->id, false)
                    ->where('id_eleve', $eleve->id, false)
                    ->where('sequence', 2, false)
                    ->first();

                $note_sn1 = $n_sn->where('id_matiere', $matiere->id, false)
                    ->where('id_eleve', $eleve->id, false)
                    ->where('sequence', 3, false)
                    ->first();
                $note_sn2 = $n_sn->where('id_matiere', $matiere->id, false)
                    ->where('id_eleve', $eleve->id, false)
                    ->where('sequence', 4, false)
                    ->first();
                $note_rattrap1 = $n_rattrap->where('id_matiere', $matiere->id, false)
                    ->where('id_eleve', $eleve->id, false)
                    ->where('sequence', 5, false)
                    ->first();
                $note_rattrap2 = $n_rattrap->where('id_matiere', $matiere->id, false)
                    ->where('id_eleve', $eleve->id, false)
                    ->where('sequence', 6, false)
                    ->first();


                if ($note_cc1 != null || $note_sn1 != null) {
                  //  @$note1 = $note_cc1->note + $note_sn1->note;
                   @$note1 = $note_cc1->note;
                    if ($note_rattrap1 != null) {
                        if ($note1 >= 50) {
                            $noteS1 = $note1;
                        } else {
                           // $noteS1 = ($note_cc1 ? $note_cc1->note : 0) + $note_rattrap1->note;
                            $noteS1 = ($note_cc1 ? $note_cc1->note : 0);
                        }
                    } else {
                        $noteS1 = $note1;
                    }
                } else {
                    if ($note_rattrap1 != null) {

                        $noteS1 = $note_cc1->note;

                    } else {
                        $noteS1 = '-';
                    }

                }
               /* if ($note_cc2 != null || $note_sn2 != null) {
                    @$note1 = $note_cc2->note + $note_sn2->note;
                    if ($note_rattrap2 != null) {
                        if ($note1 >= 50) {
                            $noteS2 = $note1;
                        } else {
                            $noteS2 = ($note_cc2 ? $note_cc2->note : 0) + $note_rattrap2->note;
                        }
                    } else {
                        $noteS2 = $note1;
                    }
                } else {
                    if ($note_rattrap2 != null) {

                        $noteS2 = $note_rattrap2->note;

                    } else {
                        $noteS2 = '-';
                    }

                }*/
                $note = 0;
                //if (is_numeric($noteS1) && is_numeric($noteS2)) {
                if (is_numeric($noteS1)) {
                    $note = ($noteS1);

                }


                if (is_numeric($note)) {
                    if ((!$cpst) && (!$cps))
                        $sNote += ($note * $credit);

                }


                if ((!$cpst) && (!$cps)) {
                    $matieres[$l] = new ReleveCcMatiere($matiere->id, $note, $credit, $nom, $matiere->type, $matiere->semestre, $matiere->id_code);

                }

                if ($cpst) {
                    $composantes[$cst] = new ReleveCcComposante($matiere->id, $note, $matiere->id_classe_matiere, $nom, $credit, $matiere->type, $matiere->semestre, $matiere->id_code);
                    $cst++;

                }


                if ($cps) {
                    $composes[$c] = new ReleveCcCompose($matiere->id, $note, $credit, $cpstList, $nom, $credit, $matiere->type, $matiere->semestre, $matiere->id_code);
                    $c++;

                }


                $l++;

            }
           /*dump($composantes);
            die();*/
            $minus_credit_cpst = true;

            foreach ($composantes as $compt) {
                $co_note = 0;
                $co_n = 0;
                $s_co_coef = 0;
                $lnoms = '';
                $co_note += $compt->note;
                $compt->nom;
                $s_co_coef += $compt->credit;
                $co_n++;


            

            if ($s_co_coef > 0) {
                //$co_note=round($co_note/$s_co_coef,2);
//                    dump($co_note);
//                    die();
                $co_note = $co_note / $co_n;


            }

            /* if($co_n>0){
                 $co_note=$co_note/$co_n;
             }*/


            // if(is_numeric($co->note))
            // $co_note=round(($co_note+$co->note)/2,2);


            if (sizeof($lnoms) >= 1)
                $lnoms = '(' . substr($lnoms, 0, sizeof($lnoms) - 2) . ')';


            $sNote += ($co_note * $compt->credit);
            // $sNote+=$co_note;

            $matieres[$l] = new ReleveCcMatiere($compt->id_matiere, $co_note, $s_co_coef, $compt->nom, $compt->type, $compt->semestre, $compt->id_code);
            $l++;

        }

///fini ici pour la fermeture des groupes matières

            if($sCoef==0) $sCoef=1;
            $proces_v[$k]=new ReleveCcPV($eleve->id,$matieres,$sNote/$sCoef);
        /*   dump($proces_v);
          die();*/

            $k++;

        }
       /* dump($proces_v);
        die();*/

        $classe=classe::find($id);
        $values['eleves'] = $e;
        $values['PV'] = $proces_v;
        $values['matieres'] = $m;
        $values['nb_matieres'] = $m->count();
        $values['classe'] = $classe;
        $values['cycle']= cycle::find($id_cycle);
        $values['nniveau']=NNiveau::find($nniveau);
        $values['niveau']= niveau::find($classe->id_niveau);
        $values['semestre'] = $semestre;

        return view('eleves/print_releve_cc', $values);


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
class ReleveCcPV{
    public $eleve;
    public $matieres;
    public $mgp;
    public  function __construct($id_e,$ma,$mg)
    {
        $this->eleve=eleve::find($id_e);
        $this->matieres=$ma;
        $this->mgp=$mg;

    }
}
class ReleveCcMatiere{
    public $id_matiere;
    public $note;
    public $credit;
    public $altName;
    public $type;
    public $semestre;
    public $id_code;


    public  function __construct($id_m,$not,$cr,$an,$ty,$se,$co)
    {
        $this->id_matiere=$id_m;
        $this->note=$not;
        $this->credit=$cr;
        $this->altName=$an;
        $this->type=$ty;
        $this->semestre=$se;
        $this->id_code=$co;


    }

}

class ReleveCcComposante{
    public $id_matiere;
    public $note;
    public $id_classe_matiere;
    public $nom;
    public $credit;
    public $type;
    public $semestre;
    public $id_code;
    public function __construct($id,$n,$icm,$no,$credit,$tys,$se,$co)
    {
        $this->id_matiere=$id;
        $this->note=$n;
        $this->id_classe_matiere=$icm;
        $this->nom=$no;
        $this->credit=$credit;
        $this->type=$tys;
        $this->semestre=$se;
        $this->id_code=$co;
    }
}
class ReleveCcCompose{
    public $id_matiere;
    public $note;
    public $coef;
    public $cpsList;
    public $nom;
    public $credit;
    public $type;
    public $semestre;
    public $id_code;

    public function __construct($id,$n,$c,$cl,$no,$cr,$ty,$se,$co)
    {
        $this->id_matiere=$id;
        $this->note=$n;
        $this->coef=$c;
        $this->cpsList=$cl;
        $this->nom=$no;
        $this->credit=$cr;
        $this->type=$ty;
        $this->semestre=$se;
        $this->id_code=$co;
    }
}
