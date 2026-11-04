<div class="certificate-top certificate-ngaoundere-top">
    <div class="certificate-institution" lang="fr">
        <strong>{{ $tutelle->republique_francophone ?: 'RÉPUBLIQUE DU CAMEROUN' }}</strong><br>
        {{ $tutelle->patrie_francophone ?: 'Paix – Travail – Patrie' }}<br>
        {{ $tutelle->ministere_tutelle ?: 'Ministère de l’Enseignement Supérieur' }}<br>
        <strong>{{ $tutelle->nom }}</strong><br>{{ $tutelle->faculte ?: $certificate_authority['faculty_fr'] }}<br>
        <strong>{{ $campus->nom }}</strong><br>
        B.P. : {{ $campus->boite_postale ?: config('academic_stationery.boite_postale') }}<br>
        Tél. : {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>
        {{ config('academic_stationery.email') }}<br>{{ $campus->site_web ?: config('academic_stationery.site_web') }}
    </div>
    <div class="certificate-campus"><img src="{{ $logo_campus ?: URL::asset('img/logo.jpg') }}" alt="Logo du campus"></div>
    <div class="certificate-university">@if($logo_tutelle)<img src="{{ $logo_tutelle }}" alt="Logo tutelle">@endif</div>
    <div class="certificate-iut">@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Logo IUT">@endif</div>
    <div class="certificate-institution" lang="en">
        <strong>{{ $tutelle->republique_anglophone ?: 'REPUBLIC OF CAMEROON' }}</strong><br>
        {{ $tutelle->patrie_anglophone ?: 'Peace – Work – Fatherland' }}<br>
        {{ $tutelle->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br>
        <strong>{{ $tutelle->tutelle_anglophone ?: $tutelle->nom }}</strong><br>{{ $tutelle->faculte_anglophone ?: $certificate_authority['faculty_en'] }}<br>
        <strong>{{ $campus->nom }}</strong><br>
        P.O. Box: {{ $campus->boite_postale ?: config('academic_stationery.boite_postale') }}<br>
        Tel.: {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>
        {{ config('academic_stationery.email') }}<br>{{ $campus->site_web ?: config('academic_stationery.site_web') }}
    </div>
</div>