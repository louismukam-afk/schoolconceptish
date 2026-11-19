<?php
    $ngaoundereDegreeEn = stripos($degree, 'licence') !== false ? 'PROFESSIONAL LICENCE' : strtoupper($degreeEn);
    $ngaoundereBirth = $student->date_naiss ? date('Y-m-d', strtotime($student->date_naiss)) : '........................';
    $ngaoundereJury = $attestation_jury_date;
    $ngaoundereJuryTimestamp = DateTime::createFromFormat('d/m/Y', $attestation_jury_date);
    if ($ngaoundereJuryTimestamp) {
        $ngaoundereMonths = array(1=>'JANVIER',2=>'FEVRIER',3=>'MARS',4=>'AVRIL',5=>'MAI',6=>'JUIN',7=>'JUILLET',8=>'AOUT',9=>'SEPTEMBRE',10=>'OCTOBRE',11=>'NOVEMBRE',12=>'DECEMBRE');
        $ngaoundereJury = $ngaoundereJuryTimestamp->format('j').' '.$ngaoundereMonths[(int)$ngaoundereJuryTimestamp->format('n')].' '.$ngaoundereJuryTimestamp->format('Y');
    }
?>
<section class="individual certificate certificate-ngaoundere" aria-label="Attestation Ngaoundere">
    <div class="ngaoundere-certificate-frame" aria-hidden="true"></div>
    <i class="ngaoundere-corner ngaoundere-corner-tl" aria-hidden="true"></i><i class="ngaoundere-corner ngaoundere-corner-tr" aria-hidden="true"></i><i class="ngaoundere-corner ngaoundere-corner-bl" aria-hidden="true"></i><i class="ngaoundere-corner ngaoundere-corner-br" aria-hidden="true"></i>
    @include('eleves.academic_documents.attestation_ngaoundere_header')
    <div class="certificate-heading">
        <h1>ATTESTATION DE R&Eacute;USSITE DE {{ mb_strtoupper($degree, 'UTF-8') }}</h1>
        <div class="certificate-reference">N&deg;{{ $attestation_references[$student->id] }} {{ $ngaoundereDegreeEn }}</div>
        <div class="certificate-english">{{ $ngaoundereDegreeEn }} SUCCESS TESTIMONIAL</div>
    </div>
    <div class="certificate-content">
        <p class="certificate-certification">{{ $certificate_authority['authority_fr'] }} de <strong>{{ $tutelle->nom }}</strong> et le Pr&eacute;sident du Conseil d'&Eacute;tablissement de <strong>{{ $campus->nom }}</strong> certifient que : <em lang="en">{{ $certificate_authority['authority_en'] }} of {{ $tutelle->tutelle_anglophone ?: $tutelle->nom }} and the Chairman of the {{ $campus->nom }} hereby certify that:</em></p>
        <div class="ngaoundere-identity">
            <div>M. / Mlle : <strong>{{ $student->nom }} {{ $student->prenom }}</strong><br><em>Mr/Miss</em></div>
            <div>Date de naissance : <strong>{{ $ngaoundereBirth }}</strong><br><em>Born on</em></div>
            <div>Lieu de naissance : <strong>{{ $student->lieu_naiss ?: '........' }}</strong><br><em>At</em></div>
            <div>Matricule : <strong>{{ $student->matricule }}</strong><br><em>Registration</em></div>
        </div>
        @include('eleves.academic_documents.attestation_ngaoundere_references')
        <p>Vu le proc&egrave;s-verbal du jury en date du <strong>{{ $ngaoundereJury }}</strong> / <em lang="en">Mindful of the official report of the jury of {{ $ngaoundereJury }};</em></p>
        <p>A suivi avec succ&egrave;s la formation pour l'obtention du dipl&ocirc;me de <strong>{{ mb_strtoupper($degree, 'UTF-8') }}</strong>, fili&egrave;re <strong>{{ $attestation_filiere ? mb_strtoupper($attestation_filiere->nom, 'UTF-8') : '' }}</strong>, parcours <strong>{{ mb_strtoupper($context['classe']->nom, 'UTF-8') }}</strong> / <em lang="en">Has successfully completed the course work for award of a {{ $ngaoundereDegreeEn }} in Specialization {{ $attestation_filiere ? mb_strtoupper($attestation_filiere->nom, 'UTF-8') : '' }}.</em></p>
        <div class="ngaoundere-results"><div>Ann&eacute;e acad&eacute;mique : <strong>{{ $context['year'] }}</strong></div><div>Mention Globale : <strong>{{ $r['summary']['mention'] }}</strong> / {{ isset($mentionEn[$r['summary']['mention']])?$mentionEn[$r['summary']['mention']]:$r['summary']['mention'] }}</div></div>
        <p class="ngaoundere-purpose">En foi de quoi, la pr&eacute;sente attestation lui est d&eacute;livr&eacute;e pour servir et valoir ce que de droit. / <em lang="en">This certificate is issued to the above named to serve the purposes for which it is intended.</em></p>
    </div>
    <div class="certificate-signatures"><strong>{{ $certificate_authority['campus_signatory_fr'] }}</strong><strong>Le Directeur de l'IUT</strong></div>
    <div class="certificate-issued">{{ $certificate_authority['city'] }}, le {{ $attestation_issue_date }}</div>
    <div class="ngaoundere-bottom">{{ config('academic_stationery.note_releve') }} <em>Only one transcript shall be delivered. It is the owner's interest to make certified true copies.</em></div>
</section>
