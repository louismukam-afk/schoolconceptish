@if((int) $trimestre === 6 && !empty($semester_summaries))
    <?php
        $studentId = isset($releve->eleve->id) ? $releve->eleve->id : null;
        $studentSummary = null;

        if (is_array($semester_summaries) && $studentId !== null && isset($semester_summaries[$studentId])) {
            $studentSummary = $semester_summaries[$studentId];
        } elseif (is_object($semester_summaries) && $studentId !== null && isset($semester_summaries->{$studentId})) {
            $studentSummary = $semester_summaries->{$studentId};
        }

        $semesterFive = null;
        $semesterSix = null;

        if ($studentSummary) {
            $semesterFive = is_array($studentSummary) && isset($studentSummary['semester_5'])
                ? $studentSummary['semester_5']
                : (isset($studentSummary->semester_5) ? $studentSummary->semester_5 : null);
            $semesterSix = is_array($studentSummary) && isset($studentSummary['semester_6'])
                ? $studentSummary['semester_6']
                : (isset($studentSummary->semester_6) ? $studentSummary->semester_6 : null);
        }

        if (!$semesterSix && isset($releve->matieres)) {
            $fallbackNoteTotal = 0;
            $fallbackCreditTotal = 0;
            $fallbackGpaTotal = 0;

            foreach ($releve->matieres as $fallbackMatiere) {
                if (!isset($fallbackMatiere->note) || !is_numeric($fallbackMatiere->note)) continue;
                $fallbackCredit = isset($fallbackMatiere->credit) ? (float) $fallbackMatiere->credit : 0;
                if ($fallbackCredit <= 0) continue;

                $fallbackNoteTotal += ((float) $fallbackMatiere->note) * $fallbackCredit;
                $fallbackCreditTotal += $fallbackCredit;
                $fallbackGpaTotal += getGrade($fallbackMatiere->note)[1] * $fallbackCredit;
            }

            if ($fallbackCreditTotal > 0) {
                $semesterSix = array(
                    'credits' => $fallbackCreditTotal,
                    'note100' => $fallbackNoteTotal / $fallbackCreditTotal,
                    'gpa' => $fallbackGpaTotal / $fallbackCreditTotal
                );
            }
        }

        $creditsFive = $semesterFive ? (float) (is_array($semesterFive) ? $semesterFive['credits'] : $semesterFive->credits) : 0;
        $creditsSix = $semesterSix ? (float) (is_array($semesterSix) ? $semesterSix['credits'] : $semesterSix->credits) : 0;
        $noteFive = $semesterFive ? (is_array($semesterFive) ? $semesterFive['note100'] : $semesterFive->note100) : null;
        $noteSix = $semesterSix ? (is_array($semesterSix) ? $semesterSix['note100'] : $semesterSix->note100) : null;
        $gpaFive = $semesterFive ? (is_array($semesterFive) ? $semesterFive['gpa'] : $semesterFive->gpa) : null;
        $gpaSix = $semesterSix ? (is_array($semesterSix) ? $semesterSix['gpa'] : $semesterSix->gpa) : null;

        $totalCredits = $creditsFive + $creditsSix;
        $weightedNoteFive = $noteFive !== null ? $noteFive * $creditsFive : 0;
        $weightedNoteSix = $noteSix !== null ? $noteSix * $creditsSix : 0;
        $weightedGpaFive = $gpaFive !== null ? $gpaFive * $creditsFive : 0;
        $weightedGpaSix = $gpaSix !== null ? $gpaSix * $creditsSix : 0;
        $cumulativeNote = $totalCredits > 0 ? ($weightedNoteFive + $weightedNoteSix) / $totalCredits : null;
        $cumulativeGpa = $totalCredits > 0 ? ($weightedGpaFive + $weightedGpaSix) / $totalCredits : null;

        $diplomaClass = '';
        if ($cumulativeGpa !== null) {
            if ($cumulativeGpa >= 3.60) $diplomaClass = 'PREMIERE CLASSE';
            elseif ($cumulativeGpa >= 3.00) $diplomaClass = 'DEUXIEME CLASSE (Division Superieure)';
            elseif ($cumulativeGpa >= 2.50) $diplomaClass = 'DEUXIEME CLASSE (Division Inferieure)';
            elseif ($cumulativeGpa >= 2.25) $diplomaClass = 'TROISIEME CLASSE';
            elseif ($cumulativeGpa >= 2.00) $diplomaClass = 'VALIDATION';
        }
    ?>
    <table class="table table-striped table-condensed table-inverse ndere-semester-summary" style="margin-top:8px;margin-bottom:0;">
        <tbody>
        <tr>
            <td><strong>Total credits S5</strong></td>
            <td style="text-align:center">{{ $semesterFive !== null ? number_format($creditsFive, 0) : '' }}</td>
            <td><strong>Moyenne du Semestre S5<br><em>Average Semester S5</em></strong></td>
            <td style="text-align:center">{{ $noteFive !== null ? number_format($noteFive / 5, 2) : '' }}</td>
            <td style="text-align:center">{{ $noteFive !== null ? number_format($noteFive, 2) : '' }}</td>
            <td><strong>Premier Semestre MGP<br><em>First Semester GPA</em></strong></td>
            <td style="text-align:center">{{ $gpaFive !== null ? number_format($gpaFive, 1) : '' }}</td>
        </tr>
        <tr>
            <td><strong>Total credits S6</strong></td>
            <td style="text-align:center">{{ $semesterSix !== null ? number_format($creditsSix, 0) : '' }}</td>
            <td><strong>Moyenne du Semestre S6<br><em>Average Semester S6</em></strong></td>
            <td style="text-align:center">{{ $noteSix !== null ? number_format($noteSix / 5, 2) : '' }}</td>
            <td style="text-align:center">{{ $noteSix !== null ? number_format($noteSix, 2) : '' }}</td>
            <td><strong>Second Semestre MGP<br><em>Second Semester GPA</em></strong></td>
            <td style="text-align:center">{{ $gpaSix !== null ? number_format($gpaSix, 1) : '' }}</td>
        </tr>
        <tr>
            <td><strong>Total credits L3</strong></td>
            <td style="text-align:center">{{ ($semesterFive !== null || $semesterSix !== null) ? number_format($totalCredits, 0) : '' }}</td>
            <td><strong>Moyenne L3<br><em>Average L3</em></strong></td>
            <td style="text-align:center">{{ $cumulativeNote !== null ? number_format($cumulativeNote / 5, 2) : '' }}</td>
            <td style="text-align:center">{{ $cumulativeNote !== null ? number_format($cumulativeNote, 2) : '' }}</td>
            <td><strong>Moyenne Generale Ponderee Cumulative<br><em>Cumulative GPA</em></strong></td>
            <td style="text-align:center">{{ $cumulativeGpa !== null ? number_format($cumulativeGpa, 1) : '' }}</td>
        </tr>
        </tbody>
    </table>
    <table class="table table-condensed table-inverse ndere-diploma-summary" style="margin-top:0;margin-bottom:0;">
        <tbody>
        <tr>
            <td style="width:17%;"><strong>CLASSE DE DIPLOME :</strong></td>
            <td style="width:48%;text-align:center;"><strong>{{ $diplomaClass }}</strong></td>
            <td style="width:20%;text-align:center;"><strong>DECISION</strong></td>
            <td style="width:15%;text-align:center;"><strong>{{ $cumulativeGpa !== null ? getDecision($cumulativeGpa) : '' }}</strong></td>
        </tr>
        </tbody>
    </table>
    <table class="table table-condensed table-inverse ndere-diploma-legend" style="margin-top:0;">
        <tbody>
        <tr>
            <td style="width:68%;font-size:9px;line-height:1.35;">
                [00-30[ = 0.0 = F;&nbsp;&nbsp;&nbsp; [30-40[ = 1.0 = E;&nbsp;&nbsp;&nbsp; [40-45[ = 1.3 = D;&nbsp;&nbsp;&nbsp;
                [45-50[ = 1.7 = C-;&nbsp;&nbsp;&nbsp; [50-55[ = 2.0 = C;&nbsp;&nbsp;&nbsp; [55-60[ = 2.3 = C+;<br>
                [60-65[ = 2.7 = B-;&nbsp;&nbsp;&nbsp; [65-70[ = 3.0 = B;&nbsp;&nbsp;&nbsp; [70-80[ = 3.3 = B+;&nbsp;&nbsp;&nbsp;
                [80-90[ = 3.7 = A;&nbsp;&nbsp;&nbsp; [90-100[ = 4.0 = A+.<br><br>
                *=NOTE OBTENUE APRES RATTRAPAGE&nbsp;&nbsp;&nbsp;&nbsp;
                MGP= MOYENNE GENERALE PONDEREE&nbsp;&nbsp;&nbsp;&nbsp; MGPC= MOYENNE GENERALE PONDEREE CUMULATIVE<br>
                GPA=GRADE POINT AVERAGE&nbsp;&nbsp;&nbsp;&nbsp; NP= NOTE PONDEREE&nbsp;&nbsp;&nbsp;&nbsp; WP= Weighted Point
            </td>
            <td style="width:32%;font-size:9px;line-height:1.25;">
                <strong>CLASSE DE DIPLOME</strong><br>
                <strong>MOYENNE PONDEREE / CLASSIFICATION DU DIPLOME</strong><br>
                3.60-4.00 / PREMIERE CLASSE<br>
                3.00-3.59 / DEUXIEME CLASSE-DS<br>
                2.50-2.99 / DEUXIEME CLASSE-DI<br>
                2.25-2.49 / TROISIEME CLASSE<br>
                2.00-2.24 / VALIDATION<br>
                DS (Division Superieure)<br>
                DI (Division Inferieure)
            </td>
        </tr>
        </tbody>
    </table>
@endif
