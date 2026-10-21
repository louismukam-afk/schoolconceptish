@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ trans('admin.succes') }}</div>
        @endif
        <div class="panel panel-primary">
            <div class="panel-heading"><strong>{{ $enseignant->nom }}</strong></div>
            <div class="panel-body">
                <div class="btn-group" style="margin-bottom:15px">
                    <a href="{{ route('fiches_progression_enseignants', array('annee_scolaire'=>$selectedYear)) }}" class="btn btn-default">Retour aux enseignants</a>
                    <a href="{{ route('fiches_progression_enseignant_fiches', array($enseignant->id, 'annee_scolaire'=>$selectedYear)) }}" class="btn btn-info">Lister ses fiches</a>
                </div>

                <form method="GET" class="well">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Annee academique</label>
                            <select name="annee_scolaire" class="form-control">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @if($selectedYear==$year) selected @endif>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control">Filtrer</button>
                        </div>
                    </div>
                </form>

                @forelse($groupes as $groupe=>$cours)
                    <h4 style="margin-top:25px">{{ $groupe }}</h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Cours / Matiere</th>
                            <th>Semestre</th>
                            <th>Periode</th>
                            <th>Fiches existantes</th>
                            <th>Ajouter</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($cours as $c)
                            <tr>
                                <td>{{ $c->matiere ? $c->matiere->nom : 'Matiere' }}</td>
                                <td>Semestre {{ $c->semestre }}</td>
                                <td>{{ $c->date_debut }} - {{ $c->date_fin }}</td>
                                <td>
                                    @if(isset($fichesParCours[$c->id]))
                                        <span class="label label-success">{{ count($fichesParCours[$c->id]) }} fiche(s)</span>
                                    @else
                                        <span class="label label-warning">Aucune fiche</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-success btn-xs js-toggle-upload" data-target="upload-cours-{{ $c->id }}">
                                        <span class="glyphicon glyphicon-upload"></span> Ajouter la fiche
                                    </button>
                                </td>
                            </tr>
                            <tr id="upload-cours-{{ $c->id }}" style="display:none">
                                <td colspan="5">
                                    <form method="POST" action="{{ route('fiches_progression_store') }}" enctype="multipart/form-data" class="well">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="id_personnel" value="{{ $enseignant->id }}">
                                        <input type="hidden" name="id_cours_enseignant" value="{{ $c->id }}">
                                        <input type="hidden" name="annee_scolaire" value="{{ $selectedYear }}">
                                        <input type="hidden" name="redirect_to" value="{{ URL::full() }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label>Titre</label>
                                                <input type="text" name="titre" class="form-control" value="Fiche progression - {{ $c->matiere ? $c->matiere->nom : 'Matiere' }}">
                                            </div>
                                            <div class="col-md-5">
                                                <label>Fichier PDF ou Word</label>
                                                <input type="file" name="fiche" class="form-control" accept=".pdf,.doc,.docx" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label>&nbsp;</label>
                                                <button class="btn btn-success form-control">Enregistrer</button>
                                            </div>
                                            <div class="col-md-12">
                                                <label>Description</label>
                                                <textarea name="description" rows="2" class="form-control"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @empty
                    <div class="alert alert-warning">Aucun cours programme pour cet enseignant sur cette annee academique.</div>
                @endforelse
            </div>
        </div>
    </div>
    <script>
        (function(){
            var buttons = document.getElementsByClassName('js-toggle-upload');
            Array.prototype.forEach.call(buttons, function(button) {
                button.onclick = function() {
                    var row = document.getElementById(button.getAttribute('data-target'));
                    if (row) row.style.display = row.style.display === 'none' ? '' : 'none';
                };
            });
        })();
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('/personnel') }}">@lang('menu.gestion_personnel')</a></strong></li>
    <li><strong><a href="{{ route('fiches_progression_enseignants') }}">Fiches de progression</a></strong></li>
    <li class="active"><strong>{{ $enseignant->nom }}</strong></li>
@endsection
