<style>
    .pv-tutelle-header { text-align: center; margin-bottom: 10px; }
    .pv-tutelle-top { display: table; width: 100%; font-size: 12px; font-weight: bold; }
    .pv-tutelle-left, .pv-tutelle-center, .pv-tutelle-right { display: table-cell; width: 33.33%; vertical-align: middle; padding: 2px 6px; }
    .pv-tutelle-left { text-align: left; }
    .pv-tutelle-right { text-align: right; }
    .pv-tutelle-line { border-top: 2px solid #b30000; margin: 8px auto 6px; width: 78%; }
    .pv-tutelle-header h3 { margin: 4px 0; font-size: 16px; font-weight: bold; }
    .pv-tutelle-logo {
        max-width: 72px;
        max-height: 58px;
        width: auto;
        height: auto;
        object-fit: contain;
        display: inline-block;
        vertical-align: middle;
        border-radius: 6px;
        background: rgba(255,255,255,0.5);
    }
    .pv-tutelle-school {
        display: block;
        margin-top: 2px;
        font-size: 11px;
        line-height: 1.1;
        text-transform: uppercase;
    }
    .pv-tutelle-center {
        font-size: 11px;
        line-height: 1.2;
    }
    .pv-meta {
        width: 96%;
        margin: 4px auto 6px;
        display: table;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 10px;
        line-height: 1.15;
    }
    .pv-meta-item {
        display: table-cell;
        border: 1px solid #d0d0d0;
        padding: 3px 5px;
        vertical-align: middle;
        text-align: center;
    }
    .pv-meta-specialite {
        width: 34%;
    }
    .pv-meta-item strong {
        white-space: nowrap;
    }
</style>

<?php
    $pvTypeLower = strtolower(isset($pv_type) ? $pv_type : '');
    $pvMode = isset($pv_title_mode) ? $pv_title_mode : 'semestre';
    $pvSemestre = isset($semestre) ? (int) $semestre : null;
    $pvIsEnglish = in_array($pvTypeLower, array('hnd', 'licence_bamenda'));

    $pvTutelles = array(
        'licence_soa' => array(
            'left' => 'INSTITUT SUPERIEUR AZIMUT DE COMMERCE, DE TECHNOLOGIE ET DE SANTE',
            'right' => 'UNIVERSITE DE YAOUNDE 2-SOA',
            'cycle' => 'LICENCE PROFESSIONNELLE',
            'format' => 'SOA',
            'logo_left' => 'img/azimut.png',
            'logo_right' => 'img/soa.jpeg'
        ),
        'licence_bamenda' => array(
            'left' => 'AZIMUT HIGHER INSTITUTE OF COMMERCE, TECHNOLOGY AND HEALTH',
            'right' => 'THE UNIVERSITY OF BAMENDA',
            'cycle' => 'BACHELORS DEGREE',
            'format' => 'BAMENDA',
            'logo_left' => 'img/azimut.png',
            'logo_right' => 'img/bamenda.webp'
        ),
        'licence_ebolowa' => array(
            'left' => 'INSTITUT SUPERIEUR AZIMUT DE COMMERCE, DE TECHNOLOGIE ET DE SANTE',
            'right' => "UNIVERSITE D'EBOLOWA",
            'cycle' => 'LICENCE PROFESSIONNELLE',
            'format' => 'EBOLOWA',
            'logo_left' => 'img/azimut.png',
            'logo_right' => 'img/ebolowa.webp'
        ),
        'licence_ngaoundere' => array(
            'left' => 'INSTITUT SUPERIEUR AZIMUT DE COMMERCE, DE TECHNOLOGIE ET DE SANTE',
            'right' => "UNIVERSITE DE NGAOUNDERE",
            'cycle' => 'LICENCE PROFESSIONNELLE',
            'format' => 'NGAOUNDERE',
            'logo_left' => 'img/azimut.png',
            'logo_right' => 'img/ngaoundere.jpg'
        ),
        'hnd' => array(
            'left' => 'AZIMUT HIGHER INSTITUTE OF COMMERCE, TECHNOLOGY AND HEALTH',
            'right' => 'THE UNIVERSITY OF BAMENDA',
            'cycle' => 'HND',
            'format' => 'HND',
            'logo_left' => 'img/azimut.png',
            'logo_right' => 'img/bamenda.webp'
        ),
        'bts' => array(
            'left' => 'INSTITUT SUPERIEUR AZIMUT DE COMMERCE, DE TECHNOLOGIE ET DE SANTE',
            'right' => '',
            'cycle' => 'BTS',
            'format' => 'BTS',
            'logo_left' => 'img/azimut.png',
            'logo_right' => ''
        )
    );

    $pvHeader = isset($pvTutelles[$pvTypeLower])
        ? $pvTutelles[$pvTypeLower]
        : array(
            'left' => 'INSTITUT SUPERIEUR AZIMUT DE COMMERCE, DE TECHNOLOGIE ET DE SANTE',
            'right' => '',
            'cycle' => strtoupper($pvTypeLower),
            'format' => strtoupper($pvTypeLower),
            'logo_left' => 'img/azimut.png',
            'logo_right' => ''
        );

    $pvHeader['center_lines'] = $pvIsEnglish
        ? array(
            'Creation order No. 14/0027/MINESUP of March 11, 2014',
            'Opening authorization No. 14/01316/MINESUP of March 11, 2014'
        )
        : array(
            'Arrete de creation n 14/0027/MINESUP du 11 Mars 2014',
            "Autorisation d'ouverture n 14/01316/MINESUP du 11 Mars 2014"
        );

    $pvCampus = isset($campus) ? $campus : null;
    $pvTutelle = isset($tutelle) ? $tutelle : null;

    if (!$pvCampus && session('campus_id')) {
        $pvCampus = \gesecole\Campus::find(session('campus_id'));
    }

    if (!$pvTutelle && in_array($pvTypeLower, array('licence_soa', 'licence_bamenda', 'licence_ebolowa', 'licence_ngaoundere', 'hnd'))) {
        if ($pvTypeLower == 'licence_soa') {
            $keywords = array('YAOUNDE', 'SOA');
        } elseif ($pvTypeLower == 'licence_ebolowa') {
            $keywords = array('EBOLOWA', 'ISABEE');
        } elseif ($pvTypeLower == 'licence_ngaoundere') {
            $keywords = array('NGAOUNDERE', 'NGAOUND');
        } else {
            $keywords = array('BAMENDA');
        }
        $pvTutelle = \gesecole\TutelleAcademique::where(function ($query) use ($keywords) {
            foreach ($keywords as $keyword) {
                $query->orWhere('nom', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('tutelle_anglophone', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('ville', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('localisation', 'LIKE', '%'.$keyword.'%');
            }
        })->first();
    }

    $pvClean = function ($value) {
        return trim((string) $value);
    };

    $pvLogoPath = function ($value, $fallback = '') use ($pvClean) {
        $path = str_replace('\\', '/', $pvClean($value));
        if ($path === '') {
            return $fallback;
        }
        if (strpos($path, 'public/') === 0) {
            $path = substr($path, 7);
        }
        if (strpos($path, '/public/') !== false) {
            $path = substr($path, strpos($path, '/public/') + 8);
        }
        return ltrim($path, '/');
    };

    if (in_array($pvTypeLower, array('licence_soa', 'licence_bamenda', 'licence_ebolowa', 'licence_ngaoundere', 'hnd'))) {
        if ($pvCampus) {
            $campusName = $pvClean(isset($pvCampus->nom) ? $pvCampus->nom : '');
            if ($campusName !== '' && !$pvIsEnglish) {
                $pvHeader['left'] = $campusName;
            }
            $pvHeader['logo_left'] = $pvLogoPath(isset($pvCampus->logo) ? $pvCampus->logo : '', $pvHeader['logo_left']);

            $centerLines = array();
            if ($pvClean(isset($pvCampus->arrete_creation) ? $pvCampus->arrete_creation : '') !== '') {
                $centerLines[] = $pvCampus->arrete_creation;
            }
            if ($pvClean(isset($pvCampus->autorisation_ouverture) ? $pvCampus->autorisation_ouverture : '') !== '') {
                $centerLines[] = $pvCampus->autorisation_ouverture;
            }

            $contacts = array();
            if ($pvClean(isset($pvCampus->boite_postale) ? $pvCampus->boite_postale : '') !== '') {
                $contacts[] = $pvCampus->boite_postale;
            }
            if ($pvClean(isset($pvCampus->ville) ? $pvCampus->ville : '') !== '') {
                $contacts[] = $pvCampus->ville;
            }
            if ($pvClean(isset($pvCampus->telephone) ? $pvCampus->telephone : '') !== '') {
                $contacts[] = 'Tel '.$pvCampus->telephone;
            }
            if ($pvClean(isset($pvCampus->email) ? $pvCampus->email : '') !== '') {
                $contacts[] = $pvCampus->email;
            }
            if ($pvClean(isset($pvCampus->site_web) ? $pvCampus->site_web : '') !== '') {
                $contacts[] = $pvCampus->site_web;
            }
            if (count($contacts) > 0) {
                $centerLines[] = implode(', ', $contacts);
            }
            if (count($centerLines) > 0 && !$pvIsEnglish) {
                $pvHeader['center_lines'] = $centerLines;
            }
        }

        if ($pvTutelle) {
            $tutelleName = $pvIsEnglish
                ? $pvClean(isset($pvTutelle->tutelle_anglophone) ? $pvTutelle->tutelle_anglophone : '')
                : $pvClean(isset($pvTutelle->nom) ? $pvTutelle->nom : '');
            if ($tutelleName === '') $tutelleName = $pvClean(isset($pvTutelle->nom) ? $pvTutelle->nom : '');
            if ($tutelleName === '') $tutelleName = $pvClean(isset($pvTutelle->tutelle_anglophone) ? $pvTutelle->tutelle_anglophone : '');
            if ($tutelleName !== '') {
                $pvHeader['right'] = $tutelleName;
                $pvHeader['format'] = $tutelleName;
            }
            $pvHeader['logo_right'] = $pvLogoPath(isset($pvTutelle->logo) ? $pvTutelle->logo : '', $pvHeader['logo_right']);
            if (empty($pvHeader['logo_right'])) {
                $pvHeader['logo_right'] = $pvLogoPath(isset($pvTutelle->logo_faculte) ? $pvTutelle->logo_faculte : '', 'img/soa.jpeg');
            }

            if ($pvIsEnglish) {
                $englishLines = array();
                foreach (array('republique_anglophone', 'patrie_anglophone', 'ministere_tutelle_anglophone', 'faculte_anglophone') as $field) {
                    $line = $pvClean(isset($pvTutelle->$field) ? $pvTutelle->$field : '');
                    if ($line !== '') {
                        $englishLines[] = $line;
                    }
                }
                if (count($englishLines) > 0) {
                    $pvHeader['center_lines'] = $englishLines;
                }
            }
        }
    }

    $logoLeft = !empty($pvHeader['logo_left']) ? $pvHeader['logo_left'] : 'img/azimut.png';
    $logoRight = !empty($pvHeader['logo_right']) ? $pvHeader['logo_right'] : '';

    if ($pvMode == 'annuel') {
        $pvTitle = $pvIsEnglish ? 'ANNUAL SUMMARY RESULTS' : 'BILAN ANNUEL';
    } elseif ($pvMode == 'rattrapage') {
        $pvTitle = $pvIsEnglish
            ? (($pvSemestre == 2 ? 'SECOND' : 'FIRST').' SEMESTER RESIT RESULTS')
            : ('RATTRAPAGES DU SEMESTRE '.($pvSemestre ? $pvSemestre : ''));
    } else {
        $pvTitle = $pvIsEnglish
            ? (($pvSemestre == 2 ? 'SECOND' : 'FIRST').' SEMESTER FINAL RESULTS')
            : ('RESULTATS DU SEMESTRE '.($pvSemestre ? $pvSemestre : '').' NORMALE');
    }
    if (isset($pvListeTitle) && $pvListeTitle !== '') {
        $pvTitle .= $pvListeTitle;
    }

    $pvDisplayCycle = isset($cycle) && isset($cycle->nom) && trim($cycle->nom) !== ''
        ? $cycle->nom
        : $pvHeader['cycle'];
?>

<div class="pv-tutelle-header">
    <div class="pv-tutelle-top">
        <div class="pv-tutelle-left">
            @if(!empty($logoLeft))
                <img src="{{ asset($logoLeft) }}" alt="Logo Azimut" class="pv-tutelle-logo" />
            @endif
            <span class="pv-tutelle-school">{{ $pvHeader['left'] }}</span>
        </div>
        <div class="pv-tutelle-center">
            {!! implode('<br>', array_map('e', $pvHeader['center_lines'])) !!}
        </div>
        <div class="pv-tutelle-right">
            @if(!empty($logoRight))
                <img src="{{ asset($logoRight) }}" alt="Logo tutelle" class="pv-tutelle-logo" />
            @endif
            <span class="pv-tutelle-school">{{ $pvHeader['right'] }}</span>
        </div>
    </div>
    <div class="pv-tutelle-line"></div>
    <h3>{{ $pvTitle }} {{ $annee }}</h3>
    <div class="pv-meta">
        <span class="pv-meta-item"><strong>CYCLE :</strong> {{ $pvDisplayCycle }}</span>
        <span class="pv-meta-item"><strong>{{ $pvIsEnglish ? 'LEVEL' : 'NIVEAU' }} :</strong> {{ $nniveau->nom }}</span>
        <span class="pv-meta-item pv-meta-specialite"><strong>{{ $pvIsEnglish ? 'SPECIALITY' : 'SPECIALITE' }} :</strong> {{ $classe->nom }}</span>
        <span class="pv-meta-item"><strong>{{ $pvIsEnglish ? 'MENTOR UNIVERSITY' : 'TUTELLE' }} :</strong> {{ $pvHeader['format'] }}</span>
        @if(!empty($date_debut) || !empty($date_fin))
            <span class="pv-meta-item">
                <strong>{{ $pvIsEnglish ? 'PERIOD' : 'PERIODE' }} :</strong>
                {{ !empty($date_debut) ? $date_debut : '...' }}
                {{ $pvIsEnglish ? 'to' : 'au' }}
                {{ !empty($date_fin) ? $date_fin : '...' }}
            </span>
        @endif
    </div>
</div>
