<?php
    $student=$r['student'];
    $semesterTotals=array();
    $subjectsByType=array();$typeTotals=array();$semesterNames=array();
    foreach($r['subjects'] as $subject){
        $semester=(int)$subject['semestre'];
        $type=(int)$subject['type'];$semesterNames[$semester]=$semester;$subjectsByType[$semester][$type][]=$subject;
        if(!isset($semesterTotals[$semester]))$semesterTotals[$semester]=array('sum'=>0,'credits'=>0,'expected'=>0,'complete'=>true);
        if(!isset($typeTotals[$semester][$type]))$typeTotals[$semester][$type]=array('sum'=>0,'credits'=>0,'expected'=>0,'acquired'=>0,'complete'=>true);
        $semesterTotals[$semester]['expected']+=(float)$subject['credit'];
        $typeTotals[$semester][$type]['expected']+=(float)$subject['credit'];
        if(!$subject['result']['complete'] || $subject['result']['final']===null){$semesterTotals[$semester]['complete']=false;$typeTotals[$semester][$type]['complete']=false;}
        else{$value=$subject['result']['final'];$weight=(float)$subject['credit'];$semesterTotals[$semester]['sum']+=$value*$weight;$typeTotals[$semester][$type]['sum']+=$value*$weight;if($value>=$context['profile']['credit_threshold']){$semesterTotals[$semester]['credits']+=$weight;$typeTotals[$semester][$type]['credits']+=$weight;$typeTotals[$semester][$type]['acquired']+=$weight;}}
    }
    ksort($semesterNames);$typeOrder=array(0,2,1);$typeLabels=array(0=>'UE Fondamentale / Fundamental TU',2=>'UE Professionnelle / Professional TU',1=>'Autre UE / Other TU');$typeCodes=array(0=>'UEF',2=>'UEP',1=>'UEA');
    $birth=$student->date_naiss;if($birth && preg_match('/^\d{4}-\d{2}-\d{2}/',$birth))$birth=date('d/m/Y',strtotime($birth));
    $isabeeGrade=function($mark){if($mark===null)return '';foreach(array(18=>'A+',16=>'A',14=>'B+',13=>'B',12=>'B-',11=>'C+',10=>'C',8=>'C-',6=>'D') as $threshold=>$grade)if($mark>=$threshold)return $grade;return 'E';};
