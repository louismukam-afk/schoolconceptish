@extends('skeleton')
@section('sub-content')
<h3>Anonymats — {{ $context['tutelle']->nom }}</h3>
<p>{{ $context['campus']->nom }} · {{ $context['year'] }} · {{ $context['classe']->nom }} · {{ $context['cycle']->nom }} · {{ $context['nniveau']->nom }}</p>
@if($errors->any())
<div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
<p>Choisissez la matière et l’évaluation. La génération ajoute les étudiants manquants et conserve les codes déjà attribués. Les codes par matière et les codes communs au semestre constituent deux listes distinctes.</p>
@include('eleves.academic_documents._anonymat_selection')
@if($selected)
<?php $q=array();foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode')as $key)$q[$key]=$context[$key];$q['id_session']=$selected->id;$q['sequence']=$sequence;$q['mode_generation']=$mode; ?>
@if($canManage)
<form method="post" action="{{ url('/eleves/notes/anonymats/generer') }}">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
@foreach($q as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
@if($mode==='matiere')
<label><input type="checkbox" name="toutes_matieres" value="1"> Générer pour toutes les matières configurées de S{{ $selected->semestre }} pour cette évaluation</label>
@endif
<p><button class="btn btn-primary">Générer les codes manquants</button></p>
</form>
@endif
<p><a class="btn btn-success" href="{{ url('/eleves/notes/anonymats/saisie').'?'.http_build_query($q) }}">Saisir / modifier les notes par anonymat</a></p>
<p>
<?php $parity=(int)$selected->semestre%2?1:2; ?>
@foreach(array('Contrôle continu'=>$parity,'TPE'=>$parity+6)as $name=>$seq)
<?php $weightColumn=$seq<=2?'cc'.$parity:'tpe'.$parity; ?>
@if((float)$selected->$weightColumn>0)
<a class="btn btn-default" href="{{ url('/eleves/documents-tutelles/notes').'?'.http_build_query(array_merge($q,array('sequence'=>$seq))) }}">Saisir {{ $name }}</a>
@endif
@endforeach
<a class="btn btn-info" target="_blank" href="{{ url('/eleves/documents-tutelles/apercu').'?'.http_build_query(array_merge($q,array('document'=>'pv_modules'))) }}">PV par modules</a>
<a class="btn btn-info" target="_blank" href="{{ url('/eleves/documents-tutelles/apercu').'?'.http_build_query(array_merge($q,array('document'=>'releve_modules'))) }}">Relevés par modules</a>
</p>
<?php $documents=$canManage?array('anonymat_codes','anonymat_report','anonymat_presence','anonymat_fiches'):array('anonymat_report'); ?>
<form method="get" action="{{ url('/eleves/documents-tutelles/apercu') }}" target="_blank" class="well">
@foreach($q as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
<label>État à imprimer</label><select name="document">
@foreach($documents as $doc)
<option value="{{ $doc }}">{{ config('academic_documents.documents.'.$doc) }}</option>
@endforeach
</select><select name="page_size"><option value="a4">A4</option><option value="a3">A3</option></select> <button class="btn btn-default">Aperçu / PDF</button>
</form>
<form method="post" action="{{ url('/eleves/documents-tutelles/generer') }}" target="_blank" class="well">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
@foreach($q as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
<label>État à archiver</label><select name="document">
@foreach($documents as $doc)
<option value="{{ $doc }}">{{ config('academic_documents.documents.'.$doc) }}</option>
@endforeach
</select><select name="page_size"><option value="a4">A4</option><option value="a3">A3</option></select> <button class="btn btn-default">Générer et archiver</button>
</form>
<p>{{ count($rows) }} code(s) pour cette sélection. <a href="{{ url('/eleves/documents-tutelles/historique').'?'.http_build_query(array('id_tutelle'=>$context['id_tutelle'])) }}">Historique de la tutelle</a></p>
<table class="table table-bordered"><thead><tr><th>Code d’anonymat</th>
@if($canManage)
<th>Matricule</th><th>Étudiant</th><th>Retirer un code sans note</th>
@endif
</tr></thead><tbody>
@foreach($rows as $row)
<tr><td>{{ $row->code }}</td>
@if($canManage)
<td>{{ $row->student->matricule }}</td><td>{{ $row->student->nom }} {{ $row->student->prenom }}</td><td>
@if(!isset($notes[$row->id_eleve]))
<form method="post" action="{{ url('/eleves/notes/anonymats/supprimer') }}" onsubmit="return confirm('Retirer ce code ? Les notes existantes ne seront pas supprimées.');">
<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="id_anonymat" value="{{ $row->id }}"><input type="hidden" name="version" value="{{ $row->version }}">
@foreach($q as $key=>$value)
<input type="hidden" name="{{ $key }}" value="{{ $value }}">
@endforeach
<button class="btn btn-danger btn-xs">Retirer</button></form>
@else
Code conservé : note existante
@endif
</td>
@endif
</tr>
@endforeach
</tbody></table>
@if(!count($rows))
<p>Aucun code pour cette sélection. Un responsable doit générer les codes avant la saisie.</p>
@endif
@endif
@endsection