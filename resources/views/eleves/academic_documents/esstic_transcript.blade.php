<?php
    $essticMention=function($mark){
        if($mark===null)return 'Incomplet';
        if($mark>=16)return 'Très bien';
        if($mark>=14)return 'Bien';
        if($mark>=12)return 'Assez bien';
        if($mark>=10)return 'Passable';
        return 'Insuffisant';
    };
?>
<section class="individual esstic-transcript">
    <div class="esstic-official"><div lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'République du Cameroun' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix - Travail - Patrie' }}<br>{{ $context['tutelle']->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}</div><div lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'Republic of Cameroon' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace - Work - Fatherland' }}<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}</div></div>
    <div class="esstic-brand"><div>@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Logo ESSTIC">@endif</div><div><strong>{{ $context['campus']->nom }}</strong><br>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}<br>B.P. : {{ $context['campus']->boite_postale ?: config('academic_stationery.boite_postale') }} &nbsp; Tél. : {{ $context['campus']->telephone ?: config('academic_stationery.telephone') }}<br>{{ config('academic_stationery.formations') }}</div><div><img src="{{ $logo_campus ?: URL::asset('img/logo.jpg') }}" alt="Logo du campus"></div></div>
    <div class="esstic-rule"></div><h1>RELEVÉ DE NOTES / TRANSCRIPT</h1>
    <div class="esstic-identity"><div>Nom / Name : <strong>{{ $r['student']->nom }} {{ $r['student']->prenom }}</strong><br>Date de naissance / Born on : {{ $r['student']->date_naiss ? date('d/m/Y',strtotime($r['student']->date_naiss)) : '' }}<br>Lieu de naissance / At : {{ $r['student']->lieu_naiss }}<br>Matricule / Registration : {{ $r['student']->matricule }}<br>Année académique / Academic year : {{ $context['year'] }}</div><div>Filière : {{ $esstic_filiere ? $esstic_filiere->nom : '' }}<br>Spécialité : {{ $context['classe']->nom }}<br>Cycle : {{ $context['cycle']->nom }}<br>Niveau : {{ $context['nniveau']->nom }}<br>Période : {{ $context['periode']==='annuel'?'Année complète':($context['periode']==='s1'?'Semestre impair':'Semestre pair') }}</div></div>
    <table class="esstic-marks"><colgroup><col class="esstic-ue"><col class="esstic-code"><col class="esstic-course"><col class="esstic-mark"><col class="esstic-mgp"><col class="esstic-credit"><col class="esstic-earned"><col class="esstic-semester"><col class="esstic-year"><col class="esstic-mention"></colgroup><thead><tr><th>Code UE</th><th>Code</th><th>Intitulé UE / Cours<br>Course title</th><th>Moyenne /20</th><th>P/4</th><th>Crédits prévus</th><th>Crédits obtenus</th><th>Semestre</th><th>Année académique</th><th>Mention</th></tr></thead><tbody>
        @foreach($r['modules'] as $module)
            <?php $earned=0;foreach($module['subjects'] as $subject)if($subject['result']['complete'] && $subject['result']['final'] >= $context['profile']['credit_threshold'])$earned+=(float)$subject['credit'];$average=$module['average']; ?>
            <tr class="esstic-module"><td>{{ $module['id_matiere_mere'] && isset($esstic_module_codes[$module['id_matiere_mere']]) ? $esstic_module_codes[$module['id_matiere_mere']] : '' }}</td><td></td><th class="left">{{ $module['nom'] }}</th><td>{{ $average===null?'':number_format($average,2,',',' ') }}</td><td>{{ $average===null?'':number_format($average/5,2,',',' ') }}</td><td>{{ $module['credits'] }}</td><td>{{ $earned }}</td><td>{{ $module['semestre'] }}</td><td>{{ $context['year'] }}</td><td>{{ $essticMention($average) }}</td></tr>
            @foreach($module['subjects'] as $subject)
                <?php $mark=$subject['result']['final']; ?>
                <tr><td></td><td>{{ $subject['code'] }}</td><td class="left">{{ $subject['nom'] }}</td><td>{{ $mark===null?'':number_format($mark,2,',',' ') }}</td><td>{{ $mark===null?'':number_format($mark/5,2,',',' ') }}</td><td>{{ $subject['credit'] }}</td><td>{{ $subject['result']['complete'] && $mark >= $context['profile']['credit_threshold'] ? $subject['credit'] : 0 }}</td><td>{{ $subject['semestre'] }}</td><td>{{ $context['year'] }}</td><td>{{ $essticMention($mark) }}</td></tr>
            @endforeach
        @endforeach
    </tbody></table>
    <table class="esstic-bilan"><tbody><tr><th>Total des crédits prévus</th><td>{{ $r['summary']['expected'] }}</td><th>Total des crédits obtenus</th><td>{{ $r['summary']['credits'] }}</td></tr><tr><th>Moyenne générale /20</th><td>{{ $r['summary']['incomplete']?'Dossier incomplet':number_format($r['summary']['average'],2,',',' ') }}</td><th>MGP /4</th><td>{{ $r['summary']['incomplete']?'':number_format($r['summary']['mgp'],2,',',' ') }}</td></tr><tr><th>Mention</th><td>{{ $r['summary']['incomplete']?'':$r['summary']['mention'] }}</td><th>Décision</th><td>{{ $r['summary']['decision'] }}</td></tr></tbody></table>
    <div class="esstic-issued">Yaoundé, le {{ substr($generated_at,0,10) }}</div>
    <div class="esstic-signatures"><div>Le Directeur de l'ISH</div><div>Le Directeur de l'ESSTIC</div></div>
    <div class="esstic-note">{{ config('academic_stationery.note_releve') }}</div>
</section>
