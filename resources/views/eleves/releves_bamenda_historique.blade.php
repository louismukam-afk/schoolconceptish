@extends('skeleton')

@section('sub-content')
<div class="col-md-12">
    <div class="btn-toolbar" style="margin-bottom: 15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Students home
        </a>
        <a href="{{ url('/eleves/releves_bamenda') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Bamenda page
        </a>
        @if($classe)
            <a href="{{ url('/eleves/releves_bamenda/print?id='.$classe->id.'&cycle='.($cycle ? $cycle->id : '').'&nniveau='.($nniveau ? $nniveau->id : '')) }}" class="btn btn-primary">
                <span class="glyphicon glyphicon-plus"></span> Generate transcript
            </a>
        @endif
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="panel panel-primary">
        <div class="panel-heading"><strong>Bamenda transcripts history</strong></div>
        <div class="panel-body">
            <p>
                @if($classe)<strong>Speciality:</strong> {{ $classe->nom }}@else<strong>Specialities:</strong> All Bamenda specialities @endif
                @if($cycle) | <strong>Cycle:</strong> {{ $cycle->nom }} @endif
                @if($nniveau) | <strong>Level:</strong> {{ $nniveau->nom }} @endif
            </p>
            @if(count($generations))
                <div class="form-group">
                    <input type="text" id="history-search" class="form-control" placeholder="Search...">
                </div>
                <table class="table table-bordered table-striped" id="history-table">
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Academic year</th>
                        <th>Speciality</th>
                        <th>Cycle</th>
                        <th>Level</th>
                        <th>Transcripts</th>
                        <th>Period</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($generations as $generation)
                        <tr>
                            <td>{{ $generation->created_at }}</td>
                            <td>{{ $generation->annee_academique }}</td>
                            <td>{{ $generation->classe ? $generation->classe->nom : $generation->id_classe }}</td>
                            <td>{{ $generation->cycle ? $generation->cycle->nom : $generation->id_cycle }}</td>
                            <td>{{ $generation->nniveau ? $generation->nniveau->nom : $generation->id_nniveau }}</td>
                            <td>{{ $generation->nb_releves }}</td>
                            <td>
                                @if($generation->periode_debut || $generation->periode_fin)
                                    {{ $generation->periode_debut }} - {{ $generation->periode_fin }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-xs btn-primary" href="{{ url('/eleves/releves_bamenda/generation/'.$generation->id.'/print') }}" target="_blank">
                                    <span class="glyphicon glyphicon-print"></span> Open
                                </a>
                                <a class="btn btn-xs btn-danger" href="{{ url('/eleves/releves_bamenda/generation/'.$generation->id.'/destroy') }}" onclick="return confirm('Delete this transcript history?');">
                                    <span class="glyphicon glyphicon-trash"></span> Delete
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-info">No Bamenda transcript history found.</div>
            @endif
        </div>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('history-search');
        var table = document.getElementById('history-table');
        if (!input || !table) return;
        input.onkeyup = function () {
            var filter = input.value.toLowerCase();
            var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            for (var i = 0; i < rows.length; i++) {
                rows[i].style.display = rows[i].innerText.toLowerCase().indexOf(filter) >= 0 ? '' : 'none';
            }
        };
    })();
</script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li class="active"><strong>Bamenda transcripts history</strong></li>
@endsection
