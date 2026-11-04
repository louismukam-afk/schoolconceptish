<?php namespace gesecole\Services;

class AcademicResultCalculator {
    public static function grade($mark)
    {
        if($mark===null)return null;
        foreach(array(17=>'A+',16=>'A',15=>'A-',14=>'B+',13=>'B',12=>'B-',11=>'C+',10=>'C',9=>'C-',8=>'D+',7=>'D')as $threshold=>$grade)if($mark>=$threshold)return $grade;
        return 'F';
    }
    public static function sequences($semester)
    {
        return (int) $semester % 2 ? array('cc' => 1, 'sn' => 3, 'rat' => 5, 'tpe' => 7) : array('cc' => 2, 'sn' => 4, 'rat' => 6, 'tpe' => 8);
    }

    // SchoolConcept et Test OK stockent les contributions pondérées /100.
    // Une valeur de 15 pour un CC à 30 % est donc 10/20, jamais 15/20.
    public function subject($notes, $weights, $semester)
    {
        $seq = self::sequences($semester);
        $normal = 0; $retake = 0; $complete = true; $configured = 0; $components = array();
        foreach (array('cc', 'tpe', 'sn') as $name) {
            $weight = isset($weights[$name]) ? (float) $weights[$name] : 0;
            $value = array_key_exists($seq[$name], $notes) ? $notes[$seq[$name]] : null;
            $components[$name] = $value === null ? null : (float) $value;
            if ($weight <= 0) continue;
            $configured += $weight;
            if ($value === null) $complete = false;
            else $normal += (float) $value;
        }
        $ratValue = array_key_exists($seq['rat'], $notes) ? $notes[$seq['rat']] : null;
        $components['rat'] = $ratValue === null ? null : (float) $ratValue;
        $normalComplete = $complete && abs($configured - 100) < 0.01;
        $retakeComplete = isset($weights['rat']) && (float) $weights['rat'] > 0 && $ratValue !== null;
        $retakeWeight = isset($weights['rat']) ? (float) $weights['rat'] : 0;
        foreach (array('cc', 'tpe') as $name) {
            $weight = isset($weights[$name]) ? (float) $weights[$name] : 0;
            $retakeWeight += $weight;
            if ($weight > 0 && $components[$name] === null) $retakeComplete = false;
            if ($weight > 0 && $components[$name] !== null) $retake += $components[$name];
        }
        $retakeComplete = $retakeComplete && abs($retakeWeight - 100) < 0.01;
        if ($retakeComplete) $retake += (float) $ratValue;
        $normal20 = $normalComplete ? round($normal / 5, 2) : null;
        $rat20 = $retakeComplete ? round($retake / 5, 2) : null;
        $final = $normal20; $session = 'N';
        // Même priorité que les listes Test OK : rattrapage si échec en normale.
        if (($normal20 === null || $normal20 < 10) && $rat20 !== null && ($final === null || $rat20 > $final)) {
            $final = $rat20; $session = 'R';
        }
        return array('components' => $components, 'normal' => $normal20, 'retake' => $rat20, 'final' => $final, 'grade'=>self::grade($final), 'session' => $session, 'valid' => $final !== null && $final >= 10, 'complete' => $final !== null);
    }

    public function summarize($subjects, $profile, $period)
    {
        $credit = 0; $expected = 0; $sum = 0; $den = 0; $incomplete = false; $semesters = array();
        foreach ($subjects as $subject) {
            $cr = (float) $subject['credit']; $expected += $cr;
            $result = $subject['result'];
            if (!$result['complete']) $incomplete = true;
            $value = $result['final'] === null ? 0 : $result['final'];
            if ($result['complete'] && $value >= $profile['credit_threshold']) $credit += $cr;
            $weight = $profile['average'] === 'arithmetic' ? 1 : $cr;
            $sum += $value * $weight; $den += $weight;
            $s = (int) $subject['semestre'] % 2 ? 1 : 2;
            if (!isset($semesters[$s])) $semesters[$s] = array('sum' => 0, 'credit' => 0);
            $semesters[$s]['sum'] += $value * $cr; $semesters[$s]['credit'] += $cr;
        }
        $average = $den > 0 ? round($sum / $den, 2) : null;
        if ($profile['average'] === 'weighted_semesters' && $period === 'annuel') {
            $total = 0;
            foreach (array(1, 2) as $s) {
                if (!isset($semesters[$s]) || $semesters[$s]['credit'] <= 0) { $incomplete = true; continue; }
                $total += round($semesters[$s]['sum'] / $semesters[$s]['credit'], 2);
            }
            $average = round($total / 2, 2);
        }
        $decision = 'NON ADMIS';
        if ($expected <= 0 || $incomplete || $average === null) $decision = 'A DELIBERER';
        elseif ($profile['admission'] === 'credits') $decision = $credit >= $profile['credits_required'] ? 'ADMIS' : 'NON ADMIS';
        elseif ($average > $profile['average_required']) $decision = 'ADMIS';
        elseif ($average == $profile['average_required']) $decision = 'A DELIBERER';
        if($period!=='annuel')$decision=$incomplete || $expected<=0?'A DELIBERER':'SEMESTRE EVALUE';
        $mention=$average===null?'':($average>=16?'Très bien':($average>=14?'Bien':($average>=12?'Assez bien':($average>=10?'Passable':'Insuffisant'))));
        return array('average' => $average, 'grade'=>self::grade($average), 'mgp'=>$average===null?null:round($average/5,2), 'mention'=>$mention, 'credits' => $credit, 'expected' => $expected, 'decision' => $decision, 'incomplete' => $incomplete);
    }
}
