@extends('skeleton')
@section('sub-content')
<h3>Saisie par anonymat — {{ $context['tutelle']->nom }}</h3>
<p>{{ $context['campus']->nom }} · {{ $context['year'] }} · {{ $context['classe']->nom }} · {{ $context['nniveau']->nom }}</p>
<h4>{{ $selected->nom }} · S{{ $selected->semestre }} · {{ $sequence<=4?'Session normale':'Rattrapage' }} · {{ $mode==='matiere'?'Code par matière':'Code commun au semestre' }}</h4>
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
<p>Notes sur 20, pondérées à {{ $weight }} %. Un champ vide conserve la note existante. Les notes à zéro doivent être saisies explicitement. Les identités ne figurent pas dans cette liste.</p>
<form method="post" action="{{ url('/eleves/notes/anonymats/notes') }}" onsubmit="return !this.querySelector('input[name=&quot;supprimer[]&quot;]:checked') || confirm('Supprimer les notes cochées ? Les codes restent conservés.');">
<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="session_fingerprint" value="{{ hash('sha256',json_encode($selected)) }}">
@foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode')as $key)
<input type="hidden" name="{{ $key }}" value="{{ $context[$key] }}">
@endforeach
<input type="hidden" name="id_session" value="{{ $selected->id }}"><input type="hidden" name="sequence" value="{{ $sequence }}"><input type="hidden" name="mode_generation" value="{{ $mode }}">
<table class="table table-bordered"><thead><tr><th>Code d’anonymat</th><th>Note /20</th><th>Supprimer la note</th></tr></thead><tbody>
@foreach($rows as $row)
<tr><td>{{ $row['code'] }}</td><td><input type="number" min="0" max="20" step="0.01" class="form-control" name="notes_anonymes[{{ $row['id'] }}]" value="{{ $row['note'] }}"><input type="hidden" name="versions[{{ $row['id'] }}]" value="{{ $row['version'] }}"></td><td>
@if($row['version']!=='new')
<input type="checkbox" name="supprimer[]" value="{{ $row['id'] }}">
@endif
</td></tr>
@endforeach
</tbody></table>
@if(count($rows))
<button class="btn btn-success">Enregistrer les notes par anonymat</button>
@else
<p>Aucun code attribué. Demandez la génération des anonymats au responsable.</p>
@endif
</form>
@endsection