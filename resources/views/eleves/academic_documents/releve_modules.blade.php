<table class="module-transcript"><thead><tr><th>Code</th><th>UV / matière</th><th>Semestre</th><th>Crédits</th><th>Note /20</th><th>Grade</th><th>Validation</th><th>Session</th></tr></thead><tbody>
@foreach($r['modules'] as $module)
<tr class="module-heading"><th colspan="8" class="left">S{{ $module['semestre'] }} — {{ $module['nom'] }}@if(!$module['is_grouped']) — UV sans groupe
@endif
</th></tr>
@foreach($module['subjects'] as $subject)
<tr class="{{ !$subject['result']['complete']?'incomplete':'' }}"><td>{{ $subject['code'] }}</td><td class="left">{{ $subject['nom'] }}@if($subject['enseignants'])<br><small>{{ $subject['enseignants'] }}</small>
@endif
</td><td>{{ $subject['semestre'] }}</td><td>{{ $subject['credit'] }}</td><td>{{ $subject['result']['final']===null?'Absente':number_format($subject['result']['final'],2) }}</td><td>{{ $subject['result']['grade'] }}</td><td>{{ $subject['result']['valid']?'V':'NV' }}</td><td>{{ $subject['result']['session'] }}</td></tr>
@endforeach
@if($module['is_grouped'])<tr class="module-average"><th colspan="3" class="left">Moyenne du module /20</th><td>{{ $module['credits'] }}</td><td>{{ $module['average']===null?($module['complete']?'Non calculable':'Incomplet'):number_format($module['average'],2) }}</td><td colspan="3">Pondérée par les crédits des UV</td></tr>
@endif

@endforeach
</tbody></table>
@include('eleves.academic_documents.module_notice')