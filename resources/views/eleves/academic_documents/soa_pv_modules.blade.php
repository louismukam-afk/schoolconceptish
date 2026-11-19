<?php
    $soaPvSubjects = array();
    $soaPvSemesters = array();
    foreach ($modules as $module) foreach ($module['subjects'] as $subject) $soaPvSubjects[] = $subject;
    foreach ($modules as $module) $soaPvSemesters[(int) $module['semestre']] = true;
    $soaPvSemester = count($soaPvSemesters) === 1 ? key($soaPvSemesters) : '';
    $soaPvLegendColumns = array_chunk(array_values($modules), max(1, (int) ceil(count($modules) / 3)));
    $soaPvLegendRowsData = array();
    $soaPvLegendRows = 0;
    foreach ($soaPvLegendColumns as $column) $soaPvLegendRows = max($soaPvLegendRows, count($column));
    for ($rowIndex = 0; $rowIndex < $soaPvLegendRows; $rowIndex++) {
        $legendRow = array();
        for ($columnIndex = 0; $columnIndex < 3; $columnIndex++) {
            $legendGroup = isset($soaPvLegendColumns[$columnIndex][$rowIndex]) ? $soaPvLegendColumns[$columnIndex][$rowIndex] : null;
            if ($legendGroup) {
                $legendGroup['legend_credits'] = 0;
                foreach ($legendGroup['subjects'] as $legendSubject) $legendGroup['legend_credits'] += (float) $legendSubject['credit'];
            }
            $legendRow[] = $legendGroup;
        }
        $soaPvLegendRowsData[] = $legendRow;
    }
    $soaPvScoreColumns = count($soaPvSubjects);
    foreach ($modules as $module) if ($module['is_grouped']) $soaPvScoreColumns += 2;
    $soaPvColumnWidths = $soaPvScoreColumns ? array_fill(0, $soaPvScoreColumns, 54 / $soaPvScoreColumns) : array();
    $soaPvRows = array();
    foreach ($results as $result) {
        $studentModules = array();
        foreach ($modules as $moduleKey => $module) {
            $resultModule = isset($result['modules'][$moduleKey]) ? $result['modules'][$moduleKey] : null;
            $studentSubjects = array();
            if ($resultModule) foreach ($resultModule['subjects'] as $resultSubject) $studentSubjects[$resultSubject['programme_id'] . ':' . $resultSubject['semestre']] = $resultSubject;
            $moduleSubjects = array();
            foreach ($module['subjects'] as $subject) $moduleSubjects[$subject['key']] = isset($studentSubjects[$subject['key']]) ? $studentSubjects[$subject['key']] : null;
            $earnedCredits = 0;
            $hasMarks = false;
            if ($resultModule) foreach ($resultModule['subjects'] as $resultSubject) {
                $mark = $resultSubject['result']['final'];
                if ($mark !== null) {
                    $hasMarks = true;
                    if ($resultSubject['result']['complete'] && $mark >= $context['profile']['credit_threshold']) $earnedCredits += (float) $resultSubject['credit'];
                }
            }
            $studentModules[$moduleKey] = array('average' => $resultModule ? $resultModule['average'] : null, 'complete' => $resultModule ? $resultModule['complete'] : false, 'has_marks' => $hasMarks, 'credits' => $earnedCredits, 'subjects' => $moduleSubjects);
        }
        $soaPvRows[] = array('student' => $result['student'], 'modules' => $studentModules, 'summary' => $result['summary']);
    }
