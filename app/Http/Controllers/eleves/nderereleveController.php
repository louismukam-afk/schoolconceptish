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
use gesecole\TutelleAcademique;
use gesecole\ReleveNoteGeneration;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\Array_;

class nderereleveController extends Controller {

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

        return view('eleves/classe_releves_ndere',$values);
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
        $nniveauModel = NNiveau::find($nniveau);
        $niveauUniversitaire = $this->universityLevel($nniveauModel);
        $semesterPairs = array(1 => array(1, 2), 2 => array(3, 4), 3 => array(5, 6), 4 => array(7, 8), 5 => array(8, 9));
        $trimestre = (int) $request->input('trimestre', $semesterPairs[$niveauUniversitaire][0]);
        if (!in_array($trimestre, $semesterPairs[$niveauUniversitaire])) {
            $trimestre = $semesterPairs[$niveauUniversitaire][0];
        }
        $baseSemestre = $trimestre % 2 ? 1 : 2;
        $values= array('titre'=>trans('admin.releve_notes'));
        $id_eleves=array();
        $semestre=array(1,2);
        $sn=array(3,4);
        $cc=array(1,2);
        $rattrap=array(5,6);
     //
        $sequences_pv=array(1,2,3);
        $semestre_matiere = array($trimestre);


        $e = DB::select('SELECT e.* FROM eleve e,inscription i WHERE i.annee_scolaire=? AND i.id_classe=? AND i.id_eleve=e.id AND i.id_cycle=? AND i.id_nniveau=? AND (i.id_campus = ? OR i.id_campus IS NULL) ORDER BY e.nom,e.prenom', [session('year'), $id, $id_cycle,$nniveau, $this->currentCampusId()]);
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
        $m=matiere::Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
            ->leftJoin('codages', DB::raw('codages.id::text'), '=', 'classe_matiere.id_code')
            //->Join('groupe_matieres','classe_matiere.id','=','groupe_matieres.id_classe_matiere')
            ->where('classe_matiere.id_classe','=',$id)
            // ->where('groupe_matieres.id_classe','=',$id)
            // ->where('groupe_matieres.id_nniveau','=',$nniveau)
            ->where('id_cycle','=',$id_cycle)
            ->where('classe_matiere.id_nniveau','=',$nniveau)
            ->whereIn('classe_matiere.semestre',$semestre_matiere)
            ->select(['matiere.*','classe_matiere.semestre','coef','type','classe_matiere.id AS id_classe_matiere','classe_matiere.id_code AS classe_matiere_id_code','codages.nom AS codage_nom'])
            //->orderBy('groupe_matieres.id')
            ->get();
        $sCoef = 0;
        foreach ($m as $selectedMatiere) {
            $sCoef += $selectedMatiere->coef;
        }

        //$m=matiere::whereIn('id',$matiere_list)->whereIn('id',$matieres_pv)->get();
      /*  $m=matiere::whereIn('matiere.id',$matiere_list)
            ->Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
            //->join('codages','codages.id','=','classe_matiere.id_code'   )
            ->where('id_classe','=',$id)
            ->where('id_cycle','=',$id_cycle)
            ->where('classe_matiere.id_nniveau','=',$nniveau)
            ->select(['matiere.*','id_code','semestre','type','classe_matiere.id AS id_classe_matiere'])

            ->get();*/

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
            $matieres = array();
            $composes = array();
            $composantes = array();
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
                    @$note1 = $note_cc1->note + $note_sn1->note;
                    if ($note_rattrap1 != null) {
                        if ($note1 >= 50) {
                            $noteS1 = $note1;
                        } else {
                            $noteS1 = ($note_cc1 ? $note_cc1->note : 0) + $note_rattrap1->note;
                        }
                    } else {
                        $noteS1 = $note1;
                    }
                } else {
                    if ($note_rattrap1 != null) {

                        $noteS1 = $note_rattrap1->note;

                    } else {
                        $noteS1 = '-';
                    }

                }
                if ($note_cc2 != null || $note_sn2 != null) {
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

                }
                $note = 0;
                if ($baseSemestre == 1 && is_numeric($noteS1)) {
                    $note = $noteS1;
                } elseif ($baseSemestre == 2 && is_numeric($noteS2)) {
                    $note = $noteS2;
                } elseif (is_numeric($noteS1) && is_numeric($noteS2)) {
                    $note = ($noteS1 + $noteS2) / 2;

                } elseif (!is_numeric($noteS2)) {
                    $note = $noteS1;
                } elseif (!is_numeric($noteS1)) {
                    $note = $noteS2;
                }

                if (is_numeric($note)) {
                    if ((!$cpst) && (!$cps))
                        $sNote += ($note * $credit);

                }


                if ((!$cpst) && (!$cps)) {
                    $matieres[$l] = new NdereSoaMatiere($matiere->id, $note, $credit, $nom, $matiere->type, $matiere->semestre, $this->resolveMatiereCode($matiere));

                }

                if ($cpst) {
                    $composantes[$cst] = new NdereSoaComposante($matiere->id, $note, $matiere->id_classe_matiere, $nom, $credit, $matiere->type, $matiere->semestre, $this->resolveMatiereCode($matiere));
                    $cst++;

                }


                if ($cps) {
                    $composes[$c] = new NdereSoaCompose($matiere->id, $note, $credit, $cpstList, $nom, $credit, $matiere->type, $matiere->semestre, $this->resolveMatiereCode($matiere));
                    $c++;

                }
                $l++;

            }

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

            $matieres[$l] = new NdereSoaMatiere($compt->id_matiere, $co_note, $s_co_coef, $compt->nom, $compt->type, $compt->semestre, $compt->id_code);
            $l++;
        }

