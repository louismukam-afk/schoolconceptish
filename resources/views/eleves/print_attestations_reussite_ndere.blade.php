@extends('skeleton')
@section('sub-content')
@include('perso.functions')
<?php
    $attestationPageSize = (isset($page_size) && strtoupper($page_size) == 'A3') ? 'A3' : 'A4';
    $attestationPageClass = strtolower($attestationPageSize);
    $attestationCity = isset($attestation_city) && trim($attestation_city) !== '' ? $attestation_city : 'Ngaoundere';
    $attestationHomeUrl = isset($attestation_home_url) && trim($attestation_home_url) !== '' ? $attestation_home_url : '/eleves/attestations_reussite_ndere';
    $attestationHistoryUrl = isset($attestation_history_url) && trim($attestation_history_url) !== '' ? $attestation_history_url : '/eleves/attestations_reussite_ndere/historique';
    $attestationSignaturePrimary = isset($attestation_signature_primary) && trim($attestation_signature_primary) !== '' ? $attestation_signature_primary : "Le Directeur de l'IUT";
    $attestationSignaturePrimaryEn = isset($attestation_signature_primary_en) && trim($attestation_signature_primary_en) !== '' ? $attestation_signature_primary_en : 'The Director';
    $isSoaAttestation = isset($attestation_template) && strtolower(trim($attestation_template)) == 'soa';
    $isEbolowaAttestation = isset($attestation_template) && strtolower(trim($attestation_template)) == 'ebolowa';
    $isCompactAttestation = $isSoaAttestation || $isEbolowaAttestation;
    $attestationPageOrientation = isset($attestation_page_orientation) && in_array(strtolower(trim($attestation_page_orientation)), array('portrait', 'landscape')) ? strtolower(trim($attestation_page_orientation)) : 'landscape';
    $attestationContentWidth = $attestationPageSize == 'A3'
        ? ($attestationPageOrientation == 'landscape' ? '402mm' : '279mm')
        : ($attestationPageOrientation == 'landscape' ? '279mm' : '192mm');
    $attestationContentHeight = $attestationPageSize == 'A3'
        ? ($attestationPageOrientation == 'landscape' ? '279mm' : '404mm')
        : ($attestationPageOrientation == 'landscape' ? '192mm' : '281mm');
    $attestationQuery = \Input::all();
    $attestationQuery['page_size'] = 'a4';
    $attestationA4Url = \URL::current().'?'.http_build_query($attestationQuery);
    $attestationQuery['page_size'] = 'a3';
    $attestationA3Url = \URL::current().'?'.http_build_query($attestationQuery);

    $cleanValue = function ($value) {
        return trim((string) $value);
    };
    $assetPath = function ($value, $fallback = '') use ($cleanValue) {
        $path = str_replace('\\', '/', $cleanValue($value));
        if ($path === '') return $fallback;
        if (strpos($path, 'public/') === 0) $path = substr($path, 7);
        if (strpos($path, '/public/') !== false) $path = substr($path, strpos($path, '/public/') + 8);
        return ltrim($path, '/');
    };

    $campusName = ($campus && $cleanValue(isset($campus->nom) ? $campus->nom : '') !== '') ? $campus->nom : 'CENTRE DE FORMATION HINTEL';
    $campusSigle = ($campus && $cleanValue(isset($campus->sigle) ? $campus->sigle : '') !== '') ? $campus->sigle : 'ISH';
    $campusLogo = $assetPath($campus && isset($campus->logo) ? $campus->logo : '', 'img/school-concept.png');
    if ($isSoaAttestation) {
        $campusLogo = 'img/school-concept.png';
    }
    $campusPostal = ($campus && isset($campus->boite_postale) && $campus->boite_postale) ? $campus->boite_postale : 'BP 4441 Yaounde';
    $campusPhone = ($campus && isset($campus->telephone) && $campus->telephone) ? $campus->telephone : '(237)696459283/672280944';
    $campusSite = ($campus && isset($campus->site_web) && $campus->site_web) ? $campus->site_web : 'hintelhotel.com';

    $republiqueFr = ($tutelle && $cleanValue(isset($tutelle->republique_francophone) ? $tutelle->republique_francophone : '') !== '') ? $tutelle->republique_francophone : 'REPUBLIQUE DU CAMEROUN';
    $republiqueEn = ($tutelle && $cleanValue(isset($tutelle->republique_anglophone) ? $tutelle->republique_anglophone : '') !== '') ? $tutelle->republique_anglophone : 'REPUBLIC OF CAMEROON';
    $patrieFr = ($tutelle && $cleanValue(isset($tutelle->patrie_francophone) ? $tutelle->patrie_francophone : '') !== '') ? $tutelle->patrie_francophone : 'Paix-Travail-Patrie';
    $patrieEn = ($tutelle && $cleanValue(isset($tutelle->patrie_anglophone) ? $tutelle->patrie_anglophone : '') !== '') ? $tutelle->patrie_anglophone : 'Peace-Work-Fatherland';
    $ministereFr = ($tutelle && $cleanValue(isset($tutelle->ministere_tutelle) ? $tutelle->ministere_tutelle : '') !== '') ? $tutelle->ministere_tutelle : "MINISTERE DE L'ENSEIGNEMENT SUPERIEUR";
    $ministereEn = ($tutelle && $cleanValue(isset($tutelle->ministere_tutelle_anglophone) ? $tutelle->ministere_tutelle_anglophone : '') !== '') ? $tutelle->ministere_tutelle_anglophone : 'MINISTRY OF HIGHER EDUCATION';
    $defaultTutelleName = $isSoaAttestation ? 'UNIVERSITE DE YAOUNDE II-SOA' : ($isEbolowaAttestation ? "UNIVERSITE D'EBOLOWA" : 'UNIVERSITE DE NGAOUNDERE');
    $defaultTutelleNameEn = $isSoaAttestation ? 'THE UNIVERSITY OF YAOUNDE II SOA' : ($isEbolowaAttestation ? 'THE UNIVERSITY OF EBOLOWA' : 'THE UNIVERSITY OF NGAOUNDERE');
    $defaultTutellePostal = $isSoaAttestation ? 'B.P. : 1365 YAOUNDE' : ($isEbolowaAttestation ? 'EBOLOWA-CAMEROUN' : 'BP : 454 Ngaoundere-Cameroun');
    $defaultTutellePhone = $isSoaAttestation ? '(237)22062698' : ($isEbolowaAttestation ? '' : '222 25 40 39');
    $defaultFacultyFr = $isSoaAttestation ? 'FACULTE DES SCIENCES ECONOMIQUES ET DE GESTION' : ($isEbolowaAttestation ? 'ETABLISSEMENT DE TUTELLE' : 'INSTITUT UNIVERSITAIRE DE TECHNOLOGIE (IUT)');
    $defaultFacultyEn = $isSoaAttestation ? 'FACULTY OF ECONOMICS AND MANAGEMENT' : ($isEbolowaAttestation ? 'SUPERVISING FACULTY' : 'HIGHER INSTITUTE OF TECHNOLOGY');
    $tutelleName = ($tutelle && $cleanValue(isset($tutelle->nom) ? $tutelle->nom : '') !== '') ? $tutelle->nom : $defaultTutelleName;
    $tutelleNameEn = ($tutelle && $cleanValue(isset($tutelle->tutelle_anglophone) ? $tutelle->tutelle_anglophone : '') !== '') ? $tutelle->tutelle_anglophone : $defaultTutelleNameEn;
    $tutellePostal = ($tutelle && isset($tutelle->localisation) && $tutelle->localisation) ? $tutelle->localisation : $defaultTutellePostal;
    $tutellePhone = ($tutelle && isset($tutelle->telephone) && $tutelle->telephone) ? $tutelle->telephone : $defaultTutellePhone;
    $facultyFr = ($tutelle && $cleanValue(isset($tutelle->faculte) ? $tutelle->faculte : '') !== '') ? $tutelle->faculte : $defaultFacultyFr;
    $facultyEn = ($tutelle && $cleanValue(isset($tutelle->faculte_anglophone) ? $tutelle->faculte_anglophone : '') !== '') ? $tutelle->faculte_anglophone : $defaultFacultyEn;
    $tutelleLogo = $assetPath($tutelle && isset($tutelle->logo) ? $tutelle->logo : '', '');
    if ($isSoaAttestation && $tutelleLogo === '') {
        $tutelleLogo = 'img/releve_soa_univ_yde2.jpeg';
    }
    $facultyLogo = $assetPath($tutelle && isset($tutelle->logo_faculte) ? $tutelle->logo_faculte : '', '');

    $domaineFr = ($niveau && isset($niveau->domaines) && $niveau->domaines) ? $niveau->domaines : 'TECHNOLOGIE ET SCIENCES INDUSTRIELLES';
    $domaineEn = 'TECHNOLOGY AND INDUSTRIAL SCIENCES';
    if ($isCompactAttestation && $domaineFr == 'TECHNOLOGIE ET SCIENCES INDUSTRIELLES') {
        $domaineFr = ($niveau && isset($niveau->nom) && $niveau->nom) ? $niveau->nom : 'COMMERCIALE ET GESTION';
    }
    if ($isCompactAttestation) {
        $domaineEn = ($niveau && isset($niveau->nom_anglais) && $niveau->nom_anglais) ? $niveau->nom_anglais : 'COMMERCIAL AND MANAGEMENT';
    }
    $gpaFromAverage20 = function ($average20) {
        if ($average20 >= 18) return 4.0;
        if ($average20 >= 16) return 3.7;
        if ($average20 >= 14) return 3.3;
        if ($average20 >= 13) return 3.0;
        if ($average20 >= 12) return 2.7;
        if ($average20 >= 11) return 2.3;
        if ($average20 >= 10) return 2.0;
        if ($average20 >= 9) return 1.7;
        if ($average20 >= 7) return 1.3;
        return 0;
    };
    $sessionLabelFromYear = function ($anneeAcademique) {
        if (preg_match('/(\d{4})\s*$/', (string) $anneeAcademique, $matches)) {
            return 'Juillet-'.$matches[1];
        }
        return 'Juillet-'.date('Y');
    };
