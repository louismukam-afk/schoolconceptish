@extends('skeleton')
@section('sub-content')
    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/attestations_reussite_ndere/historique') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-folder-open"></span> Historique des attestations
        </a>
        <a href="{{ url('/eleves/attestations_reussite_soa') }}" class="btn btn-success">
            <span class="glyphicon glyphicon-certificate"></span> Attestations SOA
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>

    <h4>Choix de la specialite pour les attestations de reussite Ngaoundere</h4>
    <hr>

    <?php
        $selectionTitle = 'Selection de la specialite';
        $selectionEmptyMessage = "Selectionnez d'abord le cycle, le niveau et la filiere pour afficher les specialites.";
    ?>
    @include('eleves.partials.cycle_niveau_filiere_filter')

    <div class="liste-classe col-md-12">
        <ul id="specialite_list" style="display:none;">
            @foreach($cycles as $cy)
                <h3 class="page-header col-md-12">{{$cy->nom}}</h3>
                @foreach($niveaux as $n)
                    <div class="col-md-6 niveau-block" data-cycle="{{$cy->id}}" data-filiere="{{$n->id}}" style="display:none;">
                        <h4 class="page-header">{{(App::getLocale()=='en')?$n->nom_anglais:$n->nom}}</h4>
                        @foreach($classes as $c)
                            @if($c->id_niveau==$n->id)
                                <li class="col-md-12">
                                    <a href="#generate_attestations_ndere"
                                       data-toggle="modal"
                                       data-backdrop="false"
                                       class="btn btn-xs btn-success"
                                       data-classe="{{$c->id}}"
                                       data-specialite="{{(App::getLocale()=='en' && $c->nom_anglais)?$c->nom_anglais:$c->nom}}"
                                       onclick="return prepareAttestationNdereGeneration(this);">
                                        <span class="glyphicon glyphicon-certificate"></span>
                                        Generer les attestations
                                    </a>
                                    {{(App::getLocale()=='en' && $c->nom_anglais)?$c->nom_anglais:$c->nom}}
                                </li>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            @endforeach
        </ul>
    </div>

    <div class="modal fade" id="generate_attestations_ndere">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">x</button>
                    <h4 class="modal-title">Generer les attestations de reussite Ngaoundere</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" id="generate_attestations_ndere_form" action="{{URL::to('/eleves/attestations_reussite_ndere/generate/')}}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="id" id="attestation_ndere_classe">
                        <input type="hidden" name="ajax" value="1">

                        <p><strong>Specialite :</strong> <span id="attestation_ndere_specialite"></span></p>
                        <div class="form-group">
                            <label>Cycle</label>
                            <select name="cycle" id="attestation_ndere_cycle" class="form-control" required>
                                <option value="">-- Selectionner --</option>
                                @foreach($cycles as $cycle)
                                    <option value="{{$cycle->id}}">{{$cycle->nom}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Niveau</label>
                            <select name="nniveau" id="attestation_ndere_nniveau" class="form-control" required disabled>
                                <option value="">-- Selectionner un cycle d'abord --</option>
                                @foreach($nniveaux as $nn)
                                    <option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}">{{$nn->nom}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Debut de periode</label>
                                    <input type="date" name="periode_debut" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Fin de periode</label>
                                    <input type="date" name="periode_fin" class="form-control">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success" id="attestation_ndere_btn">Generer</button>
                        <span id="attestation_ndere_status" class="text-muted"></span>
                    </form>
                    <div id="attestation_ndere_message" class="alert" style="display:none;margin-top:10px;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function prepareAttestationNdereGeneration(link) {
            if (!window.selectionCycleNiveauFiliere || !window.selectionCycleNiveauFiliere.hasSelection()) {
                alert("Veuillez selectionner le cycle, le niveau et la filiere d'abord.");
                return false;
            }

            var classe = document.getElementById('attestation_ndere_classe');
            var label = document.getElementById('attestation_ndere_specialite');
            if (classe) classe.value = link.getAttribute('data-classe');
            if (label) label.innerHTML = link.getAttribute('data-specialite') || '';
            window.selectionCycleNiveauFiliere.setModalValues('#attestation_ndere_cycle', '#attestation_ndere_nniveau');
            return true;
        }

        (function () {
            function updateLevels() {
                var cycle = document.getElementById('attestation_ndere_cycle');
                var niveau = document.getElementById('attestation_ndere_nniveau');
                if (!cycle || !niveau) return;
                var cycleId = cycle.value;
                niveau.disabled = !cycleId;
                for (var i = 0; i < niveau.options.length; i++) {
                    var option = niveau.options[i];
                    if (!option.value) {
                        option.style.display = '';
                        continue;
                    }
                    option.style.display = option.getAttribute('data-cycle') === cycleId ? '' : 'none';
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var cycle = document.getElementById('attestation_ndere_cycle');
                var form = document.getElementById('generate_attestations_ndere_form');
                if (cycle) cycle.addEventListener('change', updateLevels);
                if (!form) return;

                form.addEventListener('submit', function (event) {
                    event.preventDefault();
                    var button = document.getElementById('attestation_ndere_btn');
                    var status = document.getElementById('attestation_ndere_status');
                    if (button) button.disabled = true;
                    if (status) status.innerHTML = ' Generation en cours...';

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', form.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState !== 4) return;
                        if (button) button.disabled = false;
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                                window.location.href = data.url;
                            } else if (status) {
                                status.innerHTML = ' Erreur : ' + (data.message || 'generation impossible');
                            }
                        } catch (e) {
                            if (status) status.innerHTML = ' Reponse serveur invalide. ' + (xhr.status ? 'HTTP ' + xhr.status : '');
                        }
                    };
                    xhr.send(new FormData(form));
                });
            });
        })();
    </script>
@endsection
