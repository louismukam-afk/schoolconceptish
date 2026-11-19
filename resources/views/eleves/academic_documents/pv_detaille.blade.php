<?php
    $pvDetailSubjects = count($results) ? $results[0]['subjects'] : array();
    $pvDetailSubjectColumns = array_chunk(array_values($pvDetailSubjects), max(1, (int) ceil(count($pvDetailSubjects) / 3)));
    $pvDetailLegendRows = array();
    $pvDetailLegendRowCount = 0;
    foreach ($pvDetailSubjectColumns as $pvDetailColumn) $pvDetailLegendRowCount = max($pvDetailLegendRowCount, count($pvDetailColumn));
    for ($pvDetailRow = 0; $pvDetailRow < $pvDetailLegendRowCount; $pvDetailRow++) {
        $pvDetailLegendRows[] = array(
            isset($pvDetailSubjectColumns[0][$pvDetailRow]) ? $pvDetailSubjectColumns[0][$pvDetailRow] : null,
            isset($pvDetailSubjectColumns[1][$pvDetailRow]) ? $pvDetailSubjectColumns[1][$pvDetailRow] : null,
            isset($pvDetailSubjectColumns[2][$pvDetailRow]) ? $pvDetailSubjectColumns[2][$pvDetailRow] : null
        );
    }
    $pvDetailComponents = function ($subject, $component) {
        $result = $subject['result'];
        $weight = isset($subject['weights'][$component]) ? (float) $subject['weights'][$component] : 0;
        $value = isset($result['components'][$component]) ? $result['components'][$component] : null;
        return $weight > 0 && $value !== null ? number_format($value * 20 / $weight, 2) : '-';
    };
?>
<div class="pv-detail">
    <h3>L&Eacute;GENDE DES MATI&Egrave;RES</h3>
    <table class="pv-detail-legend">
        <tbody>
        @foreach($pvDetailLegendRows as $legendRow)
            <tr>
            @foreach($legendRow as $subject)
                <td>
                @if($subject)
                    <strong>{{ $subject['code'] }}</strong> &mdash; {{ $subject['nom'] }}
                    <small>(S{{ $subject['semestre'] }}, {{ $subject['credit'] }} cr.)</small>
                @endif
                </td>
            @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
    <table class="pv-detail-matrix">
        <thead>
            <tr>
                <th rowspan="2">N&deg;</th>
                <th rowspan="2">Matricule</th>
                <th rowspan="2">Nom et pr&eacute;nom</th>
                @foreach($pvDetailSubjects as $subject)
                    <?php $pvHasTpe = isset($subject['weights']['tpe']) && (float) $subject['weights']['tpe'] > 0; ?>
                    <th colspan="{{ $pvHasTpe ? 4 : 3 }}" class="pv-detail-subject-heading">
                        {{ $subject['code'] }} &mdash; {{ $subject['nom'] }}
                    </th>
                @endforeach
                <th rowspan="2">Moyenne<br>/20</th>
                <th rowspan="2">Cr&eacute;dits</th>
                <th rowspan="2">D&eacute;cision</th>
            </tr>
            <tr>
                @foreach($pvDetailSubjects as $subject)
                    <?php $pvHasTpe = isset($subject['weights']['tpe']) && (float) $subject['weights']['tpe'] > 0; ?>
                    <th>CC<br>/20</th>
                    <th>SN<br>/20</th>
                    @if($pvHasTpe)<th>TPE<br>/20</th>@endif
                    <th>Moy.<br>/20</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($results as $index => $result)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $result['student']->matricule }}</td>
                <td class="pv-detail-name">{{ $result['student']->nom }} {{ $result['student']->prenom }}</td>
                @foreach($result['subjects'] as $subject)
                    <td>{{ $pvDetailComponents($subject, 'cc') }}</td>
                    <td>{{ $pvDetailComponents($subject, 'sn') }}</td>
                    @if(isset($subject['weights']['tpe']) && (float) $subject['weights']['tpe'] > 0)
                        <td>{{ $pvDetailComponents($subject, 'tpe') }}</td>
                    @endif
                    <td class="pv-detail-final">
                        {{ $subject['result']['final'] === null ? '-' : number_format($subject['result']['final'], 2) }}
                        @if($subject['result']['final'] !== null)<small>({{ $subject['result']['session'] }})</small>@endif
                    </td>
                @endforeach
                <td>{{ $result['summary']['incomplete'] ? 'Incomplet' : number_format($result['summary']['average'], 2) }}</td>
                <td>{{ $result['summary']['credits'] }}</td>
                <td>{{ $result['summary']['decision'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
