@extends('skeleton')
@section('sub-content')
<h3>Historique des documents par tutelle</h3>
<p>Campus sélectionné · Année {{ session('year') }}. Les documents réimprimés conservent les données et les règles de leur génération.</p>
<p>Pour utiliser la présentation actuelle, ouvrez un <strong>nouvel aperçu A4 ou A3</strong> : il est établi à partir des notes actuelles. Choisissez le format dans le logiciel avant d’ouvrir la fenêtre d’impression.</p>
<a class="btn btn-primary" href="{{ url('/eleves/documents-tutelles') }}">Produire un document</a>
<table class="table table-bordered"><thead><tr><th>Date</th><th>Tutelle / modèle</th><th>Spécialité / niveau</th><th>Document</th><th>Période</th><th>Effectif</th><th>Actions</th></tr></thead><tbody>
@foreach($generations as $g)
<?php $ctx=json_decode($g->contexte,true); ?>
<tr><td>{{ $g->created_at }}</td><td>{{ isset($ctx['tutelle']['nom'])?$ctx['tutelle']['nom']:config('academic_documents.profiles.'.$g->modele.'.label') }}</td><td>{{ isset($ctx['classe']['nom'])?$ctx['classe']['nom']:'' }} · {{ isset($ctx['nniveau']['nom'])?$ctx['nniveau']['nom']:'' }}</td><td>{{ config('academic_documents.documents.'.$g->document) }}</td><td>{{ $g->periode }}</td><td>{{ $g->effectif }}</td><td>
<a class="btn btn-info btn-xs" target="_blank" href="{{ url('/eleves/documents-tutelles/generations/'.$g->id) }}">Afficher / imprimer</a>
@if(isset($ctx['document_selection']) || !in_array($g->document,array('releve','releve_modules','attestation')))
<?php
    $edition=array('modele'=>$g->modele,'id_tutelle'=>$g->id_tutelle,'id_classe'=>$g->id_classe,'id_cycle'=>$g->id_cycle,'id_nniveau'=>$g->id_nniveau,'periode'=>$g->periode,'document'=>$g->document,'session_mode'=>isset($ctx['session_mode'])?$ctx['session_mode']:'final');
    foreach(array('id_eleve','id_session','date_soutenance','attestation_reference','attestation_jury_date','attestation_issue_date','mode_generation','sequence','rapport_type','rapport_semestre','rapport_examen','rapport_format','rapport_portee','id_lot') as $field)if(isset($ctx['document_selection'][$field]))$edition[$field]=$ctx['document_selection'][$field];
?>
@foreach(array('a4','a3') as $format)
<a class="btn btn-default btn-xs" target="_blank" href="{{ url('/eleves/documents-tutelles/apercu').'?'.http_build_query(array_merge($edition,array('page_size'=>$format))) }}">Nouvel aperçu {{ strtoupper($format) }}</a>
@endforeach
@endif
@if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU))
<form method="post" style="display:inline" action="{{ url('/eleves/documents-tutelles/generations/'.$g->id.'/supprimer') }}" onsubmit="return confirm('Retirer ce document de l’historique ?');"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="btn btn-danger btn-xs">Supprimer</button></form>
@endif
</td></tr>
@endforeach
</tbody></table>
{!! $generations->appends($filters)->render() !!}
@endsection