?>
<section class="individual isabee-transcript">
    <div class="isabee-official">
        <div lang="fr"><strong>Republique du Cameroun</strong><br>Paix &ndash; Travail &ndash; Patrie<br>********<br>Minist&egrave;re de l&rsquo;Enseignement Sup&eacute;rieur<br>****<br>UNIVERSIT&Eacute; D&rsquo;EBOLOWA<br>INSTITUT SUPERIEUR D&rsquo;AGRICULTURE, DU BOIS, DE L&rsquo;EAU, ET DE L&rsquo;ENVIRONNEMENT (ISABEE)<br>www.univ-Ebolowa-isabee.com<br>Email : isabee@gmail.com<br>BP: 118 Ebolowa (Cameroun)<br>T&eacute;l./Fax : (237) 695320131/656016406</div>
        <div class="isabee-faculty">@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Universit&eacute; d'Ebolowa">@endif</div>
        <div lang="en"><strong>Republic of Cameroon</strong><br>Peace &ndash; Work &ndash; Fatherland<br>********<br>Ministry of Higher Education<br>****<br>UNIVERSITY OF EBOLOWA<br>HIGHER INSTITUTE OF AGRICULTURE, FORESTRY, WATER AND ENVIRONMENT (HIAFWE)<br>www.univ-Ebolowa-isabee.com<br>Email : isabee@gmail.com<br>PO.BOX: 118 Ebolowa (Cameroon)<br>phone./Fax : (237) 695320131/656016406</div>
    </div>
    <div class="isabee-school">
        <div class="isabee-mark">@if($logo_tutelle)<img src="{{ $logo_tutelle }}" alt="Logo ISABEE">@endif</div>
        <div class="isabee-school-title"><strong>INSTITUT SUPERIEUR HINTEL</strong><br>Autorisation de Cr&eacute;ation N&deg; T4-09342 du 17 D&eacute;cembre 2014<br>Arr&ecirc;t&eacute; MINESUP N&deg; 16-0855/MINESUP/ESUP du 14/09/2016<br>BP:4615 Yaound&eacute;-Cameroun Tel:(+237)695320131/656016406 Fax:(+237)220 04 68<br>Cycle BTS - LICENCES ET MASTERS PROFESSIONNELLES</div>
        <div class="isabee-mark">@if($logo_campus)<img src="{{ $logo_campus }}" alt="Logo de l'ISH">@endif</div>
    </div>
    <div class="isabee-identity">
        <div><strong>Nom / NAME :</strong> {{ $student->nom }} {{ $student->prenom }}<br><strong>Date de naissance / BORN ON :</strong> {{ $birth }}<br><strong>Lieu de naissance / AT :</strong> {{ $student->lieu_naiss }}<br><strong>Matricule / REGISTRATION :</strong> {{ $student->matricule }}<br><strong>Ann&eacute;e acad&eacute;mique / ACADEMIC YEAR :</strong> {{ $context['year'] }}</div>
        <div><strong>Fili&egrave;re :</strong> {{ $isabee_filiere ? $isabee_filiere->nom : $context['classe']->nom }}<br><strong>Sp&eacute;cialit&eacute; :</strong> {{ $context['classe']->nom }}<br><strong>Cycle :</strong> {{ $context['cycle']->nom }}<br><strong>Niveau :</strong> {{ $context['nniveau']->nom }}<br><strong>Session :</strong> {{ $context['session_mode']==='normal'?'Normale':'Apr&egrave;s rattrapage' }}</div>
    </div>
    <table class="isabee-marks"><colgroup><col class="isabee-ue"><col class="isabee-code"><col class="isabee-course"><col class="isabee-type"><col class="isabee-mark"><col class="isabee-mgp"><col class="isabee-grade"><col class="isabee-credit"><col class="isabee-earned"><col class="isabee-year"><col class="isabee-semester"></colgroup>
        <thead><tr><th>Code UE</th><th>Code<br>cours</th><th>Intitul&eacute; UE / Cours</th><th>Type</th><th>Note<br>/20</th><th>P/4</th><th>Grade</th><th>Cr&eacute;dits<br>pr&eacute;vus</th><th>Cr&eacute;dits<br>acquis</th><th>Ann&eacute;e<br>acad&eacute;mique</th><th>Session</th></tr></thead><tbody>
        @foreach($semesterNames as $currentSemester)
            <tr class="isabee-semester-row"><th colspan="11">Semestre {{ $currentSemester }} / Semester {{ $currentSemester }}</th></tr>
            <?php $availableTypes=array_keys($subjectsByType[$currentSemester]);$types=array();foreach($typeOrder as $preferred)if(in_array($preferred,$availableTypes))$types[]=$preferred;foreach($availableTypes as $available)if(!in_array($available,$typeOrder))$types[]=$available; ?>
            @foreach($types as $type)
                @if(isset($subjectsByType[$currentSemester][$type]))
                    <?php $group=$typeTotals[$currentSemester][$type];$groupAverage=$group['complete']&&$group['expected']>0?$group['sum']/$group['expected']:null;$groupCode=isset($typeCodes[$type])?$typeCodes[$type]:'UE';$groupLabel=isset($typeLabels[$type])?$typeLabels[$type]:'Autre UE / Other TU'; ?>
                    <tr class="isabee-ue-row"><td>{{ $groupCode }}</td><td></td><th colspan="2" class="isabee-left">{{ $groupLabel }}</th><td>{{ $groupAverage===null?'':number_format($groupAverage,2,',',' ') }}</td><td>{{ $groupAverage===null?'':number_format($groupAverage/5,2,',',' ') }}</td><td>{{ $isabeeGrade($groupAverage) }}</td><td>{{ number_format($group['expected'],1) }}</td><td>{{ number_format($group['acquired'],1) }}</td><td>{{ $context['year'] }}</td><td>S{{ $currentSemester }}</td></tr>
                    @foreach($subjectsByType[$currentSemester][$type] as $subject)
                        <?php $mark=$subject['result']['final']; ?>
                        <tr><td></td><td>{{ $subject['code'] }}</td><td class="isabee-left">{{ $subject['nom'] }}</td><td>OB</td><td>{{ $mark===null?'':number_format($mark,2,',',' ') }}</td><td>{{ $mark===null?'':number_format($mark/5,2,',',' ') }}</td><td>{{ $isabeeGrade($mark) }}</td><td>{{ number_format((float)$subject['credit'],1) }}</td><td>{{ $subject['result']['complete']&&$mark>=$context['profile']['credit_threshold']?number_format((float)$subject['credit'],1):'0' }}</td><td>{{ $context['year'] }}</td><td>S{{ $currentSemester }}</td></tr>
                    @endforeach
                @endif
            @endforeach
            <?php $tot=$semesterTotals[$currentSemester];$avg=$tot['complete']&&$tot['expected']>0?$tot['sum']/$tot['expected']:null; ?>
            <tr class="isabee-total"><th colspan="4">TOTAL SEMESTRE / SEMESTER AVERAGE {{ $currentSemester }}</th><td>{{ $avg===null?'':number_format($avg,2,',',' ') }}</td><td>{{ $avg===null?'':number_format($avg/5,2,',',' ') }}</td><td></td><td>{{ number_format($tot['expected'],1) }}</td><td>{{ number_format($tot['credits'],1) }}</td><td>{{ $context['year'] }}</td><td>S{{ $currentSemester }}</td></tr>
        @endforeach
        <tr class="isabee-total"><th colspan="4">TOTAL ANNUEL / ANNUAL AVERAGE</th><td>{{ $r['summary']['incomplete']?'':number_format($r['summary']['average'],2,',',' ') }}</td><td>{{ $r['summary']['incomplete']?'':number_format($r['summary']['mgp'],2,',',' ') }}</td><td>{{ $r['summary']['decision'] }}</td><td>{{ number_format($r['summary']['expected'],1) }}</td><td>{{ number_format($r['summary']['credits'],1) }}</td><td>{{ $context['year'] }}</td><td>ANNUEL</td></tr>
    </tbody></table>
    <div class="isabee-summary"><div class="isabee-grading"><strong>Grade / Appreciation / Note</strong><div><span>A+</span><span>Excellent / Excellent</span><span>18-20</span></div><div><span>A</span><span>Very good / Tr&egrave;s bien</span><span>16-17.99</span></div><div><span>B+</span><span>Good / Bien</span><span>14-15.99</span></div><div><span>B</span><span>Fairly good / Assez bien</span><span>13-13.99</span></div><div><span>B-</span><span>Fairly good / Assez bien</span><span>12-12.99</span></div><div><span>C+</span><span>Passable / Passable</span><span>11-11.99</span></div><div><span>C</span><span>Fair / Moyen</span><span>10-10.99</span></div><div><span>C-</span><span>Passable / Fair</span><span>8-9.99</span></div><div><span>D</span><span>Weak / Faible</span><span>6-7.99</span></div><div><span>E</span><span>Fail / Echec</span><span>0-5.99</span></div></div><div>MGP / GPA<br><strong>{{ $r['summary']['incomplete']?'':number_format($r['summary']['mgp'],2,',',' ') }}</strong></div><div>D&Eacute;CISION / DECISION<br><strong>{{ $r['summary']['decision'] }}</strong></div></div>
    <div class="isabee-issued">Fait &agrave; {{ $context['campus']->ville ?: 'Ebolowa' }}, le ................................<br>Done in {{ $context['campus']->ville ?: 'Ebolowa' }}, on ................................</div>
    <div class="isabee-signatures"><strong>Le Directeur de l'ISH</strong><strong>Le Directeur de l'ISABEE</strong></div>
    <p class="isabee-note">{{ config('academic_stationery.note_releve') }}</p>
</section>
