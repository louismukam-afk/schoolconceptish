@extends('skeleton')

@section('sub-content')
    <?php
        $pageSize = (isset($page_size) && strtolower($page_size) == 'a3') ? 'A3' : 'A4';
        $pageOrientation = ($pageSize == 'A3') ? 'landscape' : 'portrait';
        $pvTypeLower = strtolower(isset($pv_type) ? $pv_type : '');
        $pvIsEnglish = in_array($pvTypeLower, array('hnd', 'licence_bamenda'));
        $globalMode = !empty($global_mode);
        $onlyDoneMode = !empty($only_done);
        $baseUrl = url('/eleves/notes/pv/rattrapages/'.$classe->id)
            .'?semestre='.$semestre
            .'&cycle='.$cycle->id
            .'&nniveau='.$nniveau->id
            .'&pv_type='.$pv_type
            .'&page_size='.strtolower($pageSize);
        if (!empty($date_debut)) $baseUrl .= '&date_debut='.$date_debut;
        if (!empty($date_fin)) $baseUrl .= '&date_fin='.$date_fin;
        $matiereCodeFor = function ($matiere) {
            if (!empty($matiere->code_matiere)) return $matiere->code_matiere;
            if (!empty($matiere->id_code)) return $matiere->id_code;
            if (!empty($matiere->code)) return $matiere->code;
            return 'MAT '.$matiere->id_matiere;
        };
    ?>

    <style>
        @page { size: {{ $pageSize }} {{ $pageOrientation }}; margin: 10mm; }
        .rattrapage-toolbar { margin: 10px 0 15px; }
        .rattrapage-toolbar .btn { margin-right: 5px; margin-bottom: 5px; }
        .pv-global-zone { margin-top: 12px; }
        .pv-global-title { text-align: center; font-weight: bold; margin: 10px 0; }
        .student-block { page-break-after: always; page-break-inside: avoid; margin-bottom: 18px; }
        .student-block:last-child { page-break-after: auto; }
        .student-header { text-align: center; margin-bottom: 12px; }
        .student-header h3 { margin: 5px 0; font-size: 18px; font-weight: bold; }
        .pv-tutelle-header { text-align: center; margin-bottom: 10px; }
        .pv-tutelle-top { display: table; width: 100%; font-size: 12px; font-weight: bold; }
        .pv-tutelle-left, .pv-tutelle-center, .pv-tutelle-right { display: table-cell; width: 33.33%; vertical-align: top; padding: 2px 6px; }
        .pv-tutelle-left { text-align: left; }
        .pv-tutelle-right { text-align: right; }
        .pv-tutelle-line { border-top: 2px solid #b30000; margin: 8px auto 6px; width: 78%; }
        .pv-tutelle-header h3 { margin: 4px 0; font-size: 16px; font-weight: bold; }
        .student-meta { font-size: 12px; line-height: 1.45; }
        .student-title { font-weight: bold; margin: 12px 0 6px; font-size: 14px; }
        .rattrapage-table { width: 100%; border-collapse: collapse; }
        .rattrapage-table th, .rattrapage-table td { border: 1px solid #222; padding: 4px; font-size: 10px; }
        .rattrapage-table th { text-align: center; background: #f3f3f3; }
        .global-rattrapage-table th, .global-rattrapage-table td { font-size: 8px; padding: 2px 3px; }
        .global-rattrapage-table .name-col { min-width: 155px; text-align: left; font-weight: bold; }
        .global-rattrapage-table .note-col { width: 36px; }
        .rattrapage-table .code { width: 75px; font-weight: bold; }
        .rattrapage-table .matiere { text-align: left; }
        .matiere-legend { width: 100%; margin-top: 12px; border-collapse: collapse; table-layout: fixed; }
        .matiere-legend th, .matiere-legend td { border: 1px solid #222; padding: 3px 5px; font-size: 8px; }
        .matiere-legend th { background: #d9d9d9; text-align: center; }
        .matiere-legend .legend-code { width: 80px; font-weight: bold; text-align: center; }
        .matiere-legend .legend-credit { width: 45px; text-align: center; }
        .decision-ok { background: #d8efd8; font-weight: bold; }
        .decision-ko { background: #f5d1d1; font-weight: bold; }
        .summary-cell { text-align: left; line-height: 1.35; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        @media print {
            .rattrapage-toolbar, .breadcrumb, .navbar, .sidebar { display: none !important; }
            .student-block { min-height: auto; page-break-after: auto; }
        }
    </style>

    <div class="rattrapage-toolbar">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/notes/matieres/show').'?id='.$classe->id.'&cycle='.$cycle->id.'&nniveau='.$nniveau->id }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Page PV
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
        <button type="button" class="btn btn-warning" onclick="window.print()">
            <span class="glyphicon glyphicon-print"></span> PDF / Imprimer {{ $pageSize }}
        </button>
        @if($globalMode)
            <a class="btn btn-default" href="{{ $baseUrl }}{{ $onlyDoneMode ? '&only_done=1' : '' }}">
                <span class="glyphicon glyphicon-th-list"></span> Format detaille
            </a>
        @else
            <a class="btn btn-primary" href="{{ $baseUrl }}&global=1{{ $onlyDoneMode ? '&only_done=1' : '' }}">
                <span class="glyphicon glyphicon-list-alt"></span> PV global rattrapages
            </a>
        @endif
        @if($onlyDoneMode)
            <a class="btn btn-default" href="{{ $baseUrl }}{{ $globalMode ? '&global=1' : '' }}">
                <span class="glyphicon glyphicon-filter"></span> Tous les concernes
            </a>
        @else
            <a class="btn btn-info" href="{{ $baseUrl }}{{ $globalMode ? '&global=1' : '' }}&only_done=1">
                <span class="glyphicon glyphicon-ok"></span> Uniquement rattrapages faits
            </a>
        @endif
    </div>

    <?php $pv_title_mode = 'rattrapage'; ?>
    @include('eleves.partials.pv_tutelle_header')

    @if(count($results) == 0)
        <div class="alert alert-success">
            {{ $pvIsEnglish ? 'No resit result found for this selection.' : 'Aucun resultat de rattrapage trouve pour cette selection.' }}
        </div>
    @endif

    @if($globalMode)
        <div class="pv-global-zone">
            <div class="pv-global-title">
                {{ $pvIsEnglish ? 'GLOBAL RESIT RESULTS' : 'PV GLOBAL DES RATTRAPAGES' }}
                - {{ $pvIsEnglish ? 'Semester' : 'Semestre' }} {{ $semestre }}
                @if($onlyDoneMode)
                    ({{ $pvIsEnglish ? 'only students with resit marks' : 'uniquement les etudiants ayant compose' }})
                @endif
            </div>

            <table class="rattrapage-table global-rattrapage-table table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th rowspan="2">#</th>
                        <th rowspan="2">{{ $pvIsEnglish ? 'Matricule' : 'Matricule' }}</th>
                        <th rowspan="2">{{ $pvIsEnglish ? 'Names' : 'Noms' }}</th>
                        @foreach($matieres as $m)
                            <th colspan="2">{{ $matiereCodeFor($m) }}</th>
                        @endforeach
                        <th rowspan="2">{{ $pvIsEnglish ? 'Credits' : 'Credits' }}</th>
                        <th rowspan="2">{{ $pvIsEnglish ? 'Average /20' : 'Moyenne /20' }}</th>
                        <th rowspan="2">{{ $pvIsEnglish ? 'Decision' : 'Decision' }}</th>
                    </tr>
                    <tr>
                        @foreach($matieres as $m)
                            <th class="note-col">CC</th>
                            <th class="note-col">{{ $pvIsEnglish ? 'Resit' : 'RAT' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    @foreach($results as $item)
                        <?php
                            $ligneMap = array();
                            foreach ($item['non_valides'] as $ligne) {
                                $ligneMap[(int) $ligne['matiere']->id_matiere] = $ligne;
                            }
                            $totalCredits = 0;
                            $sum = 0;
                            $count = 0;
                            $allValidated = true;
                        ?>
                        <tr>
                            <td class="text-right">{{ $i++ }}</td>
                            <td>{{ $item['eleve']->matricule }}</td>
                            <td class="name-col">{{ $item['eleve']->nom }} {{ $item['eleve']->prenom }}</td>
                            @foreach($matieres as $m)
                                <?php $ligne = isset($ligneMap[(int) $m->id_matiere]) ? $ligneMap[(int) $m->id_matiere] : null; ?>
                                @if($ligne)
                                    <?php
                                        $totalCredits += $ligne['credit'];
                                        $sum += $ligne['note20'];
                                        $count++;
                                        if (empty($ligne['valide'])) $allValidated = false;
                                    ?>
                                    <td class="text-right">{{ $ligne['cc'] !== null ? number_format($ligne['cc'], 2) : '' }}</td>
                                    <td class="text-right {{ !empty($ligne['valide']) ? 'decision-ok' : 'decision-ko' }}">
                                        {{ isset($ligne['rat']) && $ligne['rat'] !== null ? number_format($ligne['rat'], 2) : '' }}
                                    </td>
                                @else
                                    <td></td>
                                    <td></td>
                                @endif
                            @endforeach
                            <td class="text-right">{{ number_format($totalCredits, 0) }}</td>
                            <td class="text-right">{{ $count > 0 ? number_format($sum / $count, 2) : '' }}</td>
                            <td class="text-center {{ $count == 0 ? '' : ($allValidated ? 'decision-ok' : 'decision-ko') }}">
                                @if($count == 0)
                                    -
                                @else
                                    {{ $allValidated ? ($pvIsEnglish ? 'VALIDATED' : 'VALIDE') : ($pvIsEnglish ? 'RESIT' : 'RATTRAPAGE') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="matiere-legend">
                <thead>
                    <tr>
                        <th>{{ $pvIsEnglish ? 'Code' : 'Code' }}</th>
                        <th>{{ $pvIsEnglish ? 'Course name' : 'Nom de la matiere' }}</th>
                        <th>{{ $pvIsEnglish ? 'Credit' : 'Credit' }}</th>
                        <th>{{ $pvIsEnglish ? 'Code' : 'Code' }}</th>
                        <th>{{ $pvIsEnglish ? 'Course name' : 'Nom de la matiere' }}</th>
                        <th>{{ $pvIsEnglish ? 'Credit' : 'Credit' }}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $legendMatieres = is_array($matieres) ? $matieres : $matieres->all();
                        $legendChunks = array_chunk($legendMatieres, max(1, (int) ceil(count($legendMatieres) / 2)));
                        $legendMaxRows = 0;
                        foreach ($legendChunks as $legendChunk) {
                            $legendMaxRows = max($legendMaxRows, count($legendChunk));
                        }
                    ?>
                    @for($legendRow = 0; $legendRow < $legendMaxRows; $legendRow++)
                        <tr>
                            @foreach($legendChunks as $legendChunk)
                                <?php $legendMatiere = isset($legendChunk[$legendRow]) ? $legendChunk[$legendRow] : null; ?>
                                @if($legendMatiere)
                                    <td class="legend-code">{{ $matiereCodeFor($legendMatiere) }}</td>
                                    <td>{{ ($pvIsEnglish && !empty($legendMatiere->nom_anglais)) ? $legendMatiere->nom_anglais : $legendMatiere->nom }}</td>
                                    <td class="legend-credit">{{ number_format((float) $legendMatiere->credit, 0) }}</td>
                                @else
                                    <td></td><td></td><td></td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    @else
    @foreach($results as $item)
        <div class="student-block">

            <div class="student-title">
                {{ $pvIsEnglish ? 'Student' : 'Etudiant' }} :
                {{ $item['eleve']->matricule }} - {{ $item['eleve']->nom }} {{ $item['eleve']->prenom }}
            </div>

            <table class="rattrapage-table table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ $pvIsEnglish ? 'Code' : 'Code' }}</th>
                        <th>{{ $pvIsEnglish ? 'Course' : 'Matiere' }}</th>
                        <th>{{ $pvIsEnglish ? 'Credit' : 'Credit' }}</th>
                        <th>CC</th>
                        <th>{{ $pvIsEnglish ? 'Normal Exam' : 'Examen normal' }}</th>
                        <th>{{ $pvIsEnglish ? 'Resit' : 'Rattrapage' }}</th>
                        <th>TPE</th>
                        <th>{{ $pvIsEnglish ? 'Normal /20' : 'Normale /20' }}</th>
                        <th>{{ $pvIsEnglish ? 'Final /20' : 'Finale /20' }}</th>
                        <th>{{ $pvIsEnglish ? 'Decision' : 'Decision' }}</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; $totalCredits = 0; ?>
                    @foreach($item['non_valides'] as $ligne)
                        <?php $totalCredits += $ligne['credit']; ?>
                        <tr>
                            <td class="text-right">{{ $i++ }}</td>
                            <td class="code">{{ $matiereCodeFor($ligne['matiere']) }}</td>
                            <td class="matiere">{{ ($pvIsEnglish && !empty($ligne['matiere']->nom_anglais)) ? $ligne['matiere']->nom_anglais : $ligne['matiere']->nom }}</td>
                            <td class="text-right">{{ number_format($ligne['credit'], 0) }}</td>
                            <td class="text-right">{{ $ligne['cc'] !== null ? number_format($ligne['cc'], 2) : '' }}</td>
                            <td class="text-right">{{ $ligne['sn'] !== null ? number_format($ligne['sn'], 2) : '' }}</td>
                            <td class="text-right">{{ isset($ligne['rat']) && $ligne['rat'] !== null ? number_format($ligne['rat'], 2) : '' }}</td>
                            <td class="text-right">{{ $ligne['tpe'] !== null ? number_format($ligne['tpe'], 2) : '' }}</td>
                            <td class="text-right">{{ isset($ligne['normal20']) ? number_format($ligne['normal20'], 2) : number_format($ligne['note20'], 2) }}</td>
                            <td class="text-right">{{ number_format($ligne['note20'], 2) }}</td>
                            <td class="text-center {{ !empty($ligne['valide']) ? 'decision-ok' : 'decision-ko' }}">
                                @if(!empty($ligne['valide']))
                                    {{ $pvIsEnglish ? 'V' : 'VALIDE' }}
                                @else
                                    {{ $pvIsEnglish ? 'R' : 'RATTRAPAGE' }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th colspan="3" class="text-right">{{ $pvIsEnglish ? 'Course total' : 'Total matieres' }}</th>
                        <th class="text-right">{{ number_format($totalCredits, 0) }}</th>
                        <th colspan="6"></th>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach
    @endif
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{ url('eleves/notes') }}">Notes</a></strong></li>
    <li class="active"><strong>Matieres a rattraper</strong></li>
@endsection
