<section class="ndere-transcript individual">
    <div class="ndere-official">
        <div lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'REPUBLIQUE DU CAMEROUN' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix - Travail - Patrie' }}<br>{{ $context['tutelle']->ministere_tutelle ?: "Ministere de l'Enseignement Superieur" }}<br>{{ $context['tutelle']->nom }}</div>
        <div class="ndere-university">@if($logo_tutelle)<img src="{{ $logo_tutelle }}" alt="Logo de la tutelle">@endif<strong>{{ $context['tutelle']->nom }}</strong><br>{{ $context['tutelle']->tutelle_anglophone ?: $context['tutelle']->nom }}</div>
        <div lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'REPUBLIC OF CAMEROON' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace - Work - Fatherland' }}<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br>{{ $context['tutelle']->tutelle_anglophone ?: $context['tutelle']->nom }}</div>
    </div>
    <div class="ndere-school"><strong>{{ $context['campus']->nom }}</strong><br>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}<br>B.P. : {{ $context['campus']->boite_postale ?: config('academic_stationery.boite_postale') }} &nbsp; Tel. : {{ $context['campus']->telephone ?: config('academic_stationery.telephone') }}<br>{{ config('academic_stationery.formations') }}</div>
    <div class="ndere-title-row">
        <div class="ndere-side-logo">@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Logo IUT">@endif<span>{{ $context['tutelle']->faculte ?: 'INSTITUT UNIVERSITAIRE DE TECHNOLOGIE (IUT)' }}</span></div>
        <div class="ndere-title"><strong>RELEVE DE NOTES / TRANSCRIPT</strong><br>N&deg; {{ $page['student']->matricule }}<br><strong>SEMESTRE {{ $page['semester'] }}</strong></div>
        <div class="ndere-side-logo">@if($logo_campus)<img src="{{ $logo_campus }}" alt="Logo du campus">@endif</div>
    </div>
    <div class="ndere-identity">
        <div><div>Nom / Name: <strong>{{ $page['student']->nom }} {{ $page['student']->prenom }}</strong></div><div>Date de naissance / Born on: {{ $page['student']->date_naiss ? date('d/m/Y',strtotime($page['student']->date_naiss)) : '' }}</div><div>Lieu de naissance / At: {{ $page['student']->lieu_naiss }}</div><div>Matricule / Registration: {{ $page['student']->matricule }}</div><div>Annee academique / Academic year: {{ $context['year'] }}</div></div>
        <div><div>Domaine / Area: {{ $ngaoundere_filiere && isset($ngaoundere_filiere->domaines) ? $ngaoundere_filiere->domaines : '' }}</div><div>Specialite / Specialisation: {{ $context['classe']->nom }}</div><div>Parcours / Major: {{ $ngaoundere_filiere ? $ngaoundere_filiere->nom : '' }}</div><div>Niveau / Level: {{ $context['nniveau']->nom }}</div><div>Cycle / Degree: {{ $context['cycle']->nom }}</div></div>
    </div>
    <table class="ndere-marks"><colgroup><col class="ndere-code"><col class="ndere-course"><col class="ndere-mark"><col class="ndere-grade"><col class="ndere-credit"><col class="ndere-year"><col class="ndere-semester"><col class="ndere-observation"></colgroup>
        <thead><tr><th>Code / Code</th><th>Intitule UE / Cours<br>Course title</th><th>Note/20<br>Marks</th><th>Notation / Grade</th><th>Credits</th><th>Annee<br>Year</th><th>Semestre<br>Semester</th><th>Observation</th></tr></thead>
        <tbody>
        @foreach($page['subjects'] as $subject)
            <tr><td>{{ $subject['code'] }}</td><td class="left">{{ $subject['nom'] }}</td><td>{{ $subject['result']['final']===null?'':number_format($subject['result']['final'],2,',',' ') }}</td><td>{{ $subject['result']['grade'] }}</td><td>{{ $subject['credit'] }}</td><td>{{ $context['year'] }}</td><td>{{ $page['semester'] }}</td><td>{{ \gesecole\Services\NgaoundereTranscriptService::observation($subject['result']['final']) }}</td></tr>
        @endforeach
        <tr class="ndere-bilan"><th colspan="2">Bilan / Overall results</th><td colspan="2" class="left">Total des credits: {{ $page['summary']['expected'] }}</td><td colspan="2" class="left">Moyenne semestrielle /20</td><td colspan="2">{{ $page['summary']['incomplete']?'Dossier incomplet':number_format($page['summary']['average'],2,',',' ') }}</td></tr>
        @if($page['annual'])
            <tr class="ndere-bilan"><th colspan="2">Moyenne generale /20</th><td colspan="2">{{ $page['annual']['incomplete']?'Dossier incomplet':number_format($page['annual']['average'],2,',',' ') }}</td><th colspan="2">Total des credits annuels</th><td>{{ $page['annual']['expected'] }}</td><td>Decision: {{ $page['annual']['decision'] }}</td></tr>
        @endif
        </tbody>
    </table>
    <div class="ndere-signatures"><div>Le Chef de l'ISH<br><span>Signature et cachet</span></div><div>Yaounde, le {{ substr($generated_at,0,10) }}<br>Le Directeur de l'IUT<br><span>Signature et cachet</span></div></div>
    <p class="ndere-note">{{ config('academic_stationery.note_releve') }}</p>
</section>
