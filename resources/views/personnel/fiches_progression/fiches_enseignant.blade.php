@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><strong>Fiches de progression - {{ $enseignant->nom }}</strong></div>
            <div class="panel-body">
                <div class="btn-group" style="margin-bottom:15px">
                    <a href="{{ route('fiches_progression_enseignants', array('annee_scolaire'=>$selectedYear)) }}" class="btn btn-default">Retour aux enseignants</a>
                    <a href="{{ route('fiches_progression_enseignant_cours', array($enseignant->id, 'annee_scolaire'=>$selectedYear)) }}" class="btn btn-success">Ajouter une fiche</a>
                </div>
                @include('personnel.fiches_progression.partials_filtres_annee')
                @include('personnel.fiches_progression.partials_table_fiches', array('fiches'=>$fiches))
            </div>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('/personnel') }}">@lang('menu.gestion_personnel')</a></strong></li>
    <li><strong><a href="{{ route('fiches_progression_enseignants') }}">Fiches de progression</a></strong></li>
    <li class="active"><strong>Fiches enseignant</strong></li>
@endsection
