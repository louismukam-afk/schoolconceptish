<?php namespace gesecole\Http\Controllers\eleves;

use gesecole\classe;
use gesecole\cycle;
use gesecole\eleve;
use gesecole\Http\Controllers\Controller;
use gesecole\niveau;
use gesecole\NNiveau;
use gesecole\parametres;
use gesecole\ReleveNoteGeneration;
use gesecole\TutelleAcademique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ndereAttestationReussiteController extends Controller
{
    const GENERATION_TYPE = 'attestation_reussite_ngaoundere';
    private $sourceReleveGeneration = null;

    public function __construct()
    {
        session(array('big_title' => trans('eleve.big_title')));
        $year = session('year');
        if ($year == null) {
            $p = parametres::whereNom('annee_scolaire')->get()->first();
            if (count($p) <= 0) {
                $p = new parametres();
                $p->nom = 'annee_scolaire';
                $p->valeur = '2017-2018';
                $p->save();
                session(array('year' => $p->valeur));
            } else {
                session(array('year' => $p->valeur));
            }
        }
    }

    public function index()
    {
        $values = array('titre' => 'Attestations de reussite Ngaoundere');
        $values['classes'] = classe::orderBy('nom')->get();
        $values['cycles'] = cycle::all();
        $values['niveaux'] = niveau::all();
        $values['nniveaux'] = NNiveau::all();
        if (session('success')) {
            $values['success'] = session('success');
            Session::forget('success');
        }
        return view('eleves/classe_attestations_reussite_ndere', $values);
    }

    public function generate(Request $request)
    {
        $rules = array(
            'id' => 'required|numeric',
            'cycle' => 'required|numeric',
            'nniveau' => 'required|numeric',
        );
        if ($request->input('periode_debut')) {
            $rules['periode_debut'] = 'date';
        }
        if ($request->input('periode_fin')) {
            $rules['periode_fin'] = 'date';
        }
        $this->validate($request, $rules);

        $idClasse = (int) $request->input('id');
        $idCycle = (int) $request->input('cycle');
        $idNniveau = (int) $request->input('nniveau');
        $classe = classe::findOrFail($idClasse);
        $cycle = cycle::find($idCycle);
        $nniveau = NNiveau::find($idNniveau);
        $niveau = niveau::find($classe->id_niveau);
        $campus = $this->currentCampus();
        $tutelle = $this->resolveNdereTutelle($idClasse);
        $attestations = $this->buildAttestations($idClasse, $idCycle, $idNniveau);

        $generation = new ReleveNoteGeneration();
        $generation->type = self::GENERATION_TYPE;
        $generation->annee_academique = session('year');
        $generation->id_niveau = $classe->id_niveau;
        $generation->id_classe = $idClasse;
        $generation->id_cycle = $idCycle;
        $generation->id_nniveau = $idNniveau;
        $generation->periode_debut = $request->input('periode_debut') ?: null;
        $generation->periode_fin = $request->input('periode_fin') ?: null;
        $generation->nb_releves = count($attestations);

        $payload = array(
            'attestations' => $attestations,
            'classe' => $classe,
            'cycle' => $cycle,
            'nniveau' => $nniveau,
            'niveau' => $niveau,
            'campus' => $campus,
            'tutelle' => $tutelle,
            'source_releve_generation' => $this->sourceReleveGeneration,
            'periode_debut' => $generation->periode_debut,
            'periode_fin' => $generation->periode_fin,
        );
        $generation->contenu = json_encode($payload);
        $generation->hash_generation = hash('sha256', $generation->contenu);
        $generation->id_creator = \Auth::check() ? \Auth::user()->id : null;
        $generation->save();

        if ($request->ajax() || $request->input('ajax') == 1) {
            return \Response::json(array(
                'success' => true,
                'id' => $generation->id,
                'nb_attestations' => $generation->nb_releves,
                'url' => \URL::to('/eleves/attestations_reussite_ndere/generation/'.$generation->id.'/print')
            ));
        }

        return redirect()->to('/eleves/attestations_reussite_ndere/generation/'.$generation->id.'/print');
    }

    public function printSaved($id)
    {
        $generation = ReleveNoteGeneration::where('type', self::GENERATION_TYPE)->where('id', $id)->firstOrFail();
        $payload = json_decode($generation->contenu);
        if (!$payload) {
            $payload = new \stdClass();
        }
        if ((!isset($payload->attestations) || count($payload->attestations) <= 0)
            && $generation->id_classe && $generation->id_cycle && $generation->id_nniveau) {
            $rebuiltAttestations = $this->buildAttestations($generation->id_classe, $generation->id_cycle, $generation->id_nniveau);
            if (count($rebuiltAttestations) > 0) {
                $payload->attestations = $rebuiltAttestations;
                $payload->source_releve_generation = $this->sourceReleveGeneration;
                $generation->nb_releves = count($rebuiltAttestations);
                $generation->contenu = json_encode($payload);
                $generation->hash_generation = hash('sha256', $generation->contenu);
                $generation->save();
                $payload = json_decode($generation->contenu);
            }
        }
        $classe = isset($payload->classe) ? $payload->classe : classe::find($generation->id_classe);

        return view('eleves/print_attestations_reussite_ndere', array(
            'titre' => 'Attestations de reussite Ngaoundere',
            'attestations' => isset($payload->attestations) ? $payload->attestations : array(),
            'classe' => $classe,
            'cycle' => isset($payload->cycle) ? $payload->cycle : cycle::find($generation->id_cycle),
            'nniveau' => isset($payload->nniveau) ? $payload->nniveau : NNiveau::find($generation->id_nniveau),
            'niveau' => isset($payload->niveau) ? $payload->niveau : niveau::find($generation->id_niveau),
            'campus' => isset($payload->campus) ? $payload->campus : $this->currentCampus(),
            'tutelle' => isset($payload->tutelle) ? $payload->tutelle : $this->resolveNdereTutelle($classe ? $classe->id : null),
            'generation' => $generation,
            'periode_debut' => $generation->periode_debut,
            'periode_fin' => $generation->periode_fin,
            'page_size' => in_array(strtolower($requestPageSize = \Input::get('page_size', 'a4')), array('a3', 'a4')) ? strtoupper($requestPageSize) : 'A4',
        ));
    }

    public function history(Request $request)
    {
        $query = ReleveNoteGeneration::where('type', self::GENERATION_TYPE);
        if ($request->input('annee_academique')) {
            $query->where('annee_academique', $request->input('annee_academique'));
        }

        return view('eleves/attestations_reussite_ndere_historique', array(
            'titre' => 'Historique des attestations de reussite Ngaoundere',
            'generations' => $query->orderBy('created_at', 'desc')->get()
        ));
    }

    public function destroy($id)
    {
        $generation = ReleveNoteGeneration::where('type', self::GENERATION_TYPE)->where('id', $id)->firstOrFail();
        $generation->delete();
        return redirect()->to('/eleves/attestations_reussite_ndere/historique')
            ->with('success', 'Historique des attestations supprime.');
    }

    private function buildAttestations($idClasse, $idCycle, $idNniveau)
    {
        $this->sourceReleveGeneration = null;
        $source = ReleveNoteGeneration::where('type', 'licence_ngaoundere')
            ->where('annee_academique', '=', session('year'))
            ->where('id_classe', '=', $idClasse)
            ->where('id_cycle', '=', $idCycle)
            ->where('id_nniveau', '=', $idNniveau)
            ->where('semestre', '=', 6)
            ->orderBy('id', 'desc')
            ->first();

        if (!$source) {
            return array();
        }

        $this->sourceReleveGeneration = array(
            'id' => $source->id,
            'date' => $source->created_at,
            'annee_academique' => $source->annee_academique,
            'semestre' => $source->semestre,
        );

        $payload = json_decode($source->contenu);
        if (!$payload || !isset($payload->PV)) {
            return array();
        }

        $attestations = array();
        foreach ($payload->PV as $releve) {
            if (!isset($releve->eleve) || !isset($releve->eleve->id)) continue;

            $student = eleve::join('inscription', 'inscription.id_eleve', '=', 'eleve.id')
                ->where('inscription.annee_scolaire', '=', session('year'))
                ->where('inscription.id_classe', '=', $idClasse)
                ->where('inscription.id_cycle', '=', $idCycle)
                ->where('inscription.id_nniveau', '=', $idNniveau)
                ->where('inscription.desinscrire', '=', 0)
                ->where('eleve.id', '=', $releve->eleve->id)
                ->select('eleve.*')
                ->first();

            if (!$student) continue;

            $average100 = $this->extractAverageFromSavedReleve($releve, isset($payload->semester_summaries) ? $payload->semester_summaries : null);
            if ($average100 === null || $average100 < 50) continue;

            $attestations[] = array(
                'eleve' => $student,
                'moyenne100' => $average100,
                'moyenne20' => $average100 / 5,
            );
        }

        return $attestations;
    }

    private function extractAverageFromSavedReleve($releve, $semesterSummaries)
    {
        $studentId = isset($releve->eleve->id) ? $releve->eleve->id : null;
        $studentSummary = null;

        if ($studentId !== null && is_array($semesterSummaries) && isset($semesterSummaries[$studentId])) {
            $studentSummary = $semesterSummaries[$studentId];
        } elseif ($studentId !== null && is_object($semesterSummaries) && isset($semesterSummaries->{$studentId})) {
            $studentSummary = $semesterSummaries->{$studentId};
        }

        if ($studentSummary) {
            $semesterFive = $this->summarySemesterValue($studentSummary, 'semester_5');
            $semesterSix = $this->summarySemesterValue($studentSummary, 'semester_6');
            $creditsFive = $this->summaryNumber($semesterFive, 'credits');
            $creditsSix = $this->summaryNumber($semesterSix, 'credits');
            $noteFive = $this->summaryNumber($semesterFive, 'note100');
            $noteSix = $this->summaryNumber($semesterSix, 'note100');
            $totalCredits = $creditsFive + $creditsSix;

            if ($totalCredits > 0 && ($noteFive !== null || $noteSix !== null)) {
                return ((($noteFive !== null ? $noteFive : 0) * $creditsFive) + (($noteSix !== null ? $noteSix : 0) * $creditsSix)) / $totalCredits;
            }

            if ($noteSix !== null) {
                return $noteSix;
            }
        }

        if (isset($releve->mgp) && is_numeric($releve->mgp)) {
            return (float) $releve->mgp;
        }

        return null;
    }

    private function summarySemesterValue($summary, $key)
    {
        if (is_array($summary) && isset($summary[$key])) return $summary[$key];
        if (is_object($summary) && isset($summary->{$key})) return $summary->{$key};
        return null;
    }

    private function summaryNumber($summary, $key)
    {
        if (is_array($summary) && isset($summary[$key]) && is_numeric($summary[$key])) return (float) $summary[$key];
        if (is_object($summary) && isset($summary->{$key}) && is_numeric($summary->{$key})) return (float) $summary->{$key};
        return null;
    }

    private function resolveNdereTutelle($idClasse)
    {
        if (!$idClasse) return null;
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
}
