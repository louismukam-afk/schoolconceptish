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
use Illuminate\Support\Facades\Session;

class soaAttestationReussiteController extends Controller
{
    const GENERATION_TYPE = 'attestation_reussite_soa';
    private $sourceReleveGeneration = null;

    public function __construct()
    {
        session(array('big_title' => trans('eleve.big_title')));
        if (session('year') == null) {
            $p = parametres::whereNom('annee_scolaire')->get()->first();
            if (count($p) <= 0) {
                $p = new parametres();
                $p->nom = 'annee_scolaire';
                $p->valeur = '2017-2018';
                $p->save();
            }
            session(array('year' => $p->valeur));
        }
    }

    public function index()
    {
        $values = array('titre' => 'Attestations de reussite SOA');
        $values['classes'] = classe::orderBy('nom')->get();
        $values['cycles'] = cycle::all();
        $values['niveaux'] = niveau::all();
        $values['nniveaux'] = NNiveau::all();
        if (session('success')) {
            $values['success'] = session('success');
            Session::forget('success');
        }
        return view('eleves/classe_attestations_reussite_soa', $values);
    }

    public function generate(Request $request)
    {
        $rules = array(
            'id' => 'required|numeric',
            'cycle' => 'required|numeric',
            'nniveau' => 'required|numeric',
        );
        if ($request->input('periode_debut')) $rules['periode_debut'] = 'date';
        if ($request->input('periode_fin')) $rules['periode_fin'] = 'date';
        $this->validate($request, $rules);

        $idClasse = (int) $request->input('id');
        $idCycle = (int) $request->input('cycle');
        $idNniveau = (int) $request->input('nniveau');
        $classe = classe::findOrFail($idClasse);
        $cycle = cycle::find($idCycle);
        $nniveau = NNiveau::find($idNniveau);
        $niveau = niveau::find($classe->id_niveau);
        $campus = $this->currentCampus();
        $tutelle = $this->resolveSoaTutelle($idClasse);
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
        $generation->contenu = json_encode(array(
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
        ));
        $generation->hash_generation = hash('sha256', $generation->contenu);
        $generation->id_creator = \Auth::check() ? \Auth::user()->id : null;
        $generation->save();

        if ($request->ajax() || $request->input('ajax') == 1) {
            return \Response::json(array(
                'success' => true,
                'id' => $generation->id,
                'nb_attestations' => $generation->nb_releves,
                'url' => \URL::to('/eleves/attestations_reussite_soa/generation/'.$generation->id.'/print')
            ));
        }

        return redirect()->to('/eleves/attestations_reussite_soa/generation/'.$generation->id.'/print');
    }

    public function printSaved($id)
    {
        $generation = ReleveNoteGeneration::where('type', self::GENERATION_TYPE)->where('id', $id)->firstOrFail();
        $payload = json_decode($generation->contenu);
        if (!$payload) $payload = new \stdClass();
        if ((!isset($payload->attestations) || count($payload->attestations) <= 0)
            && $generation->id_classe && $generation->id_cycle && $generation->id_nniveau) {
            $rebuilt = $this->buildAttestations($generation->id_classe, $generation->id_cycle, $generation->id_nniveau);
            if (count($rebuilt) > 0) {
                $payload->attestations = $rebuilt;
                $payload->source_releve_generation = $this->sourceReleveGeneration;
                $generation->nb_releves = count($rebuilt);
                $generation->contenu = json_encode($payload);
                $generation->hash_generation = hash('sha256', $generation->contenu);
                $generation->save();
                $payload = json_decode($generation->contenu);
            }
        }
        $classe = isset($payload->classe) ? $payload->classe : classe::find($generation->id_classe);

        return view('eleves/print_attestations_reussite_ndere', array(
            'titre' => 'Attestations de reussite SOA',
            'attestations' => isset($payload->attestations) ? $payload->attestations : array(),
            'classe' => $classe,
            'cycle' => isset($payload->cycle) ? $payload->cycle : cycle::find($generation->id_cycle),
            'nniveau' => isset($payload->nniveau) ? $payload->nniveau : NNiveau::find($generation->id_nniveau),
            'niveau' => isset($payload->niveau) ? $payload->niveau : niveau::find($generation->id_niveau),
            'campus' => isset($payload->campus) ? $payload->campus : $this->currentCampus(),
            'tutelle' => isset($payload->tutelle) ? $payload->tutelle : $this->resolveSoaTutelle($classe ? $classe->id : null),
            'generation' => $generation,
            'periode_debut' => $generation->periode_debut,
            'periode_fin' => $generation->periode_fin,
            'page_size' => in_array(strtolower($requestPageSize = \Input::get('page_size', 'a4')), array('a3', 'a4')) ? strtoupper($requestPageSize) : 'A4',
            'attestation_city' => 'Soa',
            'attestation_home_url' => '/eleves/attestations_reussite_soa',
            'attestation_history_url' => '/eleves/attestations_reussite_soa/historique',
            'attestation_signature_primary' => 'Le Doyen',
            'attestation_signature_primary_en' => 'The Dean',
            'attestation_template' => 'soa',
            'attestation_page_orientation' => 'portrait',
        ));
    }

