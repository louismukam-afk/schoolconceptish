@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading"><strong>Choisir un enseignant</strong></div>
            <div class="panel-body">
                <div class="btn-group" style="margin-bottom:15px">
                    <a href="{{ route('fiches_progression') }}" class="btn btn-default">Ancien flux</a>
                    <a href="{{ route('fiches_progression_par_specialite', array('annee_scolaire'=>$selectedYear)) }}" class="btn btn-default">Cours avec fiches</a>
                    <a href="{{ route('fiches_progression_manquantes', array('annee_scolaire'=>$selectedYear)) }}" class="btn btn-default">Cours sans fiches</a>
                </div>

                <form method="GET" class="well">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Annee academique</label>
                            <select name="annee_scolaire" class="form-control">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @if($selectedYear==$year) selected @endif>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Rechercher un enseignant</label>
                            <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Nom ou matricule">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control"><span class="glyphicon glyphicon-search"></span> Rechercher</button>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Date de naissance</th>
                        <th>Lieu de naissance</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($enseignants as $enseignant)
                        <tr>
                            <td>{{ $enseignant->nom }}</td>
                            <td>{{ $enseignant->date_naiss }}</td>
                            <td>{{ $enseignant->lieu_naiss }}</td>
                            <td>
                                <a class="btn btn-success btn-xs" href="{{ route('fiches_progression_enseignant_cours', array($enseignant->id, 'annee_scolaire'=>$selectedYear)) }}">
                                    <span class="glyphicon glyphicon-plus"></span> Ajouter une fiche
                                </a>
                                <a class="btn btn-info btn-xs" href="{{ route('fiches_progression_enseignant_fiches', array($enseignant->id, 'annee_scolaire'=>$selectedYear)) }}">
                                    <span class="glyphicon glyphicon-list"></span> Ses fiches
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Aucun enseignant trouve.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div class="text-center"><?php echo $enseignants->appends(array('q'=>$search, 'annee_scolaire'=>$selectedYear))->render(); ?></div>
            </div>
        </div>
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('/personnel') }}">@lang('menu.gestion_personnel')</a></strong></li>
    <li><strong><a href="{{ route('fiches_progression') }}">Fiches de progression</a></strong></li>
    <li class="active"><strong>Enseignants</strong></li>
@endsection
