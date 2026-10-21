@extends('skeleton')
@section('sub-content')
<div class="btn-toolbar" style="margin-bottom:15px;">
    <a href="{{ url('/eleves') }}" class="btn btn-default">
        <span class="glyphicon glyphicon-home"></span> Accueil eleves
    </a>
    <a href="{{ url('/eleves/releve_estic/historique') }}" class="btn btn-warning">
        <span class="glyphicon glyphicon-folder-open"></span> Historique des releves Ebolowa
    </a>
    <a href="{{ url('/eleves/attestations_reussite_ebolowa') }}" class="btn btn-success">
        <span class="glyphicon glyphicon-certificate"></span> Attestations Ebolowa
    </a>
    <a href="javascript:history.back();" class="btn btn-info">
        <span class="glyphicon glyphicon-arrow-left"></span> Retour
    </a>
</div>

    <h4>{{trans('eleve.choix_classe')}}</h4>
    <hr>
    @include('eleves.partials.cycle_niveau_filiere_filter', array('selectionBaseUrl' => URL::to('/eleves/releve_estic/print/')))
    <div class="liste-classe col-md-12">
        <ul>
            @foreach($niveaux as $n)
                <div class="col-md-6 niveau-block" data-filiere="{{$n->id}}" style="display:none;">
                    <h4 class="page-header"> {{$n->getNom()}}</h4>

                    @foreach($classes as $c)
                        <div class="col-md-12">
                            @if($c->id_niveau==$n->id)
                                <li class="col-md-12">
                                    <a class="special cycle-niveau-specialite-link" href="#" data-classe="{{$c->id}}">{{$c->getNom()}}</a>
                                    <a href="#generate_releves_estic" class="btn btn-xs btn-success" data-toggle="modal" data-classe="{{$c->id}}" data-niveau="{{$n->getNom()}}" data-specialite="{{$c->getNom()}}" onclick="return chooseEsticGeneration(this)">Generer et sauvegarder</a>
                                </li>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </ul>
    </div>

    <div class="modal fade" id="generate_releves_estic">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">x</button><h4 class="modal-title">Generation sauvegardee des releves Ebolowa</h4></div>
            <div class="modal-body">
                <form method="POST" id="generate_releves_estic_form" action="{{URL::to('/eleves/releve_estic/generate/')}}">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="id" id="estic_id_classe">
                    <input type="hidden" name="ajax" value="1">
                    <p><strong>Filiere :</strong> <span id="estic_filiere"></span><br><strong>Specialite :</strong> <span id="estic_specialite"></span></p>
                    <div class="form-group"><label>Cycle</label><select name="cycle" id="estic_cycle" class="form-control" required><option value="">-- Selectionner --</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}">{{$cycle->nom}}</option>@endforeach</select></div>
                    <div class="form-group"><label>Niveau</label><select name="nniveau" id="estic_nniveau" class="form-control" required disabled><option value="">-- Selectionner un cycle d'abord --</option>@foreach($nniveaux as $nniveau)<option value="{{$nniveau->id}}" data-cycle="{{$nniveau->id_cycle}}">{{$nniveau->nom}}</option>@endforeach</select></div>
                    <div class="form-group"><label>Debut de periode</label><input type="date" name="periode_debut" class="form-control"></div>
                    <div class="form-group"><label>Fin de periode</label><input type="date" name="periode_fin" class="form-control"></div>
                    <button class="btn btn-success" type="submit" id="generate_releves_estic_btn">Generer</button>
                    <span id="generate_releves_estic_status" class="text-muted"></span>
                </form>
            </div>
        </div></div>
    </div>

    <script>
        function chooseEsticGeneration(button) {
            if (!window.selectionCycleNiveauFiliere || !window.selectionCycleNiveauFiliere.hasSelection()) {
                alert("Veuillez selectionner le cycle, le niveau et la filiere d'abord.");
                return false;
            }
            document.getElementById('estic_id_classe').value = button.getAttribute('data-classe');
            document.getElementById('estic_filiere').innerHTML = button.getAttribute('data-niveau');
            document.getElementById('estic_specialite').innerHTML = button.getAttribute('data-specialite');
            document.getElementById('generate_releves_estic_status').innerHTML = '';
            window.selectionCycleNiveauFiliere.setModalValues('#estic_cycle', '#estic_nniveau');
            return true;
        }
        document.getElementById('estic_cycle').onchange = function () {
            var cycleId = this.value;
            var niveau = document.getElementById('estic_nniveau');
            var options = niveau.options;
            niveau.value = '';
            niveau.disabled = !cycleId;
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                if (!option.value) {
                    option.hidden = false;
                    continue;
                }
                option.hidden = option.getAttribute('data-cycle') !== cycleId;
            }
            niveau.options[0].text = cycleId ? '-- Selectionner un niveau --' : "-- Selectionner un cycle d'abord --";
        };
        (function () {
            function updateChosen(select) {
                if (window.jQuery) {
                    window.jQuery(select).trigger('chosen:updated');
                }
            }

            function refreshEsticNiveau() {
                var cycleId = document.getElementById('estic_cycle').value;
                var niveau = document.getElementById('estic_nniveau');
                if (!niveau) return;

                niveau.value = '';
                niveau.disabled = !cycleId;
                for (var i = 0; i < niveau.options.length; i++) {
                    var option = niveau.options[i];
                    if (!option.value) {
                        option.style.display = '';
                        continue;
                    }
                    option.style.display = option.getAttribute('data-cycle') === cycleId ? '' : 'none';
                }
                niveau.options[0].text = cycleId ? '-- Selectionner un niveau --' : "-- Selectionner un cycle d'abord --";
                updateChosen(niveau);
            }

            function refreshChooseLevelNiveau() {
                var cycle = document.getElementById('cycle_select_classe');
                var niveau = document.getElementById('nniveau_select_classe');
                if (!cycle || !niveau) return;

                niveau.disabled = !cycle.value;
                updateChosen(niveau);
            }

            if (window.jQuery) {
                window.jQuery(function ($) {
                    $('#estic_cycle').on('change', refreshEsticNiveau);
                    $(document).on('change', '#cycle_select_classe', refreshChooseLevelNiveau);
                    $(document).on('shown.bs.modal', '#choose_level', refreshChooseLevelNiveau);
                });
            } else {
                var esticCycle = document.getElementById('estic_cycle');
                var globalCycle = document.getElementById('cycle_select_classe');
                if (esticCycle) esticCycle.addEventListener('change', refreshEsticNiveau);
                if (globalCycle) globalCycle.addEventListener('change', refreshChooseLevelNiveau);
            }
        })();
        document.getElementById('generate_releves_estic_form').onsubmit = function (event) {
            event.preventDefault();
            var form = this, button = document.getElementById('generate_releves_estic_btn'), status = document.getElementById('generate_releves_estic_status');
            button.disabled = true; status.innerHTML = ' Generation en cours...';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;
                button.disabled = false;
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300 && data.success) {
                        status.innerHTML = ' ' + data.nb_releves + ' releve(s) sauvegarde(s).';
                        window.location.href = data.url;
                    } else {
                        status.innerHTML = ' Erreur : ' + (data.message || 'generation impossible');
                    }
                } catch (e) { status.innerHTML = ' Reponse serveur invalide.'; }
            };
            xhr.send(new FormData(form));
        };
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li class="active"><strong>@lang('admin.choix_classe')</strong></li>
@endsection
