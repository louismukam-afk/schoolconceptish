<?php namespace gesecole\Services;

use gesecole\Campus;
use gesecole\TutelleAcademique;
use gesecole\classe;
use gesecole\cycle;
use gesecole\NNiveau;
use Illuminate\Support\Facades\DB;

class AcademicDocumentService {
    public static function isManagedContext($classId,$cycleId,$levelId,$year)
    {
        if(!$classId || !$year)return false;
        $query=DB::table('session_exams as se')->join('classe_matiere as cm','cm.id','=','se.id_classe_matiere')
            ->join('tutelles_academiques as t','t.id','=','se.id_tutelle')->whereNotNull('t.modele_documents')
            ->where('cm.id_classe',(int)$classId)->where('se.annee_academique',$year);
        if($cycleId)$query->where('cm.id_cycle',(int)$cycleId);
        if($levelId)$query->where('cm.id_nniveau',(int)$levelId);
        return $query->exists();
    }

    public function context($input, $campusId, $year)
    {
        $profiles = config('academic_documents.profiles');
        if (!isset($input['modele']) || !isset($profiles[$input['modele']])) abort(422, 'Modèle de tutelle invalide.');
        $context = array('modele' => $input['modele'], 'profile' => $profiles[$input['modele']], 'year' => $year);
        foreach (array('id_tutelle', 'id_classe', 'id_cycle', 'id_nniveau') as $key) {
            if (!isset($input[$key]) || !ctype_digit((string) $input[$key]) || (int) $input[$key] <= 0) abort(422, 'Sélection obligatoire : '.$key);
            $context[$key] = (int) $input[$key];
        }
        if (!$campusId) abort(422, 'Sélectionnez un campus.');
        $context['id_campus'] = (int) $campusId;
        $context['campus'] = Campus::findOrFail($campusId);
        $context['tutelle'] = TutelleAcademique::findOrFail($context['id_tutelle']);
        if ($context['tutelle']->modele_documents !== $context['modele']) abort(422, 'Ce modèle ne correspond pas à la tutelle sélectionnée.');
        $context['classe'] = classe::findOrFail($context['id_classe']);
        $context['cycle'] = cycle::findOrFail($context['id_cycle']);
        $context['nniveau'] = NNiveau::where('id', $context['id_nniveau'])->where('id_cycle', $context['id_cycle'])->first();
        if (!$context['nniveau']) abort(422, 'Ce niveau ne correspond pas au cycle sélectionné.');
        if (!DB::table('campus_specialite')->where('id_campus', $campusId)->where('id_classe', $context['id_classe'])->exists()) abort(403, 'Spécialité non affectée au campus.');
        if (!DB::table('specialite_tutelle')->where('id_tutelle', $context['id_tutelle'])->where('id_classe', $context['id_classe'])->exists()) abort(403, 'Spécialité non affectée à cette tutelle.');
        $context['periode'] = isset($input['periode']) ? (string) $input['periode'] : 'annuel';
        if (!in_array($context['periode'], array('s1', 's2', 'annuel'), true)) abort(422, 'Période invalide.');
        $context['session_mode'] = isset($input['session_mode']) ? $input['session_mode'] : 'final';
        if (!in_array($context['session_mode'], array('normal', 'final'), true)) abort(422, 'Session de résultats invalide.');
        return $context;
    }

    public function students($context)
    {
        $query = DB::table('inscription as i')->join('eleve as e', 'e.id', '=', 'i.id_eleve')
            ->where('i.annee_scolaire', $context['year'])->where('i.id_classe', $context['id_classe'])
            ->where('i.id_cycle', $context['id_cycle'])->where('i.id_nniveau', $context['id_nniveau'])
            ->where(function ($q) { $q->where('i.desinscrire', 0)->orWhereNull('i.desinscrire'); });
        $this->campus($query, 'i.id_campus', $context);
        return $query->select('e.id', 'e.nom', 'e.prenom', 'e.matricule', 'e.date_naiss', 'e.lieu_naiss')->distinct()->orderBy('e.nom')->orderBy('e.prenom')->get();
    }

