<?php namespace gesecole\Services;

class NgaoundereTranscriptService {
    public function pages($results, $context)
    {
        $pages = array();
        $calculator = new AcademicResultCalculator;
        foreach ($results as $result) {
            $bySemester = array();
            foreach ($result['subjects'] as $subject) {
                $semester = (int) $subject['semestre'];
                if ($semester > 0) $bySemester[$semester][] = $subject;
            }
            ksort($bySemester);
            foreach ($bySemester as $semester => $subjects) {
                $summary = $calculator->summarize($subjects, $context['profile'], $semester % 2 ? 's1' : 's2');
                $pages[] = array(
                    'student' => $result['student'],
                    'semester' => $semester,
                    'subjects' => $subjects,
                    'summary' => $summary,
                    'annual' => $context['periode'] === 'annuel' && $semester % 2 === 0 ? $result['summary'] : null,
                );
            }
        }
        return $pages;
    }

    public static function observation($mark)
    {
        if ($mark === null) return 'Note manquante';
        if ($mark >= 17) return 'Excellent';
        if ($mark >= 16) return 'Tres bien';
        if ($mark >= 14) return 'Bien';
        if ($mark >= 12) return 'Assez bien';
        if ($mark >= 10) return 'Passable';
        if ($mark >= 8) return 'Faible';
        return 'Insuffisant';
    }
}
