@extends('skeleton')

@section('sub-content')
    <style>
        .student-panel { margin-bottom: 12px; }
        .student-panel .panel-heading { font-weight: bold; }
        .delib-table th, .delib-table td { vertical-align: middle !important; }
        .target-input { max-width: 90px; }
    </style>

    @if(session('success'))
        <div class="alert alert-success">{{ trans('admin.succes') }}</div>
    @endif

    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/notes/matieres/show').'?id='.$classe->id.'&cycle='.$id_cycle.'&nniveau='.$nniveau->id }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Page PV
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>

    <h3>{{ $titre }}</h3>
    <p>
        <strong>Classe :</strong> {{ $classe->nom }} |
        <strong>Cycle :</strong> {{ $cycle->nom }} |
        <strong>Niveau :</strong> {{ $nniveau->nom }} |
        <strong>Mode :</strong> {{ $mode == 'rattrapage' ? 'Rattrapage' : 'Semestriel' }} |
        <strong>Note cible :</strong> {{ number_format($target_note, 2) }}/20
    </p>

    <form method="POST" action="{{ url('/eleves/notes/deliberations/apply') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id_classe" value="{{ $classe->id }}">
        <input type="hidden" name="cycle" value="{{ $id_cycle }}">
        <input type="hidden" name="nniveau" value="{{ $nniveau->id }}">
        <input type="hidden" name="semestre" value="{{ $semestre }}">
        <input type="hidden" name="mode" value="{{ $mode }}">

        <div class="well well-sm">
            <label>Note cible générale /20</label>
            <input type="number" step="0.01" min="0" max="20" value="{{ number_format($target_note, 2, '.', '') }}" class="form-control target-input" id="global_target_note" onchange="applyGlobalTargetNote()">
        </div>

        <?php $idx = 0; ?>
        @foreach($results as $r)
            <div class="panel panel-default student-panel">
                <div class="panel-heading">
                    {{ $r['eleve']->matricule }} - {{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}
                    <span class="pull-right">Moy S{{ $semestre }} : {{ $semestre == 1 ? number_format($r['moyenne_s1'], 2) : number_format($r['moyenne_s2'], 2) }}</span>
                </div>
                <div class="panel-body">
                    <table class="table table-bordered table-condensed delib-table">
                        <thead>
                        <tr>
                            <th>Appliquer</th>
                            <th>Matiere</th>
                            <th>Note actuelle /20</th>
                            <th>Session</th>
                            <th>Credit</th>
                            <th>Note cible /20</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($matieres as $m)
                            @if(($semestre == 1 && !empty($m->has_s1)) || ($semestre == 2 && !empty($m->has_s2)))
                                <?php $mm = isset($r['matieres'][$m->id_matiere]['s'.$semestre]) ? $r['matieres'][$m->id_matiere]['s'.$semestre] : null; ?>
                                @if(!$mm || !$mm['valide'])
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="apply[{{ $idx }}]" value="1">
                                            <input type="hidden" name="eleve_id[{{ $idx }}]" value="{{ $r['eleve']->id }}">
                                            <input type="hidden" name="matiere_id[{{ $idx }}]" value="{{ $m->id_matiere }}">
                                            <input type="hidden" name="session_id[{{ $idx }}]" value="{{ $m->id_session }}">
                                        </td>
                                        <td>{{ $m->nom }}</td>
                                        <td>{{ $mm ? number_format($mm['note20'], 2) : '0.00' }}</td>
                                        <td>{{ $mm ? $mm['session_validation'] : 'Non validee' }}</td>
                                        <td>{{ number_format($m->credit, 0) }}</td>
                                        <td><input type="number" step="0.01" min="0" max="20" name="target_note[{{ $idx }}]" value="{{ number_format($target_note, 2, '.', '') }}" class="form-control target-input deliberation-target"></td>
                                    </tr>
                                    <?php $idx++; ?>
                                @endif
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        @if($idx > 0)
            <button type="submit" class="btn btn-success btn-lg">Appliquer les deliberations cochees</button>
        @else
            <div class="alert alert-success">Aucune matiere a deliberer pour cette selection.</div>
        @endif
    </form>

    <script>
        function applyGlobalTargetNote() {
            var globalValue = document.getElementById('global_target_note').value;
            var inputs = document.getElementsByClassName('deliberation-target');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].value = globalValue;
            }
        }
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{ url('eleves/notes') }}">Notes</a></strong></li>
    <li class="active"><strong>Deliberations</strong></li>
@endsection