    public function sessions($context, $evaluatedOnly = false)
    {
        $moduleIds = DB::table('groupe_matieres')->where('id_classe',$context['id_classe'])->where('id_nniveau',$context['id_nniveau'])->lists('id_matiere_mere');
        $query = DB::table('session_exams as se')
            ->join('classe_matiere as cm', 'cm.id', '=', 'se.id_classe_matiere')
            ->join('matiere as m', 'm.id', '=', 'cm.id_matiere')
            ->where('se.id_tutelle', $context['id_tutelle'])->where('se.annee_academique', $context['year'])
            ->where('cm.id_classe', $context['id_classe'])->where('cm.id_cycle', $context['id_cycle'])->where('cm.id_nniveau', $context['id_nniveau']);
        // Les UE mères sont des regroupements de matières : ne pas compter
        // une deuxième fois leurs crédits ni exiger une note directe de l'UE.
        if(count($moduleIds))$query->whereNotIn('cm.id_matiere',array_values(array_unique($moduleIds)));
        $query->where(function($q){foreach(array('cc1','tpe1','sn1','ratrappage1','cc2','tpe2','sn2','ratrappage2')as $column)$q->orWhere('se.'.$column,'>',0);});
        if ($context['periode'] === 's1') $query->whereIn('cm.semestre', array(0, 1, 3, 5, 7, 9));
        if ($context['periode'] === 's2') $query->whereIn('cm.semestre', array(0, 2, 4, 6, 8, 10));
        if($evaluatedOnly){
            $service=$this;
            $query->whereExists(function($q)use($context,$service){
                $q->select(DB::raw(1))->from('note as evaluated')->whereRaw('evaluated.id_session = se.id')->whereRaw('evaluated.id_classe_matiere = cm.id')
                    ->where('evaluated.annee_scolaire',$context['year'])->where('evaluated.id_classe',$context['id_classe']);
                $service->campus($q,'evaluated.id_campus',$context);
            });
        }
        $rows = $query->select('se.*', 'cm.id as programme_id', 'cm.semestre', 'cm.coef as credit', 'cm.type', 'cm.id_code', 'm.id as matiere_id', 'm.nom', 'm.nom_anglais')->orderBy('cm.semestre')->orderBy('m.nom')->get();
        $seen = array();
        foreach ($rows as $row) {
            if (isset($seen[$row->programme_id])) abort(422, 'Plusieurs sessions pour la même matière, tutelle et année. Corrigez la configuration avant de produire les résultats.');
            $seen[$row->programme_id] = true;
        }
        $expanded = array();
        foreach ($rows as $row) {
            if ((int) $row->semestre !== 0) {
                $parity=(int)$row->semestre%2?1:2;
                if((float)$row->{'cc'.$parity}+(float)$row->{'tpe'.$parity}+(float)$row->{'sn'.$parity}+(float)$row->{'ratrappage'.$parity}>0)$expanded[] = $row;
                continue;
            }
            foreach (array(1,2) as $semester) {
                if (($context['periode'] === 's1' && $semester === 2) || ($context['periode'] === 's2' && $semester === 1)) continue;
                if ((float) $row->{'cc'.$semester} + (float) $row->{'sn'.$semester} + (float) $row->{'tpe'.$semester} <= 0) continue;
                $copy = clone $row; $copy->semestre = $semester; $expanded[] = $copy;
            }
        }
        return $expanded;
    }

    public function campus($query, $column, $context)
    {
        $historical = preg_match('/^(\d{4})-(\d{4})$/', $context['year'], $parts)
            && (int) $parts[2] === (int) $parts[1] + 1 && (int) $parts[1] < (int) config('campus.first_school_year', 2026);
        $query->where(function ($q) use ($column, $context, $historical) {
            $q->where($column, $context['id_campus']);
            if ($historical) $q->orWhereNull($column);
        });
        return $query;
    }