?>
<style>
    @page {
        size: {{ $attestationPageSize }} {{ $attestationPageOrientation }};
        margin: {{ $isCompactAttestation ? '8mm 9mm 8mm 9mm' : '9mm 10mm 9mm 10mm' }};
    }
    @media print {
        .no-print,
        .attestation-toolbar,
        .main-menu,
        .navbar,
        .navbar-side,
        .sidebar-collapse,
        .breadcrumb,
        .action,
        .back-button,
        .big-title,
        .footer,
        footer {
            display:none !important;
            height:0 !important;
            overflow:hidden !important;
            margin:0 !important;
            padding:0 !important;
        }
        body { background:#fff !important; }
        #wrapper, #page-wrapper, #page-inner, .row, .row-fluid, .col-md-12, .col-lg-12 {
            margin:0 !important;
            padding:0 !important;
            width:100% !important;
            min-height:0 !important;
        }
        .attestation-page { page-break-after: always; margin: 0 !important; box-shadow:none !important; }
        .attestation-soa-page {
            width: {{ $attestationContentWidth }} !important;
            height: {{ $attestationContentHeight }} !important;
            min-height: {{ $attestationContentHeight }} !important;
            max-height: {{ $attestationContentHeight }} !important;
            padding: 4mm 6mm 12mm !important;
            overflow: hidden !important;
        }
        .attestation-page:not(.attestation-soa-page) { border: 3px double #000 !important; }
        .attestation-page:last-child { page-break-after: auto; }
    }
    .attestation-toolbar {
        margin: 0 0 10px 0;
        padding: 6px;
        background: #fff;
        border: 1px solid #ddd;
        text-align: right;
    }
    h3.big-title {
        display: none;
    }
    .attestation-page {
        width: 100%;
        min-height: {{ $isCompactAttestation ? $attestationContentHeight : ($attestationPageSize == 'A3' ? '277mm' : '186mm') }};
        margin: 0 auto 20px;
        padding: 7mm 9mm 13mm;
        background: #fff;
        color: #000;
        font-family: "Times New Roman", Times, serif;
        border: 3px double #000;
        box-sizing: border-box;
        position: relative;
        overflow: hidden;
    }
    .attestation-page-a3 { min-height: 277mm; padding-bottom: 14mm; font-size: 1.22em; }
    .attestation-head { display: table; width: 100%; table-layout: fixed; line-height: 1.12; font-size: 10px; font-weight: bold; text-align: center; }
    .attestation-head-col { display: table-cell; width: 32%; vertical-align: top; }
    .attestation-head-center { width: 36%; vertical-align: middle; }
    .attestation-school-name { font-size: 11px; text-transform: uppercase; }
    .attestation-logo-row { display: table; width: 100%; table-layout: fixed; margin-bottom: 2px; }
    .attestation-logo-cell { display: table-cell; text-align: center; vertical-align: middle; }
    .attestation-logo { max-width: 62px; max-height: 58px; object-fit: contain; }
    .attestation-title { text-align: center; margin: 6px 0 6px; line-height: 1.12; }
    .attestation-title-main { display: block; font-size: 22px; font-weight: bold; text-decoration: underline; text-transform: uppercase; }
    .attestation-title-sub { display: block; font-size: 15px; font-weight: bold; text-transform: uppercase; }
    .attestation-body { font-size: 14px; line-height: 1.32; text-align: justify; margin-top: 4px; }
    .attestation-body p { margin: 4px 0; }
    .attestation-strong { font-weight: bold; text-transform: uppercase; }
    .attestation-identity { width: 100%; border-collapse: collapse; margin: 4px 0 6px; font-size: 14px; line-height: 1.18; }
    .attestation-identity td { border: 0; padding: 2px 4px; vertical-align: bottom; }
    .attestation-label-en { display: block; font-size: 11px; font-style: italic; font-weight: normal; }
    .attestation-legal { font-size: 12px; line-height: 1.25; }
    .attestation-result { display: table; width: 100%; margin: 7px 0 5px; font-size: 14px; font-weight: bold; }
    .attestation-result-left, .attestation-result-right { display: table-cell; width: 50%; vertical-align: top; }
    .attestation-result-right { text-align: right; }
    .attestation-signatures { display: table; width: 100%; margin-top: 6px; font-size: 13px; font-weight: bold; text-align: center; }
    .attestation-signature { display: table-cell; width: 33.33%; vertical-align: top; }
    .attestation-footer-note { position: absolute; left: 9mm; right: 9mm; bottom: 2.5mm; text-align: center; font-size: 8.6px; line-height: 1.08; }
    .attestation-soa-page {
        border: 0;
        width: {{ $attestationContentWidth }};
        height: {{ $attestationContentHeight }};
        min-height: {{ $attestationContentHeight }};
        max-height: {{ $attestationContentHeight }};
        padding: {{ $attestationPageSize == 'A3' ? '7mm 11mm 16mm' : '4mm 6mm 12mm' }};
        overflow: hidden;
    }
    .attestation-soa-header { display: table; width: 100%; table-layout: fixed; font-family: Arial, Helvetica, sans-serif; font-size: 8.2px; line-height: 1.08; text-align: center; }
    .attestation-soa-header-col { display: table-cell; width: 40%; vertical-align: top; font-weight: bold; }
    .attestation-soa-header-center { display: table-cell; width: 20%; vertical-align: top; font-weight: bold; padding: 1mm 2mm 0; }
    .attestation-soa-logo-wrap { height: 15mm; margin-top: 1mm; text-align: center; }
    .attestation-soa-logo { max-width: 13mm; max-height: 15mm; width: auto; height: auto; object-fit: contain; }
    .attestation-soa-site { font-weight: normal; font-size: 8px; }
    .attestation-soa-body { margin-top: 10mm; font-size: 13.2px; line-height: 1.28; text-align: left; }
    .attestation-soa-title { text-align: center; font-size: 19.8px; line-height: 1.06; font-weight: bold; text-transform: uppercase; }
    .attestation-soa-title span { display: block; }
    .attestation-soa-line { width: 140px; border-top: 1px solid #000; margin: 6px auto 10px; }
    .attestation-soa-intro { text-align: justify; margin: 0 0 8px; }
    .attestation-soa-body p { margin: 6px 0; }
    .attestation-soa-field { display: table; width: 100%; table-layout: fixed; margin: 3.5px 0; }
    .attestation-soa-label { display: table-cell; width: 29%; vertical-align: bottom; font-weight: bold; }
    .attestation-soa-value { display: table-cell; width: 71%; vertical-align: bottom; border-bottom: 1px dotted #777; font-weight: bold; text-transform: uppercase; padding-left: 6px; }
    .attestation-soa-label-en { display: block; font-size: 10.2px; font-style: italic; font-weight: normal; }
    .attestation-soa-two { display: table; width: 100%; table-layout: fixed; }
    .attestation-soa-two .attestation-soa-field { display: table-cell; width: 50%; padding-right: 10px; }
    .attestation-soa-two .attestation-soa-label { width: 38%; }
    .attestation-soa-two .attestation-soa-value { width: 62%; }
    .attestation-soa-results { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13.2px; }
    .attestation-soa-results td { padding: 2.5px 5px; vertical-align: middle; }
    .attestation-soa-results-label { font-weight: bold; width: 38%; }
    .attestation-soa-results-value { font-weight: bold; text-align: center; border-bottom: 1px dotted #777; }
    .attestation-soa-place { margin-top: 13px; text-align: right; font-size: 13.2px; }
    .attestation-soa-signature { margin-top: 22px; text-align: right; padding-right: 34px; font-weight: bold; font-size: 13.4px; }
    .attestation-soa-footer { position: absolute; left: 8mm; right: 8mm; bottom: 4mm; text-align: center; font-size: 8.1px; line-height: 1.08; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-header { font-size: 11px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-logo-wrap { height: 24mm; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-logo { max-width: 21mm; max-height: 24mm; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-site { font-size: 10px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-body { font-size: 17px; line-height: 1.42; margin-top: 19mm; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-title { font-size: 26px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-label-en { font-size: 13px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-results { font-size: 17px; margin-top: 18px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-place { font-size: 17px; margin-top: 28px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-signature { font-size: 17px; margin-top: 42px; }
    .attestation-page-a3.attestation-soa-page .attestation-soa-footer { font-size: 11px; }
    .attestation-ebolowa-page .attestation-soa-header { font-size: 8.8px; }
    .attestation-ebolowa-page .attestation-soa-logo-wrap { height: 14mm; }
    .attestation-ebolowa-page .attestation-soa-logo { max-width: 16mm; max-height: 14mm; }
    .attestation-ebolowa-page .attestation-soa-body { margin-top: 7mm; font-size: 13.8px; line-height: 1.24; }
    .attestation-ebolowa-page .attestation-soa-title { font-size: 21px; }
    .attestation-ebolowa-page .attestation-soa-line { margin-bottom: 8px; }
    .attestation-ebolowa-page .attestation-soa-field { margin: 2.5px 0; }
    .attestation-ebolowa-page .attestation-soa-results { font-size: 13.6px; margin-top: 7px; }
    .attestation-ebolowa-page .attestation-soa-place { margin-top: 10px; font-size: 13.6px; }
    .attestation-ebolowa-page .attestation-soa-signature { margin-top: 17px; font-size: 13.8px; }
</style>

<div class="btn-toolbar no-print attestation-toolbar">
    <a href="{{url('/eleves')}}" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-home"></span> Accueil</a>
    <a href="{{url($attestationHomeUrl)}}" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-certificate"></span> Attestations</a>
    <a href="{{url($attestationHistoryUrl)}}" class="btn btn-warning btn-sm"><span class="glyphicon glyphicon-folder-open"></span> Historique</a>
    <a href="{{ $attestationA4Url }}" class="btn btn-sm {{ $attestationPageSize == 'A4' ? 'btn-success' : 'btn-default' }}"><span class="glyphicon glyphicon-resize-full"></span> A4 {{ $isSoaAttestation ? 'portrait' : 'paysage' }}</a>
    <a href="{{ $attestationA3Url }}" class="btn btn-sm {{ $attestationPageSize == 'A3' ? 'btn-success' : 'btn-default' }}"><span class="glyphicon glyphicon-resize-full"></span> A3 {{ $isSoaAttestation ? 'portrait' : 'paysage' }}</a>
    <button onclick="window.print();" class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-print"></span> Imprimer</button>
    <a href="javascript:history.back();" class="btn btn-info btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Retour</a>
</div>

@if(count($attestations) <= 0)
    <div class="alert alert-warning">Aucune attestation de reussite trouvee pour cette generation.</div>
@endif

@foreach($attestations as $attestation)
    <?php
        $eleve = is_array($attestation) ? $attestation['eleve'] : $attestation->eleve;
        $moyenne100 = is_array($attestation) ? $attestation['moyenne100'] : $attestation->moyenne100;
        $moyenne20 = is_array($attestation) ? $attestation['moyenne20'] : $attestation->moyenne20;
        if ($isCompactAttestation) {
            if (is_numeric($moyenne20) && (float) $moyenne20 > 20) {
                $moyenne20 = ((float) $moyenne20) / 5;
                $moyenne100 = $moyenne20 * 5;
            } elseif (is_numeric($moyenne20) && is_numeric($moyenne100) && (float) $moyenne20 <= 4 && (float) $moyenne100 <= 20) {
                $moyenne20 = (float) $moyenne100;
                $moyenne100 = $moyenne20 * 5;
            } elseif (is_numeric($moyenne20)) {
                $moyenne20 = (float) $moyenne20;
                $moyenne100 = $moyenne20 * 5;
            }
        }
        $studentName = trim((isset($eleve->nom) ? $eleve->nom : '').' '.(isset($eleve->prenom) ? $eleve->prenom : ''));
        $birthDate = isset($eleve->date_naiss) && $eleve->date_naiss ? date('d/m/Y', strtotime($eleve->date_naiss)) : '........................';
        $birthPlace = isset($eleve->lieu_naiss) && $eleve->lieu_naiss ? $eleve->lieu_naiss : '........................';
        $sexe = isset($eleve->sexe) && $eleve->sexe ? $eleve->sexe : '...';
        $matricule = isset($eleve->matricule) && $eleve->matricule ? $eleve->matricule : '........................';
        $classeName = $classe && isset($classe->nom) ? $classe->nom : '';
        $classeNameEn = $classe && isset($classe->nom_anglais) && $classe->nom_anglais ? $classe->nom_anglais : $classeName;
        $annee = isset($generation) ? $generation->annee_academique : session('year');
        $mentionBase = $isCompactAttestation ? $moyenne20 : $moyenne100;
        $mentionMax = $isCompactAttestation ? 20 : 100;
        $mentionFr = getMention($mentionBase, $mentionMax);
        $oldLocale = session('locale');
        session(array('locale' => 'en'));
        $mentionEn = getMention($mentionBase, $mentionMax);
        session(array('locale' => $oldLocale));
        $mention = $mentionFr.($mentionEn && $mentionEn != $mentionFr ? ' / '.$mentionEn : '');
        $juryDate = $periode_fin ? date('d F Y', strtotime($periode_fin)) : date('d F Y', strtotime(isset($generation) ? $generation->created_at : 'now'));
        $gpa = $gpaFromAverage20($moyenne20);
        $sessionLabel = $sessionLabelFromYear($annee);
    ?>
    <div class="attestation-page attestation-page-{{ $attestationPageClass }} {{ $isCompactAttestation ? 'attestation-soa-page' : '' }} {{ $isEbolowaAttestation ? 'attestation-ebolowa-page' : '' }}">
        @if($isCompactAttestation)
            <div class="attestation-soa-header">
                <div class="attestation-soa-header-col">
                    <div>{{ $campusName }}</div>
                    <div>Arrete de creation n&deg; 14/0027/MINESUP du 11 Mars 2014</div>
                    <div>Autorisation d'ouverture n&deg; 14/01316/MINESUP du 11 Mars 2014</div>
                    <div>{{ $campusPostal }}, Tel {{ $campusPhone }}</div>
                    <div class="attestation-soa-logo-wrap">@if($campusLogo)<img src="{{ URL::asset($campusLogo) }}" class="attestation-soa-logo" alt="Hintel">@endif</div>
                    <div class="attestation-soa-site">{{ $campusSite }}</div>
                </div>
                <div class="attestation-soa-header-center">
                    <div>{{ $ministereFr }}</div>
                    <div>{{ $ministereEn }}</div>
                </div>
                <div class="attestation-soa-header-col">
                    <div>{{ $tutelleName }}</div>
                    <div>{{ $facultyFr }}</div>
                    <div>{{ $tutellePostal }}@if($tutellePhone), Tel : {{ $tutellePhone }}@endif</div>
                    <div>{{ $tutelleNameEn }}</div>
                    <div>{{ $facultyEn }}</div>
                    <div class="attestation-soa-logo-wrap">@if($tutelleLogo)<img src="{{ URL::asset($tutelleLogo) }}" class="attestation-soa-logo" alt="Tutelle">@endif</div>
                    <div class="attestation-soa-site">www.univ-yde2.org</div>
                </div>
            </div>

            <div class="attestation-soa-body">
                <div class="attestation-soa-title">
                    <span>ATTESTATION DE REUSSITE</span>
                    <span>INDIVIDUAL RESULTS TESTIMONIAL</span>
                </div>
                <div class="attestation-soa-line"></div>

                <p class="attestation-soa-intro">
                    Le Doyen de la <strong>{{ $facultyFr }}</strong> de l'<strong>{{ $tutelleName }}</strong> atteste que :<br>
                    <em>The Dean of the <strong>{{ $facultyEn }}</strong> of the <strong>{{ $tutelleNameEn }}</strong> certifies that:</em>
                </p>

                <div class="attestation-soa-field">
                    <div class="attestation-soa-label">Monsieur / Mademoiselle<span class="attestation-soa-label-en">Mister / Miss</span></div>
                    <div class="attestation-soa-value">{{ $studentName }}</div>
                </div>
                <div class="attestation-soa-two">
                    <div class="attestation-soa-field">
                        <div class="attestation-soa-label">Ne(e) le :<span class="attestation-soa-label-en">Born on</span></div>
                        <div class="attestation-soa-value">{{ $birthDate }}</div>
                    </div>
                    <div class="attestation-soa-field">
                        <div class="attestation-soa-label">a :<span class="attestation-soa-label-en">at</span></div>
                        <div class="attestation-soa-value">{{ $birthPlace }}</div>
                    </div>
                </div>
                <div class="attestation-soa-field">
                    <div class="attestation-soa-label">Matricule N&deg; :<span class="attestation-soa-label-en">Registration Number</span></div>
                    <div class="attestation-soa-value">{{ $matricule }}</div>
                </div>

                <p>
                    A subi avec succes les epreuves sanctionnant le diplome de :<br>
                    <em>Has successfully completed and passed the examination of the:</em>
                </p>
                <div class="attestation-soa-field">
                    <div class="attestation-soa-label">Diplome<span class="attestation-soa-label-en">Degree</span></div>
                    <div class="attestation-soa-value">LICENCE PROFESSIONNELLE</div>
                </div>
                <div class="attestation-soa-field">
                    <div class="attestation-soa-label">Filiere :<span class="attestation-soa-label-en">Field of study</span></div>
                    <div class="attestation-soa-value">{{ $domaineFr }}</div>
                </div>
                <div class="attestation-soa-field">
                    <div class="attestation-soa-label">Specialite :<span class="attestation-soa-label-en">Speciality</span></div>
                    <div class="attestation-soa-value">{{ $classeName }}</div>
                </div>

                <table class="attestation-soa-results">
                    <tr>
                        <td class="attestation-soa-results-label">Moyenne Generale :<br><em>General Average</em></td>
                        <td class="attestation-soa-results-value">{{ number_format($moyenne20, 2) }}/20</td>
                        <td class="attestation-soa-results-label">MENTION :<br><em>Grade</em></td>
                        <td class="attestation-soa-results-value">{{ $mention }}</td>
                    </tr>
                    <tr>
                        <td class="attestation-soa-results-label">MOYENNE GENERALE PONDEREE :<br><em>GRADE POINT AVERAGE</em></td>
                        <td class="attestation-soa-results-value">{{ number_format($gpa, 1) }}</td>
                        <td class="attestation-soa-results-label">Annee Academique :<br><em>Academic Year</em></td>
                        <td class="attestation-soa-results-value">{{ $annee }}</td>
                    </tr>
                    <tr>
                        <td class="attestation-soa-results-label">A la session de :<br><em>At the session of</em></td>
                        <td colspan="3" class="attestation-soa-results-value">{{ $sessionLabel }}</td>
                    </tr>
                </table>

                <div class="attestation-soa-place">{{ $attestationCity }}, le........................</div>
                <div class="attestation-soa-signature">LE DOYEN</div>
            </div>

            <div class="attestation-soa-footer">
                N.B.: Il n'est delivre qu'une seule attestation. Il appartient au titulaire d'etablir lui-meme et faire certifier conformes les copies qui peuvent lui etre necessaires.<br>
                <em>Only one copy of this testimonial shall be delivered. It is in the interest of the owner to make as many certified true copies as he/she may desire.</em>
            </div>
        @else
        <div class="attestation-head">
            <div class="attestation-head-col">
                <div>{{ $republiqueFr }}</div>
                <div>{{ $patrieFr }}</div>
                <div>{{ $ministereFr }}</div>
                <div>{{ $tutelleName }}</div>
                <div>{{ $tutellePostal }}@if($tutellePhone), Tel: {{ $tutellePhone }}@endif</div>
                <div>{{ $facultyFr }}</div>
            </div>
            <div class="attestation-head-col attestation-head-center">
                <div class="attestation-logo-row">
                    <div class="attestation-logo-cell">@if($campusLogo)<img src="{{ URL::asset($campusLogo) }}" class="attestation-logo" alt="Hintel">@endif</div>
                    <div class="attestation-logo-cell">@if($tutelleLogo)<img src="{{ URL::asset($tutelleLogo) }}" class="attestation-logo" alt="Tutelle">@endif</div>
                    <div class="attestation-logo-cell">@if($facultyLogo)<img src="{{ URL::asset($facultyLogo) }}" class="attestation-logo" alt="Faculte">@endif</div>
                </div>
                <div class="attestation-school-name">{{ $campusName }}</div>
                <div>({{ $campusSigle }})</div>
                <div>{{ $campusPostal }}@if($campusPhone), Tel {{ $campusPhone }}@endif</div>
                <div>{{ $campusSite }}</div>
            </div>
            <div class="attestation-head-col">
                <div>{{ $republiqueEn }}</div>
                <div>{{ $patrieEn }}</div>
                <div>{{ $ministereEn }}</div>
                <div>{{ $tutelleNameEn }}</div>
                <div>{{ $tutellePostal }}@if($tutellePhone), Tel: {{ $tutellePhone }}@endif</div>
                <div>{{ $facultyEn }}</div>
            </div>
        </div>

        <div class="attestation-title">
            <span class="attestation-title-main">ATTESTATION DE REUSSITE DE LICENCE</span>
            <span class="attestation-title-sub">BACHELOR'S DEGREE SUCCESS TESTIMONIAL</span>
        </div>

        <div class="attestation-body">
            <p>
                Le Directeur de l'<span class="attestation-strong">{{ $facultyFr }}</span> de l'<span class="attestation-strong">{{ $tutelleName }}</span>
                et le Directeur de l'<span class="attestation-strong">{{ $campusName }}</span> certifient que: /
                <em>The Director of <span class="attestation-strong">{{ $facultyEn }}</span> of the <span class="attestation-strong">{{ $tutelleNameEn }}</span>
                and the Director of the <span class="attestation-strong">{{ $campusName }}</span> hereby certify that:</em>
            </p>

            <table class="attestation-identity">
                <tr>
                    <td style="width:12%;"><strong>M. / Mlle:</strong><span class="attestation-label-en">Mr/Mss</span></td>
                    <td style="width:43%;" class="attestation-strong">{{ $studentName }}</td>
                    <td style="width:15%;"><strong>Matricule N&deg;:</strong><span class="attestation-label-en">Registration</span></td>
                    <td style="width:30%;" class="attestation-strong">{{ $matricule }}</td>
                </tr>
                <tr>
                    <td><strong>Ne(e) le:</strong><span class="attestation-label-en">Born on</span></td>
                    <td>{{ $birthDate }} &agrave; {{ $birthPlace }}</td>
                    <td><strong>Sexe:</strong><span class="attestation-label-en">Sex</span></td>
                    <td>{{ $sexe }}</td>
                </tr>
            </table>

            <div class="attestation-legal">
                <p>Vu la r&egrave;glementation en vigueur, / <em>Mindful of the regulations in force,</em></p>
                <p>Vu l'Accord-Cadre du <strong>24 Fevrier 2025</strong> entre l'<span class="attestation-strong">{{ $tutelleName }}</span> et <span class="attestation-strong">{{ $campusSigle }}</span> / <em>Mindful of the Cooperation Agreement of 24 February 2025 between the <span class="attestation-strong">{{ $tutelleNameEn }}</span> and <span class="attestation-strong">{{ $campusSigle }}</span>,</em></p>
                <p>Vu la Convention Sp&eacute;cifique entre l'<span class="attestation-strong">{{ $tutelleName }}</span> &agrave; travers l'<span class="attestation-strong">{{ $facultyFr }}</span> et l'<span class="attestation-strong">{{ $campusName }}</span>, relative aux formations pr&eacute;parant aux dipl&ocirc;mes de Licence dans les Fili&egrave;res Informatiques en date du <strong>24 Fevrier 2025</strong>, / <em>Mindful of the Specific Agreement between the <span class="attestation-strong">{{ $tutelleNameEn }}</span> through its <span class="attestation-strong">{{ $facultyEn }}</span> and <span class="attestation-strong">{{ $campusName }}</span> on courses in preparation for the award of a Bachelor's Degree in COMPUTER SCIENCES of 24th February 2025.</em></p>
                <p>Vu le proc&egrave;s-verbal du jury en date du <strong>{{ $juryDate }}</strong> / <em>Mindful of the official report of the jury of <strong>{{ $juryDate }}</strong>,</em></p>
            </div>

            <p>
                A suivi avec succ&egrave;s la formation pour l'obtention du dipl&ocirc;me de
                <span class="attestation-strong">LICENCE EN {{ $domaineFr }}</span>, fili&egrave;re:
                <span class="attestation-strong">{{ $classeName }}</span>, sp&eacute;cialit&eacute;:
                <span class="attestation-strong">{{ $classeName }}</span>, /
                <em>Has successfully completed the course work for award of a Bachelor's degree in
                <span class="attestation-strong">{{ $domaineEn }}</span>, field of study:
                <span class="attestation-strong">{{ $classeNameEn }}</span>, Speciality:
                <span class="attestation-strong">{{ $classeNameEn }}</span>.</em>
            </p>

            <div class="attestation-result">
                <div class="attestation-result-left">
                    Annee academique : {{ $annee }}<br>
                    <em>Academic Year</em>
                </div>
                <div class="attestation-result-right">
                    Mention Globale : {{ $mention }}<br>
                    Moyenne : {{ number_format($moyenne20, 2) }}/20 - {{ number_format($moyenne100, 2) }}/100
                </div>
            </div>

            <p>
                En foi de quoi, la pr&eacute;sente attestation lui est d&eacute;livr&eacute;e pour servir et valoir ce que de droit. /
                <em>This certificate is, therefore, issued to the above named to serve the purposes for which it is intended.</em>
            </p>
        </div>

        <div class="attestation-signatures">
            <div class="attestation-signature">{{ $attestationSignaturePrimary }}<br><em>{{ $attestationSignaturePrimaryEn }}</em></div>
            <div class="attestation-signature">Le Directeur de {{ $campusSigle }}<br><em>The Director</em></div>
            <div class="attestation-signature">{{ $attestationCity }}, le ........................<br><em>on</em></div>
        </div>

        <div class="attestation-footer-note">
            Il n'est delivre qu'un seul exemplaire de cette attestation de reussite. Le titulaire peut etablir et faire certifier des copies conformes /
            <em>Only one success certificate is issued. The owner can establish and certify sample copies.</em>
        </div>
        @endif
    </div>
@endforeach
@endsection
