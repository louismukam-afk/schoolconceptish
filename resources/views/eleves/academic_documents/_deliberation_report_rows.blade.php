<p><strong>{{ $report['metrics']->lots }}</strong> décision(s) · <strong>{{ $report['metrics']->etudiants }}</strong> étudiant(s) distinct(s) · <strong>{{ $report['metrics']->matieres }}</strong> UV / semestre · <strong>{{ $report['metrics']->lignes }}</strong> modification(s) de notes</p>
@foreach($report['batches']as $batch)
<?php $rows=isset($report['groups'][$batch->id])?$report['groups'][$batch->id]:array();$people=array();foreach($rows as $row)$people[$row->id_eleve]=true; ?>
<h3>Décision n° {{ $batch->id }} — {{ $types[$batch->type] }}</h3>
<p>{{ $batch->created_at }} · Utilisateur n° {{ $batch->id_creator }} · {{ count($people) }} étudiant(s) · {{ count($rows) }} note(s)<br><strong>Motif :</strong> {{ $batch->motif }}</p>
@if(count($rows))
<p>{{ $rows[0]->snapshot['classe'] }} · {{ $rows[0]->snapshot['cycle'] }} · {{ $rows[0]->snapshot['nniveau'] }}</p>
@endif
@if($format==='detaille')
<table class="table table-bordered"><thead><tr><th>Matricule / étudiant</th><th>Matière / module</th><th>Semestre</th><th>Examen</th><th>Finale avant /20</th><th>Finale après /20</th><th>Contribution avant → après /100</th><th>Bilan du dossier</th></tr></thead><tbody>
@foreach($rows as $row)
<?php $d=$row->snapshot; ?>
<tr><td class="left">{{ $d['matricule'] }}<br>{{ $d['etudiant'] }}</td><td class="left">{{ $d['matiere'] }}<br><small>{{ $d['module'] }}</small></td><td>S{{ $row->semestre }}</td><td>{{ $row->sequence<=4?'Normale':'Rattrapage' }}</td><td>{{ number_format($d['final_before'],2) }}</td><td>{{ number_format($d['final_after'],2) }}</td><td>{{ $d['note_before'] }} → {{ $d['note_after'] }}</td><td>{{ $d['bilan']['avant']['credits'] }} → {{ $d['bilan']['apres']['credits'] }} crédits<br>{{ $d['bilan']['avant']['decision'] }} → {{ $d['bilan']['apres']['decision'] }}</td></tr>
@endforeach
</tbody></table>
@endif
@endforeach
@if(!$report['metrics']->lots)
<p>Aucune délibération enregistrée pour ces filtres.</p>
@endif