    public function build($context, $studentId = null, $sessionId = null, $evaluatedOnly = true)
    {
        $students = $this->students($context); $sessions = $this->sessions($context, $evaluatedOnly);
        if ($sessionId) {
            $sessions = array_values(array_filter($sessions, function ($s) use ($sessionId) { return (int) $s->id === (int) $sessionId; }));
            if (!$sessions) abort(404, 'Session absente de ce contexte.');
        }
        if (!$sessions) abort(422, 'Aucune session configurée pour cette tutelle, cette année et cette spécialité. Configurez les sessions par tutelle dans Administration.');
        $studentIds = array(); $sessionIds = array(); $programmes = array();
        foreach ($students as $s) $studentIds[] = $s->id;
        foreach ($sessions as $s) { $sessionIds[] = $s->id; $programmes[] = $s->programme_id; }
        $query = DB::table('note')->where('annee_scolaire', $context['year'])->where('id_classe', $context['id_classe'])
            ->whereIn('id_eleve', $studentIds)->whereIn('id_session', $sessionIds)->whereIn('id_classe_matiere', $programmes);
        $this->campus($query, 'id_campus', $context);
        $map = array(); $metadata = array();
        foreach ($query->orderBy('id')->get() as $n) {
            if (isset($map[$n->id_eleve][$n->id_session]) && array_key_exists($n->sequence, $map[$n->id_eleve][$n->id_session])) abort(422, 'Notes en double dans une session. Résolvez les doublons avant la génération.');
            $map[$n->id_eleve][$n->id_session][$n->sequence] = $n->note;
            $metadata[$n->id_eleve][$n->id_session][$n->sequence] = $n;
        }
        $calculator = new AcademicResultCalculator; $results = array();
        $codes = DB::table('codages')->lists('nom', 'id');
        $groups = DB::table('groupe_matieres')->where('id_classe', $context['id_classe'])->where('id_nniveau', $context['id_nniveau'])->whereIn('id_classe_matiere', $programmes)->get();
        $moduleNames = DB::table('matiere')->lists('nom', 'id'); $groupMap = array();
        foreach ($groups as $g) $groupMap[$g->id_classe_matiere] = isset($moduleNames[$g->id_matiere_mere]) ? $moduleNames[$g->id_matiere_mere] : '';
        foreach ($sessions as $s) {
            $s->code = isset($codes[$s->id_code]) ? $codes[$s->id_code] : $s->id_code;
            $s->module = isset($groupMap[$s->programme_id]) ? $groupMap[$s->programme_id] : $s->nom;
        }
        $teachersQuery = DB::table('dispense as d')->join('personnel as p', 'p.id', '=', 'd.id_personnel')
            ->where('d.annee_scolaire', $context['year'])->where('d.id_classe', $context['id_classe'])->where('d.id_nniveau', $context['id_nniveau']);
        $this->campus($teachersQuery, 'p.id_campus', $context);
        $teachers = array();
        foreach ($teachersQuery->select('d.id_matiere', 'p.nom')->get() as $t) $teachers[$t->id_matiere][] = trim($t->nom);
        foreach ($students as $student) {
            $subjects = array();
            foreach ($sessions as $s) {
                $parity = (int) $s->semestre % 2 ? 1 : 2;
                $weights = array('cc' => $s->{'cc'.$parity}, 'sn' => $s->{'sn'.$parity}, 'tpe' => $s->{'tpe'.$parity}, 'rat' => $s->{'ratrappage'.$parity});
                $notes = isset($map[$student->id][$s->id]) ? $map[$student->id][$s->id] : array();
                if ($context['session_mode'] === 'normal') unset($notes[$parity === 1 ? 5 : 6]);
                $subjects[] = array('session_id' => $s->id, 'matiere_id' => $s->matiere_id, 'programme_id' => $s->programme_id, 'nom' => $s->nom, 'nom_anglais' => $s->nom_anglais, 'code' => $s->code, 'module' => $s->module, 'type' => $s->type, 'semestre' => $s->semestre, 'credit' => $s->credit, 'enseignants' => isset($teachers[$s->matiere_id]) ? implode(', ', array_unique($teachers[$s->matiere_id])) : '', 'weights' => $weights, 'result' => $calculator->subject($notes, $weights, $s->semestre));
            }
            $summary = $calculator->summarize($subjects, $context['profile'], $context['periode']);
            $results[] = array('student' => $student, 'subjects' => $subjects, 'summary' => $summary, 'rank' => null);
        }
        usort($results, function ($a, $b) {
            if($a['summary']['incomplete']!==$b['summary']['incomplete'])return $a['summary']['incomplete']?1:-1;
            if ($a['summary']['average'] == $b['summary']['average']) return strcmp($a['student']->nom.' '.$a['student']->prenom, $b['student']->nom.' '.$b['student']->prenom);
            return $a['summary']['average'] < $b['summary']['average'] ? 1 : -1;
        });
        $previous = null; $rank = 0; $position = 0;
        foreach ($results as $i => &$r) {
            if($r['summary']['incomplete']){$r['rank']=null;continue;}
            $position++;
            if ($r['summary']['average'] !== $previous) $rank = $position;
            $r['rank'] = $rank;
            $previous = $r['summary']['average'];
        }
        unset($r);
        if ($studentId) {
            $results = array_values(array_filter($results, function ($r) use ($studentId) { return (int) $r['student']->id === (int) $studentId; }));
            if (!$results) abort(404, 'Étudiant non inscrit dans le contexte sélectionné.');
        }
        return array('context' => $context, 'results' => $results, 'sessions' => $sessions, 'metadata' => $metadata);
    }
}
