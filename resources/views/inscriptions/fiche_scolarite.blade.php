@extends('skeleton')
@section('sub-content')
    @include('perso.functions')
    <?php
        $fichePageSize = strtolower(\Input::get('page_size', 'a4')) == 'a3' ? 'A3' : 'A4';
        $fichePrintZoom = $fichePageSize == 'A3' ? '100%' : '82%';
        $ficheQuery = \Input::all();
        $ficheQuery['page_size'] = 'a4';
        $ficheA4Url = \URL::current().'?'.http_build_query($ficheQuery);
        $ficheQuery['page_size'] = 'a3';
        $ficheA3Url = \URL::current().'?'.http_build_query($ficheQuery);
    ?>
    <style >

        .table  tr,.table  tr td, .table  th,.table  th td{
            padding-top: 0!important;
            padding-bottom: 0!important;
            height: 12px!important;
            font-size: 10px!important;
            line-height: 11px!important;

        }

        .table  tr.center-cell,.table  tr td.center-cell, .table  th.center-cell,.table  th td.center-cell{
            text-align: center;
            vertical-align: middle;
        }

        table td.back-green, table th.back-green {
            color-adjust: exact!important;
            -webkit-print-color-adjust: exact!important;
            background-color: greenyellow!important;
        }

        table td.back-yellow, table th.back-yellow {
            color-adjust: exact!important;
            -webkit-print-color-adjust: exact!important;
            background-color: yellow!important;
        }

        table td.back-black, table th.back-black {
            color-adjust: exact!important;
            -webkit-print-color-adjust: exact!important;
            background-color: black!important;
        }

        table tr td.border-left-bold, table tr th.border-left-bold {
            border-left-width: 10px!important;
        }
        .dt-buttons {
            display: none;
        }
        .fiche-toolbar {
            margin-bottom: 15px;
        }
        #idTableListe {
            width: 100% !important;
        }
    </style>
    <div class="row fiche-toolbar">
        <div class="col-md-12">
            <a href="{{ $ficheA4Url }}" class="btn {{ $fichePageSize == 'A4' ? 'btn-primary' : 'btn-default' }}">
                <span class="glyphicon glyphicon-file"></span> A4 paysage
            </a>
            <a href="{{ $ficheA3Url }}" class="btn {{ $fichePageSize == 'A3' ? 'btn-primary' : 'btn-default' }}">
                <span class="glyphicon glyphicon-file"></span> A3 paysage
            </a>
            <button type="button" class="btn btn-success" id="fiche-export-excel">
                <span class="glyphicon glyphicon-download-alt"></span> Export Excel
            </button>
            <a href="#" class="btn btn-warning btn-lg" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> {{trans('inscription.imprimer')}}</a>
        </div>
    </div>
    <link rel="stylesheet" media="print" href="{{URL::asset('css/print_scolarite_state.css')}}">
    <style media="print">
        @page {
            size: {{ $fichePageSize }} landscape;
            margin: 2mm 2mm 2mm 2mm;
        }
        html,
        body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        body {
            zoom: {{ $fichePrintZoom }};
        }
        .fiche-toolbar,
        .dt-buttons,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate,
        .dataTables_length {
            display: none !important;
        }
        .list-cont {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        .list-cont h2 {
            font-size: {{ $fichePageSize == 'A3' ? '20px' : '16px' }} !important;
            margin: 2mm 0 !important;
        }
        #idTableListe {
            width: 100% !important;
            max-width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            margin-top: 2mm !important;
        }
        #idTableListe th,
        #idTableListe td {
            font-size: {{ $fichePageSize == 'A3' ? '9.5px' : '7.5px' }} !important;
            line-height: 1.05 !important;
            padding: 1px !important;
            white-space: normal !important;
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
        }
        #idTableListe th:nth-child(1),
        #idTableListe td:nth-child(1) {
            width: 3% !important;
        }
        #idTableListe th:nth-child(2),
        #idTableListe td:nth-child(2) {
            width: 15% !important;
        }
    </style>

    @include('perso.header')


    <?php $maxNbVersements = 9 ; $sumFrais = []; $sumPayments = []; $sumIns = 0;?>
    <div class="list-cont">
        <h2 style="font-size: 22px;text-align: center">{{  $titre_liste  }}</h2>

        <table id="idTableListe" class="table table-bordered   table-striped table-condensed table-inverse" style="margin-top: 5px;">
            <thead>
            <tr>
                <th class="center-cell back-green" rowspan="3">#</th>
                <th class="center-cell back-green" rowspan="3">{{  trans('admin.nom_prenom')  }}</th>
                <th class="center-cell back-green text-uppercase" colspan="{{count($frais) + 1}}">{{trans('admin.modalite_inscription')}}</th>
                <th class="center-cell back-green text-uppercase border-left-bold   " colspan="{{$maxNbVersements + 1}}">{{trans('admin.schoolfees')}}</th>
                <th class="center-cell back-green" rowspan="3">@lang('admin.total')</th>
                <th class="center-cell back-green" rowspan="3">@lang('admin.reste')</th>
            </tr>
            <tr>
                <th class="center-cell">INS</th>
                @foreach($frais as $f)
                    <th class="center-cell"> {{$f->getPrintedNom()}} </th>
                @endforeach
                <th class="center-cell border-left-bold" rowspan="2"><div>@lang('admin.montant')</div><div>(@lang('inscription.devise'))</div></th>

                    @for ($i = 0; $i < $maxNbVersements; $i++)
                        <th class="center-cell" rowspan="2"> <div>{{($i + 1).getIeme(($i + 1))}}</div><div>@lang('admin.versement')</div></th>
                    @endfor

            </tr>
            <tr>

                <th class="center-cell">{{$scolarite_ancien->inscription . ($scolarite_nouveau ? '/'.$scolarite_nouveau->inscription : '')}}</th>
                @foreach($frais as $f)
                    <th class="center-cell"> @if($f->isKind()) ----  @else {{$f->montant}} @endif </th>
                @endforeach
            </tr>
            </thead>


        <tbody>
        <?php $i=1?>
        @foreach($eleves as $e)
            <?php $m= 0?>
            <tr>
                <td class="center-cell" rowspan="2">{{$i++}}</td>
                <td class="center-vertical-cell" rowspan="2"><strong >{{$e->nom.' '.$e->prenom}}</strong></td>
                <td class="center-cell" rowspan="2">
                    {{$e->montant_inscription}}
                    <?php $sumIns += $e->montant_inscription ?>
                </td>
                @foreach($frais as $f)
                    <td class="center-cell" rowspan="2">
                    @foreach($e->payements_frais as $p)
                        @if($p->type == $f->id)
                            @if ($f->isKind())
                                <div style="font-size: 13px; font-weight: 600"> X </div>
                            @else
                                {{$p->montant}}
                            @endif
                            @if(isset($sumFrais[$m]))
                                <?php $sumFrais[$m] += $p->montant ?>
                            @else
                                <?php $sumFrais[$m] = $p->montant ?>
                            @endif
                            <?php break ?>
                        @endif
                    @endforeach
                    </td>
                    <?php $m++ ?>
                @endforeach
                <td class="center-cell border-left-bold" rowspan="2">{{$e->scolarite->total}}</td>
                @for($j = 0; $j < $maxNbVersements; $j++)
                    <td class="center-cell" >
                            @if(isset($e->payements[$j]))
                                {{$e->payements[$j]->montant}}
                                @if(isset($sumPayments[$j]))
                                    <?php $sumPayments[$j] += $e->payements[$j]->montant ?>
                                @else
                                    <?php $sumPayments[$j] = $e->payements[$j]->montant ?>
                                @endif
                            @endif
                    </td>
                @endfor
                <td rowspan="2">{{ $e->total_paye }} <div style="color: green; font-weight: 600"> {{ $e->montant_bourse ? '(+' . $e->montant_bourse . ')' : '' }} </div></td>
                <td rowspan="2">{{ $e->scolarite->total - $e->total_paye }}</td>

            </tr>
            <tr>
                @for($k = 0; $k < $maxNbVersements; $k++)
                    <td class="center-cell" >
                        @if(isset($e->payements[$k]))
                            {{(new DateTime($e->payements[$k]->date_payement))->format('d/m/Y')}}
                        @endif
                    </td>
                @endfor
            </tr>
        @endforeach
        <tr>
            <td class="center-cell back-yellow text-uppercase" colspan="2">@lang('admin.total')</td>
            <td class="center-cell"> {{$sumIns}}</td>
            @for($n=0; $n < count($frais); $n++)
                <td class="center-cell">
                    @if(isset($sumFrais[$n]))
                        {{$sumFrais[$n]}}
                    @endif
                </td>
            @endfor
            <td class="border-left-bold back-black"></td>
            @for($n=0; $n < $maxNbVersements; $n++)
                <td class="center-cell">
                    @if(isset($sumPayments[$n]))
                        {{$sumPayments[$n]}}
                    @endif
                </td>
            @endfor
                <td class="back-black"></td>
                <td class="back-black"></td>

        </tr>
        </tbody>
    </table>

</div>

    <script>
        $(function () {
            var ficheScolariteTable = null;
            if ($.fn.DataTable) {
                ficheScolariteTable = $('#idTableListe').DataTable( {
                    dom: 'Bfrtip',
                    pageLength: 5000,
                    paging: false,
                    ordering: false,
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '<i class="fa fa-file-excel-o"></i> Excel',
                            className: 'fiche-excel-button',
                            titleAttr: 'Export to Excel',
                            title: '{!! sanitize($titre_liste) !!}',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }
                    ]
                } );
            } else {
                $('#idTableListe').dataTable({
                    pageLength: 5000,
                    paging: false,
                    ordering: false
                });
            }

            $('#fiche-export-excel').on('click', function () {
                if (ficheScolariteTable && ficheScolariteTable.button) {
                    ficheScolariteTable.button('.buttons-excel').trigger();
                } else {
                    var excelButton = $('.buttons-excel, .fiche-excel-button').first();
                    if (excelButton.length) {
                        excelButton.trigger('click');
                    }
                }
            });
        });
    </script>
@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li><strong><a href="{{url('inscriptions/selection-listes')}}">@lang('inscription.listes')</a></strong></li>
@endsection
