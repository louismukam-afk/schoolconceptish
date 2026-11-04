@extends('skeleton')
@section('sub-content')
<h3>Délibérations — {{ $context['tutelle']->nom }}</h3>
<p>{{ $context['campus']->nom }} · {{ $context['year'] }} · {{ $context['classe']->nom }} · {{ $context['cycle']->nom }} · {{ $context['nniveau']->nom }}</p>
@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
<?php $q=array();foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode')as $key)$q[$key]=$context[$key]; ?>
<p><a class="btn btn-default" href="{{ url('/eleves/documents-tutelles').'?'.http_build_query($q) }}">Notes et documents</a> <a class="btn btn-info" href="{{ url('/eleves/notes/deliberations/rapport').'?'.http_build_query($q) }}">Rapport des délibérations</a></p>
<p>La délibération améliore des notes finales existantes. Les CC et TPE sont conservés ; la contribution d’examen reste dans sa pondération. Les notes absentes ne sont pas créées. Une cible impossible, une note déjà supérieure à la cible ou un module incomplet ne produit pas de proposition.</p>
<form method="get" action="{{ url('/eleves/notes/deliberations') }}" class="well">
@foreach($q as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
<input type="hidden" name="operation" value="previsualiser">
<div class="row"><div class="col-md-4 form-group"><label>Type de délibération</label><select class="form-control" name="type_deliberation">
@foreach($types as $key=>$name)
<option value="{{ $key }}" {{ $criteria['type_deliberation']===$key?'selected':'' }}>{{ $name }}</option>
@endforeach
</select></div><div class="col-md-4 form-group"><label>Semestre du programme</label><select class="form-control" name="semestre"><option value="0">Tous les semestres de la période</option>
@for($semester=1;$semester<=10;$semester++)
<option value="{{ $semester }}" {{ $criteria['semestre']===$semester?'selected':'' }}>S{{ $semester }}</option>
@endfor
</select></div><div class="col-md-4 form-group"><label>Examen à ajuster</label><select class="form-control" name="evaluation">
@foreach(array('retenue'=>'Session actuellement retenue pour chaque UV','normal'=>'Session normale','rattrapage'=>'Rattrapage')as $key=>$name)
<option value="{{ $key }}" {{ $criteria['evaluation']===$key?'selected':'' }}>{{ $name }}</option>
@endforeach
</select></div></div>
<div class="row"><div class="col-md-3 form-group"><label>Base des critères</label><select class="form-control" name="base">
@foreach(array('note20'=>'Note /20','note100'=>'Note /100','credit'=>'Crédits')as $key=>$name)
<option value="{{ $key }}" {{ $criteria['base']===$key?'selected':'' }}>{{ $name }}</option>
@endforeach
</select></div><div class="col-md-3 form-group"><label>Valeur cible</label><input class="form-control" type="number" step="0.01" min="0" name="valeur_cible" value="{{ $criteria['valeur_cible'] }}" required></div>
<div class="col-md-3 form-group"><label>Borne minimale actuelle (facultative)</label><input class="form-control" type="number" step="0.01" min="0" name="interval_min" value="{{ $criteria['interval_min'] }}"><label><input type="checkbox" name="min_inclus" value="1" {{ $criteria['min_inclus']?'checked':'' }}> Inclure la borne</label></div>
<div class="col-md-3 form-group"><label>Borne maximale actuelle (facultative)</label><input class="form-control" type="number" step="0.01" min="0" name="interval_max" value="{{ $criteria['interval_max'] }}"><label><input type="checkbox" name="max_inclus" value="1" {{ $criteria['max_inclus']?'checked':'' }}> Inclure la borne</label></div></div>
<div class="row"><div class="col-md-6 form-group"><label>Portée</label><select class="form-control" name="portee"><option value="specialite" {{ $criteria['portee']==='specialite'?'selected':'' }}>Toute la spécialité dans la période choisie</option><option value="selection" {{ $criteria['portee']==='selection'?'selected':'' }}>Matières / modules cochés ci-dessous</option></select></div>
<div class="col-md-6 form-group"><label>Étudiant (obligatoire en mode individuel)</label><select class="form-control" name="id_eleve"><option value="">Tous les étudiants du contexte</option>
@foreach($data['results']as $r)
<option value="{{ $r['student']->id }}" {{ (string)$r['student']->id===$criteria['id_eleve']?'selected':'' }}>{{ $r['student']->matricule }} · {{ $r['student']->nom }} {{ $r['student']->prenom }}</option>
@endforeach
</select></div></div>
<div class="row"><div class="col-md-6"><strong>Matières / UV</strong><div style="max-height:180px;overflow:auto">
@foreach($data['sessions']as $s)
<?php $key=$s->programme_id.':'.$s->semestre; ?>
<label style="display:block"><input type="checkbox" name="matieres[]" value="{{ $key }}" {{ in_array($key,$criteria['matieres'],true)?'checked':'' }}> S{{ $s->semestre }} · {{ $s->nom }} · {{ $s->credit }} crédits</label>
@endforeach
</div></div><div class="col-md-6"><strong>Modules / groupes de matières</strong><div style="max-height:180px;overflow:auto">
@foreach($data['modules']as $key=>$m)
@if($m['is_grouped'])
<label style="display:block"><input type="checkbox" name="modules[]" value="{{ $key }}" {{ in_array($key,$criteria['modules'],true)?'checked':'' }}> S{{ $m['semestre'] }} · {{ $m['nom'] }}</label>
@endif
@endforeach
</div></div></div>
<p>Pour les types par crédits, les bornes et la cible sont des crédits sur la sélection. La capitalisation de {{ $context['profile']['label'] }} commence à {{ $context['profile']['credit_threshold'] }}/20. Les modules ne donnent pas une seconde fois les crédits de leurs UV.</p>
<button class="btn btn-primary">Prévisualiser les propositions</button>
</form>
@if($plan!==null)
<h4>Aperçu — aucune note n’est encore modifiée</h4>
<p>{{ count($plan['proposals']) }} proposition(s) ; {{ $plan['skipped'] }} cas sans amélioration applicable. Les propositions groupées sont appliquées en entier. Les bilans ci-dessous décrivent chaque proposition prise séparément ; le rapport conserve le bilan réel de l’ensemble appliqué.</p>
<form method="post" action="{{ url('/eleves/notes/deliberations/appliquer') }}">
<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="payload" value="{{ $payload }}"><input type="hidden" name="signature" value="{{ $signature }}">
@foreach($q as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
@foreach($plan['proposals']as $p)
<div class="panel panel-default"><div class="panel-heading"><label><input type="checkbox" name="propositions[]" value="{{ $p['key'] }}"> {{ $p['student']->matricule }} · {{ $p['student']->nom }} {{ $p['student']->prenom }} — {{ $p['label'] }}</label></div><div class="panel-body">
@if(isset($p['module_before']))
<p>Moyenne du module : {{ number_format($p['module_before'],2) }} → {{ number_format($p['module_after'],2) }}/20</p>
@endif
@if(isset($p['credits_before']))
<p>Crédits sur la sélection : {{ $p['credits_before'] }} → {{ $p['credits_after'] }}</p>
@endif
<table class="table table-bordered"><thead><tr><th>Matière</th><th>Semestre</th><th>Examen ajusté</th><th>Note finale avant /20</th><th>Note finale après /20</th><th>Contribution d’examen avant → après /100</th></tr></thead><tbody>
@foreach($p['changes']as $change)
<tr><td>{{ $change['matiere'] }}</td><td>{{ $change['semestre'] }}</td><td>{{ $change['sequence']<=4?'Normale':'Rattrapage' }}</td><td>{{ number_format($change['final_before'],2) }}</td><td>{{ number_format($change['final_after'],2) }}</td><td>{{ $change['note_before'] }} → {{ $change['note_after'] }}</td></tr>
@endforeach
</tbody></table><p>Crédits du dossier : {{ $p['before']['credits'] }} → {{ $p['after']['credits'] }} · Décision : {{ $p['before']['decision'] }} → {{ $p['after']['decision'] }}</p>
</div></div>
@endforeach
@if(count($plan['proposals']))
<label>Motif / décision du jury</label><textarea class="form-control" name="motif" minlength="3" maxlength="1000" required></textarea><br><button class="btn btn-success">Appliquer les propositions cochées et conserver le rapport</button>
@else
<p>Aucune proposition applicable. Vérifiez l’intervalle, la cible, les notes manquantes et les pondérations des sessions.</p>
@endif
</form>
@endif
@endsection