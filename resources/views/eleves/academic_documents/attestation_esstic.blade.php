<?php
    $essticDegree = stripos($degree, 'licence') !== false ? 'LICENCE PROFESSIONNELLE' : strtoupper($degree);
    $essticDegreeEn = stripos($degree, 'licence') !== false ? 'PROFESSIONAL LICENSE' : (stripos($degree, 'master') !== false ? "MASTER'S DEGREE" : strtoupper($degree));
    $essticSchoolFr = $tutelle->faculte ?: "Ecole Superieure des Sciences et Techniques de l'Information et de la Communication (ESSTIC)";
    $essticSchoolEn = $tutelle->faculte_anglophone ?: 'Higher School of Information and Communication Sciences and Techniques (ESSTIC)';
    $essticFieldFr = $esstic_filiere ? $esstic_filiere->nom : $context['classe']->nom;
    $essticFieldEn = $esstic_filiere && !empty($esstic_filiere->nom_anglais) ? $esstic_filiere->nom_anglais : $essticFieldFr;
    $essticSpecialityEn = !empty($context['classe']->nom_anglais) ? $context['classe']->nom_anglais : $context['classe']->nom;
    $essticBirth = $student->date_naiss ? date('Y-m-d', strtotime($student->date_naiss)) : '................';
    $essticJuryDate = DateTime::createFromFormat('d/m/Y', $attestation_jury_date);
    if ($essticJuryDate) {
        $essticMonths = array(1 => 'janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre');
        $essticJuryDate = $essticJuryDate->format('j').' '.$essticMonths[(int) $essticJuryDate->format('n')].' '.$essticJuryDate->format('Y');
    } else {
        $essticJuryDate = $attestation_jury_date;
    }