///fini ici pour la fermeture des groupes matières

            if($sCoef==0) $sCoef=1;
            $proces_v[$k]=new NdereSoaPV($eleve->id,$matieres,$sNote/$sCoef);
//           dump($proces_v);
//          die();

            $k++;
        }
        $classe=classe::find($id);
        $values['eleves'] = $e;
        $values['PV'] = $proces_v;
        $values['matieres'] = $m;
        $values['module_groups'] = $this->buildModuleGroups($m, $gm);
        $values['semester_summaries'] = $this->buildSemesterSummaries($e, $id, $id_cycle, $nniveau, $trimestre, $m, $gm, $n_cc, $n_sn, $n_rattrap);
        if ((int) $trimestre === 6) {
            $previousGeneration = ReleveNoteGeneration::where('type', 'licence_ngaoundere')
                ->where('annee_academique', session('year'))
                ->where('id_classe', $id)
                ->where('id_cycle', $id_cycle)
                ->where('id_nniveau', $nniveau)
                ->where('semestre', 5)
                ->orderBy('id', 'desc')
                ->first();
            if ($previousGeneration) {
                $previousPayload = json_decode($previousGeneration->contenu);
                if (isset($previousPayload->PV)) {
                    $previousSummaries = $this->buildSavedSemesterSummaries($previousPayload->PV, 5);
                    foreach ($previousSummaries as $studentId => $studentSummary) {
                        if (isset($values['semester_summaries'][$studentId])) {
                            $values['semester_summaries'][$studentId]['semester_5'] = $studentSummary['semester_5'];
                        }
                    }
                }
            }
        }
        $values['nb_matieres'] = $m->count();
        $values['classe'] = $classe;
        $values['cycle']= cycle::find($id_cycle);
        $values['nniveau']=NNiveau::find($nniveau);
        $values['niveau']= niveau::find($classe->id_niveau);
        $values['semestre'] = $semesterPairs[$niveauUniversitaire];
        $values['pv_type'] = 'licence_ngaoundere';
        $values['trimestre'] = $trimestre;
        $values['sequence'] = $baseSemestre == 1 ? 5 : 6;
        $values['campus'] = $this->currentCampus();
        $values['tutelle'] = $this->resolveNdereTutelle($id);
        $pageSize = strtolower((string) $request->input('page_size', 'a4'));
        $values['page_size'] = in_array($pageSize, array('a3', 'a4')) ? strtoupper($pageSize) : 'A4';

        if ($request->input('save') == 1) {
            $generation = new ReleveNoteGeneration();
            $generation->type = 'licence_ngaoundere';
            $generation->annee_academique = session('year');
            $generation->id_niveau = $classe ? $classe->id_niveau : null;
            $generation->id_classe = (int) $id;
            $generation->id_cycle = (int) $id_cycle;
            $generation->id_nniveau = (int) $nniveau;
            $generation->periode_debut = $request->input('periode_debut') ?: null;
            $generation->periode_fin = $request->input('periode_fin') ?: null;
            $generation->semestre = $trimestre;
            $generation->nb_releves = count($proces_v);
            $payload = array('PV'=>$proces_v, 'eleves'=>$e, 'matieres'=>$m, 'classe'=>$classe,
                'module_groups'=>$values['module_groups'],
                'semester_summaries'=>$values['semester_summaries'],
                'cycle'=>$values['cycle'], 'nniveau'=>$values['nniveau'], 'niveau'=>$values['niveau'],
                'campus'=>$values['campus'], 'tutelle'=>$values['tutelle'], 'trimestre'=>$trimestre,
                'sequence'=>$values['sequence'], 'semestre'=>$trimestre,
                'page_size'=>$values['page_size'],
                'periode_debut'=>$generation->periode_debut, 'periode_fin'=>$generation->periode_fin);
            $generation->contenu = json_encode($payload);
            $generation->hash_generation = hash('sha256', $generation->contenu);
            $generation->id_creator = \Auth::check() ? \Auth::user()->id : null;
            $generation->save();
            if ($request->ajax() || $request->input('ajax') == 1) {
                return \Response::json(array('success'=>true, 'id'=>$generation->id,
                    'nb_releves'=>$generation->nb_releves,
                    'url'=>\URL::to('/eleves/releves_ndere/generation/'.$generation->id.'/print')));
            }

            $values['generation'] = $generation;
        }
        return view('eleves/print_releves_ndere', $values);


    }

    private function buildModuleGroups($matieres, $groups)
    {
        $byClasseMatiere = array();
        foreach ($matieres as $matiereItem) {
            $matiereItem->id_code = $this->resolveMatiereCode($matiereItem);
            $byClasseMatiere[$matiereItem->id_classe_matiere] = $matiereItem;
        }
        $result = array();
        foreach ($groups as $group) {
            $mother = null;
            foreach ($matieres as $candidate) {
                if ((int) $candidate->id === (int) $group->id_matiere_mere) {
                    $mother = $candidate;
                    break;
                }
            }
            if (!$mother) continue;
            $key = (int) $group->id_matiere_mere;
            if (!isset($result[$key])) {
                $result[$key] = array('module' => $mother, 'matieres' => array());
            }
            if (isset($byClasseMatiere[$group->id_classe_matiere])) {
                $result[$key]['matieres'][] = $byClasseMatiere[$group->id_classe_matiere];
            }
        }
        return array_values($result);
    }

    private function resolveMatiereCode($matiere)
    {
        if (isset($matiere->codage_nom) && trim((string) $matiere->codage_nom) !== '') {
            return $matiere->codage_nom;
        }
        if (isset($matiere->classe_matiere_id_code) && trim((string) $matiere->classe_matiere_id_code) !== '') {
            return $matiere->classe_matiere_id_code;
        }
        if (isset($matiere->id_code) && trim((string) $matiere->id_code) !== '') {
            return $matiere->id_code;
        }
        if (isset($matiere->code_matiere) && trim((string) $matiere->code_matiere) !== '') {
            return $matiere->code_matiere;
        }
        if (isset($matiere->code) && trim((string) $matiere->code) !== '') {
            return $matiere->code;
        }
        return isset($matiere->id) ? $matiere->id : '';
    }

    private function buildSemesterSummaries($eleves, $idClasse, $idCycle, $idNniveau, $selectedSemester, $selectedMatieres, $groups, $nCc, $nSn, $nRattrap)
    {
        $sets = array($selectedSemester => $selectedMatieres);
        if ((int) $selectedSemester === 6) {
            $sets[5] = matiere::Join('classe_matiere','matiere.id','=','classe_matiere.id_matiere')
                ->leftJoin('codages', DB::raw('codages.id::text'), '=', 'classe_matiere.id_code')
                ->where('classe_matiere.id_classe','=',$idClasse)
                ->where('id_cycle','=',$idCycle)
                ->where('classe_matiere.id_nniveau','=',$idNniveau)
                ->where('classe_matiere.semestre','=',5)
                ->select(['matiere.*','classe_matiere.semestre','coef','type','classe_matiere.id AS id_classe_matiere','classe_matiere.id_code AS classe_matiere_id_code','codages.nom AS codage_nom'])
                ->get();
        }

        $motherIds = $groups->fetch('id_matiere_mere')->toArray();
        $summaries = array();
        foreach ($eleves as $eleve) {
            $summaries[$eleve->id] = array();
            foreach ($sets as $semester => $matieres) {
                $noteTotal = 0;
                $creditTotal = 0;
                $gpaTotal = 0;
                foreach ($matieres as $matiere) {
                    if ($this->isIn($matiere->id, $motherIds)) continue;
                    $note = $this->semesterMatterNote($eleve->id, $matiere->id, $semester, $nCc, $nSn, $nRattrap);
                    if (!is_numeric($note)) continue;
                    $credit = (float) $matiere->coef;
                    if ($credit <= 0) continue;
                    $noteTotal += ((float) $note) * $credit;
                    $creditTotal += $credit;
                    $gpaTotal += $this->gradePoint($note) * $credit;
                }
                $summaries[$eleve->id]['semester_'.$semester] = array(
                    'credits'=>$creditTotal,
                    'note100'=>$creditTotal > 0 ? $noteTotal / $creditTotal : null,
                    'gpa'=>$creditTotal > 0 ? $gpaTotal / $creditTotal : null
                );
            }
        }
        return $summaries;
    }

    private function buildSavedSemesterSummaries($procesV, $semester)
    {
        $summaries = array();
        foreach ($procesV as $pv) {
            if (!isset($pv->eleve) || !isset($pv->eleve->id)) continue;

            $creditTotal = 0;
            $noteTotal = 0;
            $gpaTotal = 0;
            $hasNote = false;
            if (isset($pv->matieres)) {
                foreach ($pv->matieres as $matiereItem) {
                    $credit = isset($matiereItem->credit) && is_numeric($matiereItem->credit)
                        ? (float) $matiereItem->credit : 0;
                    if ($credit <= 0) continue;
                    $creditTotal += $credit;
                    if (isset($matiereItem->note) && is_numeric($matiereItem->note)) {
                        $note = (float) $matiereItem->note;
                        $noteTotal += $note * $credit;
                        $gpaTotal += $this->gradePoint($note) * $credit;
                        $hasNote = true;
                    }
                }
            }

            $summaries[$pv->eleve->id] = array(
                'semester_'.$semester => array(
                    'credits' => $creditTotal,
                    'note100' => $hasNote && $creditTotal > 0 ? $noteTotal / $creditTotal : null,
                    'gpa' => $hasNote && $creditTotal > 0 ? $gpaTotal / $creditTotal : null
                )
            );
        }
        return $summaries;
    }

    private function semesterMatterNote($idEleve, $idMatiere, $semester, $nCc, $nSn, $nRattrap)
    {
        $ccSequence = ((int) $semester % 2) ? 1 : 2;
        $snSequence = ((int) $semester % 2) ? 3 : 4;
        $rattrapSequence = ((int) $semester % 2) ? 5 : 6;
        $cc = $nCc->where('id_matiere', $idMatiere, false)->where('id_eleve', $idEleve, false)->where('sequence', $ccSequence, false)->first();
        $sn = $nSn->where('id_matiere', $idMatiere, false)->where('id_eleve', $idEleve, false)->where('sequence', $snSequence, false)->first();
        $rattrap = $nRattrap->where('id_matiere', $idMatiere, false)->where('id_eleve', $idEleve, false)->where('sequence', $rattrapSequence, false)->first();
        if ($cc === null && $sn === null && $rattrap === null) return null;
        $note = ($cc ? (float) $cc->note : 0) + ($sn ? (float) $sn->note : 0);
        if ($rattrap && $note < 50) {
            $note = ($cc ? (float) $cc->note : 0) + (float) $rattrap->note;
        }
        return $note;
    }

    private function gradePoint($note)
    {
        if ($note >= 80) return 4;
        if ($note >= 75) return 3.7;
        if ($note >= 70) return 3.3;
        if ($note >= 65) return 3;
        if ($note >= 60) return 2.7;
        if ($note >= 55) return 2.3;
        if ($note >= 50) return 2;
        if ($note >= 45) return 1.7;
        if ($note >= 40) return 1.3;
        if ($note >= 35) return 1;
        return 0;
    }

    private function universityLevel($nniveau)
    {
        if ($nniveau && preg_match('/([1-5])/', $nniveau->nom, $matches)) {
            return (int) $matches[1];
        }
        return 1;
    }

    private function resolveNdereTutelle($idClasse)
    {
        $tutelles = TutelleAcademique::whereHas('specialites', function ($query) use ($idClasse) {
            $query->where('classe.id', '=', $idClasse);
        })->get();
        foreach ($tutelles as $tutelle) {
            $identity = strtoupper(
                (isset($tutelle->nom) ? $tutelle->nom : '').' '.
                (isset($tutelle->tutelle_anglophone) ? $tutelle->tutelle_anglophone : '').' '.
                (isset($tutelle->ville) ? $tutelle->ville : '').' '.
                (isset($tutelle->localisation) ? $tutelle->localisation : '')
            );
            if (strpos($identity, 'NGAOUND') !== false) {
                return $tutelle;
            }
        }
        if (count($tutelles) > 0) {
            return $tutelles->first();
        }
        return TutelleAcademique::where(function ($query) {
            $query->where('nom', 'LIKE', '%NGAOUND%')
                ->orWhere('tutelle_anglophone', 'LIKE', '%NGAOUND%')
                ->orWhere('ville', 'LIKE', '%NGAOUND%')
                ->orWhere('localisation', 'LIKE', '%NGAOUND%');
        })->first();
    }

    public function generate(Request $request)
    {
        $request->merge(array('save' => 1));
        return $this->create($request);
    }

    public function printSaved($id)
    {
        $generation = ReleveNoteGeneration::findOrFail($id);
        $payload = json_decode($generation->contenu);
        $classe = isset($payload->classe) ? $payload->classe : classe::find($generation->id_classe);
        $tutelle = $this->resolveNdereTutelle($classe->id);
        $savedSemester = $generation->semestre
            ? (int) $generation->semestre
            : (isset($payload->trimestre) ? (int) $payload->trimestre : 1);
        $savedSequence = $savedSemester % 2 ? 5 : 6;
        $semesterSummaries = isset($payload->PV)
            ? $this->buildSavedSemesterSummaries($payload->PV, $savedSemester)
            : array();
        if ($savedSemester === 6) {
            $previousGeneration = ReleveNoteGeneration::where('type', 'licence_ngaoundere')
                ->where('annee_academique', $generation->annee_academique)
                ->where('id_classe', $generation->id_classe)
                ->where('id_cycle', $generation->id_cycle)
                ->where('id_nniveau', $generation->id_nniveau)
                ->where('semestre', 5)
                ->where('id', '<>', $generation->id)
                ->orderBy('id', 'desc')
                ->first();
            if ($previousGeneration) {
                $previousPayload = json_decode($previousGeneration->contenu);
                if (isset($previousPayload->PV)) {
                    $previousSummaries = $this->buildSavedSemesterSummaries($previousPayload->PV, 5);
                    foreach ($previousSummaries as $studentId => $studentSummary) {
                        if (isset($semesterSummaries[$studentId])) {
                            $semesterSummaries[$studentId]['semester_5'] = $studentSummary['semester_5'];
                        }
                    }
                }
            }
        }
        return view('eleves/print_releves_ndere', array(
            'titre'=>trans('admin.releve_notes'), 'PV'=>isset($payload->PV) ? $payload->PV : array(),
            'eleves'=>isset($payload->eleves) ? $payload->eleves : array(),
            'matieres'=>isset($payload->matieres) ? $payload->matieres : array(),
            'module_groups'=>isset($payload->module_groups) ? $payload->module_groups : array(),
            'semester_summaries'=>$semesterSummaries,
            'classe'=>$classe, 'cycle'=>isset($payload->cycle) ? $payload->cycle : cycle::find($generation->id_cycle),
            'nniveau'=>isset($payload->nniveau) ? $payload->nniveau : NNiveau::find($generation->id_nniveau),
            'niveau'=>niveau::find($generation->id_niveau),
            'semestre'=>array(1,2), 'trimestre'=>$savedSemester,
            'sequence'=>$savedSequence,
            'campus'=>isset($payload->campus) ? $payload->campus : $this->currentCampus(),
            'tutelle'=>$tutelle, 'generation'=>$generation,
            'page_size'=>in_array(strtolower(\Input::get('page_size', isset($payload->page_size) ? $payload->page_size : 'a4')), array('a3', 'a4')) ? strtoupper(\Input::get('page_size', isset($payload->page_size) ? $payload->page_size : 'a4')) : 'A4'
        ));
    }

    public function history(Request $request)
    {
        $query = ReleveNoteGeneration::where('type', 'licence_ngaoundere');
        if ($request->input('annee_academique')) $query->where('annee_academique', $request->input('annee_academique'));
        return view('eleves/releves_ndere_historique', array(
            'titre'=>'Historique des relevés Ngaoundéré',
            'generations'=>$query->orderBy('created_at', 'desc')->get()
        ));
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
        $generation = ReleveNoteGeneration::where('type', 'licence_ngaoundere')->where('id', $id)->firstOrFail();
        $generation->delete();

        return redirect()->to('/eleves/releves_ndere/historique')
            ->with('success', 'Historique de releve supprime avec succes.');
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
class NdereSoaPV{
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
class NdereSoaMatiere{
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

class NdereSoaComposante{
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
class NdereSoaCompose{
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