?>
<div class="soa-pv-modules">
    <h3>L&Eacute;GENDE DES MATI&Egrave;RES</h3>
    <table class="soa-pv-legend">
        <tbody>
        @foreach($soaPvLegendRowsData as $legendRow)
            <tr>
            @foreach($legendRow as $legendGroup)
                <td>
                @if($legendGroup)
                    @if($legendGroup['is_grouped'])
                        <strong>
                        @if($legendGroup['id_matiere_mere'] && isset($module_codes[$legendGroup['id_matiere_mere']]))
                            {{ $module_codes[$legendGroup['id_matiere_mere']] }} -
                        @endif
                        {{ $legendGroup['nom'] }}</strong> <small>({{ number_format($legendGroup['legend_credits'], 0) }} cr.)</small><br>
                    @endif
                    @foreach($legendGroup['subjects'] as $legendSubject)
                        <span><strong>{{ $legendSubject['code'] }}</strong> &mdash; {{ $legendSubject['nom'] }} <small>({{ $legendSubject['credit'] }} cr.)</small></span><br>
                    @endforeach
                @endif
                </td>
            @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="soa-pv-matrix">
        <colgroup>
            <col style="width:3%"><col style="width:10%"><col style="width:15%">
            @foreach($soaPvColumnWidths as $columnWidth)
                <col style="width:{{ $columnWidth }}%">
            @endforeach
            <col style="width:7%"><col style="width:5%"><col style="width:6%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">N&deg;</th><th rowspan="2">Matricule</th><th rowspan="2">Nom</th>
                @foreach($modules as $module)
                    @if($module['is_grouped'])
                        <th colspan="{{ count($module['subjects']) + 2 }}" class="soa-pv-group-heading">
                            @if($module['id_matiere_mere'] && isset($module_codes[$module['id_matiere_mere']]) && $module_codes[$module['id_matiere_mere']])
                                {{ $module_codes[$module['id_matiere_mere']] }} -
                            @endif
                            {{ $module['nom'] }}
                        </th>
                    @else
                        <th rowspan="2" class="soa-pv-group-heading">
                            {{ $module['subjects'][0]['code'] }} - {{ $module['subjects'][0]['nom'] }}
                        </th>
                    @endif
                @endforeach
                <th rowspan="2">Moy<br>Sem {{ $soaPvSemester }}</th><th rowspan="2">Total<br>Cr&eacute;dits</th><th rowspan="2">D&eacute;cision</th>
            </tr>
            <tr>
                @foreach($modules as $module)
                    @if($module['is_grouped'])
                        @foreach($module['subjects'] as $subject)
                            <th>{{ $subject['code'] }}</th>
                        @endforeach
                        <th>Moy<br>UE</th><th>Cr.</th>
                    @endif
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($soaPvRows as $index => $result)
            <tr>
                <td>{{ $index + 1 }}</td><td>{{ $result['student']->matricule }}</td><td class="soa-pv-name">{{ $result['student']->nom }} {{ $result['student']->prenom }}</td>
                @foreach($modules as $moduleKey => $module)
                    @foreach($module['subjects'] as $subject)
                        <?php $gradeSubject = $result['modules'][$moduleKey]['subjects'][$subject['key']]; $grade = $gradeSubject ? $gradeSubject['result'] : null; ?>
                        <td class="{{ !$grade || $grade['final'] === null ? 'soa-pv-no-grade' : (!$grade['complete'] ? 'incomplete' : '') }}">{{ !$grade || $grade['final'] === null ? 'NC' : number_format($grade['final'], 2) }}</td>
                    @endforeach
                    @if($module['is_grouped'])
                        <td class="soa-pv-group-average">{{ $result['modules'][$moduleKey]['average'] === null ? (!$result['modules'][$moduleKey]['has_marks'] ? 'NC' : (!$result['modules'][$moduleKey]['complete'] ? 'INC' : '--')) : number_format($result['modules'][$moduleKey]['average'], 2) }}</td>
                        <td>{{ number_format($result['modules'][$moduleKey]['credits'], 0) }}</td>
                    @endif
                @endforeach
                <td>{{ $result['summary']['incomplete'] ? 'INC' : number_format($result['summary']['average'], 2) }}</td>
                <td>{{ $result['summary']['credits'] }}</td>
                <td class="soa-pv-decision {{ $result['summary']['decision'] === 'ADMIS' ? 'soa-pv-valid' : 'soa-pv-fail' }}">{{ $result['summary']['decision'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
