<?php namespace gesecole\Http\Controllers\eleves;

use gesecole\Http\Controllers\Controller;
use gesecole\classe;
use gesecole\cycle;
use gesecole\groupeMatieres;
use gesecole\matiere;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\note;
use gesecole\parametres;
use gesecole\ReleveNoteGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class bts_releveSessionController extends Controller {

    public function __construct()
    {
        session(['big_title' => trans('eleve.big_title')]);
        if (session('year') == null) {
            $p = parametres::whereNom('annee_scolaire')->first();
            if ($p == null) {
                $p = new parametres();
                $p->nom = 'annee_scolaire';
                $p->valeur = '2017-2018';
                $p->save();
            }
            session(['year' => $p->valeur]);
        }
    }

    public function index()
    {
        $values = array('titre' => trans('admin.releve_notes'));
        $values['classes'] = classe::orderBy('nom')->get();
        $values['cycles'] = cycle::all();
        $values['niveaux'] = niveau::all();
        $values['nniveaux'] = NNiveau::all();
        if (session('success')) {
            $values['success'] = session('success');
            Session::forget('success');
        }

        return view('eleves/classe_releves_bts', $values);
    }

    public function history(Request $request, $idClasse = null)
    {
        $query = ReleveNoteGeneration::where('type', 'bts')
            ->orderBy('created_at', 'desc');

        if ($idClasse) {
            $query->where('id_classe', (int) $idClasse);
        }
        if ($request->input('cycle')) {
            $query->where('id_cycle', (int) $request->input('cycle'));
        }
        if ($request->input('nniveau')) {
            $query->where('id_nniveau', (int) $request->input('nniveau'));
        }
        if ($request->input('annee_academique')) {
            $query->where('annee_academique', $request->input('annee_academique'));
        }

        $classe = $idClasse ? classe::find($idClasse) : null;

        return view('eleves/releves_bts_historique', array(
            'titre' => 'Historique des releves BTS',
            'generations' => $query->get(),
            'classe' => $classe,
            'cycle' => $request->input('cycle') ? cycle::find($request->input('cycle')) : null,
            'nniveau' => $request->input('nniveau') ? NNiveau::find($request->input('nniveau')) : null,
        ));
    }

    public function create(Request $request)
    {
        $context = $this->validateContext($request);
        $values = $this->buildReleves($context);

        if ($request->input('save') == 1) {
            $generation = $this->saveGeneration($context, $values, $request);
            $values['generation'] = $generation;
        }

        return view('eleves/print_releves_bts', $values);
    }
public function generate(Request $request)
{
    try {
        $context = $this->validateContext($request);
        $values = $this->buildReleves($context);
        $generation = $this->saveGeneration($context, $values, $request);

        $url = url('/eleves/releves_bts/generation/'.$generation->id.'/print');

        return \Response::json(array(
            'success' => true,
            'id' => $generation->id,
            'nb_releves' => $generation->nb_releves,
            'url' => $url,
        ));

    } catch (\Exception $e) {
        return \Response::json(array(
            'success' => false,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ), 500);
    }
}
 
    public function generate1(Request $request)
    {
        $context = $this->validateContext($request);
        $values = $this->buildReleves($context);
        $generation = $this->saveGeneration($context, $values, $request);

        $url = url('/eleves/releves_bts/generation/'.$generation->id.'/print');
        if ($request->ajax() || $request->input('ajax') == 1) {
            return \Response::json(array(
                'success' => true,
                'id' => $generation->id,
                'nb_releves' => $generation->nb_releves,
                'url' => $url,
            ));
        }

        return redirect()->to($url);
    }

    public function printSaved($id)
    {
        $generation = ReleveNoteGeneration::where('type', 'bts')->findOrFail($id);
        $payload = json_decode($generation->contenu);

        $values = array(
            'titre' => trans('admin.releve_notes'),
            'PV' => isset($payload->PV) ? $payload->PV : array(),
            'eleves' => isset($payload->eleves) ? $payload->eleves : array(),
            'matieres' => isset($payload->matieres) ? $payload->matieres : array(),
            'nb_matieres' => isset($payload->nb_matieres) ? $payload->nb_matieres : 0,
            'classe' => isset($payload->classe) ? $payload->classe : classe::find($generation->id_classe),
            'cycle' => isset($payload->cycle) ? $payload->cycle : cycle::find($generation->id_cycle),
            'nniveau' => isset($payload->nniveau) ? $payload->nniveau : NNiveau::find($generation->id_nniveau),
            'niveau' => isset($payload->niveau) ? $payload->niveau : niveau::find($generation->id_niveau),
            'semestre' => array(1, 2),
            'generation' => $generation,
        );

        return view('eleves/print_releves_bts', $values);
    }

    public function destroy($id)
    {
        $generation = ReleveNoteGeneration::where('type', 'bts')->findOrFail($id);
        $generation->delete();

        return redirect()->to('/eleves/releves_bts/historique')
            ->with('success', 'Historique de releve BTS supprime avec succes.');
    }

    private function validateContext(Request $request)
    {
        $this->validate($request, array(
            'id' => 'required|numeric',
            'cycle' => 'required|numeric',
            'nniveau' => 'required|numeric',
        ));

        $classe = classe::findOrFail((int) $request->input('id'));

        return array(
            'id_classe' => $classe->id, // specialite
            'id_niveau' => $classe->id_niveau, // filiere
            'id_cycle' => (int) $request->input('cycle'),
            'id_nniveau' => (int) $request->input('nniveau'),
            'annee' => session('year'),
        );
    }

    private function buildReleves($context)
    {
        $idClasse = $context['id_classe'];
        $idCycle = $context['id_cycle'];
        $idNniveau = $context['id_nniveau'];
        $annee = $context['annee'];

        $eleves = DB::table('eleve as e')
            ->join('inscription as i', 'i.id_eleve', '=', 'e.id')
            ->where('i.annee_scolaire', $annee)
            ->where('i.id_classe', $idClasse)
            ->where('i.id_cycle', $idCycle)
            ->where('i.id_nniveau', $idNniveau)
            ->orderBy('e.nom')
            ->orderBy('e.prenom')
            ->select('e.*')
            ->get();

        $idEleves = array();
        foreach ($eleves as $eleve) {
            $idEleves[] = $eleve->id;
        }

        $matieres = DB::table('matiere as m')
            ->join('classe_matiere as cm', 'm.id', '=', 'cm.id_matiere')
            ->join('session_exams as se', function($join) use ($annee) {
                $join->on('se.id_classe_matiere', '=', 'cm.id')
                    ->where('se.annee_academique', '=', $annee);
            })
            ->where('cm.id_classe', $idClasse)
            ->where('cm.id_cycle', $idCycle)
            ->where('cm.id_nniveau', $idNniveau)
            ->select(
                'm.*',
                'cm.id as id_classe_matiere',
                'cm.coef',
                'cm.semestre',
                'cm.type',
                'cm.id_code',
                'se.id as id_session',
                'se.cc1',
                'se.tpe1',
                'se.sn1',
                'se.ratrappage1',
                'se.cc2',
                'se.tpe2',
                'se.sn2',
                'se.ratrappage2'
            )
            ->orderBy('cm.semestre')
            ->orderBy('m.nom')
            ->get();

        $idMatieres = array();
        foreach ($matieres as $matiereItem) {
            $idMatieres[] = $matiereItem->id;
        }

        $notes = array();
        if (count($idEleves) > 0 && count($idMatieres) > 0) {
            $notes = note::where('annee_scolaire', $annee)
                ->whereIn('id_eleve', $idEleves)
                ->whereIn('id_matiere', $idMatieres)
                ->whereIn('sequence', array(1, 2, 3, 4, 5, 6, 7, 8))
                ->get();
        }

        $notesMap = array();
        foreach ($notes as $n) {
            $notesMap[$n->id_eleve][$n->id_matiere][$n->sequence] = (float) $n->note;
        }

        $groups = $this->getGroupConfig($idClasse, $idNniveau);
        $pv = array();
        foreach ($eleves as $eleve) {
            $pv[] = $this->buildEleveReleve($eleve, $matieres, $notesMap, $groups);
        }

        $classe = classe::find($idClasse);

        return array(
            'titre' => trans('admin.releve_notes'),
            'eleves' => $eleves,
            'PV' => $pv,
            'matieres' => $matieres,
            'nb_matieres' => count($matieres),
            'classe' => $classe,
            'cycle' => cycle::find($idCycle),
            'nniveau' => NNiveau::find($idNniveau),
            'niveau' => $classe ? niveau::find($classe->id_niveau) : null,
            'semestre' => array(1, 2),
        );
    }

    private function getGroupConfig($idClasse, $idNniveau)
    {
        $gm = groupeMatieres::whereIdClasse($idClasse)
            ->where('id_nniveau', '=', $idNniveau)
            ->get();

        $meres = array();
        $composantes = array();
        foreach ($gm as $g) {
            $meres[$g->id_matiere_mere] = true;
            $composantes[$g->id_classe_matiere] = $g->id_matiere_mere;
        }

        return array('meres' => $meres, 'composantes' => $composantes);
    }

    private function buildEleveReleve($eleve, $matieres, $notesMap, $groups)
    {
        $rows = array();
        $componentsByMother = array();
        $sNote = 0;
        $sCoef = 0;

        foreach ($matieres as $matiereItem) {
            $noteFinale = $this->calculateMatiereNote($eleve->id, $matiereItem, $notesMap);
            $row = (object) array(
                'id_matiere' => $matiereItem->id,
                'note' => $noteFinale,
                'credit' => (float) $matiereItem->coef,
                'altName' => \App::getLocale() == 'en' ? $matiereItem->nom_anglais : $matiereItem->nom,
                'type' => $matiereItem->type,
                'semestre' => $matiereItem->semestre,
                'id_code' => $matiereItem->id_code,
                'id_classe_matiere' => $matiereItem->id_classe_matiere,
            );

            if (isset($groups['composantes'][$matiereItem->id_classe_matiere])) {
                $mother = $groups['composantes'][$matiereItem->id_classe_matiere];
                $componentsByMother[$mother][] = $row;
                continue;
            }

            if (isset($groups['meres'][$matiereItem->id])) {
                continue;
            }

            $rows[] = $row;
            if (is_numeric($noteFinale)) {
                $sNote += $noteFinale * $row->credit;
                $sCoef += $row->credit;
            }
        }

        foreach ($matieres as $matiereItem) {
            if (!isset($groups['meres'][$matiereItem->id]) || !isset($componentsByMother[$matiereItem->id])) {
                continue;
            }

            $sum = 0;
            $coef = 0;
            foreach ($componentsByMother[$matiereItem->id] as $component) {
                if (is_numeric($component->note)) {
                    $sum += $component->note * $component->credit;
                    $coef += $component->credit;
                }
            }

            $noteComposee = $coef > 0 ? round($sum / $coef, 2) : 0;
            $row = (object) array(
                'id_matiere' => $matiereItem->id,
                'note' => $noteComposee,
                'credit' => (float) $matiereItem->coef,
                'altName' => \App::getLocale() == 'en' ? $matiereItem->nom_anglais : $matiereItem->nom,
                'type' => $matiereItem->type,
                'semestre' => $matiereItem->semestre,
                'id_code' => $matiereItem->id_code,
                'id_classe_matiere' => $matiereItem->id_classe_matiere,
            );

            $rows[] = $row;
            $sNote += $noteComposee * $row->credit;
            $sCoef += $row->credit;
        }

        return (object) array(
            'eleve' => $eleve,
            'matieres' => $rows,
            'mgp' => $sCoef > 0 ? round($sNote / $sCoef, 2) : 0,
        );
    }

    private function calculateMatiereNote($idEleve, $matiereItem, $notesMap)
    {
        $notes = isset($notesMap[$idEleve][$matiereItem->id]) ? $notesMap[$idEleve][$matiereItem->id] : array();

        $s1 = $this->calculateSemestreNote($notes, 1, $matiereItem);
        $s2 = $this->calculateSemestreNote($notes, 2, $matiereItem);

        if (is_numeric($s1) && is_numeric($s2)) {
            return round(($s1 + $s2) / 2, 2);
        }
        if (is_numeric($s1)) {
            return round($s1, 2);
        }
        if (is_numeric($s2)) {
            return round($s2, 2);
        }

        return 0;
    }

    private function calculateSemestreNote($notes, $semestre, $matiereItem)
    {
        if ($semestre == 1) {
            $ccSeq = 1; $snSeq = 3; $tpeSeq = 7; $ratSeq = 5;
            $ccPct = (float) $matiereItem->cc1;
            $snPct = (float) $matiereItem->sn1;
            $tpePct = (float) $matiereItem->tpe1;
        } else {
            $ccSeq = 2; $snSeq = 4; $tpeSeq = 8; $ratSeq = 6;
            $ccPct = (float) $matiereItem->cc2;
            $snPct = (float) $matiereItem->sn2;
            $tpePct = (float) $matiereItem->tpe2;
        }

        $hasAny = isset($notes[$ccSeq]) || isset($notes[$snSeq]) || isset($notes[$tpeSeq]) || isset($notes[$ratSeq]);
        if (!$hasAny) {
            return null;
        }

        $cc = isset($notes[$ccSeq]) ? (float) $notes[$ccSeq] : 0;
        $sn = isset($notes[$snSeq]) ? (float) $notes[$snSeq] : 0;
        $tpe = isset($notes[$tpeSeq]) ? (float) $notes[$tpeSeq] : 0;
        $normal = $cc + $sn + $tpe;

        if (isset($notes[$ratSeq]) && $normal < 50) {
            $rat = (float) $notes[$ratSeq];
            return $cc + $tpe + $rat;
        }

        return $normal;
    }
    private function saveGeneration($context, $values, Request $request)
{
    $payload = array(
        'PV' => $values['PV'],
        'eleves' => $values['eleves'],
        'matieres' => $values['matieres'],
        'nb_matieres' => $values['nb_matieres'],
        'classe' => $values['classe'],
        'cycle' => $values['cycle'],
        'nniveau' => $values['nniveau'],
        'niveau' => $values['niveau'],
        'generated_at' => date('Y-m-d H:i:s'),
    );

    $json = json_encode($payload);

    $classe = classe::find($context['id_classe']);

    $generation = new ReleveNoteGeneration();
    $generation->type = 'bts';
    $generation->annee_academique = $context['annee'];
    $generation->id_niveau = $classe ? $classe->id_niveau : null;
    $generation->id_classe = $context['id_classe'];
    $generation->id_cycle = $context['id_cycle'];
    $generation->id_nniveau = $context['id_nniveau'];
    $generation->periode_debut = $request->input('periode_debut') ? $request->input('periode_debut') : null;
    $generation->periode_fin = $request->input('periode_fin') ? $request->input('periode_fin') : null;
    $generation->nb_releves = count($values['PV']);
    $generation->hash_generation = hash('sha256', $json);
    $generation->contenu = $json;
    $generation->id_creator = Auth::check() ? Auth::user()->id : null;
    $generation->save();

    return $generation;
}

    private function saveGeneration1($context, $values, Request $request)
    {
        $payload = array(
            'annee_academique' => $context['annee'],
            'id_niveau' => $context['id_niveau'],
            'id_classe' => $context['id_classe'],
            'id_cycle' => $context['id_cycle'],
            'id_nniveau' => $context['id_nniveau'],
            'PV' => $values['PV'],
            'eleves' => $values['eleves'],
            'matieres' => $values['matieres'],
            'nb_matieres' => $values['nb_matieres'],
            'classe' => $values['classe'],
            'cycle' => $values['cycle'],
            'nniveau' => $values['nniveau'],
            'niveau' => $values['niveau'],
            'generated_at' => date('Y-m-d H:i:s'),
        );
        $json = json_encode($payload);

        $generation = new ReleveNoteGeneration();
        $generation->type = 'bts';
        $generation->annee_academique = $context['annee'];
        $generation->id_niveau = $context['id_niveau'];
        $generation->id_classe = $context['id_classe'];
        $generation->id_cycle = $context['id_cycle'];
        $generation->id_nniveau = $context['id_nniveau'];
        $generation->periode_debut = $request->input('periode_debut') ? $request->input('periode_debut') : null;
        $generation->periode_fin = $request->input('periode_fin') ? $request->input('periode_fin') : null;
        $generation->nb_releves = count($values['PV']);
        $generation->hash_generation = hash('sha256', $json);
        $generation->contenu = $json;
        $generation->id_creator = Auth::check() ? Auth::user()->id : null;
        $generation->save();

        return $generation;
    }
}
