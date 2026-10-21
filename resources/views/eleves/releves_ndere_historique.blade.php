@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{url('/eleves')}}" class="btn btn-default"><span class="glyphicon glyphicon-home"></span> Accueil eleves</a>
        <a href="{{url('/eleves/releves_ndere')}}" class="btn btn-default"><span class="glyphicon glyphicon-home"></span> Page Ngaoundere</a>
        <a href="javascript:history.back();" class="btn btn-info"><span class="glyphicon glyphicon-arrow-left"></span> Retour</a>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading"><strong>Historique des releves Ngaoundere</strong></div>
        <div class="panel-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <input type="search" id="ndere-history-search" class="form-control" placeholder="Rechercher...">
            <br>
            <table id="ndere-history-table" class="table table-striped table-bordered">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Specialite</th>
                    <th>Cycle / niveau</th>
                    <th>Annee</th>
                    <th>Semestre</th>
                    <th>Periode</th>
                    <th>Releves</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($generations as $generation)
                    <?php
                        $payload = json_decode($generation->contenu);
                        $semesterValue = $generation->semestre ? (int) $generation->semestre : (isset($payload->trimestre) ? (int) $payload->trimestre : null);
                        $sequenceValue = $semesterValue && $semesterValue % 2 == 0 ? 6 : 5;
                        $semesterClass = $semesterValue === 6 ? 'label-success' : 'label-info';
                    ?>
                    <tr>
                        <td>{{$generation->created_at ? date('d/m/Y H:i', strtotime($generation->created_at)) : '-'}}</td>
                        <td>{{$generation->classe ? $generation->classe->nom : '-'}}</td>
                        <td>{{ $generation->cycle ? $generation->cycle->nom : '-' }} / {{ $generation->nniveau ? $generation->nniveau->nom : '-' }}</td>
                        <td>{{$generation->annee_academique}}</td>
                        <td>
                            @if($semesterValue)
                                <span class="label {{ $semesterClass }}">Semestre {{ $semesterValue }}</span>
                                <small>(sequence {{ $sequenceValue }})</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $generation->periode_debut ? $generation->periode_debut : '...' }} au {{ $generation->periode_fin ? $generation->periode_fin : '...' }}</td>
                        <td>{{$generation->nb_releves}}</td>
                        <td>
                            <a class="btn btn-primary btn-xs" href="{{url('/eleves/releves_ndere/generation/'.$generation->id.'/print')}}">
                                <span class="glyphicon glyphicon-print"></span> Ouvrir / imprimer
                            </a>
                            <a class="btn btn-danger btn-xs" href="{{url('/eleves/releves_ndere/generation/'.$generation->id.'/destroy')}}" onclick="return confirm('Supprimer cet historique de releve ?');">
                                <span class="glyphicon glyphicon-trash"></span> Supprimer
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
$(function () {
    var table = $('#ndere-history-table').dataTable({dom:'rtip', pageLength:25, order:[[0,'desc']]});
    $('#ndere-history-search').on('keyup change', function () { table.fnFilter(this.value); });
});
</script>
@endsection
