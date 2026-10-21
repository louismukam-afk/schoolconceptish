@extends('skeleton')

@section('sub-content')

    <?php
    /*
    |--------------------------------------------------------------------------
    | PARAMETRES D'AFFICHAGE
    |--------------------------------------------------------------------------
    */
    $nbEtudiants = isset($results) ? count($results) : 0;
    $pvListe = isset($_GET['liste']) ? strtolower(trim($_GET['liste'])) : '';
    if (in_array($pvListe, array('admis', 'non_admis', 'merite')) && isset($results)) {
        $results = array_values(array_filter($results, function ($result) use ($pvListe) {
            $decision = strtoupper(trim(isset($result['decision']) ? $result['decision'] : ''));
            $isAdmis = in_array($decision, array('ADMIS', 'ADMISE', 'VALIDE', 'V'));
            if ($pvListe == 'admis') {
                return $isAdmis;
            }
            if ($pvListe == 'non_admis') {
                return !$isAdmis;
            }
            return $isAdmis && isset($result['rang']) && $result['rang'] !== null;
        }));
        if ($pvListe == 'merite') {
            usort($results, function ($a, $b) {
                $ra = isset($a['rang']) && $a['rang'] !== null ? (int) $a['rang'] : 999999;
                $rb = isset($b['rang']) && $b['rang'] !== null ? (int) $b['rang'] : 999999;
                if ($ra == $rb) return 0;
                return ($ra > $rb) ? 1 : -1;
            });
        } else {
            usort($results, function ($a, $b) {
                $na = isset($a['eleve']) ? trim($a['eleve']->nom.' '.$a['eleve']->prenom) : '';
                $nb = isset($b['eleve']) ? trim($b['eleve']->nom.' '.$b['eleve']->prenom) : '';
                return strcasecmp($na, $nb);
            });
        }
        $nbEtudiants = count($results);
    }
    $pvListeTitles = array(
        'admis' => ' - LISTE DES ADMIS',
        'non_admis' => ' - LISTE DES NON ADMIS',
        'merite' => ' - LISTE DE MERITE'
    );
    $pvListeTitle = isset($pvListeTitles[$pvListe]) ? $pvListeTitles[$pvListe] : '';
    $pvTypeLower = strtolower(isset($pv_type) ? $pv_type : '');
    $pvIsSoa = ($pvTypeLower == 'licence_soa');
    $pvIsEnglish = in_array($pvTypeLower, array('hnd', 'licence_bamenda'));
    $moduleGroups = array();
    $pvHasTpe = false;

    // Nombre réel de colonnes Excel / tableau
    $nbColonnes = $pvIsSoa ? 4 : 3; // N°, Matricule, Noms, type ligne SOA

    foreach ($matieres as $m) {
        if ($pvIsSoa) {
            // Note brute + note UV /20
            $nbColonnes += 2;
        } else {
            // CC + EXAM + NOTE /100 + CREDIT
            $nbColonnes += 4;

            // TPE uniquement si configuré
            if (!empty($m->has_tpe)) {
                $pvHasTpe = true;
                $nbColonnes++;
            }
        }

        $moduleKey = !empty($m->id_module) ? (int) $m->id_module : ('single_'.$m->id_matiere);
        if (!isset($moduleGroups[$moduleKey])) {
            $moduleGroups[$moduleKey] = array(
                'code' => !empty($m->code_module) ? $m->code_module : (!empty($m->code_matiere) ? $m->code_matiere : ''),
                'nom' => !empty($m->module_nom) ? $m->module_nom : $m->nom,
                'nom_anglais' => !empty($m->module_nom_anglais) ? $m->module_nom_anglais : $m->nom_anglais,
                'credit' => 0,
                'colspan' => 0,
                'matieres' => array()
            );
        }

        $matiereColspan = $pvIsSoa ? 2 : (!empty($m->has_tpe) ? 5 : 4);
        $moduleGroups[$moduleKey]['credit'] += (float) $m->credit;
        $moduleGroups[$moduleKey]['colspan'] += $matiereColspan;
        $moduleGroups[$moduleKey]['matieres'][] = $m;
    }

    $pvTotalCredits = !empty($total_credits) ? (float) $total_credits : 0;
    if ($pvTotalCredits <= 0) {
        foreach ($matieres as $m) {
            $pvTotalCredits += (float) $m->credit;
        }
    }
    $moduleSummaryChunks = ($pvIsSoa || $pvIsEnglish)
        ? array_chunk($moduleGroups, max(1, (int) ceil(count($moduleGroups) / 2)), true)
        : array();

    $matiereCodeFor = function ($matiere) {
        if (!empty($matiere->code_matiere)) return $matiere->code_matiere;
        if (!empty($matiere->id_code)) return $matiere->id_code;
        if (!empty($matiere->code)) return $matiere->code;
        return 'MAT '.$matiere->id_matiere;
    };

    if ($pvIsSoa) {
        foreach ($moduleGroups as $moduleKey => $module) {
            $moduleGroups[$moduleKey]['colspan'] += 2; // Note UE /20 + Crédit
        }
        $nbColonnes += count($moduleGroups) * 2;
    } elseif (isset($pv_type) && strtolower($pv_type) == 'hnd') {
        $nbColonnes++; // moyenne /100
    }

    // SOA suit le modele Excel : Total credit + Moyenne generale + Decision.
    $nbColonnes += $pvIsSoa ? 3 : 4;

    /*
    |--------------------------------------------------------------------------
    | FORMAT D'IMPRESSION AUTOMATIQUE
    |--------------------------------------------------------------------------
    */
    if ($nbColonnes <= 16) {
        $defaultPageSize = 'A4';
        $defaultOrientation = 'portrait';
        $printFontSize = 8;
    } elseif ($nbColonnes <= 25) {
        $defaultPageSize = 'A4';
        $defaultOrientation = 'landscape';
        $printFontSize = 7;
    } else {
        $defaultPageSize = 'A3';
        $defaultOrientation = 'landscape';
        $printFontSize = 5.5;
    }

    if ($nbEtudiants > 35) {
        $printFontSize = max(5, $printFontSize - 0.5);
    }
    ?>

    <style>
        .pv-toolbar {
            margin: 10px 0 15px;
        }

        .pv-toolbar .btn,
        .pv-toolbar .dt-button {
            margin: 2px;
        }

        .pv-title {
            text-align: center;
            margin-bottom: 10px;
        }

        .pv-title h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .pv-title h3 {
            margin: 4px 0;
            font-size: 16px;
            font-weight: bold;
        }

        .pv-tutelle-header { text-align: center; margin-bottom: 10px; }
        .pv-tutelle-top { display: table; width: 100%; font-size: 12px; font-weight: bold; }
        .pv-tutelle-left, .pv-tutelle-center, .pv-tutelle-right { display: table-cell; width: 33.33%; vertical-align: top; padding: 2px 6px; }
        .pv-tutelle-left { text-align: left; }
        .pv-tutelle-right { text-align: right; }
        .pv-tutelle-line { border-top: 2px solid #b30000; margin: 8px auto 6px; width: 78%; }
        .pv-tutelle-header h3 { margin: 4px 0; font-size: 16px; font-weight: bold; }

        .pv-meta {
            font-size: 12px;
            margin-bottom: 8px;
            text-align: center;
        }

        .pv-scroll {
            overflow-x: auto;
            width: 100%;
        }

        #pv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        #pv-table th,
        #pv-table td {
            border: 1px solid #222 !important;
            padding: 3px 4px !important;
            vertical-align: middle !important;
            text-align: center;
        }

        .pv-list-summary {
            width: 96%;
            margin: 14px auto;
            border-collapse: collapse;
            font-size: 12px;
        }

        .pv-list-summary th,
        .pv-list-summary td {
            border: 1px solid #222;
            padding: 6px 8px;
            text-align: center;
        }

        .pv-list-summary th:nth-child(2),
        .pv-list-summary td:nth-child(2) {
            text-align: left;
        }

        #pv-print-zone.pv-list-only .pv-scroll {
            display: none;
        }

        #pv-print-zone.pv-list-only .pv-module-summary,
        #pv-print-zone.pv-list-only .soa-summary-grid,
        #pv-print-zone.pv-list-only .pv-bottom-legend {
            display: none;
        }

        @media print {
            .pv-list-summary {
                font-size: 11px;
            }
        }

        #pv-table thead th {
            white-space: normal;
            line-height: 1.1;
            background: #bfbfbf;
            font-weight: bold;
        }

        #pv-table th.module {
            background: #969696;
            color: #111;
            font-size: 9px;
            text-transform: uppercase;
        }

        #pv-table th.matiere,
        #pv-table th.code {
            min-width: 92px;
            max-width: 150px;
            width: auto;
            height: auto;
            padding: 5px 6px !important;
            font-size: 8.5px;
            line-height: 1.15;
            white-space: normal;
        }

        #pv-table th.rotate > span {
            display: inline-block;
            max-width: 82px;
            transform: rotate(-90deg);
            white-space: nowrap;
            font-size: 8px;
            line-height: 1;
        }

        #pv-table th.credit-row {
            height: 20px;
            font-size: 8px;
        }

        #pv-table th.fixed-head {
            background: #bfbfbf;
            min-width: 36px;
        }

        #pv-table th.name-head {
            min-width: 165px;
            font-size: 20px;
        }

        #pv-table td.nom {
            text-align: left;
            min-width: 170px;
            white-space: normal;
        }

        #pv-table tbody td:not(.nom) {
            white-space: nowrap;
        }

        #pv-table .echec {
            background: #f5d1d1;
        }

        #pv-table .valide {
            background: #d8efd8;
        }

        #pv-table .vide {
            background: #efefef;
            color: #888;
        }

        .small-pct {
            display: block;
            font-size: 8px;
            font-weight: normal;
        }

        .pv-module-summary {
            width: 96%;
            margin: 16px auto 10px;
            border-collapse: collapse;
            font-size: 10px;
        }

        .pv-module-summary th,
        .pv-module-summary td {
            border: 1px solid #222;
            padding: 5px 8px;
            line-height: 1.25;
        }

        .pv-module-summary th {
            background: #969696;
            text-align: center;
            font-size: 10.5px;
            text-transform: uppercase;
        }

        .pv-module-summary td:first-child,
        .pv-module-summary td:last-child {
            width: 90px;
            text-align: center;
            white-space: nowrap;
        }

        .pv-module-summary td:nth-child(2) {
            text-align: left;
        }

        .soa-summary-grid {
            width: 96%;
            margin: 12px auto 6px;
            display: table;
            table-layout: fixed;
            border-spacing: 14px 0;
        }

        .soa-summary-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .soa-summary-col .pv-module-summary {
            width: 100%;
            margin: 0;
            font-size: 8.5px;
        }

        .soa-summary-col .pv-module-summary th,
        .soa-summary-col .pv-module-summary td {
            padding: 2px 5px;
            line-height: 1.15;
        }

        .soa-summary-col .pv-module-summary th {
            font-size: 8.5px;
        }

        .english-summary-grid {
            margin-top: 10px;
        }

        .english-summary {
            table-layout: fixed;
        }

        .english-summary td:first-child {
            width: 78px;
        }

        .english-summary td:last-child,
        .english-summary th:last-child {
            width: 42px;
            text-align: center;
        }

        .pv-bottom-legend {
            width: 96%;
            margin: 6px auto 0;
            font-size: 8px;
            font-weight: bold;
            text-align: left;
        }

        /*
         * Le tableau Excel existe dans le DOM pour DataTables,
         * mais il reste hors écran. Ne pas utiliser display:none,
         * car certaines versions de DataTables peuvent alors ignorer
         * des colonnes pendant l'export.
         */
        #pv-excel-container {
            position: absolute;
            left: -99999px;
            top: -99999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            #pv-print-zone,
            #pv-print-zone * {
                visibility: visible;
            }

            #pv-print-zone {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .pv-toolbar,
            .dt-buttons,
            .dataTables_filter,
            .dataTables_info,
            .dataTables_paginate,
            .dataTables_length {
                display: none !important;
            }

            .pv-scroll {
                overflow: visible !important;
            }

            #pv-table {
                width: 100% !important;
                font-size: {{ $printFontSize }}px;
            }

            #pv-table th,
            #pv-table td {
                padding: 1.5px !important;
                line-height: 1.05;
                border: 0.5px solid #000 !important;
            }

            #pv-table td.nom {
                min-width: auto;
                white-space: normal;
            }

            #pv-table th.matiere {
                min-width: auto;
                white-space: normal;
            }
        }
    </style>

    <style id="dynamic-page-style" media="print">
        @page {
            size: {{ $defaultPageSize }} {{ $defaultOrientation }};
            margin: 6mm;
        }
    </style>

    {{-- ============================================================
         BARRE D'OUTILS
         ============================================================ --}}
    <div class="pv-toolbar no-print" id="pv-toolbar">
        <button type="button"
                class="btn btn-success"
                onclick="printPV('{{ $defaultPageSize }}','{{ $defaultOrientation }}')">
            PDF automatique
        </button>

        <button type="button"
                class="btn btn-danger"
                onclick="printPV('A4','portrait')">
            PDF A4 Portrait
        </button>

        <button type="button"
                class="btn btn-danger"
                onclick="printPV('A4','landscape')">
            PDF A4 Paysage
        </button>

        <button type="button"
                class="btn btn-primary"
                onclick="printPV('A3','portrait')">
            PDF A3 Portrait
        </button>

        <button type="button"
                class="btn btn-primary"
                onclick="printPV('A3','landscape')">
            PDF A3 Paysage
        </button>
    </div>

    {{-- ============================================================
         PV ECRAN / PDF
         ============================================================ --}}
    <div id="pv-print-zone" class="{{ in_array($pvListe, array('admis', 'non_admis', 'merite')) ? 'pv-list-only' : '' }}">

        <?php $pv_title_mode = 'semestre'; ?>
        @include('eleves.partials.pv_tutelle_header')
        @if(false)
        <div class="pv-title">
            <h2>CENTRE DE FORMATION HINTEL</h2>

            <h3>
                RESULTATS DU SEMESTRE {{ $semestre }}
                — {{ strtoupper(isset($pv_type) ? $pv_type : '') }}
            </h3>

            <strong>
                ANNEE ACADEMIQUE : {{ $annee }}
            </strong>
        </div>

        <div class="pv-meta">
            <strong>CYCLE :</strong>
            {{ isset($cycle) && isset($cycle->nom)
                ? $cycle->nom
                : strtoupper(isset($pv_type) ? $pv_type : '')
            }}

            &nbsp;&nbsp; | &nbsp;&nbsp;

            <strong>NIVEAU :</strong>
            {{ $nniveau->nom }}

            &nbsp;&nbsp; | &nbsp;&nbsp;

            <strong>SPECIALITE :</strong>
            {{ $classe->nom }}

            @if(!empty($date_debut) || !empty($date_fin))
                &nbsp;&nbsp; | &nbsp;&nbsp;
                <strong>PERIODE :</strong>
                {{ !empty($date_debut) ? $date_debut : '...' }}
                au
                {{ !empty($date_fin) ? $date_fin : '...' }}
            @endif
        </div>

        @endif
        @if(!in_array($pvListe, array('admis', 'non_admis', 'merite')) && !$pvIsSoa && !$pvIsEnglish)
        <table class="pv-module-summary">
            <tbody>
            @foreach($moduleGroups as $module)
                <tr>
                    <th colspan="3">
                        {{ $module['code'] }}
                        @if(!empty($module['code']) && (!empty($module['nom']) || !empty($module['nom_anglais'])))
                            -
                        @endif
                        {{ $pvIsEnglish && !empty($module['nom_anglais']) ? $module['nom_anglais'] : $module['nom'] }}
                    </th>
                    <th>{{ number_format((float)$module['credit'], 0) }}</th>
                </tr>
                @foreach($module['matieres'] as $moduleMatiere)
                    <tr>
                        <td>{{ !empty($moduleMatiere->code_matiere) ? $moduleMatiere->code_matiere : ('MAT '.$moduleMatiere->id_matiere) }}</td>
                        <td colspan="2">{{ $pvIsEnglish && !empty($moduleMatiere->nom_anglais) ? $moduleMatiere->nom_anglais : $moduleMatiere->nom }}</td>
                        <td>{{ number_format((float)$moduleMatiere->credit, 0) }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
        @endif

        @if(in_array($pvListe, array('admis', 'non_admis', 'merite')))
            <table class="pv-list-summary">
                <thead>
                <tr>
                    <th>N°</th>
                    <th>NOMS ET PRENOMS</th>
                    <th>RANG</th>
                    <th>MOYENNE /20</th>
                    <th>CREDITS VALIDES</th>
                    <th>CREDITS PREVUS</th>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $index => $r)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>
                        <td>{{ isset($r['rang']) && $r['rang'] !== null ? $r['rang'] : '' }}</td>
                        <td>
                            <?php
                                $listeMoyenne = isset($r['moyenne20']) ? $r['moyenne20'] : null;
                                if ($listeMoyenne !== null && (float) $listeMoyenne > 20 && (float) $listeMoyenne <= 100) {
                                    $listeMoyenne = round(((float) $listeMoyenne) / 5, 2);
                                }
                            ?>
                            {{ $listeMoyenne !== null ? number_format((float) $listeMoyenne, 2) : '' }}
                        </td>
                        <td>{{ number_format((float) (isset($r['credit_valides']) ? $r['credit_valides'] : 0), 0) }}</td>
                        <td>{{ number_format((float) (isset($r['credit_prevus']) ? $r['credit_prevus'] : $pvTotalCredits), 0) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <div class="pv-scroll">

            @if($pvIsSoa)
            <table id="pv-table"
                   class="table table-bordered table-condensed">

                <thead>
                <tr>
                    <th class="fixed-head" rowspan="4">N°</th>
                    <th class="fixed-head" rowspan="4">MAT</th>
                    <th class="fixed-head name-head" rowspan="4">NOMS</th>
                    <th class="fixed-head" rowspan="4">UE</th>

                    @foreach($moduleGroups as $module)
                        <th class="module" colspan="{{ $module['colspan'] }}">
                            {{ $module['code'] }}
                        </th>
                    @endforeach

                    <th class="fixed-head" rowspan="4">TOTAL CREDIT /{{ number_format((float)$pvTotalCredits, 0) }}</th>
                    <th class="fixed-head" rowspan="4">MOYENNE GENERALE /20</th>
                    <th class="fixed-head" rowspan="4">DECISION</th>
                </tr>

                <tr>
                    @foreach($moduleGroups as $module)
                        @foreach($module['matieres'] as $moduleMatiere)
                            <th class="code" colspan="2">
                                {{ $matiereCodeFor($moduleMatiere) }}
                            </th>
                        @endforeach
                        <th rowspan="3">Note UE/20</th>
                        <th rowspan="3">Crédit</th>
                    @endforeach
                </tr>

                <tr>
                    @foreach($moduleGroups as $module)
                        @foreach($module['matieres'] as $moduleMatiere)
                            <th class="credit-row" colspan="2">
                                {{ number_format((float)$moduleMatiere->credit, 0) }}
                            </th>
                        @endforeach
                    @endforeach
                </tr>

                <tr>
                    @foreach($moduleGroups as $module)
                        @foreach($module['matieres'] as $moduleMatiere)
                            <th>/20</th>
                            <th>Note UV/20</th>
                        @endforeach
                    @endforeach
                </tr>
                </thead>

                <tbody>
                <?php $i = 1; ?>
                @foreach($results as $r)
                    <tr>
                        <td rowspan="2">{{ $i++ }}</td>
                        <td rowspan="2">{{ $r['eleve']->matricule }}</td>
                        <td class="nom" rowspan="2">{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>
                        <td><strong>CC</strong></td>

                        @foreach($moduleGroups as $module)
                            <?php
                                $moduleSomme = 0;
                                $moduleCreditsMoy = 0;
                                $moduleCreditsValides = 0;
                            ?>
                            @foreach($module['matieres'] as $m)
                                <?php
                                    $matiereKey = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere;
                                    $mm = isset($r['matieres'][$matiereKey]) ? $r['matieres'][$matiereKey] : (isset($r['matieres'][$m->id_matiere]) ? $r['matieres'][$m->id_matiere] : null);
                                    $noteUv20 = $mm && isset($mm['note20']) ? $mm['note20'] : null;
                                    $creditMatiere = (float) $m->credit;
                                    if ($noteUv20 !== null) {
                                        $moduleSomme += ((float) $noteUv20) * $creditMatiere;
                                        $moduleCreditsMoy += $creditMatiere;
                                    }
                                    if ($mm && !empty($mm['valide'])) {
                                        $moduleCreditsValides += $creditMatiere;
                                    }
                                ?>
                                <td>{{ $mm && isset($mm['cc20']) && $mm['cc20'] !== null ? number_format($mm['cc20'], 2) : '' }}</td>
                                <td rowspan="2" class="{{ $noteUv20 !== null && $noteUv20 < 10 ? 'echec' : ($noteUv20 !== null ? 'valide' : 'vide') }}">
                                    {{ $noteUv20 !== null ? number_format($noteUv20, 2) : '' }}
                                </td>
                            @endforeach
                            <?php $moduleNote = $moduleCreditsMoy > 0 ? round($moduleSomme / $moduleCreditsMoy, 2) : null; ?>
                            <td rowspan="2" class="{{ $moduleNote !== null && $moduleNote < 10 ? 'echec' : ($moduleNote !== null ? 'valide' : 'vide') }}">
                                {{ $moduleNote !== null ? number_format($moduleNote, 2) : '' }}
                            </td>
                            <td rowspan="2">{{ number_format($moduleCreditsValides, 0) }}</td>
                        @endforeach

                        <td rowspan="2">{{ number_format($r['credit_valides'], 0) }}</td>
                        <td rowspan="2">{{ $r['moyenne20'] !== null ? number_format($r['moyenne20'], 2) : '' }}</td>
                        <td rowspan="2"><strong>{{ in_array(strtoupper($r['decision']), array('ADMIS', 'VALIDE', 'V')) ? 'V' : 'R' }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>EXAM</strong></td>
                        @foreach($moduleGroups as $module)
                            @foreach($module['matieres'] as $m)
                                <?php
                                    $matiereKey = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere;
                                    $mm = isset($r['matieres'][$matiereKey]) ? $r['matieres'][$matiereKey] : (isset($r['matieres'][$m->id_matiere]) ? $r['matieres'][$m->id_matiere] : null);
                                ?>
                                <td>{{ $mm && isset($mm['sn20']) && $mm['sn20'] !== null ? number_format($mm['sn20'], 2) : '' }}</td>
                            @endforeach
                        @endforeach
                    </tr>
                @endforeach
                </tbody>

            </table>

            <div class="soa-summary-grid">
                @foreach($moduleSummaryChunks as $summaryChunk)
                    <div class="soa-summary-col">
                        <table class="pv-module-summary soa-summary">
                            <tbody>
                            @foreach($summaryChunk as $module)
                                <tr>
                                    <th colspan="3">
                                        {{ $module['code'] }}
                                        @if(!empty($module['code']) && !empty($module['nom']))
                                            -
                                        @endif
                                        {{ $module['nom'] }}
                                    </th>
                                    <th>{{ number_format((float)$module['credit'], 0) }}</th>
                                </tr>
                                @foreach($module['matieres'] as $moduleMatiere)
                                    <tr>
                                        <td>{{ !empty($moduleMatiere->code_matiere) ? $moduleMatiere->code_matiere : $moduleMatiere->id_code }}</td>
                                        <td colspan="2">{{ $moduleMatiere->nom }}</td>
                                        <td>{{ number_format((float)$moduleMatiere->credit, 0) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
            @else

            <table id="pv-table"
                   class="table table-bordered table-condensed">

                <thead>
                <tr>
                    <th class="fixed-head" rowspan="5">N</th>
                    <th class="fixed-head" rowspan="5">MAT</th>
                    <th class="fixed-head name-head" rowspan="5">{{ $pvIsEnglish ? 'NAMES' : 'NOMS' }}</th>

                    @foreach($moduleGroups as $module)
                        <th class="module" colspan="{{ $module['colspan'] }}">
                            {{ $module['code'] }}
                            @if(!$pvIsEnglish && !empty($module['code']) && (!empty($module['nom']) || !empty($module['nom_anglais'])))
                                -
                            @endif
                            @if(!$pvIsEnglish)
                                {{ $module['nom'] }}
                            @endif
                        </th>
                    @endforeach

                    @if($pvTypeLower == 'hnd')
                        <th class="fixed-head rotate" rowspan="5"><span>AVERAGE /100</span></th>
                    @endif

                    <th class="fixed-head rotate" rowspan="5"><span>{{ $pvIsEnglish ? 'AVERAGE /20' : 'MOYENNE /20' }}</span></th>
                    <th class="fixed-head rotate" rowspan="5"><span>{{ $pvIsEnglish ? 'TOTAL CREDITS' : 'TOTAL CREDIT' }}</span></th>
                    <th class="fixed-head rotate" rowspan="5"><span>{{ $pvIsEnglish ? 'RANK' : 'RANG' }}</span></th>
                    <th class="fixed-head rotate" rowspan="5"><span>DECISION</span></th>
                </tr>

                <tr>
                    @foreach($matieres as $m)
                        <?php $colspan = !empty($m->has_tpe) ? 5 : 4; ?>
                        <th class="code" colspan="{{ $colspan }}">
                            <span>{{ !empty($m->code_matiere) ? $m->code_matiere : ('MAT '.$m->id_matiere) }}</span>
                        </th>
                    @endforeach
                </tr>

                <tr>
                    @foreach($matieres as $m)
                        <?php $colspan = !empty($m->has_tpe) ? 5 : 4; ?>
                        <th class="matiere" colspan="{{ $colspan }}">
                            <span>{{ $pvIsEnglish ? $matiereCodeFor($m) : $m->nom }}</span>
                        </th>
                    @endforeach
                </tr>

                <tr>
                    @foreach($matieres as $m)
                        <?php $colspan = !empty($m->has_tpe) ? 5 : 4; ?>
                        <th class="credit-row" colspan="{{ $colspan }}">
                            CREDIT : {{ number_format((float)$m->credit, 0) }}
                        </th>
                    @endforeach
                </tr>

                <tr>
                    @foreach($matieres as $m)

                        <th>
                            CC
                            <span class="small-pct">
                                /{{ number_format((float)$m->pct_cc, 0) }}
                            </span>
                        </th>

                        <th>
                            EXAM
                            <span class="small-pct">
                                /{{ number_format((float)$m->pct_sn, 0) }}
                            </span>
                        </th>

                        @if(!empty($m->has_tpe))
                            <th>
                                TPE
                                <span class="small-pct">
                                    /{{ number_format((float)$m->pct_tpe, 0) }}
                                </span>
                            </th>
                        @endif

                        <th>{{ $pvIsEnglish ? 'MARK /100' : 'NOTE /100' }}</th>
                        <th>CREDIT</th>

                    @endforeach
                </tr>

                </thead>

                <tbody>

                <?php $i = 1; ?>

                @foreach($results as $r)

                    <tr>
                        <td>{{ $i++ }}</td>

                        <td>
                            {{ $r['eleve']->matricule }}
                        </td>

                        <td class="nom">
                            {{ $r['eleve']->nom }}
                            {{ $r['eleve']->prenom }}
                        </td>

                        @foreach($matieres as $m)

                            <?php
                            $matiereKey = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere;
                            $mm = isset($r['matieres'][$matiereKey])
                                ? $r['matieres'][$matiereKey]
                                : (isset($r['matieres'][$m->id_matiere]) ? $r['matieres'][$m->id_matiere] : null);

                            $total = $mm
                                ? $mm['total100']
                                : null;
                            ?>

                            <td>
                                {{ $mm && $mm['cc'] !== null
                                    ? number_format($mm['cc'], 2)
                                    : ''
                                }}
                            </td>

                            <td>
                                {{ $mm && $mm['sn'] !== null
                                    ? number_format($mm['sn'], 2)
                                    : ''
                                }}
                            </td>

                            @if(!empty($m->has_tpe))
                                <td>
                                    {{ $mm && $mm['tpe'] !== null
                                        ? number_format($mm['tpe'], 2)
                                        : ''
                                    }}
                                </td>
                            @endif

                            <td class="
                                {{ $total !== null && $total < 50
                                    ? 'echec'
                                    : ($total !== null ? 'valide' : 'vide')
                                }}
                            ">
                                {{ $total !== null
                                    ? number_format($total, 2)
                                    : ''
                                }}
                            </td>

                            <td>
                                {{ $mm
                                    ? number_format($mm['credit_valide'], 0)
                                    : '0'
                                }}
                            </td>

                        @endforeach

                        @if(isset($pv_type) && strtolower($pv_type) == 'hnd')
                            <td>
                                {{ $r['moyenne100'] !== null
                                    ? number_format($r['moyenne100'], 2)
                                    : ''
                                }}
                            </td>
                        @endif

                        <td>
                            {{ $r['moyenne20'] !== null
                                ? number_format($r['moyenne20'], 2)
                                : ''
                            }}
                        </td>

                        <td>
                            {{ number_format($r['credit_valides'], 0) }}
                            /
                            {{ number_format($r['credit_prevus'], 0) }}
                        </td>

                        <td>
                            {{ $r['rang'] !== null
                                ? $r['rang']
                                : ''
                            }}
                        </td>

                        <td>
                            <strong>
                                {{ $r['decision'] }}
                            </strong>
                        </td>
                    </tr>

                @endforeach

                </tbody>

            </table>

            @if($pvIsEnglish)
                <div class="soa-summary-grid english-summary-grid">
                    @foreach($moduleSummaryChunks as $summaryChunk)
                        <div class="soa-summary-col">
                            <table class="pv-module-summary soa-summary english-summary">
                                <tbody>
                                @foreach($summaryChunk as $module)
                                    <tr>
                                        <th colspan="2">
                                            {{ $module['code'] }}
                                            @if(!empty($module['code']) && (!empty($module['nom_anglais']) || !empty($module['nom'])))
                                                -
                                            @endif
                                            {{ !empty($module['nom_anglais']) ? $module['nom_anglais'] : $module['nom'] }}
                                        </th>
                                        <th>{{ number_format((float)$module['credit'], 0) }}</th>
                                    </tr>
                                    @foreach($module['matieres'] as $moduleMatiere)
                                        <tr>
                                            <td>{{ $matiereCodeFor($moduleMatiere) }}</td>
                                            <td>{{ !empty($moduleMatiere->nom_anglais) ? $moduleMatiere->nom_anglais : $moduleMatiere->nom }}</td>
                                            <td>{{ number_format((float)$moduleMatiere->credit, 0) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
                <div class="pv-bottom-legend">
                    LEGEND: MARK(CA + EXAM{{ $pvHasTpe ? ' + TPE' : '' }}) according to configured percentages.&nbsp;&nbsp;
                    ELIMINATORY MARK: EXAM'S MARK &lt; 24.5/100.&nbsp;&nbsp;
                    V = VALIDATED&nbsp;&nbsp; R = RESIT&nbsp;&nbsp; F = FAILED
                </div>
            @endif

            @endif

        </div>

    </div>


    {{-- ============================================================
         TABLEAU TECHNIQUE POUR EXCEL
         ------------------------------------------------------------
         IMPORTANT :
         Une SEULE ligne de THEAD ici.
         DataTables sait parfaitement l'exporter.
         La ligne des matières sera ensuite ajoutée proprement
         dans le fichier XLSX avec customize().
         ============================================================ --}}
    <div id="pv-excel-container">

        <table id="pv-table-excel">

            <thead>
            <tr>
                <th>N°</th>
                <th>MATRICULE</th>
                <th>NOMS</th>

                @if($pvIsSoa)
                    <th>UE</th>
                    @foreach($moduleGroups as $module)
                        @foreach($module['matieres'] as $m)
                            <th>{{ $module['code'] }} - {{ $matiereCodeFor($m) }} CC /20</th>
                            <th>{{ $module['code'] }} - {{ $matiereCodeFor($m) }} EXAM /20</th>
                            <th>{{ $module['code'] }} - {{ $matiereCodeFor($m) }} NOTE UV /20</th>
                            <th>{{ $module['code'] }} - {{ $matiereCodeFor($m) }} CREDIT UV</th>
                        @endforeach
                        <th>{{ $module['code'] }} NOTE UE /20</th>
                        <th>{{ $module['code'] }} CREDIT UE</th>
                    @endforeach
                @else
                    @foreach($matieres as $m)

                        <th>
                            CC /{{ number_format((float)$m->pct_cc, 0) }}
                        </th>

                        <th>
                            EXAM /{{ number_format((float)$m->pct_sn, 0) }}
                        </th>

                        @if(!empty($m->has_tpe))
                            <th>
                                TPE /{{ number_format((float)$m->pct_tpe, 0) }}
                            </th>
                        @endif

                        <th>NOTE /100</th>
                        <th>CRÉDIT</th>

                    @endforeach
                @endif

                @if(isset($pv_type) && strtolower($pv_type) == 'hnd')
                    <th>MOY. /100</th>
                @endif

                <th>TOTAL CRÉDIT</th>
                <th>MOY. /20</th>
                @if(!$pvIsSoa)
                    <th>RANG</th>
                @endif
                <th>DÉCISION</th>
            </tr>
            </thead>

            <tbody>

            <?php $numeroExcel = 1; ?>

            @foreach($results as $r)

                <tr>
                    <td>{{ $numeroExcel++ }}</td>

                    <td>
                        {{ $r['eleve']->matricule }}
                    </td>

                    <td>
                        {{ $r['eleve']->nom }}
                        {{ $r['eleve']->prenom }}
                    </td>

                    @if($pvIsSoa)
                        <td>CC / EXAM</td>
                        @foreach($moduleGroups as $module)
                            <?php
                                $moduleSommeExcel = 0;
                                $moduleCreditsMoyExcel = 0;
                                $moduleCreditsValidesExcel = 0;
                            ?>
                            @foreach($module['matieres'] as $m)
                                <?php
                                $matiereKeyExcel = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere;
                                $mmExcel = isset($r['matieres'][$matiereKeyExcel])
                                    ? $r['matieres'][$matiereKeyExcel]
                                    : (isset($r['matieres'][$m->id_matiere]) ? $r['matieres'][$m->id_matiere] : null);
                                $noteUvExcel = $mmExcel && isset($mmExcel['note20']) ? $mmExcel['note20'] : null;
                                $creditMatiereExcel = (float) $m->credit;
                                if ($noteUvExcel !== null) {
                                    $moduleSommeExcel += ((float) $noteUvExcel) * $creditMatiereExcel;
                                    $moduleCreditsMoyExcel += $creditMatiereExcel;
                                }
                                if ($mmExcel && !empty($mmExcel['valide'])) {
                                    $moduleCreditsValidesExcel += $creditMatiereExcel;
                                }
                                ?>
                                <td>{{ $mmExcel && isset($mmExcel['cc20']) && $mmExcel['cc20'] !== null ? number_format($mmExcel['cc20'], 2, '.', '') : '' }}</td>
                                <td>{{ $mmExcel && isset($mmExcel['sn20']) && $mmExcel['sn20'] !== null ? number_format($mmExcel['sn20'], 2, '.', '') : '' }}</td>
                                <td>{{ $noteUvExcel !== null ? number_format($noteUvExcel, 2, '.', '') : '' }}</td>
                                <td>{{ $mmExcel ? number_format($mmExcel['credit_valide'], 0, '.', '') : '0' }}</td>
                            @endforeach
                            <?php $moduleNoteExcel = $moduleCreditsMoyExcel > 0 ? round($moduleSommeExcel / $moduleCreditsMoyExcel, 2) : null; ?>
                            <td>{{ $moduleNoteExcel !== null ? number_format($moduleNoteExcel, 2, '.', '') : '' }}</td>
                            <td>{{ number_format($moduleCreditsValidesExcel, 0, '.', '') }}</td>
                        @endforeach
                    @else
                        @foreach($matieres as $m)

                            <?php
                            $matiereKeyExcel = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere;
                            $mmExcel = isset($r['matieres'][$matiereKeyExcel])
                                ? $r['matieres'][$matiereKeyExcel]
                                : (isset($r['matieres'][$m->id_matiere]) ? $r['matieres'][$m->id_matiere] : null);

                            $totalExcel = $mmExcel
                                ? $mmExcel['total100']
                                : null;
                            ?>

                            <td>
                                @if($mmExcel && $mmExcel['cc'] !== null)
                                    {{ number_format($mmExcel['cc'], 2, '.', '') }}
                                @endif
                            </td>

                            <td>
                                @if($mmExcel && $mmExcel['sn'] !== null)
                                    {{ number_format($mmExcel['sn'], 2, '.', '') }}
                                @endif
                            </td>

                            @if(!empty($m->has_tpe))
                                <td>
                                    @if($mmExcel && $mmExcel['tpe'] !== null)
                                        {{ number_format($mmExcel['tpe'], 2, '.', '') }}
                                    @endif
                                </td>
                            @endif

                            <td>
                                @if($totalExcel !== null)
                                    {{ number_format($totalExcel, 2, '.', '') }}
                                @endif
                            </td>

                            <td>
                                {{ $mmExcel
                                    ? number_format($mmExcel['credit_valide'], 0, '.', '')
                                    : '0'
                                }}
                            </td>

                        @endforeach
                    @endif

                    @if(isset($pv_type) && strtolower($pv_type) == 'hnd')
                        <td>
                            @if($r['moyenne100'] !== null)
                                {{ number_format($r['moyenne100'], 2, '.', '') }}
                            @endif
                        </td>
                    @endif

                    <td>
                        {{ number_format($r['credit_valides'], 0) }}
                        /
                        {{ number_format($r['credit_prevus'], 0) }}
                    </td>

                    <td>
                        @if($r['moyenne20'] !== null)
                            {{ number_format($r['moyenne20'], 2, '.', '') }}
                        @endif
                    </td>

                    @if(!$pvIsSoa)
                        <td>
                            {{ $r['rang'] !== null
                                ? $r['rang']
                                : ''
                            }}
                        </td>
                    @endif

                    <td>
                        {{ $pvIsSoa ? (in_array(strtoupper($r['decision']), array('ADMIS', 'VALIDE', 'V')) ? 'V' : 'R') : $r['decision'] }}
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    </div>


    <script>

        /*
        |--------------------------------------------------------------------------
        | IMPRESSION PDF
        |--------------------------------------------------------------------------
        */
        function printPV(size, orientation) {

            var style =
                document.getElementById('dynamic-page-style');

            style.innerHTML =
                '@page {' +
                    'size: ' + size + ' ' + orientation + ';' +
                    'margin: 6mm;' +
                '}';

            setTimeout(function () {
                window.print();
            }, 150);
        }


        $(document).ready(function () {

            /*
            |--------------------------------------------------------------------------
            | INFORMATIONS DU PV
            |--------------------------------------------------------------------------
            */
            var etablissement =
                'CENTRE DE FORMATION HINTEL';

            var pvType =
                {!! json_encode(
                    strtoupper(isset($pv_type) ? $pv_type : '')
                ) !!};

            var semestre =
                {!! json_encode($semestre) !!};

            var classeNom =
                {!! json_encode(
                    isset($classe) && isset($classe->nom)
                        ? $classe->nom
                        : ''
                ) !!};

            var niveauNom =
                {!! json_encode(
                    isset($nniveau) && isset($nniveau->nom)
                        ? $nniveau->nom
                        : ''
                ) !!};

            var annee =
                {!! json_encode(
                    isset($annee)
                        ? $annee
                        : session('year')
                ) !!};

            var cycleNom =
                {!! json_encode(
                    isset($cycle) && isset($cycle->nom)
                        ? $cycle->nom
                        : (
                            isset($pv_type)
                                ? strtoupper($pv_type)
                                : ''
                        )
                ) !!};


            /*
            |--------------------------------------------------------------------------
            | MATIERES
            |--------------------------------------------------------------------------
            */
            var matieresExcel = [

                @foreach($matieres as $m)

                {
                    code:
                        {!! json_encode(!empty($m->code_matiere) ? $m->code_matiere : ('MAT '.$m->id_matiere)) !!},

                    nom:
                        {!! json_encode($pvIsEnglish && !empty($m->nom_anglais) ? $m->nom_anglais : $m->nom) !!},

                    moduleKey:
                        {!! json_encode(!empty($m->id_module) ? ('module_'.$m->id_module) : ('single_'.$m->id_matiere)) !!},

                    moduleCode:
                        {!! json_encode(!empty($m->code_module) ? $m->code_module : (!empty($m->code_matiere) ? $m->code_matiere : '')) !!},

                    moduleNom:
                        {!! json_encode($pvIsEnglish && !empty($m->module_nom_anglais) ? $m->module_nom_anglais : (!empty($m->module_nom) ? $m->module_nom : $m->nom)) !!},

                    hasTpe:
                        {{ !empty($m->has_tpe)
                            ? 'true'
                            : 'false'
                        }}
                },

                @endforeach
            ];


            /*
            |--------------------------------------------------------------------------
            | OUTILS
            |--------------------------------------------------------------------------
            */
            function nettoyerNomFichier(texte) {

                return String(texte || '')
                    .replace(/[\\\/:*?"<>|]/g, '_')
                    .replace(/\s+/g, '_');
            }


            function excelColumnName(numero) {

                var nom = '';

                while (numero > 0) {

                    var modulo =
                        (numero - 1) % 26;

                    nom =
                        String.fromCharCode(
                            65 + modulo
                        ) + nom;

                    numero =
                        Math.floor(
                            (numero - 1) / 26
                        );
                }

                return nom;
            }


            function xmlEscape(value) {

                return String(
                    value == null ? '' : value
                )
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&apos;');
            }


            /*
            |--------------------------------------------------------------------------
            | FICHIER
            |--------------------------------------------------------------------------
            */
            var nomFichier =
                'PV_' +
                nettoyerNomFichier(pvType) +
                '_Semestre_' +
                semestre +
                '_' +
                nettoyerNomFichier(classeNom) +
                '_' +
                nettoyerNomFichier(niveauNom);


            /*
            |--------------------------------------------------------------------------
            | DATATABLE EXCEL
            |--------------------------------------------------------------------------
            */
            if (
                $.fn.DataTable.isDataTable(
                    '#pv-table-excel'
                )
            ) {

                $('#pv-table-excel')
                    .DataTable()
                    .destroy();
            }


            var tableExcel =
                $('#pv-table-excel')
                .DataTable({

                    paging: false,

                    ordering: false,

                    searching: false,

                    info: false,

                    autoWidth: false,

                    dom: 'B',

                    buttons: [

                        {
                            extend:
                                'excelHtml5',

                            text:
                                '<i class="glyphicon glyphicon-download-alt"></i> Exporter Excel',

                            className:
                                'btn btn-success',

                            filename:
                                nomFichier,

                            /*
                             * IMPORTANT :
                             * Aucun title/messageTop DataTables.
                             * Nous construisons nous-mêmes toutes les lignes
                             * du haut afin qu'elles soient toujours présentes.
                             */
                            title: null,

                            messageTop: null,


                            exportOptions: {

                                stripHtml: true,

                                format: {

                                    body: function (
                                        data,
                                        row,
                                        column,
                                        node
                                    ) {

                                        return $(node)
                                            .text()
                                            .replace(/\s+/g, ' ')
                                            .trim();
                                    }
                                }
                            },


                            /*
                            |--------------------------------------------------------------------------
                            | CONSTRUCTION COMPLETE DES ENTETES EXCEL
                            |--------------------------------------------------------------------------
                            */
                            customize: function (xlsx) {

                                var sheet =
                                    xlsx.xl
                                    .worksheets[
                                        'sheet1.xml'
                                    ];

                                var $sheet =
                                    $(sheet);


                                /*
                                 * DataTables sans title produit :
                                 *
                                 * ligne 1 = CC / EXAM / ... (sous-entêtes)
                                 * ligne 2+ = étudiants
                                 *
                                 * Nous ajoutons 7 lignes :
                                 *
                                 * 1 : établissement
                                 * 2 : titre du PV
                                 * 3 : année académique
                                 * 4 : cycle
                                 * 5 : niveau
                                 * 6 : spécialité / classe
                                 * 7 : matières
                                 *
                                 * La ligne DataTables existante devient ligne 8.
                                 */

                                var decalage = 7;


                                /*
                                |--------------------------------------------------------------------------
                                | 1. DECALER TOUTES LES LIGNES EXISTANTES
                                |--------------------------------------------------------------------------
                                */
                                var rows =
                                    $sheet
                                    .find(
                                        'sheetData row'
                                    )
                                    .get()
                                    .reverse();


                                $.each(
                                    rows,
                                    function () {

                                        var $row =
                                            $(this);

                                        var ancienNumero =
                                            parseInt(
                                                $row.attr('r'),
                                                10
                                            );

                                        var nouveauNumero =
                                            ancienNumero +
                                            decalage;

                                        $row.attr(
                                            'r',
                                            nouveauNumero
                                        );


                                        $row
                                            .find('c')
                                            .each(
                                                function () {

                                                    var $cell =
                                                        $(this);

                                                    var ref =
                                                        $cell.attr(
                                                            'r'
                                                        );

                                                    if (!ref) {
                                                        return;
                                                    }

                                                    var colonne =
                                                        ref.replace(
                                                            /[0-9]/g,
                                                            ''
                                                        );

                                                    $cell.attr(
                                                        'r',
                                                        colonne +
                                                        nouveauNumero
                                                    );
                                                }
                                            );
                                    }
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | 2. NOMBRE TOTAL DE COLONNES
                                |--------------------------------------------------------------------------
                                */
                                var totalColonnes =
                                    3;

                                $.each(
                                    matieresExcel,
                                    function (
                                        index,
                                        matiere
                                    ) {

                                        totalColonnes +=
                                            matiere.hasTpe
                                                ? 5
                                                : 4;
                                    }
                                );


                                if (
                                    String(pvType)
                                    .toLowerCase()
                                    === 'hnd'
                                ) {

                                    totalColonnes++;
                                }


                                totalColonnes +=
                                    4;


                                var derniereColonne =
                                    excelColumnName(
                                        totalColonnes
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | 3. CELLULE TEXTE
                                |--------------------------------------------------------------------------
                                */
                                function cellule(
                                    colonne,
                                    ligne,
                                    texte
                                ) {

                                    return (
                                        '<c r="' +
                                        colonne +
                                        ligne +
                                        '" t="inlineStr">' +

                                        '<is>' +
                                        '<t>' +
                                        xmlEscape(texte) +
                                        '</t>' +
                                        '</is>' +

                                        '</c>'
                                    );
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | 4. LIGNES D'IDENTIFICATION
                                |--------------------------------------------------------------------------
                                */

                                var ligne1 =
                                    '<row r="1">' +
                                    cellule(
                                        'A',
                                        1,
                                        etablissement
                                    ) +
                                    '</row>';


                                var ligne2 =
                                    '<row r="2">' +
                                    cellule(
                                        'A',
                                        2,
                                        'PROCES-VERBAL DES RESULTATS - ' +
                                        (
                                            pvType
                                                ? pvType + ' - '
                                                : ''
                                        ) +
                                        'SEMESTRE ' +
                                        semestre
                                    ) +
                                    '</row>';


                                var ligne3 =
                                    '<row r="3">' +
                                    cellule(
                                        'A',
                                        3,
                                        'ANNEE ACADEMIQUE : ' +
                                        (
                                            annee ||
                                            '-'
                                        )
                                    ) +
                                    '</row>';


                                var ligne4 =
                                    '<row r="4">' +
                                    cellule(
                                        'A',
                                        4,
                                        'CYCLE : ' +
                                        (
                                            cycleNom ||
                                            pvType ||
                                            '-'
                                        )
                                    ) +
                                    '</row>';


                                var ligne5 =
                                    '<row r="5">' +
                                    cellule(
                                        'A',
                                        5,
                                        'NIVEAU : ' +
                                        (
                                            niveauNom ||
                                            '-'
                                        )
                                    ) +
                                    '</row>';


                                var ligne6 =
                                    '<row r="6">' +
                                    cellule(
                                        'A',
                                        6,
                                        'SPECIALITE / CLASSE : ' +
                                        (
                                            classeNom ||
                                            '-'
                                        )
                                    ) +
                                    '</row>';


                                /*
                                |--------------------------------------------------------------------------
                                | 5. LIGNE DES MATIERES
                                |--------------------------------------------------------------------------
                                */
                                var ligneMatiere =
                                    '<row r="7">';


                                /*
                                 * Colonnes fixes
                                 */
                                ligneMatiere +=
                                    cellule(
                                        'A',
                                        7,
                                        'N°'
                                    );

                                ligneMatiere +=
                                    cellule(
                                        'B',
                                        7,
                                        'MATRICULE'
                                    );

                                ligneMatiere +=
                                    cellule(
                                        'C',
                                        7,
                                        'NOMS'
                                    );


                                var colonneCourante =
                                    4;


                                var fusions =
                                    [];


                                /*
                                 * Les colonnes fixes couvrent lignes 7 et 8.
                                 */
                                fusions.push(
                                    'A7:A8'
                                );

                                fusions.push(
                                    'B7:B8'
                                );

                                fusions.push(
                                    'C7:C8'
                                );


                                /*
                                 * Matières
                                 */
                                $.each(
                                    matieresExcel,
                                    function (
                                        index,
                                        matiere
                                    ) {

                                        var nb =
                                            matiere.hasTpe
                                                ? 5
                                                : 4;


                                        var debutNumero =
                                            colonneCourante;

                                        var finNumero =
                                            colonneCourante +
                                            nb -
                                            1;


                                        var debut =
                                            excelColumnName(
                                                debutNumero
                                            );

                                        var fin =
                                            excelColumnName(
                                                finNumero
                                            );


                                        ligneMatiere +=
                                            cellule(
                                                debut,
                                                7,
                                                matiere.nom
                                            );


                                        /*
                                         * Ajouter cellules vides dans
                                         * le groupe fusionné.
                                         */
                                        for (
                                            var k = 1;
                                            k < nb;
                                            k++
                                        ) {

                                            ligneMatiere +=
                                                cellule(
                                                    excelColumnName(
                                                        debutNumero +
                                                        k
                                                    ),
                                                    7,
                                                    ''
                                                );
                                        }


                                        fusions.push(
                                            debut +
                                            '7:' +
                                            fin +
                                            '7'
                                        );


                                        colonneCourante +=
                                            nb;
                                    }
                                );


                                /*
                                 * Moyenne /100 HND
                                 */
                                if (
                                    String(pvType)
                                    .toLowerCase()
                                    === 'hnd'
                                ) {

                                    var cHnd =
                                        excelColumnName(
                                            colonneCourante
                                        );

                                    ligneMatiere +=
                                        cellule(
                                            cHnd,
                                            7,
                                            'MOY. /100'
                                        );

                                    fusions.push(
                                        cHnd +
                                        '7:' +
                                        cHnd +
                                        '8'
                                    );

                                    colonneCourante++;
                                }


                                /*
                                 * Colonnes finales
                                 */
                                var colonnesFinales =
                                    [
                                        'MOY. /20',
                                        'TOTAL CREDIT',
                                        'RANG',
                                        'DECISION'
                                    ];


                                $.each(
                                    colonnesFinales,
                                    function (
                                        index,
                                        titre
                                    ) {

                                        var col =
                                            excelColumnName(
                                                colonneCourante
                                            );


                                        ligneMatiere +=
                                            cellule(
                                                col,
                                                7,
                                                titre
                                            );


                                        fusions.push(
                                            col +
                                            '7:' +
                                            col +
                                            '8'
                                        );


                                        colonneCourante++;
                                    }
                                );


                                ligneMatiere +=
                                    '</row>';


                                /*
                                |--------------------------------------------------------------------------
                                | 6. INSERER LES 7 LIGNES EN HAUT
                                |--------------------------------------------------------------------------
                                */
                                var sheetData =
                                    $sheet.find(
                                        'sheetData'
                                    );


                                sheetData.prepend(
                                    ligne1 +
                                    ligne2 +
                                    ligne3 +
                                    ligne4 +
                                    ligne5 +
                                    ligne6 +
                                    ligneMatiere
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | 7. NETTOYER LA LIGNE 8
                                |--------------------------------------------------------------------------
                                |
                                | La ligne 8 est l'entête DataTables :
                                | N°, MATRICULE, NOMS, CC, EXAM...
                                |
                                | Nous supprimons N°, matricule, noms et
                                | les titres finaux qui sont déjà fusionnés
                                | verticalement avec la ligne 7.
                                */
                                var ligne8 =
                                    $sheet.find(
                                        'sheetData row[r="8"]'
                                    );


                                ligne8.find(
                                    'c[r="A8"],' +
                                    'c[r="B8"],' +
                                    'c[r="C8"]'
                                ).remove();


                                /*
                                 * Colonnes finales à supprimer ligne 8
                                 */
                                var premiereFinale =
                                    totalColonnes -
                                    4 +
                                    1;


                                if (
                                    String(pvType)
                                    .toLowerCase()
                                    === 'hnd'
                                ) {

                                    premiereFinale--;
                                }


                                for (
                                    var c =
                                        premiereFinale;
                                    c <=
                                        totalColonnes;
                                    c++
                                ) {

                                    var lettre =
                                        excelColumnName(c);

                                    ligne8.find(
                                        'c[r="' +
                                        lettre +
                                        '8"]'
                                    ).remove();
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | 8. FUSIONS
                                |--------------------------------------------------------------------------
                                */
                                var mergeCells =
                                    $sheet.find(
                                        'mergeCells'
                                    );


                                if (
                                    !mergeCells.length
                                ) {

                                    sheetData.after(
                                        '<mergeCells count="0"></mergeCells>'
                                    );

                                    mergeCells =
                                        $sheet.find(
                                            'mergeCells'
                                        );
                                }


                                /*
                                 * Fusionner les 6 lignes d'identification
                                 * sur toute la largeur du tableau.
                                 */
                                fusions.unshift(
                                    'A6:' +
                                    derniereColonne +
                                    '6'
                                );

                                fusions.unshift(
                                    'A5:' +
                                    derniereColonne +
                                    '5'
                                );

                                fusions.unshift(
                                    'A4:' +
                                    derniereColonne +
                                    '4'
                                );

                                fusions.unshift(
                                    'A3:' +
                                    derniereColonne +
                                    '3'
                                );

                                fusions.unshift(
                                    'A2:' +
                                    derniereColonne +
                                    '2'
                                );

                                fusions.unshift(
                                    'A1:' +
                                    derniereColonne +
                                    '1'
                                );


                                /*
                                 * Eviter doublons.
                                 */
                                var existantes =
                                    {};


                                mergeCells
                                    .find(
                                        'mergeCell'
                                    )
                                    .each(
                                        function () {

                                            existantes[
                                                $(this)
                                                .attr('ref')
                                            ] = true;
                                        }
                                    );


                                $.each(
                                    fusions,
                                    function (
                                        index,
                                        ref
                                    ) {

                                        if (
                                            !existantes[
                                                ref
                                            ]
                                        ) {

                                            mergeCells.append(
                                                '<mergeCell ref="' +
                                                ref +
                                                '"/>'
                                            );

                                            existantes[
                                                ref
                                            ] = true;
                                        }
                                    }
                                );


                                mergeCells.attr(
                                    'count',
                                    mergeCells
                                    .find(
                                        'mergeCell'
                                    )
                                    .length
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | 9. DIMENSION
                                |--------------------------------------------------------------------------
                                */
                                var derniereLigne =
                                    0;


                                $sheet
                                    .find(
                                        'sheetData row'
                                    )
                                    .each(
                                        function () {

                                            var r =
                                                parseInt(
                                                    $(this)
                                                    .attr('r'),
                                                    10
                                                );

                                            if (
                                                r >
                                                derniereLigne
                                            ) {

                                                derniereLigne =
                                                    r;
                                            }
                                        }
                                    );


                                $sheet
                                    .find(
                                        'dimension'
                                    )
                                    .attr(
                                        'ref',
                                        'A1:' +
                                        derniereColonne +
                                        derniereLigne
                                    );


                                /*
                                |--------------------------------------------------------------------------
                                | 10. AUTOFILTER
                                |--------------------------------------------------------------------------
                                */
                                var autoFilter =
                                    $sheet.find(
                                        'autoFilter'
                                    );


                                if (
                                    autoFilter.length
                                ) {

                                    autoFilter.attr(
                                        'ref',
                                        'A8:' +
                                        derniereColonne +
                                        derniereLigne
                                    );
                                }
                            }
                        }
                    ]
                });


            /*
             * Déplacer le bouton Excel dans la toolbar visible.
             */
            tableExcel
                .buttons()
                .container()
                .appendTo(
                    '#pv-toolbar'
                );
        });

    </script>

@endsection


@section('breadcrumb')

    <li>
        <strong>
            <a href="{{ url('eleves') }}">
                @lang('menu.gestion_notes')
            </a>
        </strong>
    </li>

    <li>
        <strong>
            <a href="{{ url('eleves/notes') }}">
                Notes
            </a>
        </strong>
    </li>

    <li>
        <a href="{{ url('eleves/notes/show') }}">
            <strong>
                @lang('admin.choix_classe')
            </strong>
        </a>
    </li>

    <li>
        <a href="{{ url('eleves/notes/matieres/show')
            .'?id='.$classe->id
            .'&cycle='.$id_cycle
            .'&nniveau='.$nniveau->id
        }}">
            <strong>
                @lang('admin.titre_matiere')
            </strong>
        </a>
    </li>

    <li class="active">
        <strong>
            {{ $titre }}
        </strong>
    </li>

@endsection
