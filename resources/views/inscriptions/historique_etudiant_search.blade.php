@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <strong>Historique et parcours etudiant</strong>
            </div>
            <div class="panel-body">
                <form method="GET" action="{{ url('/inscriptions/historique-etudiant') }}">
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Nom, prenom ou matricule">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block">
                                <span class="fa fa-search"></span> Rechercher
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($eleves !== null)
            <div class="panel panel-default">
                <div class="panel-heading">
                    <strong>Resultats de recherche</strong>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Matricule</th>
                                <th>Nom</th>
                                <th>Prenom</th>
                                <th>Date de naissance</th>
                                <th>Lieu de naissance</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $i=1; ?>
                            @forelse($eleves as $eleve)
                                <tr>
                                    <td>{{ $i++ }}</td>
                                    <td>{{ $eleve->matricule }}</td>
                                    <td><strong>{{ $eleve->nom }}</strong></td>
                                    <td>{{ $eleve->prenom }}</td>
                                    <td>{{ $eleve->date_naiss }}</td>
                                    <td>{{ $eleve->lieu_naiss }}</td>
                                    <td>
                                        <a href="{{ url('/inscriptions/historique-etudiant/'.$eleve->id) }}" class="btn btn-xs btn-info">
                                            <span class="fa fa-folder-open"></span> Voir le parcours
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Aucun etudiant trouve.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    {!! $eleves->appends(array('search' => $search))->render() !!}
                </div>
            </div>
        @endif
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li><strong><a href="{{url('/inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>Historique etudiant</strong></li>
@endsection
