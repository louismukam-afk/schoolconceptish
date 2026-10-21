@extends('skeleton')

@section('sub-content')
<div class="col-md-12">
    <div class="btn-toolbar" style="margin-bottom: 15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/releves_soa') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Page SOA
        </a>
        @if($classe)
            <a href="{{ url('/eleves/releves_soa/print?id='.$classe->id.'&cycle='.($cycle ? $cycle->id : '').'&nniveau='.($nniveau ? $nniveau->id : '')) }}" class="btn btn-primary">
                <span class="glyphicon glyphicon-plus"></span> Générer un relevé
            </a>
        @endif
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading"><strong>Historique des relevés SOA</strong></div>
        <div class="panel-body">
            <p>
                @if($classe)<strong>Spécialité :</strong> {{ $classe->nom }}@else<strong>Spécialités :</strong> Toutes les spécialités SOA @endif
                @if($cycle) | <strong>Cycle :</strong> {{ $cycle->nom }} @endif
                @if($nniveau) | <strong>Niveau :</strong> {{ $nniveau->nom }} @endif
            </p>
            @if(count($generations))
                <div class="form-group">
                    <label for="soa-history-search">Rechercher un relevé</label>
                    <input type="search" id="soa-history-search" class="form-control" placeholder="Spécialité, année, cycle, niveau ou période">
                </div>
                <div class="table-responsive">
                    <table id="soa-history-table" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Date de génération</th>
                            <th>Spécialité</th>
                            <th>Cycle / Niveau</th>
                            <th>Année académique</th>
                            <th>Période</th>
                            <th>Nombre de relevés</th>
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
                                    <a class="btn btn-primary btn-sm" href="{{ url('/eleves/releves_soa/generation/'.$generation->id.'/print') }}">
                                        <span class="glyphicon glyphicon-print"></span> Ouvrir / imprimer
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">Aucun relevé SOA sauvegardé pour cette spécialité.</div>
            @endif
        </div>
    </div>
</div>
@if(count($generations))
<script>
    $(function () {
        var soaHistoryTable = $('#soa-history-table').dataTable({
            dom: 'rtip',
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                emptyTable: 'Aucun relevé trouvé',
                info: '_START_ à _END_ sur _TOTAL_ relevés',
                infoEmpty: 'Aucun relevé',
                infoFiltered: '(filtré sur _MAX_ relevés)',
                paginate: { first: 'Premier', last: 'Dernier', next: 'Suivant', previous: 'Précédent' }
            }
        });

        $('#soa-history-search').on('keyup change', function () {
            soaHistoryTable.fnFilter(this.value);
        });
    });
</script>
@endif
@endsection
