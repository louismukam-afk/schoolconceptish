@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        @if(session('success'))
            <div class="alert alert-success">{{ trans('admin.succes') }}</div>
        @endif

        <div class="panel panel-primary">
            <div class="panel-heading"><strong>Fiches de progression</strong></div>
            <div class="panel-body">
                <div class="btn-group" style="margin-bottom:15px">
                    <a href="{{ route('fiches_progression_enseignants') }}" class="btn btn-default">
                        <span class="glyphicon glyphicon-user"></span> Nouveau flux par enseignant
                    </a>
                    <a href="{{ route('fiches_progression_par_specialite') }}" class="btn btn-default">
                        <span class="glyphicon glyphicon-list-alt"></span> Cours avec fiches
                    </a>
                    <a href="{{ route('fiches_progression_manquantes') }}" class="btn btn-default">
                        <span class="glyphicon glyphicon-warning-sign"></span> Cours sans fiches
                    </a>
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
                        <div class="col-md-4">
                            <label>Enseignant</label>
                            <select name="id_personnel" class="form-control">
                                <option value="">Tous les enseignants</option>
                                @foreach($enseignants as $enseignant)
                                    <option value="{{ $enseignant->id }}" @if($selectedPersonnel==$enseignant->id) selected @endif>{{ $enseignant->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Campus</label>
                            <p class="form-control-static">
                                <strong>{{ $campus_actif ? $campus_actif->nom : '' }}</strong>
                            </p>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <button class="btn btn-primary form-control">
                                <span class="glyphicon glyphicon-filter"></span> Filtrer
                            </button>
                        </div>
                    </div>
                </form>

                <form method="POST" action="{{ route('fiches_progression_store') }}" enctype="multipart/form-data" class="well">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Annee academique</label>
                            <input type="text" name="annee_scolaire" class="form-control" value="{{ $selectedYear }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>Enseignant</label>
                            <select name="id_personnel" id="id_personnel" class="form-control" required>
                                <option value="">-- Choisir --</option>
                                @foreach($enseignants as $enseignant)
                                    <option value="{{ $enseignant->id }}" @if($selectedPersonnel==$enseignant->id) selected @endif>{{ $enseignant->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label>Cours programme</label>
                            <select name="id_cours_enseignant" id="id_cours_enseignant" class="form-control" required>
                                <option value="">-- Choisir un cours --</option>
                                @foreach($cours as $c)
                                    <option value="{{ $c->id }}" data-personnel="{{ $c->id_personnel }}">
                                        {{ $c->personnel ? $c->personnel->nom : '' }} -
                                        {{ $c->matiere ? $c->matiere->nom : 'Matiere' }} /
                                        {{ $c->classe ? $c->classe->nom : '' }}
                                        {{ $c->nniveau ? ' - '.$c->nniveau->nom : '' }}
                                        {{ $c->semestre ? ' - Semestre '.$c->semestre : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Titre</label>
                            <input type="text" name="titre" class="form-control" placeholder="Ex: Fiche progression Informatique II">
                        </div>
                        <div class="col-md-5">
                            <label>Fichier PDF ou Word</label>
                            <input type="file" name="fiche" class="form-control" accept=".pdf,.doc,.docx" required>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button class="btn btn-success form-control">
                                <span class="glyphicon glyphicon-upload"></span> Enregistrer
                            </button>
                        </div>
                        <div class="col-md-12">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>

                <h4>Historique des fiches</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Annee</th>
                            <th>Campus</th>
                            <th>Enseignant</th>
                            <th>Cours</th>
                            <th>Fichier</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($fiches as $fiche)
                            <tr>
                                <td>{{ $fiche->created_at }}</td>
                                <td>{{ $fiche->annee_scolaire }}</td>
                                <td>{{ $fiche->campus ? $fiche->campus->nom : '' }}</td>
                                <td>{{ $fiche->personnel ? $fiche->personnel->nom : '' }}</td>
                                <td>
                                    @if($fiche->cours)
                                        {{ $fiche->cours->matiere ? $fiche->cours->matiere->nom : 'Matiere' }}
                                        / {{ $fiche->cours->classe ? $fiche->cours->classe->nom : '' }}
                                        {{ $fiche->cours->nniveau ? ' - '.$fiche->cours->nniveau->nom : '' }}
                                    @endif
                                    @if($fiche->titre)
                                        <br><small>{{ $fiche->titre }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $fiche->fichier_original }}
                                    <br><span class="label label-default">{{ strtoupper($fiche->fichier_extension) }}</span>
                                </td>
                                <td>
                                    @if($fiche->fichier_extension == 'pdf')
                                        <button type="button" class="btn btn-info btn-xs js-preview-fiche" data-target="preview-fiche-{{ $fiche->id }}">
                                            <span class="glyphicon glyphicon-eye-open"></span> Visualiser
                                        </button>
                                    @endif
                                    <a href="{{ route('fiches_progression_download', $fiche->id) }}" class="btn btn-primary btn-xs">
                                        <span class="glyphicon glyphicon-download"></span> Telecharger
                                    </a>
                                    <a href="{{ route('fiches_progression_destroy', $fiche->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('Supprimer cette fiche ?');">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </a>
                                </td>
                            </tr>
                            @if($fiche->fichier_extension == 'pdf')
                                <tr id="preview-fiche-{{ $fiche->id }}" style="display:none">
                                    <td colspan="7">
                                        <iframe data-src="{{ route('fiches_progression_show', $fiche->id) }}" style="width:100%; height:520px; border:1px solid #ccc;"></iframe>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune fiche de progression trouvee.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            var enseignant = document.getElementById('id_personnel');
            var cours = document.getElementById('id_cours_enseignant');
            var previewButtons = document.getElementsByClassName('js-preview-fiche');
            function refreshCours() {
                var selected = enseignant ? enseignant.value : '';
                if (!cours) return;
                Array.prototype.forEach.call(cours.options, function(option) {
                    if (!option.value) {
                        option.style.display = '';
                        return;
                    }
                    option.style.display = (!selected || option.getAttribute('data-personnel') === selected) ? '' : 'none';
                    if (option.selected && option.style.display === 'none') {
                        cours.value = '';
                    }
                });
            }
            if (enseignant) {
                enseignant.onchange = refreshCours;
            }
            refreshCours();
            Array.prototype.forEach.call(previewButtons, function(button) {
                button.onclick = function() {
                    var row = document.getElementById(button.getAttribute('data-target'));
                    if (!row) return;
                    var iframe = row.getElementsByTagName('iframe')[0];
                    if (iframe && !iframe.getAttribute('src')) {
                        iframe.setAttribute('src', iframe.getAttribute('data-src'));
                    }
                    row.style.display = row.style.display === 'none' ? '' : 'none';
                };
            });
        })();
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('/personnel') }}">@lang('menu.gestion_personnel')</a></strong></li>
    <li class="active"><strong>Fiches de progression</strong></li>
@endsection
