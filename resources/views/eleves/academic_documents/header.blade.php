<header class="document-header">
    <div class="official-headings">
        <div class="official-fr" lang="fr"><strong>{{ $context['tutelle']->republique_francophone ?: 'RÉPUBLIQUE DU CAMEROUN' }}</strong><br>{{ $context['tutelle']->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>{{ $context['tutelle']->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}<br><strong>{{ $context['tutelle']->nom }}</strong><br>{{ $context['tutelle']->faculte }}</div>
        <div class="official-en" lang="en"><strong>{{ $context['tutelle']->republique_anglophone ?: 'REPUBLIC OF CAMEROON' }}</strong><br>{{ $context['tutelle']->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>{{ $context['tutelle']->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br><strong>{{ $context['tutelle']->tutelle_anglophone ?: $context['tutelle']->nom }}</strong><br>{{ $context['tutelle']->faculte_anglophone }}</div>
    </div>
    <div class="branding-row {{ $context['modele']==='polytechnique'?'branding-polytechnique':'' }}">
        <div class="campus-logo-block">
            @if(!empty($logo_campus))<img class="campus-logo" src="{{ $logo_campus }}" alt="Logo du campus">@else<img class="campus-logo" src="{{ URL::asset('img/logo.jpg') }}" alt="Logo du campus">@endif
        </div>
        <div class="branding-title">
            @if($context['modele']==='polytechnique')
                <img class="polytechnique-logo" src="{{ !empty($logo_polytechnique)?$logo_polytechnique:URL::asset('images/tutelles/testok/polytechnique.png') }}" alt="Logo Polytechnique ENSPY">
            @else
                <strong>{{ $context['campus']->nom }}</strong><br><span>{{ config('academic_stationery.formations') }}</span>
            @endif
        </div>
        <div class="tutelle-logos">
            @if(!empty($logo_tutelle))<img src="{{ $logo_tutelle }}" alt="Logo tutelle">@elseif($context['tutelle']->logo)<img src="{{ URL::asset($context['tutelle']->logo) }}" alt="Logo tutelle">@endif
            @if($context['modele']!=='polytechnique')
                @if(!empty($logo_faculte))<img src="{{ $logo_faculte }}" alt="Logo faculté">@elseif($context['tutelle']->logo_faculte)<img src="{{ URL::asset($context['tutelle']->logo_faculte) }}" alt="Logo faculté">@endif
            @endif
        </div>
    </div>
    @if($context['modele']==='polytechnique')
        <div class="branding-title"><strong>{{ $context['campus']->nom }}</strong><br><span>{{ config('academic_stationery.formations') }}</span></div>
    @endif
</header>
<div class="school">
    <span>{{ $context['campus']->autorisation_ouverture ?: config('academic_stationery.autorisation') }}</span><br>
    <span>{{ $context['campus']->arrete_creation ?: config('academic_stationery.arrete') }}</span><br>
</div>
<h2>{{ $label }}</h2>
<p class="context"><strong>Tutelle :</strong> {{ $context['profile']['label'] }} · <strong>Année :</strong> {{ $context['year'] }} · <strong>Spécialité :</strong> {{ $context['classe']->nom }}<br><strong>Cycle :</strong> {{ $context['cycle']->nom }} · <strong>Niveau :</strong> {{ $context['nniveau']->nom }} · <strong>Période :</strong> {{ $context['periode'] }} · {{ isset($deliberation_report)?'Décisions du jury':($context['session_mode']==='normal'?'Session normale':'Résultats après rattrapage') }}</p>