    public function history(Request $request)
    {
        $query = ReleveNoteGeneration::where('type', self::GENERATION_TYPE);
        if ($request->input('annee_academique')) {
            $query->where('annee_academique', $request->input('annee_academique'));
        }
        return view('eleves/attestations_reussite_soa_historique', array(
            'titre' => 'Historique des attestations de reussite SOA',
            'generations' => $query->orderBy('created_at', 'desc')->get()
        ));
    }

    public function destroy($id)
    {
        ReleveNoteGeneration::where('type', self::GENERATION_TYPE)->where('id', $id)->firstOrFail()->delete();
        return redirect()->to('/eleves/attestations_reussite_soa/historique')
            ->with('success', 'Historique des attestations supprime.');
    }

    private function buildAttestations($idClasse, $idCycle, $idNniveau)
    {
        $this->sourceReleveGeneration = null;
        $source = ReleveNoteGeneration::where('type', 'licence_soa')
            ->where('annee_academique', '=', session('year'))
            ->where('id_classe', '=', $idClasse)
            ->where('id_cycle', '=', $idCycle)
            ->where('id_nniveau', '=', $idNniveau)
            ->orderBy('id', 'desc')
            ->first();

        if (!$source) return array();
        $this->sourceReleveGeneration = array('id' => $source->id, 'date' => $source->created_at, 'annee_academique' => $source->annee_academique);
        $payload = json_decode($source->contenu);
        if (!$payload || !isset($payload->PV)) return array();

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
            if (!$student || !isset($releve->mgp) || !is_numeric($releve->mgp)) continue;

            $average20 = (float) $releve->mgp;
            if ($average20 > 20) {
                $average20 = $average20 / 5;
            }
            if ($average20 < 10) continue;

            $attestations[] = array(
                'eleve' => $student,
                'moyenne100' => $average20 * 5,
                'moyenne20' => $average20,
            );
        }
        return $attestations;
    }

    private function resolveSoaTutelle($idClasse)
    {
        if (!$idClasse) return null;
        $tutelles = TutelleAcademique::whereHas('specialites', function ($query) use ($idClasse) {
            $query->where('classe.id', '=', $idClasse);
        })->get();
        foreach ($tutelles as $tutelle) {
            $identity = strtoupper((isset($tutelle->nom) ? $tutelle->nom : '').' '.(isset($tutelle->tutelle_anglophone) ? $tutelle->tutelle_anglophone : '').' '.(isset($tutelle->ville) ? $tutelle->ville : '').' '.(isset($tutelle->localisation) ? $tutelle->localisation : ''));
            if (strpos($identity, 'SOA') !== false || strpos($identity, 'YAOUND') !== false) return $tutelle;
        }
        if (count($tutelles) > 0) return $tutelles->first();
        return TutelleAcademique::where(function ($query) {
            $query->where('nom', 'LIKE', '%SOA%')
                ->orWhere('nom', 'LIKE', '%YAOUND%')
                ->orWhere('tutelle_anglophone', 'LIKE', '%SOA%')
                ->orWhere('tutelle_anglophone', 'LIKE', '%YAOUND%')
                ->orWhere('ville', 'LIKE', '%YAOUND%')
                ->orWhere('localisation', 'LIKE', '%YAOUND%');
        })->first();
    }
}
