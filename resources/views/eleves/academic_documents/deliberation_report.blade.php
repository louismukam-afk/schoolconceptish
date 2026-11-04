@extends('skeleton')
@section('sub-content')
<h3>Rapport des délibérations — {{ $context['tutelle']->nom }}</h3>
<p>{{ $context['campus']->nom }} · Année {{ $context['year'] }}</p>
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
<p><a class="btn btn-default" href="{{ url('/eleves/documents-tutelles') }}">Choisir la tutelle / le contexte</a> <a class="btn btn-success" href="{{ url('/eleves/notes/deliberations').'?'.http_build_query($filters) }}">Préparer une délibération</a></p>
<form method="get" action="{{ url('/eleves/notes/deliberations/rapport') }}" class="well">
@foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau')as $key)
<input type="hidden" name="{{ $key }}" value="{{ $context[$key] }}">
@endforeach
<div class="row"><div class="col-md-3 form-group"><label>Portée</label><select class="form-control" name="rapport_portee"><option value="selection" {{ isset($filters['rapport_portee']) && $filters['rapport_portee']==='globale'?'':'selected' }}>Contexte sélectionné</option><option value="globale" {{ isset($filters['rapport_portee']) && $filters['rapport_portee']==='globale'?'selected':'' }}>Toute cette tutelle sur le campus</option></select></div>
<div class="col-md-3 form-group"><label>Type</label><select class="form-control" name="rapport_type"><option value="tous">Tous</option>
@foreach($types as $key=>$name)
<option value="{{ $key }}" {{ isset($filters['rapport_type']) && $filters['rapport_type']===$key?'selected':'' }}>{{ $name }}</option>
@endforeach
</select></div><div class="col-md-3 form-group"><label>Semestre</label><select class="form-control" name="rapport_semestre"><option value="0">Tous</option>
@for($i=1;$i<=10;$i++)
<option value="{{ $i }}" {{ isset($filters['rapport_semestre']) && (int)$filters['rapport_semestre']===$i?'selected':'' }}>S{{ $i }}</option>
@endfor
</select></div><div class="col-md-3 form-group"><label>Examen</label><select class="form-control" name="rapport_examen">
@foreach(array('tous'=>'Tous','normal'=>'Session normale','rattrapage'=>'Rattrapage')as $key=>$name)
<option value="{{ $key }}" {{ isset($filters['rapport_examen']) && $filters['rapport_examen']===$key?'selected':'' }}>{{ $name }}</option>
@endforeach
</select></div></div>
<label>Présentation</label><select name="rapport_format"><option value="detaille" {{ $format==='detaille'?'selected':'' }}>Détaillée</option><option value="synthetique" {{ $format==='synthetique'?'selected':'' }}>Synthétique</option></select>
<label>Décision n° (facultatif)</label><input type="number" min="0" name="id_lot" value="{{ isset($filters['id_lot'])?$filters['id_lot']:'' }}"><button class="btn btn-primary">Consulter</button>
</form>
<p>
@foreach(array('a4','a3')as $paper)
<a class="btn btn-default" target="_blank" href="{{ url('/eleves/notes/deliberations/rapport/imprimer').'?'.http_build_query(array_merge($filters,array('page_size'=>$paper))) }}">Imprimer / PDF {{ strtoupper($paper) }}</a>
@endforeach
<a class="btn btn-info" href="{{ url('/eleves/notes/deliberations/rapport/export').'?'.http_build_query($filters) }}">Exporter CSV / Excel</a></p>
<form method="post" target="_blank" action="{{ url('/eleves/documents-tutelles/generer') }}"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="document" value="deliberation_rapport">
@foreach($filters as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
<select name="page_size"><option value="a4">A4</option><option value="a3">A3</option></select> <button class="btn btn-default">Archiver ce rapport</button></form>
<p>Les impressions et l’export portent sur tous les résultats des filtres. Le journal conserve les décisions appliquées depuis l’activation du module, même si les notes sont ensuite modifiées.</p>
@include('eleves.academic_documents._deliberation_report_rows')
{!! $report['batches']->appends($filters)->render() !!}
@endsection