?>
<section class="individual certificate certificate-esstic" aria-label="Attestation ESSTIC">
    <div class="esstic-certificate-frame" aria-hidden="true"></div>
    <span class="esstic-certificate-corner corner-tl" aria-hidden="true"></span>
    <span class="esstic-certificate-corner corner-tr" aria-hidden="true"></span>
    <span class="esstic-certificate-corner corner-bl" aria-hidden="true"></span>
    <span class="esstic-certificate-corner corner-br" aria-hidden="true"></span>
    @if($logo_campus)<img class="esstic-watermark" src="{{ $logo_campus }}" alt="">@endif
    <div class="esstic-watermark-name" aria-hidden="true">{{ $campus->nom }}</div>

    <div class="esstic-certificate-top">
        <div class="esstic-certificate-institution" lang="fr">
            <strong>{{ $tutelle->republique_francophone ?: 'Republique du Cameroun' }}</strong><br>
            {{ $tutelle->patrie_francophone ?: 'Paix - Travail - Patrie' }}<br>
            {{ $tutelle->ministere_tutelle ?: "Ministere de l'Enseignement Superieur" }}<br>
            {{ $tutelle->nom }}<br>{{ $essticSchoolFr }}<br>
            <strong>{{ $campus->nom }}</strong><br>
            B.P. : {{ $campus->boite_postale ?: config('academic_stationery.boite_postale') }}<br>
            Tel : {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>
            E-mail : {{ config('academic_stationery.email') }}<br>
            Site Web : {{ $campus->site_web ?: config('academic_stationery.site_web') }}
        </div>
        <div class="esstic-certificate-logo"><img src="{{ $logo_campus ?: URL::asset('img/logo.jpg') }}" alt="Logo du campus"></div>
        <div class="esstic-certificate-logo">@if($logo_tutelle)<img src="{{ $logo_tutelle }}" alt="Logo de l'universite">@endif</div>
        <div class="esstic-certificate-logo">@if($logo_faculte)<img src="{{ $logo_faculte }}" alt="Logo ESSTIC">@endif</div>
        <div class="esstic-certificate-institution" lang="en">
            <strong>{{ $tutelle->republique_anglophone ?: 'Republic of Cameroon' }}</strong><br>
            {{ $tutelle->patrie_anglophone ?: 'Peace - Work - Fatherland' }}<br>
            {{ $tutelle->ministere_tutelle_anglophone ?: 'Ministry of Higher Education' }}<br>
            {{ $tutelle->tutelle_anglophone ?: $tutelle->nom }}<br>{{ $essticSchoolEn }}<br>
            <strong>{{ $campus->nom }}</strong><br>
            P.O. Box : {{ $campus->boite_postale ?: config('academic_stationery.boite_postale') }}<br>
            Tel : {{ $campus->telephone ?: config('academic_stationery.telephone') }}<br>
            E-mail : {{ config('academic_stationery.email') }}<br>
            Website : {{ $campus->site_web ?: config('academic_stationery.site_web') }}
        </div>
    </div>

    <div class="esstic-certificate-heading">
        <h1>ATTESTATION DE R&Eacute;USSITE DE {{ $essticDegree }} N&deg;{{ $attestation_reference ?: '........' }}</h1>
        <strong>{{ $essticDegreeEn }} SUCCESS TESTIMONIAL</strong>
    </div>

    <div class="esstic-certificate-body">
        <p>Le Directeur de l'<strong>{{ $essticSchoolFr }}</strong> de l'<strong>{{ $tutelle->nom }}</strong> et {{ $certificate_authority['campus_signatory_fr'] }} certifient que : / <em lang="en">The Director of the {{ $essticSchoolEn }} of {{ $tutelle->tutelle_anglophone ?: $tutelle->nom }} and {{ $certificate_authority['campus_signatory_en'] }} hereby certify that:</em></p>

        <div class="esstic-certificate-identity">
            <div><strong>M. / Mlle : {{ $student->nom }} {{ $student->prenom }}</strong><br><em>Mr/Miss</em></div>
            <div><strong>Date de naissance : {{ $essticBirth }}</strong><br><em>Born on</em></div>
            <div><strong>Lieu de naissance : {{ $student->lieu_naiss ?: '........' }}</strong><br><em>At</em></div>
            <div><strong>Matricule : {{ $student->matricule }}</strong><br><em>Registration</em></div>
        </div>

        <p>Vu la r&eacute;glementation en vigueur / <em lang="en">Mindful of the regulations in force,</em><br>
            Vu le proc&egrave;s-verbal du jury en date du <strong>{{ $essticJuryDate }}</strong> / <em lang="en">Mindful of the official report of the jury of {{ $essticJuryDate }},</em></p>
        <p>A suivi avec succ&egrave;s la formation pour l'obtention du dipl&ocirc;me de <strong>{{ $essticDegree }}</strong>, fili&egrave;re <strong>{{ $essticFieldFr }}</strong>, parcours <strong>{{ $context['classe']->nom }}</strong> / <em lang="en">Has successfully completed the course work for award of a <strong>{{ $essticDegreeEn }}</strong> in Specialization <strong>{{ $essticFieldEn }}</strong>, course <strong>{{ $essticSpecialityEn }}</strong>.</em></p>

        <div class="esstic-certificate-results">
            <div>Ann&eacute;e acad&eacute;mique : <strong>{{ $context['year'] }}</strong></div>
            <div>Mention globale : <strong>{{ $r['summary']['mention'] }}</strong> / {{ isset($mentionEn[$r['summary']['mention']]) ? $mentionEn[$r['summary']['mention']] : $r['summary']['mention'] }}</div>
        </div>
        <p>En foi de quoi, la pr&eacute;sente attestation lui est d&eacute;livr&eacute;e pour servir et valoir ce que de droit. / <em lang="en">This certificate is, therefore, issued to the above named to serve the purposes for which it is intended.</em></p>
    </div>

    <div class="esstic-certificate-issued">{{ $certificate_authority['city'] }}, le {{ $attestation_issue_date }}</div>
    <div class="esstic-certificate-signatures">
        <strong>{{ $certificate_authority['campus_signatory_fr'] }}</strong>
        <strong>{{ $certificate_authority['authority_fr'] }}</strong>
    </div>
    <div class="esstic-certificate-bottom">{{ config('academic_stationery.note_releve') }} <span lang="en">Only one transcript shall be delivered. The holder may obtain certified true copies.</span></div>
</section>
