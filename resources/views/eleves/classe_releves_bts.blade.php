@extends('skeleton')

@section('sub-content')
    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/releves_bts/historique') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-folder-open"></span> Historique des releves BTS
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>
    <h4>Choix de la filière et de la spécialité</h4>

    <p class="text-muted">
        Année académique : {{ session('year') }}.
        La génération sauvegardée fige les relevés pour l'année, la filière,
        la spécialité, le niveau et le cycle choisis.
    </p>

    <hr>

    <div class="panel panel-primary">
        <div class="panel-heading">
            <strong>Selection du cycle et du niveau</strong>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="cycle_filter">Cycle</label>
                    <select id="cycle_filter" class="form-control" required>
                        <option value="">-- Selectionner un cycle --</option>
                        @foreach($cycles as $cy)
                            <option value="{{ $cy->id }}">
                                {{ (App::getLocale() == 'en' && isset($cy->nom_anglais) && $cy->nom_anglais) ? $cy->nom_anglais : $cy->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="nniveau_filter">{{ trans('admin.nniveau') }}</label>
                    <select id="nniveau_filter" class="form-control" required disabled>
                        <option value="">-- Selectionner d'abord un cycle --</option>
                    </select>
                </div>

                <div class="col-md-6 form-group">
                    <label for="filiere_filter">Filiere</label>
                    <select id="filiere_filter" class="form-control" required disabled>
                        <option value="">-- Selectionner le cycle et le niveau d'abord --</option>
                        @foreach($niveaux as $n)
                            <option value="{{ $n->id }}">
                                {{ (App::getLocale() == 'en' && isset($n->nom_anglais) && $n->nom_anglais) ? $n->nom_anglais : $n->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <p class="text-muted" style="margin-bottom:0;">
                Choisir le cycle, le niveau et la filiere pour afficher uniquement ses specialites.
            </p>
        </div>
    </div>

    <div class="liste-classe col-md-12">
        <div id="specialite_empty" class="alert alert-info">
            Selectionnez un cycle, un niveau et une filiere pour afficher les specialites.
        </div>

        <div id="specialite_list" style="display:none;">
        <ul>
            @foreach($niveaux as $n)
                <div class="col-md-6 niveau-block" data-filiere="{{$n->id}}" style="display:none;">
                    <h4 class="page-header">Filière : {{ $n->getNom() }}</h4>

                    @foreach($classes as $c)
                        @if($c->id_niveau == $n->id)
                            <div class="col-md-12">
                                <li class="col-md-12">
                                    <a class="special"
                                       href="#"
                                       data-classe="{{ $c->id }}">
                                        {{ $c->getNom() }}
                                    </a>

                                    <a href="#generate_releves_bts"
                                       class="btn btn-xs btn-success"
                                       data-toggle="modal"
                                       data-backdrop="false"
                                       data-classe="{{ $c->id }}"
                                       data-filiere="{{ $n->getNom() }}"
                                       data-specialite="{{ $c->getNom() }}"
                                       onclick="return choose_bts_generation_from_button(this)">
                                        Générer et sauvegarder
                                    </a>
                                </li>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </ul>
        </div>
    </div>

    <div class="modal fade" id="generate_releves_bts">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">x</button>
                    <h4 class="modal-title">Génération sauvegardée des relevés BTS</h4>
                </div>

                <div class="modal-body">
                    <form accept-charset="UTF-8"
                          role="form"
                          method="POST"
                          id="generate_releves_bts_form"
                          action="{{ URL::to('/eleves/releves_bts/generate') }}">

                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" id="bts_id_classe" value="">
                        <input type="hidden" name="ajax" value="1">

                        <p>
                            <strong>Année académique :</strong> {{ session('year') }}<br>
                            <strong>Filière :</strong> <span id="bts_filiere_label"></span><br>
                            <strong>Spécialité :</strong> <span id="bts_specialite_label"></span>
                        </p>

                        <div class="form-group">
                            <label>Cycle</label>
                            <select name="cycle" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($cycles as $cycle)
                                    <option value="{{ $cycle->id }}">{{ $cycle->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Niveau</label>
                            <select name="nniveau" class="form-control" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($nniveaux as $nniveau)
                                    <option value="{{ $nniveau->id }}">{{ $nniveau->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Début de période</label>
                            <input type="date" class="form-control" name="periode_debut">
                        </div>

                        <div class="form-group">
                            <label>Fin de période</label>
                            <input type="date" class="form-control" name="periode_fin">
                        </div>

                        <button class="btn btn-success" type="submit" id="generate_releves_bts_btn">
                            Générer
                        </button>

                        <span id="generate_releves_bts_status"
                              class="text-muted"
                              style="margin-left: 10px;"></span>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <script>
        (function () {
            var nniveaux = [
                @foreach($nniveaux as $nn)
                    {
                        id: '{{ $nn->id }}',
                        cycle: '{{ $nn->id_cycle }}',
                        nom: '{{ addslashes($nn->nom) }}'
                    },
                @endforeach
            ];

            var cycleSelect = document.getElementById('cycle_filter');
            var nniveauSelect = document.getElementById('nniveau_filter');
            var filiereSelect = document.getElementById('filiere_filter');
            var specialiteList = document.getElementById('specialite_list');
            var specialiteEmpty = document.getElementById('specialite_empty');
            var baseUrl = '{{ URL::to('/eleves/releves_bts/print/') }}';

            function refreshNniveaux() {
                var cycleId = cycleSelect.value;
                nniveauSelect.innerHTML = '';
                if (!cycleId) {
                    nniveauSelect.disabled = true;
                    nniveauSelect.innerHTML = '<option value="">-- Selectionner d abord un cycle --</option>';
                    resetFiliere();
                    toggleSpecialites();
                    return;
                }
                nniveauSelect.disabled = false;
                nniveauSelect.innerHTML = '<option value="">-- Selectionner un niveau --</option>';
                for (var i = 0; i < nniveaux.length; i++) {
                    if (nniveaux[i].cycle == cycleId) {
                        var option = document.createElement('option');
                        option.value = nniveaux[i].id;
                        option.text = nniveaux[i].nom;
                        nniveauSelect.appendChild(option);
                    }
                }
                resetFiliere();
                toggleSpecialites();
            }

            function resetFiliere() {
                filiereSelect.value = '';
                filiereSelect.disabled = true;
                filiereSelect.options[0].text = "-- Selectionner le cycle et le niveau d'abord --";
            }

            function refreshFiliere() {
                filiereSelect.value = '';
                filiereSelect.disabled = !(cycleSelect.value && nniveauSelect.value);
                filiereSelect.options[0].text = filiereSelect.disabled ? "-- Selectionner le cycle et le niveau d'abord --" : '-- Selectionner une filiere --';
                toggleSpecialites();
            }

            function toggleSpecialites() {
                var canShow = cycleSelect.value && nniveauSelect.value && filiereSelect.value;
                var blocks = document.getElementsByClassName('niveau-block');
                for (var i = 0; i < blocks.length; i++) {
                    blocks[i].style.display = canShow && blocks[i].getAttribute('data-filiere') == filiereSelect.value ? '' : 'none';
                }
                specialiteList.style.display = canShow ? '' : 'none';
                specialiteEmpty.style.display = canShow ? 'none' : '';
            }

            function openSpecialite(event) {
                event.preventDefault();
                if (!cycleSelect.value || !nniveauSelect.value || !filiereSelect.value) {
                    alert('Veuillez selectionner le cycle, le niveau et la filiere.');
                    return;
                }
                var classeId = this.getAttribute('data-classe');
                window.location.href = baseUrl
                    + '?id=' + encodeURIComponent(classeId)
                    + '&cycle=' + encodeURIComponent(cycleSelect.value)
                    + '&nniveau=' + encodeURIComponent(nniveauSelect.value);
            }

            cycleSelect.onchange = refreshNniveaux;
            nniveauSelect.onchange = refreshFiliere;
            filiereSelect.onchange = toggleSpecialites;

            var links = document.getElementsByClassName('special');
            for (var j = 0; j < links.length; j++) {
                if (links[j].getAttribute('data-classe')) {
                    links[j].onclick = openSpecialite;
                }
            }
        })();

        function choose_bts_generation_from_button(button) {
            if (!document.getElementById('cycle_filter').value || !document.getElementById('nniveau_filter').value || !document.getElementById('filiere_filter').value) {
                alert('Veuillez selectionner le cycle, le niveau et la filiere.');
                return false;
            }

            var idClasse = button.getAttribute('data-classe');
            var filiere = button.getAttribute('data-filiere');
            var specialite = button.getAttribute('data-specialite');

            document.getElementById('bts_id_classe').value = idClasse;
            document.getElementById('bts_filiere_label').innerHTML = filiere;
            document.getElementById('bts_specialite_label').innerHTML = specialite;
            document.getElementById('generate_releves_bts_status').innerHTML = '';
            document.querySelector('#generate_releves_bts_form select[name="cycle"]').value = document.getElementById('cycle_filter').value;
            document.querySelector('#generate_releves_bts_form select[name="nniveau"]').value = document.getElementById('nniveau_filter').value;

            console.log('Classe sélectionnée : ' + idClasse);
            return true;
        }

        (function () {
            var form = document.getElementById('generate_releves_bts_form');

            if (!form) {
                return;
            }

            form.onsubmit = function (event) {
                event.preventDefault();

                var button = document.getElementById('generate_releves_bts_btn');
                var status = document.getElementById('generate_releves_bts_status');
                var idClasse = document.getElementById('bts_id_classe').value;

                if (!idClasse) {
                    alert('Veuillez choisir une spécialité avant de générer.');
                    status.innerHTML = '';
                    return;
                }

                var xhr = new XMLHttpRequest();

                button.disabled = true;
                status.innerHTML = 'Génération en cours...';

                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('Accept', 'application/json');

                xhr.onreadystatechange = function () {
                    if (xhr.readyState !== 4) {
                        return;
                    }

                    button.disabled = false;

                    console.log(xhr.responseText);

                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            var data = JSON.parse(xhr.responseText);

                            if (data.success) {
                                status.innerHTML = data.nb_releves + ' relevé(s) généré(s). Ouverture...';
                                window.location.href = data.url;
                            } else {
                                status.innerHTML = data.message || 'Génération terminée, mais réponse invalide.';
                                alert(data.message || 'Réponse invalide.');
                            }
                        } catch (e) {
                            status.innerHTML = 'Réponse non JSON. Voir console.';
                            alert(xhr.responseText);
                        }
                    } else {
                        status.innerHTML = 'Erreur ' + xhr.status;
                        alert(xhr.responseText);
                    }
                };

                xhr.send(new FormData(form));
            };
        })();
    </script>
@endsection

@section('breadcrumb')
    <li>
        <strong>
            <a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a>
        </strong>
    </li>

    <li class="active">
        <strong>@lang('admin.choix_classe')</strong>
    </li>
@endsection
