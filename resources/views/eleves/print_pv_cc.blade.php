@extends('skeleton')

@section('sub-content')

    <style>
        table{
            width:100%;
            border-collapse:collapse;
            font-size:12px;
        }
        th,td{
            border:1px solid #000;
            padding:4px;
            text-align:center;
        }
        th{
            background:#f2f2f2;
        }
        @media print{
            .no-print{display:none;}
        }
    </style>

    <style>
        .note-column { text-align:center; }
        .gris { background:#e0e0e0; color:#666; }
    </style>

    <style media="print">
        #note-excel, .dt-buttons,
        #note-excel_filter,
        #note-excel_info,
        #note-excel_paginate { display:none; }
    </style>
    <style>
        .table-ecran { display:block; }
        .table-print { display:block; }
        .note-column { text-align:center; }
        .gris { background:#e0e0e0; color:#777; }
    </style>

    <style media="print">
        body * { visibility:hidden; }

        .table-print,
        .table-print * {
            visibility:visible;
        }

        .table-print {
            position:absolute;
            top:0;
            left:0;
            width:100%;
        }

        .table-ecran { display:none; }
        .btn, .dt-buttons { display:none !important; }
    </style>
    <div style="text-align:center;margin-bottom:10px;">
        <H1>CENTRE DE FORMATION HINTEL</H1>
        <strong>PROCÈS VERBAL DES CONTRÔLES CONTINUS {{ $sequence }}</strong><br>
        Année académique : {{ $annee }}
    </div>

    <div style="margin-bottom:10px;">
        Classe : {{ $id_classe_obj->nom }} —
        Niveau : {{ $nniveau_obj->nom }} —
        Cycle : {{ $id_cycle_obj->nom }}
    </div>

    <button class="btn btn-warning no-print" onclick="window.print()">Imprimer</button>

    <table id="note-excel" class="table table-bordered table-striped table-condensed">
        <thead>
        <tr>
            <th rowspan="2">#</th>
            <th rowspan="2">Matricule</th>
            <th rowspan="2">Nom & Prénom</th>
            @foreach($matieres as $m)
                <th>{{ $m->nom }}</th>
            @endforeach
            <th rowspan="2">Moy /20</th>
            <th rowspan="2">MGP</th>
            <th rowspan="2">Crédits Prévu</th>
            <th rowspan="2">Crédits Validés</th>
            <th rowspan="2">Non validées</th>
            <th rowspan="2">Rang</th>
        </tr>
        <tr>
            @foreach($matieres as $m)
                <th>CC /20</th>
            @endforeach
        </tr>
        </thead>

        <tbody>
        @foreach($results as $i => $r)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $r['eleve']->matricule }}</td>
                <td>{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>

                @foreach($r['cc'] as $note)
                    <td>{{ $note }}</td>
                @endforeach

                <td>{{ $r['moyenne'] }}</td>
                <td>{{ $r['mgp'] }}</td>
                <td>{{ $r['credit_prevus'] }}</td>
                <td>{{ $r['credit_valides'] }}</td>
                <td>{{ implode(', ', $r['non_valides']) }}</td>
                <td>{{ $r['rang'] }}</td>
            </tr>
        @endforeach
        </tbody>

        <tfoot>
        <tr>
            <th colspan="3">Crédits</th>
            @foreach($matieres as $m)
                <th>{{ $m->credit }}</th>
            @endforeach
            <th colspan="7"></th>
        </tr>
        </tfoot>
    </table>
    <div class="table table-print">
    <div style="text-align:center;margin-bottom:10px;">
        <H1>CENTRE DE FORMATION HINTEL</H1>
        <strong>PROCÈS VERBAL DES CONTRÔLES CONTINUS {{ $sequence }}</strong><br>
        Année académique : {{ $annee }}
    </div>

    <div style=" text-align:center; margin-bottom:10px;">
        Classe : {{ $id_classe_obj->nom }} —
        Niveau : {{ $nniveau_obj->nom }} —
        Cycle : {{ $id_cycle_obj->nom }}
    </div>

    <button class="btn btn-warning no-print" onclick="window.print()">Imprimer</button>

    <table class="table table-bordered table-condensed">
        <thead>
        <tr>
            <th rowspan="2">#</th>
            <th rowspan="2">Matricule</th>
            <th rowspan="2">Nom & Prénom</th>
            @foreach($matieres as $m)
                <th>{{ $m->nom }}</th>
            @endforeach
            <th rowspan="2">Moy /20</th>
            <th rowspan="2">MGP</th>
            <th rowspan="2">Crédits Prévu</th>
            <th rowspan="2">Crédits Validés</th>
            <th rowspan="2">Non validées</th>
            <th rowspan="2">Rang</th>
        </tr>
        <tr>
            @foreach($matieres as $m)
                <th>CC /20</th>
            @endforeach
        </tr>
        </thead>

        <tbody>
        @foreach($results as $i => $r)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $r['eleve']->matricule }}</td>
                <td>{{ $r['eleve']->nom }} {{ $r['eleve']->prenom }}</td>

                @foreach($r['cc'] as $note)
                    <td>{{ $note }}</td>
                @endforeach

                <td>{{ $r['moyenne'] }}</td>
                <td>{{ $r['mgp'] }}</td>
                <td>{{ $r['credit_prevus'] }}</td>
                <td>{{ $r['credit_valides'] }}</td>
                <td>{{ implode(', ', $r['non_valides']) }}</td>
                <td>{{ $r['rang'] }}</td>
            </tr>
        @endforeach
        </tbody>

        <tfoot>
        <tr>
            <th colspan="3">Crédits</th>
            @foreach($matieres as $m)
                <th>{{ $m->credit }}</th>
            @endforeach
            <th colspan="7"></th>
        </tr>
        </tfoot>
    </table>
    </div>

<script>
    $('#note-excel').dataTable({
        dom: 'Bfrtip',
        pageLength: 2000,
        buttons: [{
            extend: 'excelHtml5',
            title: 'PV Controle continus {{ $sequence }} - {{ $id_classe_obj->nom}} - {{ $nniveau_obj->nom}} - {{$id_cycle_obj->nom}} '
        }]
    });
</script>

@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/notes')}}">Notes</a></strong></li>

    <li><a href="{{url('eleves/notes/show')}}"><strong>@lang('admin.choix_classe')</strong></a></li>
    <li><a href="{{url('eleves/notes/matieres/show').'?id='.$id_classe_obj->id.'&cycle='.$id_cycle_obj->id.'&nniveau='.$nniveau_obj->id}}"><strong>@lang('admin.titre_matiere')</strong></a></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection