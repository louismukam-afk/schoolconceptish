@extends('skeleton')

@section('sub-content')
    <h4>{{ trans('eleve.choix_classe') }}</h4>
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
        <h4 class="page-header">Specialites</h4>

        <div id="specialite_empty" class="alert alert-info">
            Selectionnez un cycle, un niveau et une filiere pour afficher les specialites.
        </div>

        <div class="row" id="specialite_list" style="display:none;">
            @foreach($niveaux as $n)
                <div class="col-md-6 niveau-block" data-filiere="{{ $n->id }}">
                    <h4 class="page-header">{{ (App::getLocale() == 'en') ? $n->nom_anglais : $n->nom }}</h4>

                    <ul class="list-unstyled">
                        @foreach($classes as $c)
                            @if($c->id_niveau == $n->id)
                                <li class="col-md-12" style="margin-bottom:6px;">
                                    <a href="#"
                                       class="specialite-link"
                                       data-classe="{{ $c->id }}">
                                        {{ (App::getLocale() == 'en') ? $c->nom_anglais : $c->nom }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endforeach
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
            var baseUrl = '{{ URL::to('/eleves/notes/matieres/show_ndere/') }}';

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

                if (canShow) {
                    specialiteList.style.display = '';
                    specialiteEmpty.style.display = 'none';
                } else {
                    specialiteList.style.display = 'none';
                    specialiteEmpty.style.display = '';
                }
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

            var links = document.getElementsByClassName('specialite-link');
            for (var j = 0; j < links.length; j++) {
                links[j].onclick = openSpecialite;
            }
        })();
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{ url('eleves/ndere') }}">Notes Ngaoundere</a></strong></li>
    <li class="active"><strong>@lang('admin.choix_classe')</strong></li>
@endsection
