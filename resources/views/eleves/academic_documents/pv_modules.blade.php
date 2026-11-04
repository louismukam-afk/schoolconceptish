<table class="results-matrix module-matrix"><thead>
<tr><th rowspan="2">N°</th><th rowspan="2">Matricule</th><th rowspan="2">Nom et prénom</th>
@foreach($modules as $module)
<th colspan="{{ count($module['subjects'])+($module['is_grouped']?1:0) }}" class="module-heading">S{{ $module['semestre'] }} — {{ $module['nom'] }}@if(!$module['is_grouped'])<small>UV sans groupe</small>
@endif
</th>
@endforeach
<th rowspan="2">Moyenne /20</th><th rowspan="2">Crédits capitalisés</th><th rowspan="2">Décision</th></tr>
<tr>@foreach($modules as $module)
@foreach($module['subjects'] as $subject)<th>{{ $subject['code'] }}<small>{{ $subject['nom'] }}</small><small>{{ $subject['credit'] }} cr.</small></th>@endforeach
@if($module['is_grouped'])<th class="module-average">Moyenne du module /20</th>
@endif

@endforeach</tr></thead><tbody>
@foreach($results as $i=>$r)<tr><td>{{ $i+1 }}</td><td>{{ $r['student']->matricule }}</td><td class="left">{{ $r['student']->nom }} {{ $r['student']->prenom }}</td>
@foreach($r['modules'] as $module)
@foreach($module['subjects'] as $subject)<td class="{{ !$subject['result']['complete']?'incomplete':'' }}">{{ $subject['result']['final']===null?'—':number_format($subject['result']['final'],2) }} {{ $subject['result']['session'] }}</td>@endforeach
@if($module['is_grouped'])<td class="module-average">{{ $module['average']===null?($module['complete']?'Non calculable':'Incomplet'):number_format($module['average'],2) }}</td>
@endif

@endforeach
<td>{{ $r['summary']['incomplete']?'Incomplet':number_format($r['summary']['average'],2) }}</td><td>{{ $r['summary']['credits'] }}</td><td>{{ $r['summary']['decision'] }}</td></tr>@endforeach
</tbody></table>
@include('eleves.academic_documents.module_notice')