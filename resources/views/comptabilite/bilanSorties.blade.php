@extends('skeleton')
@section('sub-content')
    @include('perso.functions')
    <link rel="stylesheet" media="print" href="{{URL::asset('css/print_table.css')}}">

    <style media="screen">
        .hidden-row {
            display: none;
        }
    </style>
    <div class="col-md-12">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

            @endif



            <?php $i=1; $sumDepenses = 0; $sumSalaire = 0?>

            <div class="col-md-12">
                <div class="col-md-8">
                    <div class="btn-group" >
                        <a href="{{url('/comptabilite/bilan/sorties').'?option=year'}}" class="btn btn-success"> @lang('admin.toute_lannee')</a>
                        <a href="{{url('/comptabilite/bilan/sorties').'?option=month'}}" class="btn btn-info"> @lang('admin.mois')</a>
                        <a href="{{url('/comptabilite/bilan/sorties').'?option=week'}}" class="btn btn-warning"> @lang('admin.semaine')</a>
                        <a href="{{url('/comptabilite/bilan/sorties').'?option=today'}}" class="btn btn-primary"> @lang('admin.today')</a>

                    </div>
                    <h4 style="margin-top: 15px;">
                        @if(isset($date_debut))
                            <strong>{{trans('admin.salaire').' : '}}</strong>{{$date_debut.' - '.$date_fin}}
                        @else
                            <strong>{{trans('admin.salaire').' : ' }}</strong> {{$table_titre}}
                        @endif

                    </h4>

                    <table id="table-salaire" class="table table-bordered table-striped table-condensed table-inverse" >
                        <thead>
                        <th>#</th>
                        <th>@lang('admin.personnel')</th>
                        <th>{{trans('inscription.motif')}}</th>
                        <th>Date</th>
                        <th>@lang('inscription.montant')</th>
                        </thead>
                        <tbody>
                        <?php $i=1; ?>
                        @foreach($payements as $p)
                            <?php $sumSalaire += $p->net_a_payer?>

                            <tr>
                                <td>{{$i++}}</td>
                                <td>
                                    @foreach($personnels as $pers)
                                        @if($pers->id==$p->id_personnel)
                                            {{$pers->nom}}
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                </td>
                                <td><strong >@lang('admin.salaire')</strong></td>
                                <td>{{(new DateTime($p->date_p))->format('d-m-Y')}}</td>
                                <td>{{$p->net_a_payer}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                        <tfoot>
                            <th >@lang('admin.total')</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>{{$sumSalaire}}</th>
                        </tfoot>
                    </table>


                    <h4 class="alert alert-warning">
                        Total : <strong>{{$total.' '.trans('inscription.devise')}} </strong>
                    </h4>
                </div>
                <div class="col-md-4">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="panel-title">@lang('admin.intervalle')</div>

                        </div>
                        <div class="panel-body">
                            <form accept-charset="UTF-8" role="form" method="POST" action="{{ url('/comptabilite/bilan/sorties') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="date_range" value="1">

                                <fieldset>

                                    <div class="form-group ">
                                        <label for="date_debut">{{  trans('admin.date_debut')  }} : </label>
                                        <input placeholder="yyyy-mm-dd" readonly class="form-control datepicker" id="date_debut" name="date_debut" type="text"  value="{{ old('date_debut') }}" required>

                                    </div>
                                    <div class="form-group">
                                        <label for="date_fin">{{  trans('admin.date_fin')  }} : </label>
                                        <input placeholder="yyyy-mm-dd" readonly class="form-control datepicker" id="date_fin" name="date_fin" type="text"  value="{{ old('date_fin') }}" required >

                                    </div>
                                    <input class="btn  btn-primary pull-right " type="submit" value="{{  trans('admin.confirmer')  }}">


                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>


                <div class="col-md-8">
                    <h4 style="margin-top: 15px;">
                        @if(isset($date_debut))
                            <strong>{{trans('admin.facture').' : '}}</strong>{{$date_debut.' - '.$date_fin}}
                        @else
                            <strong>{{trans('admin.facture').' : ' }}</strong>{{$table_titre}}

                        @endif

                    </h4>
                    <table id="table-decaissement" class="table table-bordered table-striped table-condensed table-inverse" >
                        <thead>
                        <th>#</th>
                        <th>@lang('admin.personnel')</th>
                        <th>{{trans('inscription.motif')}}</th>
                        <th>Date</th>
                        <th>@lang('inscription.montant')</th>
                        <th>@lang('admin.ligne_budget')</th>
                        <th>@lang('admin.categorie')</th>
                        </thead>
                        <tbody>
                        <?php $i=1; ?>
                        @foreach($decaissements as $p)
                            <?php $sumDepenses += $p->montant?>

                            <tr>
                                <td>{{$i++}}</td>
                                <td>
                                    @foreach($personnels as $pers)
                                        @if($pers->id==$p->id_personnel)
                                            {{$pers->nom}}
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                </td>
                                <td><strong >{{$p->motif}}</strong></td>
                                <td>{{(new DateTime($p->date))->format('d-m-Y')}}</td>
                                <td>{{$p->montant}}</td>
                                <td>{{$p->ligne_budgetaire ? $p->ligne_budgetaire->libelle_ligne : '' }}</td>
                                <td>{{$p->categorie_budgetaire ? $p->categorie_budgetaire->libelle : '' }}</td>
                            </tr>
                        @endforeach

                        </tbody>
                        <tfoot>
                        <th >@lang('admin.total')</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th>{{$sumDepenses}}</th>
                        </tfoot>
                    </table>


                    <h4 class="alert alert-warning">
                        Total : <strong>{{$total_d.' '.trans('inscription.devise')}} </strong>
                    </h4>


                </div>

            </div>


            <h3 class="alert alert-info col-md-12">
                {{trans('admin.depenses_totales').' : '.($total+$total_d).' '.trans('inscription.devise')}}
            </h3>


    </div>



    <script>
        $('#table-salaire').dataTable( {
            dom: 'Bfrtip',
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: '{!! trans('admin.bilan_sorties') !!} - {!! sanitize($table_titre) !!}',

                }
                ,
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'PDF',
                    title: '{!! trans('admin.bilan_sorties') !!} - {!! sanitize($table_titre) !!}',
                    footer: true,
                }
                ,
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> @lang("inscription.imprimer")',
                    titleAttr: '@lang("inscription.imprimer")',
                    title: '{!! trans('admin.bilan_sorties') !!} - {!! sanitize($table_titre) !!}',
                    customize: (window, buttonO, dtAPI) => {dataTableButtonFunction(window, buttonO, dtAPI)},
                    footer: true,
                }
            ]
        } );

        $('#table-decaissement').dataTable( {
            dom: 'Bfrtip',
            pageLength:50,
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: '{!! trans('admin.bilan_sorties') !!} - {!! sanitize($table_titre) !!}',

                }
                ,
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'PDF',
                    title: '{!! trans('admin.bilan_sorties') !!} - {!! sanitize($table_titre) !!}',
                    footer: true,
                }
                ,
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> @lang("inscription.imprimer")',
                    titleAttr: '@lang("inscription.imprimer")',
                    title: '{!! trans('admin.bilan_sorties') !!} - {!! sanitize($table_titre) !!}',
                    customize: (window, buttonO, dtAPI) => {dataTableButtonFunction(window, buttonO, dtAPI)},
                    footer: true,
                }
            ]
        } );

        var dataTableButtonFunction = function (window, buttonO, dtAPI) {
            window.document.body.innerHTML = '<style> @media print { @page  { size: A4 portrait } } </style>' + '@include('perso.header-trim')' + window.document.body.innerHTML

        }

    </script>
    @endsection

@section('breadcrumb')
    <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection