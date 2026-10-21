@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    @include('perso.functions')
    <link rel="stylesheet" media="print" href="{{URL::asset('css/print_table.css')}}">
    <style media="screen">
        .hidden-row {
            display: none;
        }
    </style>
@if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>

    @endif


        <div class="col-md-12" >
            <div class="btn-group" >
                <a href="{{url('/comptabilite/bilan/entrees').'?option=year'}}" class="btn btn-success"> @lang('admin.toute_lannee')</a>
                <a href="{{url('/comptabilite/bilan/entrees').'?option=month'}}" class="btn btn-info"> @lang('admin.mois')</a>
                <a href="{{url('/comptabilite/bilan/entrees').'?option=week'}}" class="btn btn-warning"> @lang('admin.semaine')</a>
                <a href="{{url('/comptabilite/bilan/entrees').'?option=today'}}" class="btn btn-primary"> @lang('admin.today')</a>
                <a href="#date_range_modal" data-toggle="modal" class="btn btn-default"> @lang('admin.intervalle')</a>

            </div>

            <h4 style="margin-top: 15px;">
                @if(isset($date_debut))
                    {{$date_debut.' - '.$date_fin}}
                @else
                    {{$table_titre}}
                @endif

            </h4>
            <table id="table-entrees" class="table table-bordered table-striped table-condensed table-inverse" >
                <thead>
                <th>#</th>
                <th>{{trans('inscription.nom')}}</th>
                <th>{{trans('admin.classe')}}</th>
                <th>{{trans('inscription.motif')}}</th>
                <th>Date</th>
                <th>@lang('inscription.montant')</th>
                <th></th>
                </thead>
                <tbody>
                <?php $i=1; $sumPayments = 0;?>
                @foreach($payements as $p)
                    <?php $sumPayments += $p->montant;?>

                    <tr>
                        <td>{{$i++}}</td>
                        <td>@if($p->eleve) {{$p->eleve->nom . ' ' . $p->eleve->prenom }} @endif</strong></td>
                        <td>
                            @if($p->eleve)
                                @foreach($p->eleve->inscriptions as $ins)
                                    @if($ins->annee_scolaire == $p->annee_scolaire)
                                        {{$ins->classe->code}} - {{$ins->nniveau->nom}}
                                        <?php break; ?>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                        <td><strong >{{$p->motif}}</strong></td>
                        <td>{{(new DateTime($p->date_payement))->format('d-m-Y')}}</td>
                        <td>{{$p->montant}}</td>
                        <!-- TO CHANGE -->
                        <td>     <a  class="btn btn-warning"  href="@if($p->type == \gesecole\Vars::INSCRIPTION_TYPE){{URL::to('/inscriptions/print/'.$p->id)}} @else{{URL::to('/inscriptions/payement/print/'.$p->id)}}@endif" ><span class="glyphicon glyphicon-eye-open"  ></span> {{  trans('admin.verifier')  }}</a></td>
                    </tr>
                @endforeach

                </tbody>
                <tfoot>
                    <th >@lang('admin.total')</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>{{$sumPayments}}</th>
                </tfoot>
            </table>

           <!-- <div class="paginate" style="text-align: center;"> <?php /*echo(str_replace('/?', '?', $payements->render()) );*/ ?></div> -->

            <h4 class="alert alert-warning">
                Total : <strong>{{$total.' '.trans('inscription.devise')}} </strong>
            </h4>
        </div>





</div>

<div id="date_range_modal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="width: 300px; box-shadow: none; background-color: unset">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">@lang('admin.intervalle')
                    <a href="#" class="pull-right" data-dismiss="modal">X</a></div>

                </div>
                <div class="panel-body">
                    <form accept-charset="UTF-8" role="form" method="POST" action="{{ url('/comptabilite/bilan/entrees') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="date_range" value="1">
                        <fieldset>

                            <div class="form-group">
                                <label for="date_debut">{{  trans('admin.date_debut')  }} : </label>
                                <input readonly placeholder="yyyy/mm/dd" class="form-control datepicker" id="date_debut" name="date_debut" type="text"  value="{{ old('date_debut') }}" required>

                            </div>
                            <div class="form-group ">
                                <label for="date_fin">{{  trans('admin.date_fin')  }} : </label>
                                <input readonly placeholder="yyyy/mm/dd" class="form-control datepicker" id="date_fin" name="date_fin" type="text"  value="{{ old('date_fin') }}" required >

                            </div>
                            <input  class="btn  btn-primary pull-right " type="submit" value="{{  trans('admin.confirmer')  }}">


                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    <script>

        $('#table-entrees').dataTable( {
            dom: 'Bfrtip',

            pageLength:50,
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: '{!! trans('admin.bilan_entrees') !!} - {!! sanitize($table_titre) !!}',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'PDF',
                    title: '{!! trans('admin.bilan_entrees') !!} - {!! sanitize($table_titre) !!}',
                    footer: true,
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> @lang("inscription.imprimer")',
                    titleAttr: '@lang("inscription.imprimer")',
                    title: '{!! trans('admin.bilan_entrees') !!} - {!! sanitize($table_titre) !!}',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    customize: (window, buttonO, dtAPI) => {dataTableButtonFunction(window, buttonO, dtAPI)},
                    footer: true,
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ]
        } );

        var dataTableButtonFunction = function (window, buttonO, dtAPI) {
            window.document.body.innerHTML = '<style> @media print { @page  { size: A4 landscape } } </style>' + '@include('perso.header-trim')' + window.document.body.innerHTML

        }






    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection