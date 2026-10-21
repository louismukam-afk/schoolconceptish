@extends('skeleton')

@section('sub-content')
    <?php
        $pvTypeLower = strtolower(isset($pv_type) ? $pv_type : '');
        $pvIsSoa = ($pvTypeLower == 'licence_soa');
        $pvIsEnglish = in_array($pvTypeLower, array('hnd', 'licence_bamenda'));
        $moduleGroups = array();
        $pvListe = isset($_GET['liste']) ? strtolower(trim($_GET['liste'])) : '';
        if (in_array($pvListe, array('admis', 'non_admis', 'merite')) && isset($results)) {
            $results = array_values(array_filter($results, function ($result) use ($pvListe) {
                $decision = strtoupper(trim(isset($result['decision']) ? $result['decision'] : ''));
                $isAdmis = in_array($decision, array('ADMIS', 'ADMISE', 'VALIDE', 'V'));
                if ($pvListe == 'admis') return $isAdmis;
                if ($pvListe == 'non_admis') return !$isAdmis;
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
        }
        $pvListeTitles = array('admis' => ' - LISTE DES ADMIS', 'non_admis' => ' - LISTE DES NON ADMIS', 'merite' => ' - LISTE DE MERITE');
        $pvListeTitle = isset($pvListeTitles[$pvListe]) ? $pvListeTitles[$pvListe] : '';

        foreach ($matieres as $m) {
            $moduleKey = !empty($m->id_module) ? (int) $m->id_module : ('single_'.$m->id_matiere);
            if (!isset($moduleGroups[$moduleKey])) {
                $moduleGroups[$moduleKey] = array(
                    'code' => !empty($m->code_module) ? $m->code_module : (!empty($m->code_matiere) ? $m->code_matiere : ''),
                    'nom' => !empty($m->module_nom) ? $m->module_nom : $m->nom,
                    'nom_anglais' => !empty($m->module_nom_anglais) ? $m->module_nom_anglais : $m->nom_anglais,
                    'credit' => 0,
                    'matieres' => array()
                );
            }
            $moduleGroups[$moduleKey]['credit'] += (float) $m->credit;
            $moduleGroups[$moduleKey]['matieres'][] = $m;
        }

        $pvTotalCredits = 0;
        foreach ($matieres as $m) {
            $pvTotalCredits += (float) $m->credit;
        }

        $matiereCodeFor = function ($matiere) {
            if (!empty($matiere->code_matiere)) return $matiere->code_matiere;
            if (!empty($matiere->id_code)) return $matiere->id_code;
            if (!empty($matiere->code)) return $matiere->code;
            return 'MAT '.$matiere->id_matiere;
        };

        if ($pvIsSoa) {
            $nbColonnes = 4 + count($matieres) + (count($moduleGroups) * 2) + 4;
        } else {
            $nbColonnes = 3;
            foreach ($matieres as $m) {
                if (!empty($m->has_s1)) $nbColonnes += 3;
                if (!empty($m->has_s2)) $nbColonnes += 3;
            }
            $nbColonnes += 8;
        }

        $defaultPageSize = ($nbColonnes > 24) ? 'A3' : 'A4';
        $defaultOrientation = 'landscape';
        $printFontSize = ($nbColonnes > 24) ? 5.2 : 7;

        $annualNoteFor = function ($row, $matiere) {
            $key = isset($matiere->bilan_key) ? $matiere->bilan_key : $matiere->id_matiere;
            $s1 = isset($row['matieres'][$key]['s1']) ? $row['matieres'][$key]['s1'] : null;
            $s2 = isset($row['matieres'][$key]['s2']) ? $row['matieres'][$key]['s2'] : null;
            $notes = array();
            if ($s1 && isset($s1['note20'])) $notes[] = (float) $s1['note20'];
            if ($s2 && isset($s2['note20'])) $notes[] = (float) $s2['note20'];
            if (count($notes) == 0) return null;
            return array_sum($notes) / count($notes);
        };

        $moduleNoteFor = function ($row, $module) use ($annualNoteFor) {
            $sum = 0;
            $credits = 0;
            foreach ($module['matieres'] as $matiere) {
                $note = $annualNoteFor($row, $matiere);
                if ($note === null) continue;
                $credit = (float) $matiere->credit;
                $sum += $note * $credit;
                $credits += $credit;
            }
            return $credits > 0 ? ($sum / $credits) : null;
        };
    ?>

    <style>
        .pv-toolbar { margin: 10px 0 15px; }
        .pv-title { text-align: center; margin-bottom: 10px; }
        .pv-title h2 { margin: 0; font-size: 20px; font-weight: bold; }
        .pv-title h3 { margin: 4px 0; font-size: 16px; font-weight: bold; }
        .pv-tutelle-header { text-align: center; margin-bottom: 10px; }
        .pv-tutelle-top { display: table; width: 100%; font-size: 12px; font-weight: bold; }
        .pv-tutelle-left, .pv-tutelle-center, .pv-tutelle-right { display: table-cell; width: 33.33%; vertical-align: top; padding: 2px 6px; }
        .pv-tutelle-left { text-align: left; }
        .pv-tutelle-right { text-align: right; }
        .pv-tutelle-line { border-top: 2px solid #b30000; margin: 8px auto 6px; width: 78%; }
        .pv-tutelle-header h3 { margin: 4px 0; font-size: 16px; font-weight: bold; }
        .pv-meta { font-size: 12px; margin-bottom: 8px; text-align: center; }
        .pv-scroll { overflow-x: auto; width: 100%; }
        #bilan-table, #bilan-table-excel { width: 100%; border-collapse: collapse; font-size: 9px; }
        #bilan-table th, #bilan-table td, #bilan-table-excel th, #bilan-table-excel td { border: 1px solid #222 !important; padding: 3px 4px !important; text-align: center; vertical-align: middle !important; }
        #bilan-table thead th { background: #bfbfbf; font-weight: bold; line-height: 1.1; }
        #bilan-table th.module { background: #8f8f8f; text-transform: uppercase; }
        #bilan-table th.final { background: #a9a9a9; }
        #bilan-table td.nom { text-align: left; min-width: 170px; font-weight: bold; }
        #bilan-table td.line-type { width: 40px; font-weight: bold; }
        #bilan-table td.empty-line { height: 20px; }
        .valide { background: #d8efd8; }
        .echec { background: #f5d1d1; }
        .soa-modules { width: 100%; margin-top: 14px; border-collapse: collapse; font-size: 8px; }
        .soa-modules th, .soa-modules td { border: 1px solid #222 !important; padding: 2px 4px !important; }
        .soa-modules th { background: #8f8f8f; text-align: left; }
        .soa-modules .code { width: 80px; font-weight: bold; }
        .soa-modules .credit { width: 50px; text-align: center; }
        .english-modules-grid { display: table; width: 100%; table-layout: fixed; margin-top: 14px; border-spacing: 12px 0; }
        .english-modules-col { display: table-cell; width: 50%; vertical-align: top; }
        .english-modules { margin-top: 0; table-layout: fixed; }
        .english-modules .code { width: 78px; }
        .english-modules .credit { width: 42px; }
        .legend { margin-top: 10px; font-size: 8px; font-weight: bold; }
        .pv-list-summary { width: 96%; margin: 14px auto; border-collapse: collapse; font-size: 12px; }
        .pv-list-summary th, .pv-list-summary td { border: 1px solid #222; padding: 6px 8px; text-align: center; }
        .pv-list-summary th:nth-child(2), .pv-list-summary td:nth-child(2) { text-align: left; }
        #bilan-print-zone.pv-list-only .pv-scroll { display: none; }
        #bilan-print-zone.pv-list-only .soa-modules,
        #bilan-print-zone.pv-list-only .english-modules-grid,
        #bilan-print-zone.pv-list-only .legend { display: none; }
        #bilan-excel-container { position: absolute; left: -99999px; top: -99999px; width: 1px; height: 1px; overflow: hidden; }
        @media print {
            body * { visibility: hidden; }
            #bilan-print-zone, #bilan-print-zone * { visibility: visible; }
            #bilan-print-zone { position: absolute; left: 0; top: 0; width: 100%; }
            .pv-toolbar, .dt-buttons, .dataTables_filter, .dataTables_info, .dataTables_paginate, .dataTables_length { display: none !important; }
            .pv-scroll { overflow: visible !important; }
            #bilan-table { font-size: {{ $printFontSize }}px; }
            #bilan-table th, #bilan-table td { padding: 1.5px !important; line-height: 1.05; }
            .soa-modules { font-size: 6px; }
        }
    </style>

    <style id="dynamic-page-style" media="print">
        @page { size: {{ $defaultPageSize }} {{ $defaultOrientation }}; margin: 6mm; }
    </style>

    <div class="pv-toolbar no-print" id="bilan-toolbar">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/notes/matieres/show').'?id='.$classe->id.'&cycle='.$id_cycle.'&nniveau='.$nniveau->id }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Page PV
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
        <button type="button" class="btn btn-success" onclick="printBilan('{{ $defaultPageSize }}','{{ $defaultOrientation }}')">PDF automatique</button>
        <button type="button" class="btn btn-danger" onclick="printBilan('A4','landscape')">PDF A4 Paysage</button>
        <button type="button" class="btn btn-primary" onclick="printBilan('A3','landscape')">PDF A3 Paysage</button>
    </div>

    <div id="bilan-print-zone" class="{{ in_array($pvListe, array('admis', 'non_admis', 'merite')) ? 'pv-list-only' : '' }}">
        <?php $pv_title_mode = 'annuel'; ?>
        @include('eleves.partials.pv_tutelle_header')

        @if(in_array($pvListe, array('admis', 'non_admis', 'merite')))
            <table class="pv-list-summary">
                <thead>
                <tr>
                    <th>N°</th>
                    <th>NOMS ET PRENOMS</th>
                    <th>MOYENNE ANNUELLE /20</th>
                    <th>CREDITS VALIDES</th>
                    <th>CREDITS PREVUS</th>
                </tr>
                </thead>
                <tbody>
                @foreach($results as $index => $r)
                    <?php
                        $listeMoyenneAnnuelle = isset($r['moyenne_annuelle']) ? $r['moyenne_annuelle'] : null;
                        if ($listeMoyenneAnnuelle !== null && (float) $listeMoyenneAnnuelle > 20 && (float) $listeMoyenneAnnuelle <= 100) {
                            $listeMoyenneAnnuelle = round(((float) $listeMoyenneAnnuelle) / 5, 2);
                        }
                    ?>
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>
                        <td>{{ $listeMoyenneAnnuelle !== null ? number_format((float) $listeMoyenneAnnuelle, 2) : '' }}</td>
                        <td>{{ number_format((float) (isset($r['credits_valides']) ? $r['credits_valides'] : 0), 0) }}</td>
                        <td>{{ number_format((float) (isset($r['credit_prevus']) ? $r['credit_prevus'] : $pvTotalCredits), 0) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif

        <div class="pv-scroll">
            @if($pvIsSoa)
                <table id="bilan-table" class="table table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th rowspan="4">N</th>
                        <th rowspan="4">MAT</th>
                        <th rowspan="4">NOMS</th>
                        <th>UE</th>
                        @foreach($moduleGroups as $module)
                            <th class="module" colspan="{{ count($module['matieres']) + 2 }}">{{ $module['code'] }}</th>
                        @endforeach
                        <th class="final" rowspan="4">MOYENNE</th>
                        <th class="final" rowspan="4">TOTAL CREDIT /{{ number_format($pvTotalCredits, 0) }}</th>
                        <th class="final" rowspan="4">DECISION</th>
                        <th class="final" rowspan="4">RANG</th>
                    </tr>
                    <tr>
                        <th>UV</th>
                        @foreach($moduleGroups as $module)
                            @foreach($module['matieres'] as $m)
                                <th>{{ !empty($m->code_matiere) ? $m->code_matiere : (!empty($m->code) ? $m->code : '') }}</th>
                            @endforeach
                            <th>Note UE/20</th>
                            <th>Credit</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th>CREDIT</th>
                        @foreach($moduleGroups as $module)
                            @foreach($module['matieres'] as $m)
                                <th>{{ number_format((float) $m->credit, 0) }}</th>
                            @endforeach
                            <th></th>
                            <th>{{ number_format((float) $module['credit'], 0) }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        <th></th>
                        @foreach($moduleGroups as $module)
                            @foreach($module['matieres'] as $m)
                                <th>/20</th>
                            @endforeach
                            <th>/20</th>
                            <th></th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; ?>
                    @foreach($results as $r)
                        <tr>
                            <td rowspan="2">{{ $i++ }}</td>
                            <td rowspan="2">{{ $r['eleve']->matricule }}</td>
                            <td rowspan="2" class="nom">{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>
                            <td class="line-type">CC</td>
                            @foreach($moduleGroups as $module)
                                @foreach($module['matieres'] as $m)
                                    <?php $note = $annualNoteFor($r, $m); ?>
                                    <td class="{{ $note !== null && $note >= 10 ? 'valide' : 'echec' }}">{{ $note !== null ? number_format($note, 2) : '0.00' }}</td>
                                @endforeach
                                <?php $moduleNote = $moduleNoteFor($r, $module); ?>
                                <td class="{{ $moduleNote !== null && $moduleNote >= 10 ? 'valide' : 'echec' }}">{{ $moduleNote !== null ? number_format($moduleNote, 2) : '0.00' }}</td>
                                <td>{{ $moduleNote !== null && $moduleNote >= 10 ? number_format((float) $module['credit'], 0) : '0' }}</td>
                            @endforeach
                            <td rowspan="2">{{ $r['moyenne_annuelle'] !== null ? number_format($r['moyenne_annuelle'], 2) : '' }}</td>
                            <td rowspan="2">{{ number_format($r['credits_valides'], 0) }}</td>
                            <td rowspan="2"><strong>{{ $r['decision'] }}</strong></td>
                            <td rowspan="2">{{ $r['rang'] !== null ? $r['rang'] : '' }}</td>
                        </tr>
                        <tr>
                            <td class="line-type">EXAM</td>
                            @foreach($moduleGroups as $module)
                                @foreach($module['matieres'] as $m)
                                    <td class="empty-line"></td>
                                @endforeach
                                <td class="empty-line"></td>
                                <td class="empty-line"></td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <table id="bilan-table" class="table table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th rowspan="2">N</th>
                        <th rowspan="2">MATRICULE</th>
                        <th rowspan="2">NOMS</th>
                        @foreach($matieres as $m)
                            <?php $matiereLabel = $pvIsEnglish ? $matiereCodeFor($m) : $m->nom; ?>
                            @if(!empty($m->has_s1))<th colspan="3">{{ $matiereLabel }} S1<br><small>Cr {{ $m->credit }}</small></th>@endif
                            @if(!empty($m->has_s2))<th colspan="3">{{ $matiereLabel }} S2<br><small>Cr {{ $m->credit }}</small></th>@endif
                        @endforeach
                        <th rowspan="2">MOY S1</th>
                        <th rowspan="2">MOY S2</th>
                        <th rowspan="2">MOY ANNUELLE</th>
                        <th rowspan="2">CR S1</th>
                        <th rowspan="2">CR S2</th>
                        <th rowspan="2">CR VALIDES</th>
                        <th rowspan="2">DECISION</th>
                        <th rowspan="2">RANG</th>
                    </tr>
                    <tr>
                        @foreach($matieres as $m)
                            @if(!empty($m->has_s1))<th>Note /20</th><th>Session</th><th>Cr</th>@endif
                            @if(!empty($m->has_s2))<th>Note /20</th><th>Session</th><th>Cr</th>@endif
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; ?>
                    @foreach($results as $r)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $r['eleve']->matricule }}</td>
                            <td class="nom">{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>
                            @foreach($matieres as $m)
                                @foreach(array(1, 2) as $semestre)
                                    @if(($semestre == 1 && !empty($m->has_s1)) || ($semestre == 2 && !empty($m->has_s2)))
                                        <?php $key = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere; $mm = isset($r['matieres'][$key]['s'.$semestre]) ? $r['matieres'][$key]['s'.$semestre] : null; ?>
                                        <td class="{{ $mm && $mm['valide'] ? 'valide' : 'echec' }}">{{ $mm ? number_format($mm['note20'], 2) : '0.00' }}</td>
                                        <td>{{ $mm ? $mm['session_validation'] : 'Non validee' }}</td>
                                        <td>{{ $mm && $mm['valide'] ? number_format($mm['credit'], 0) : '0' }}</td>
                                    @endif
                                @endforeach
                            @endforeach
                            <td>{{ number_format($r['moyenne_s1'] !== null ? $r['moyenne_s1'] : 0, 2) }}</td>
                            <td>{{ number_format($r['moyenne_s2'] !== null ? $r['moyenne_s2'] : 0, 2) }}</td>
                            <td>{{ $r['moyenne_annuelle'] !== null ? number_format($r['moyenne_annuelle'], 2) : '' }}</td>
                            <td>{{ number_format($r['credits_s1'], 0) }}</td>
                            <td>{{ number_format($r['credits_s2'], 0) }}</td>
                            <td>{{ number_format($r['credits_valides'], 0) }}/{{ number_format($r['credits_prevus'], 0) }}</td>
                            <td><strong>{{ $r['decision'] }}</strong></td>
                            <td>{{ $r['rang'] !== null ? $r['rang'] : '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        @if(!in_array($pvListe, array('admis', 'non_admis', 'merite')) && ($pvIsSoa || $pvIsEnglish))
            <?php
                $chunkCount = $pvIsEnglish ? 2 : 3;
                $chunks = array_chunk($moduleGroups, max(1, (int) ceil(count($moduleGroups) / $chunkCount)), true);
                $moduleColumns = array();
                foreach ($chunks as $chunk) {
                    $rows = array();
                    foreach ($chunk as $module) {
                        $moduleName = $pvIsEnglish && !empty($module['nom_anglais']) ? $module['nom_anglais'] : $module['nom'];
                        $rows[] = array('type' => 'module', 'code' => $module['code'], 'nom' => $moduleName, 'credit' => $module['credit']);
                        foreach ($module['matieres'] as $m) {
                            $rows[] = array(
                                'type' => 'matiere',
                                'code' => $matiereCodeFor($m),
                                'nom' => $pvIsEnglish && !empty($m->nom_anglais) ? $m->nom_anglais : $m->nom,
                                'credit' => $m->credit
                            );
                        }
                    }
                    $moduleColumns[] = $rows;
                }
            ?>

            @if($pvIsEnglish)
                <div class="english-modules-grid">
                    @foreach($moduleColumns as $column)
                        <div class="english-modules-col">
                            <table class="soa-modules english-modules">
                                <tbody>
                                @foreach($column as $item)
                                    <tr>
                                        @if($item['type'] == 'module')
                                            <th class="code">{{ $item['code'] }}</th>
                                            <th>{{ $item['nom'] }}</th>
                                            <th class="credit">{{ number_format((float) $item['credit'], 0) }}</th>
                                        @else
                                            <td class="code">{{ $item['code'] }}</td>
                                            <td>{{ $item['nom'] }}</td>
                                            <td class="credit">{{ number_format((float) $item['credit'], 0) }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @else
            <table class="soa-modules">
                <tbody>
                <?php
                    $maxRows = 0;
                    foreach ($moduleColumns as $column) {
                        $maxRows = max($maxRows, count($column));
                    }
                ?>
                @for($row = 0; $row < $maxRows; $row++)
                    <tr>
                        @foreach($moduleColumns as $column)
                            <?php $item = isset($column[$row]) ? $column[$row] : null; ?>
                            @if($item && $item['type'] == 'module')
                                <th class="code">{{ $item['code'] }}</th>
                                <th>{{ $item['nom'] }}</th>
                                <th class="credit">{{ number_format((float) $item['credit'], 0) }}</th>
                            @elseif($item)
                                <td class="code">{{ $item['code'] }}</td>
                                <td>{{ $item['nom'] }}</td>
                                <td class="credit">{{ number_format((float) $item['credit'], 0) }}</td>
                            @else
                                <td></td><td></td><td></td>
                            @endif
                        @endforeach
                    </tr>
                @endfor
                </tbody>
            </table>
            @endif

            <div class="legend">
                @if($pvIsEnglish)
                    LEGEND: ELIMINATORY MARK: EXAM'S MARK &lt; 24.5/100&nbsp;&nbsp;&nbsp;
                    CREDIT TOTAL = {{ number_format($pvTotalCredits, 0) }}&nbsp;&nbsp;&nbsp;
                    V = VALIDATED&nbsp;&nbsp;&nbsp; R = RESIT&nbsp;&nbsp;&nbsp; F = FAILED
                @else
                    NOTE DE L'EXAMEN INFERIEURE A 7/20 = NOTE ELIMINATOIRE&nbsp;&nbsp;&nbsp;
                    NOMBRE TOTAL DE CREDITS = {{ number_format($pvTotalCredits, 0) }}&nbsp;&nbsp;&nbsp;
                    V=VALIDE&nbsp;&nbsp;&nbsp;R=RATTRAPAGE&nbsp;&nbsp;&nbsp;AJ=AJOURNE
                @endif
            </div>
        @endif
    </div>

    <div id="bilan-excel-container">
        <table id="bilan-table-excel">
            <thead><tr>
                <th>N</th><th>MATRICULE</th><th>NOMS</th>
                @if($pvIsSoa)
                    @foreach($moduleGroups as $module)
                        @foreach($module['matieres'] as $m)
                            <th>{{ !empty($m->code_matiere) ? $m->code_matiere : (!empty($m->code) ? $m->code : '') }} /20</th>
                        @endforeach
                        <th>{{ $module['code'] }} Note UE/20</th><th>{{ $module['code'] }} Credit</th>
                    @endforeach
                    <th>MOYENNE</th><th>TOTAL CREDIT</th><th>DECISION</th><th>RANG</th>
                @else
                    @foreach($matieres as $m)
                        <?php $matiereLabel = $pvIsEnglish ? $matiereCodeFor($m) : $m->nom; ?>
                        @if(!empty($m->has_s1))<th>{{ $matiereLabel }} S1 Note /20</th><th>{{ $matiereLabel }} S1 Session</th><th>{{ $matiereLabel }} S1 Credit</th>@endif
                        @if(!empty($m->has_s2))<th>{{ $matiereLabel }} S2 Note /20</th><th>{{ $matiereLabel }} S2 Session</th><th>{{ $matiereLabel }} S2 Credit</th>@endif
                    @endforeach
                    <th>MOY S1</th><th>MOY S2</th><th>MOY ANNUELLE</th><th>CR S1</th><th>CR S2</th><th>CR VALIDES</th><th>CR PREVUS</th><th>DECISION</th><th>RANG</th><th>MATIERES NON VALIDEES</th>
                @endif
            </tr></thead>
            <tbody>
            <?php $j = 1; ?>
            @foreach($results as $r)
                <tr>
                    <td>{{ $j++ }}</td><td>{{ $r['eleve']->matricule }}</td><td>{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>
                    @if($pvIsSoa)
                        @foreach($moduleGroups as $module)
                            @foreach($module['matieres'] as $m)
                                <?php $note = $annualNoteFor($r, $m); ?>
                                <td>{{ $note !== null ? number_format($note, 2, '.', '') : '0.00' }}</td>
                            @endforeach
                            <?php $moduleNote = $moduleNoteFor($r, $module); ?>
                            <td>{{ $moduleNote !== null ? number_format($moduleNote, 2, '.', '') : '0.00' }}</td>
                            <td>{{ $moduleNote !== null && $moduleNote >= 10 ? number_format((float) $module['credit'], 0, '.', '') : '0' }}</td>
                        @endforeach
                        <td>{{ $r['moyenne_annuelle'] !== null ? number_format($r['moyenne_annuelle'], 2, '.', '') : '' }}</td>
                        <td>{{ number_format($r['credits_valides'], 0, '.', '') }}</td>
                        <td>{{ $r['decision'] }}</td>
                        <td>{{ $r['rang'] !== null ? $r['rang'] : '' }}</td>
                    @else
                        @foreach($matieres as $m)
                            @foreach(array(1, 2) as $semestre)
                                @if(($semestre == 1 && !empty($m->has_s1)) || ($semestre == 2 && !empty($m->has_s2)))
                                    <?php $key = isset($m->bilan_key) ? $m->bilan_key : $m->id_matiere; $mm = isset($r['matieres'][$key]['s'.$semestre]) ? $r['matieres'][$key]['s'.$semestre] : null; ?>
                                    <td>{{ $mm ? number_format($mm['note20'], 2, '.', '') : '0.00' }}</td>
                                    <td>{{ $mm ? $mm['session_validation'] : 'Non validee' }}</td>
                                    <td>{{ $mm && $mm['valide'] ? number_format($mm['credit'], 0, '.', '') : '0' }}</td>
                                @endif
                            @endforeach
                        @endforeach
                        <td>{{ number_format($r['moyenne_s1'] !== null ? $r['moyenne_s1'] : 0, 2, '.', '') }}</td>
                        <td>{{ number_format($r['moyenne_s2'] !== null ? $r['moyenne_s2'] : 0, 2, '.', '') }}</td>
                        <td>{{ $r['moyenne_annuelle'] !== null ? number_format($r['moyenne_annuelle'], 2, '.', '') : '' }}</td>
                        <td>{{ number_format($r['credits_s1'], 0, '.', '') }}</td>
                        <td>{{ number_format($r['credits_s2'], 0, '.', '') }}</td>
                        <td>{{ number_format($r['credits_valides'], 0, '.', '') }}</td>
                        <td>{{ number_format($r['credits_prevus'], 0, '.', '') }}</td>
                        <td>{{ $r['decision'] }}</td>
                        <td>{{ $r['rang'] !== null ? $r['rang'] : '' }}</td>
                        <td>{{ implode(', ', $r['non_valides']) }}</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <script>
        function printBilan(size, orientation) {
            document.getElementById('dynamic-page-style').innerHTML = '@page { size: ' + size + ' ' + orientation + '; margin: 6mm; }';
            setTimeout(function () { window.print(); }, 150);
        }

        $(document).ready(function () {
            $('#bilan-table-excel').DataTable({
                dom: 'Bfrtip',
                pageLength: 5000,
                paging: false,
                searching: false,
                ordering: false,
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fa fa-file-excel-o"></i> Excel', titleAttr: 'Export Excel', title: 'Bilan annuel {{ strtoupper($pv_type) }} {{ $classe->nom }} {{ $annee }}' },
                    { extend: 'pdfHtml5', text: '<i class="fa fa-file-pdf-o"></i> PDF', titleAttr: 'PDF', title: 'Bilan annuel {{ strtoupper($pv_type) }} {{ $classe->nom }} {{ $annee }}', orientation: 'landscape', pageSize: '{{ $defaultPageSize }}' }
                ]
            }).buttons().container().appendTo('#bilan-toolbar');
        });
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{ url('eleves/notes') }}">Notes</a></strong></li>
    <li><a href="{{ url('eleves/notes/matieres/show').'?id='.$classe->id.'&cycle='.$id_cycle.'&nniveau='.$nniveau->id }}"><strong>@lang('admin.titre_matiere')</strong></a></li>
    <li class="active"><strong>{{ $titre }}</strong></li>
@endsection
