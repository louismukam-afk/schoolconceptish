<?php
    $student=$r['student'];$campus=$context['campus'];
    $polyGroups=array();$polyCredits=0.0;$polyExpected=0.0;
    foreach($r['subjects'] as $subject){
        $key=trim($subject['module'])!==''?$subject['module']:'Autres enseignements';
        if(!isset($polyGroups[$key]))$polyGroups[$key]=array('subjects'=>array(),'sum'=>0.0,'credits'=>0.0,'earned'=>0.0,'complete'=>true);
        $polyGroups[$key]['subjects'][]=$subject;$weight=(float)$subject['credit'];$polyGroups[$key]['credits']+=$weight;$polyExpected+=$weight;
        if(!$subject['result']['complete']||$subject['result']['final']===null)$polyGroups[$key]['complete']=false;
        else{$mark=(float)$subject['result']['final'];$polyGroups[$key]['sum']+=$mark*$weight;if($mark>=10){$polyGroups[$key]['earned']+=$weight;$polyCredits+=$weight;}}
    }
    $polyGrade=function($mark){if($mark===null)return '';foreach(array(16=>'A',14=>'B+',12=>'B',10=>'C',8=>'D') as $threshold=>$grade)if($mark>=$threshold)return $grade;return 'E';};
    $polyDate=date('d/m/Y');
?>
<section class="individual polytechnique-transcript">
    <header class="poly-header">
        <div class="poly-official" lang="fr"><strong>Republique du Cameroun</strong><br>Paix &ndash; Travail &ndash; Patrie<br>********<br>Minist&egrave;re de l&rsquo;Enseignement Sup&eacute;rieur</div>
        <div class="poly-university">@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Universit&eacute; d'Ebolowa">@endif</div>
        <div class="poly-official" lang="en"><strong>Republic of Cameroon</strong><br>Peace &ndash; Work &ndash; Fatherland<br>********<br>Ministry of Higher Education</div>
    </header>
    <div class="poly-branding">
        <div class="poly-logo">@if($logo_polytechnique)<img src="{{ $logo_polytechnique }}" alt="Logo Polytechnique ENSPY">@endif</div>
        <div class="poly-school"><strong>{{ $campus->nom ?: 'INSTITUT SUPERIEUR HINTEL' }}</strong><br>Autorisation de Cr&eacute;ation N&deg; {{ $campus->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>Arr&ecirc;t&eacute; MINESUP N&deg; {{ $campus->arrete_creation ?: config('academic_stationery.arrete') }}<br>BP: {{ $campus->boite_postale ?: '4615' }} Yaound&eacute;-Cameroun &middot; T&eacute;l: {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>Cycle BTS - LICENCES ET MASTERS PROFESSIONNELLES</div>
        <div class="poly-logo">@if($logo_campus)<img src="{{ $logo_campus }}" alt="Logo du campus">@endif</div>
    </div>
    <div class="poly-red-rule"></div>
    <h1>RELEVE DE NOTES / TRANSCRIPT</h1>
    <div class="poly-identity">
        <div><strong>Nom / NAME:</strong> {{ $student->nom }} {{ $student->prenom }}<br><strong>Date de naissance / BORN ON:</strong> {{ $student->date_naiss }}<br><strong>Lieu de naissance / AT:</strong> {{ $student->lieu_naiss }}<br><strong>Matricule / REGISTRATION:</strong> {{ $student->matricule }}<br><strong>Ann&eacute;e Acad&eacute;mique / Academic year:</strong> {{ $context['year'] }}</div>
        <div><strong>Fili&egrave;re:</strong> {{ $context['classe']->nom }}<br><strong>Sp&eacute;cialit&eacute;:</strong> {{ $context['classe']->nom }}<br><strong>Cycle:</strong> {{ $context['cycle']->nom }}<br><strong>Niveau:</strong> {{ $context['nniveau']->nom }}</div>
    </div>
    <table class="poly-marks"><thead><tr><th>Code UE</th><th>Code UV</th><th>Intitul&eacute; UE / Cours</th><th>Moyenne</th><th>P/4</th><th>Cr&eacute;dits pr&eacute;vus</th><th>Cr&eacute;dits obtenus</th><th>Semestre</th><th>Ann&eacute;e Acad&eacute;mique</th><th>Mention</th></tr></thead><tbody>
        @foreach($polyGroups as $moduleName=>$group)
            <?php $average=$group['complete']&&$group['credits']>0?$group['sum']/$group['credits']:null;$moduleCredits=(float)$group['credits']; ?>
            <tr class="poly-module"><th>{{ strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$moduleName),0,8)) }}</th><td></td><th class="poly-left">{{ $moduleName }}</th><td>{{ $average===null?'':number_format($average,2) }}</td><td>{{ $average===null?'':number_format($average/5,2) }}</td><td>{{ number_format($moduleCredits,0) }}</td><td>{{ number_format($group['earned'],0) }}</td><td></td><td>{{ $context['year'] }}</td><td>{{ $average===null?'':$polyGrade($average) }}</td></tr>
            @foreach($group['subjects'] as $subject)
                <?php $mark=$subject['result']['final']; ?>
                <tr><td></td><td>{{ $subject['code'] }}</td><td class="poly-left">{{ $subject['nom'] }}</td><td>{{ $mark===null?'':number_format($mark,2) }}</td><td>{{ $mark===null?'':number_format($mark/5,2) }}</td><td>{{ number_format((float)$subject['credit'],0) }}</td><td>{{ $subject['result']['complete']&&$mark>=10?number_format((float)$subject['credit'],0):0 }}</td><td>{{ $subject['semestre'] }}</td><td>{{ $context['year'] }}</td><td>{{ $subject['result']['grade'] }}</td></tr>
            @endforeach
        @endforeach
    </tbody></table>
    <table class="poly-summary"><tbody><tr><th>Total des cr&eacute;dits valid&eacute;s</th><td>{{ number_format($polyCredits,0) }}</td><th>Total pr&eacute;vu</th><td>{{ number_format($polyExpected,0) }}</td><th>Moyenne</th><td>{{ number_format($r['summary']['average'],2) }}</td><th>MGP</th><td>{{ number_format($r['summary']['mgp'],2) }}</td></tr><tr><th>Cr&eacute;dits capitalis&eacute;s</th><td>{{ $r['summary']['credits'] }}</td><th>Note</th><td>{{ $r['summary']['grade'] }}</td><th>Mention</th><td>{{ $r['summary']['mention'] }}</td><th>D&eacute;cision</th><td>{{ $r['summary']['decision'] }}</td></tr></tbody></table>
    <p class="poly-scale"><strong>Note:</strong> A (16-20) Excellent; A- (14-16) Tr&egrave;s bien; B (12-14) Bien; C (10-12) Passable; D (8-10) M&eacute;diocre; E (&lt;8) Echec.</p>
    <div class="poly-issued">Yaound&eacute;, le {{ $polyDate }}</div>
    <div class="poly-signatures"><strong>Le Directeur de L'ISH</strong><strong>Le Directeur de POLYTECHNIQUE</strong></div>
</section>
