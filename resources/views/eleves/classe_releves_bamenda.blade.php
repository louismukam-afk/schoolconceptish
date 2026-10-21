@extends('skeleton')
@section('sub-content')
<div class="btn-toolbar" style="margin-bottom:15px;">
    <a href="{{ url('/eleves') }}" class="btn btn-default">
        <span class="glyphicon glyphicon-home"></span> Students home
    </a>
    <a href="{{ url('/eleves/releves_bamenda/historique') }}" class="btn btn-warning">
        <span class="glyphicon glyphicon-folder-open"></span> Bamenda transcripts history
    </a>
    <a href="javascript:history.back();" class="btn btn-info">
        <span class="glyphicon glyphicon-arrow-left"></span> Back
    </a>
</div>

    <h4>Choose class</h4>
    <hr>
    @include('eleves.partials.cycle_niveau_filiere_filter', array(
        'selectionBaseUrl' => URL::to('/eleves/releves_bamenda/print/'),
        'selectionTitle' => 'Class selection',
        'selectionLevelLabel' => 'Level',
        'selectionFiliereLabel' => 'Programme',
        'selectionCyclePlaceholder' => '-- Select a cycle --',
        'selectionLevelFirstPlaceholder' => '-- Select a cycle first --',
        'selectionLevelPlaceholder' => '-- Select a level --',
        'selectionFiliereFirstPlaceholder' => '-- Select a level --',
        'selectionFilierePlaceholder' => '-- Select a programme --',
        'selectionEmptyMessage' => 'Select cycle, level and programme first to display specialities.',
        'selectionAlertMessage' => 'Please select cycle, level and programme first.'
    ))
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
                                    <a href="#generate_releves_bamenda" class="btn btn-xs btn-success" data-toggle="modal" data-classe="{{$c->id}}" data-niveau="{{$n->getNom()}}" data-specialite="{{$c->getNom()}}" onclick="return chooseBamendaGeneration(this)">Generate and save</a>
                                </li>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </ul>
    </div>

    <div class="modal fade" id="generate_releves_bamenda">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">x</button><h4 class="modal-title">Saved generation of Bamenda transcripts</h4></div>
            <div class="modal-body">
                <form method="POST" id="generate_releves_bamenda_form" action="{{URL::to('/eleves/releves_bamenda/generate/')}}">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <input type="hidden" name="id" id="bamenda_id_classe">
                    <input type="hidden" name="ajax" value="1">
                    <p><strong>Programme:</strong> <span id="bamenda_filiere"></span><br><strong>Speciality:</strong> <span id="bamenda_specialite"></span></p>
                    <div class="form-group"><label>Cycle</label><select name="cycle" id="bamenda_cycle" class="form-control" required><option value="">-- Select --</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}">{{$cycle->nom}}</option>@endforeach</select></div>
                    <div class="form-group"><label>Level</label><select name="nniveau" id="bamenda_nniveau" class="form-control" required disabled><option value="">-- Select a cycle first --</option>@foreach($nniveaux as $nniveau)<option value="{{$nniveau->id}}" data-cycle="{{$nniveau->id_cycle}}">{{$nniveau->nom}}</option>@endforeach</select></div>
                    <div class="form-group"><label>Period start</label><input type="date" name="periode_debut" class="form-control"></div>
                    <div class="form-group"><label>Period end</label><input type="date" name="periode_fin" class="form-control"></div>
                    <button class="btn btn-success" type="submit" id="generate_releves_bamenda_btn">Generate</button>
                    <span id="generate_releves_bamenda_status" class="text-muted"></span>
                </form>
            </div>
        </div></div>
    </div>

    <script>
        function chooseBamendaGeneration(button) {
            if (!window.selectionCycleNiveauFiliere || !window.selectionCycleNiveauFiliere.hasSelection()) {
                alert('Please select cycle, level and programme first.');
                return false;
            }
            document.getElementById('bamenda_id_classe').value = button.getAttribute('data-classe');
            document.getElementById('bamenda_filiere').innerHTML = button.getAttribute('data-niveau');
            document.getElementById('bamenda_specialite').innerHTML = button.getAttribute('data-specialite');
            document.getElementById('generate_releves_bamenda_status').innerHTML = '';
            window.selectionCycleNiveauFiliere.setModalValues('#bamenda_cycle', '#bamenda_nniveau');
            return true;
        }
        (function () {
            function updateChosen(select) {
                if (window.jQuery) {
                    window.jQuery(select).trigger('chosen:updated');
                }
            }

            function refreshBamendaNiveau() {
                var cycleId = document.getElementById('bamenda_cycle').value;
                var niveau = document.getElementById('bamenda_nniveau');
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
                niveau.options[0].text = cycleId ? '-- Select a level --' : '-- Select a cycle first --';
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
                    $('#bamenda_cycle').on('change', refreshBamendaNiveau);
                    $(document).on('change', '#cycle_select_classe', refreshChooseLevelNiveau);
                    $(document).on('shown.bs.modal', '#choose_level', refreshChooseLevelNiveau);
                });
            }
        })();
        document.getElementById('generate_releves_bamenda_form').onsubmit = function (event) {
            event.preventDefault();
            var form = this, button = document.getElementById('generate_releves_bamenda_btn'), status = document.getElementById('generate_releves_bamenda_status');
            button.disabled = true; status.innerHTML = ' Generation in progress...';
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
                        status.innerHTML = ' ' + data.nb_releves + ' transcript(s) saved.';
                        window.location.href = data.url;
                    } else {
                        status.innerHTML = ' Error: ' + (data.message || 'generation failed');
                    }
                } catch (e) { status.innerHTML = ' Invalid server response.'; }
            };
            xhr.send(new FormData(form));
        };
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li class="active"><strong>Choose class</strong></li>
@endsection
