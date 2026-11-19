<?php
    $pvLogoCampus = !empty($logo_campus) ? $logo_campus : URL::asset('img/logo.jpg');
    $pvLogoTutelle = !empty($logo_tutelle) ? $logo_tutelle : ($context['tutelle']->logo ? URL::asset($context['tutelle']->logo) : '');
    $pvLogoFaculty = !empty($logo_faculte) ? $logo_faculte : ($context['tutelle']->logo_faculte ? URL::asset($context['tutelle']->logo_faculte) : '');
    $pvLogoPolytechnique = !empty($logo_polytechnique) ? $logo_polytechnique : '';
?>
<header class="pv-custom-header pv-header-{{ $context['modele'] }}">
    @if($context['modele']==='isabee')
        <div class="pv-header-official pv-header-isabee-official">
            <div lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'Republique du Cameroun' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>********<br>{{ $context['tutelle']->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}<br>****<br>{{ $context['tutelle']->nom }}<br>{{ $context['tutelle']->faculte }}</div>
            <div class="pv-header-center-logo">@if($pvLogoFaculty)<img src="{{ $pvLogoFaculty }}" alt="Logo de l'université de tutelle">@endif</div>
            <div lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'Republic of Cameroon' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>********<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br>****<br>{{ $context['tutelle']->tutelle_anglophone ?: $context['tutelle']->nom }}<br>{{ $context['tutelle']->faculte_anglophone }}</div>
        </div>
        <div class="pv-header-brand pv-header-three-logos">
            <div class="pv-header-logo">@if($pvLogoTutelle)<img src="{{ $pvLogoTutelle }}" alt="Logo ISABEE">@endif</div>
            <div class="pv-header-school"><strong>{{ $context['campus']->nom }}</strong><br>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}<br>B.P. : {{ $context['campus']->boite_postale ?: config('academic_stationery.boite_postale') }} · Tél. : {{ $context['campus']->telephone ?: config('academic_stationery.telephone') }}<br>{{ config('academic_stationery.formations') }}</div>
            <div class="pv-header-logo">@if($pvLogoCampus)<img src="{{ $pvLogoCampus }}" alt="Logo du campus">@endif</div>
        </div>
    @elseif($context['modele']==='polytechnique')
        <div class="pv-header-official pv-header-poly-official">
            <div lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'République du Cameroun' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>********<br>{{ $context['tutelle']->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}</div>
            <div class="pv-header-center-logo">@if($pvLogoFaculty)<img src="{{ $pvLogoFaculty }}" alt="Université de tutelle">@endif</div>
            <div lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'Republic of Cameroon' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>********<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}</div>
        </div>
        <div class="pv-header-brand pv-header-three-logos">
            <div class="pv-header-logo">@if($pvLogoPolytechnique)<img src="{{ $pvLogoPolytechnique }}" alt="Logo Polytechnique ENSPY">@endif</div>
            <div class="pv-header-school"><strong>{{ $context['campus']->nom }}</strong><br>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}<br>B.P. : {{ $context['campus']->boite_postale ?: config('academic_stationery.boite_postale') }} · Tél. : {{ $context['campus']->telephone ?: config('academic_stationery.telephone') }}<br>{{ config('academic_stationery.formations') }}</div>
            <div class="pv-header-logo">@if($pvLogoCampus)<img src="{{ $pvLogoCampus }}" alt="Logo du campus">@endif</div>
        </div>
    @elseif($context['modele']==='esstic')
        <div class="pv-header-official pv-header-esstic-official">
            <div lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'République du Cameroun' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>********<br>{{ $context['tutelle']->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}</div>
            <div lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'Republic of Cameroon' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>********<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}</div>
        </div>
        <div class="pv-header-brand pv-header-three-logos">
            <div class="pv-header-logo">@if($pvLogoFaculty)<img src="{{ $pvLogoFaculty }}" alt="Logo ESSTIC">@endif</div>
            <div class="pv-header-school"><strong>{{ $context['campus']->nom }}</strong><br>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}<br>B.P. : {{ $context['campus']->boite_postale ?: config('academic_stationery.boite_postale') }} · Tél. : {{ $context['campus']->telephone ?: config('academic_stationery.telephone') }}<br>{{ config('academic_stationery.formations') }}</div>
            <div class="pv-header-logo">@if($pvLogoCampus)<img src="{{ $pvLogoCampus }}" alt="Logo du campus">@endif</div>
        </div>
    @else
        <div class="pv-header-official pv-header-ndere-official">
            <div lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'République du Cameroun' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>{{ $context['tutelle']->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}<br>{{ $context['tutelle']->nom }}</div>
            <div class="pv-header-center-logo">@if($pvLogoTutelle)<img src="{{ $pvLogoTutelle }}" alt="Logo de la tutelle">@endif<strong>{{ $context['tutelle']->nom }}</strong><br>{{ $context['tutelle']->tutelle_anglophone ?: $context['tutelle']->nom }}</div>
            <div lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'Republic of Cameroon' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br>{{ $context['tutelle']->tutelle_anglophone ?: $context['tutelle']->nom }}</div>
        </div>
        <div class="pv-header-school pv-header-ndere-school"><strong>{{ $context['campus']->nom }}</strong><br>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}<br>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}<br>B.P. : {{ $context['campus']->boite_postale ?: config('academic_stationery.boite_postale') }} · Tél. : {{ $context['campus']->telephone ?: config('academic_stationery.telephone') }}<br>{{ config('academic_stationery.formations') }}</div>
        <div class="pv-header-side-logos"><span>@if($pvLogoFaculty)<img src="{{ $pvLogoFaculty }}" alt="Logo de la faculté">@endif</span><span>@if($pvLogoCampus)<img src="{{ $pvLogoCampus }}" alt="Logo du campus">@endif</span></div>
    @endif
    @if($context['modele']==='esstic')<div class="pv-header-rule pv-header-rule-red"></div>@endif
    @if($context['modele']==='polytechnique')<div class="pv-header-rule pv-header-rule-red"></div>@endif
    <div class="pv-header-document-title">
        <h2>{{ $label }}</h2>
        <p class="context"><strong>Tutelle :</strong> {{ $context['profile']['label'] }} · <strong>Année :</strong> {{ $context['year'] }} · <strong>Spécialité :</strong> {{ $context['classe']->nom }}<br><strong>Cycle :</strong> {{ $context['cycle']->nom }} · <strong>Niveau :</strong> {{ $context['nniveau']->nom }} · <strong>Période :</strong> {{ $context['periode'] }} · {{ $context['session_mode']==='normal'?'Session normale':'Résultats après rattrapage' }}</p>
    </div>
</header>
