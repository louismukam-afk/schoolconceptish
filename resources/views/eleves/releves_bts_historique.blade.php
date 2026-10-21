@extends('skeleton')

@section('sub-content')
<div class="col-md-12">
    <div class="btn-toolbar" style="margin-bottom: 15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/releves_bts') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Page BTS
        </a>
        @if($classe)
            <a href="{{ url('/eleves/releves_bts/print?id='.$classe->id.'&cycle='.($cycle ? $cycle->id : '').'&nniveau='.($nniveau ? $nniveau->id : '')) }}" class="btn btn-primary">
                <span class="glyphicon glyphicon-plus"></span> Generer un releve
            </a>
        @endif
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="panel panel-primary">
        <div class="panel-heading"><strong>Historique des releves BTS</strong></div>
        <div class="panel-body">
            <p>
                @if($classe)<strong>Specialite :</strong> {{ $classe->nom }}@else<strong>Specialites :</strong> Toutes les specialites BTS @endif
                @if($cycle) | <strong>Cycle :</strong> {{ $cycle->nom }} @endif
                @if($nniveau) | <strong>Niveau :</strong> {{ $nniveau->nom }} @endif
            </p>

            @if(count($generations))
                <div class="form-group">
                    <label for="bts-history-search">Rechercher un releve</label>
                    <input type="search" id="bts-history-search" class="form-control" placeholder="Specialite, annee, cycle, niveau ou periode">
                </div>
                <div class="table-responsive">
                    <table id="bts-history-table" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Date de generation</th>
                            <th>Specialite</th>
                            <th>Cycle / Niveau</th>
                            <th>Annee academique</th>
                            <th>Periode</th>
                            <th>Nombre de releves</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($generations as $generation)
                            <tr>
                                <td>{{ $generation->created_at ? date('d/m/Y H:i', strtotime($generation->created_at)) : '-' }}</td>
                                <td>{{ $generation->classe ? $generation->classe->nom : '-' }}</td>
                                <td>{{ $generation->cycle ? $generation->cycle->nom : '-' }} / {{ $generation->nniveau ? $generation->nniveau->nom : '-' }}</td>
                                <td>{{ $generation->annee_academique }}</td>
                                <td>{{ $generation->periode_debut ? $generation->periode_debut : '...' }} au {{ $generation->periode_fin ? $generation->periode_fin : '...' }}</td>
                                <td>{{ $generation->nb_releves }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm" href="{{ url('/eleves/releves_bts/generation/'.$generation->id.'/print') }}" target="_blank">
                                        <span class="glyphicon glyphicon-print"></span> Ouvrir / imprimer
                                    </a>
                                    <a class="btn btn-danger btn-sm" href="{{ url('/eleves/releves_bts/generation/'.$generation->id.'/destroy') }}" onclick="return confirm('Supprimer cet historique de releve BTS ?');">
                                        <span class="glyphicon glyphicon-trash"></span> Supprimer
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Aucun releve BTS sauvegarde.</div>
            @endif
        </div>
    </div>
</div>

@if(count($generations))
<script>
    $(function () {
        var btsHistoryTable = $('#bts-history-table').dataTable({
            dom: 'rtip',
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                emptyTable: 'Aucun releve trouve',
                info: '_START_ a _END_ sur _TOTAL_ releves',
                infoEmpty: 'Aucun releve',
                infoFiltered: '(filtre sur _MAX_ releves)',
                paginate: { first: 'Premier', last: 'Dernier', next: 'Suivant', previous: 'Precedent' }
            }
        });

        $('#bts-history-search').on('keyup change', function () {
            btsHistoryTable.fnFilter(this.value);
        });
    });
</script>
@endif
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/releves_bts')}}">@lang('admin.choix_classe')</a></strong></li>
    <li class="active"><strong>Historique des releves BTS</strong></li>
@endsection
