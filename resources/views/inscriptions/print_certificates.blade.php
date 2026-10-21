@extends('index')
@section('content')
    <a href="" onclick="window.print()" class="btn btn-warning btn-lg">@lang('inscription.imprimer')</a>
    <a href="{{ URL::to('/inscriptions/certificates') }}" class="btn btn-default btn-lg">Retour</a>
    <link rel="stylesheet" href="{{URL::asset('css/certificat.css')}}">

    @if(count($inscriptions) <= 0)
        <div class="alert alert-warning" style="margin-top: 15px;">
            Aucun &eacute;tudiant inscrit trouv&eacute; pour cette s&eacute;lection.
        </div>
    @else
        @foreach($inscriptions as $inscription)
            <?php $eleve = $inscription->eleve; ?>
            @include('inscriptions.partials.certificate_scolarite', array(
                'inscription' => $inscription,
                'eleve' => $eleve,
                'cycle' => $cycle,
                'classe' => $classe,
                'niveau' => $niveau,
                'nniveau' => $nniveau,
                'nom_etablissement' => $nom_etablissement,
                'nom_directeur' => $nom_directeur,
                'campus' => $campus
            ))
        @endforeach
    @endif
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li><strong><a href="{{url('inscriptions/certificates')}}">@lang('admin.certificat_scolarite')</a></strong></li>
@endsection
