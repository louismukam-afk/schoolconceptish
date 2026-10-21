@extends('skeleton')
@section('sub-content')
<style>
    table.dataTable thead .nb
    {
        width: 50px;
    }
</style>

    <div class="col-md-12">
        <h3 style="font-size: 22px">{{  ($classe==null)?trans('inscription.all_eleves'):((App::getLocale()=='en'?$classe->nom_anglais:$classe->nom).' '.$nniveau->nom) }}</h3>

        <table id="table-discipline" class="table table-striped table-condensed table-inverse" style="margin-top: 15px;">
                <thead>

                <th>@lang('admin.matricule')</th>
                <th>@lang('admin.nom')</th>
                <th class="nb">{{  trans('eleve.retard')  }}</th>
                <th class="nb">{{  trans('eleve.abscence_i')  }}</th>
                <th class="nb">{{  trans('eleve.abscence_j')  }}</th>
                <th class="nb">{{  trans('eleve.avertissement')  }}</th>
                <th class="nb">@lang('eleve.blame')</th>
                <th class="nb">@lang('eleve.exclusion')</th>

                </thead>
                <tbody>
                @foreach($eleves as $e)
                    @foreach($disciplines as $d)
                        @if($e->id==$d->id_eleve)
                            <tr>
                                <td>{{$e->matricule}}</td>
                                <td><strong >{{$e->nom.' '.$e->prenom}}</strong></td>
                                <td>{{$d->retard}}</td>
                                <td>{{$d->abscence_inj}}</td>
                                <td>{{$d->abscence_just}}</td>
                                <td>{{$d->avertissement}}</td>
                                <td>{{$d->blame}}</td>
                                <td>{{$d->exclusion}}</td>

                            </tr>
                        @endif
                    @endforeach

                @endforeach
                </tbody>
            </table>


    </div>
<script>

    $('#table-discipline').dataTable( {
        pageLength : 50,
        dom: 'Bfrtip',
        buttons: [
            //'copy', 'csv', 'excel', 'pdf', 'print'
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel-o"></i> Excel',
                titleAttr: 'Export to Excel',
                title: '{{trans('admin.etat_disc')}}',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            }
            ,
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf-o"></i> PDF',
                titleAttr: 'PDF',
                title: '{{trans('admin.etat_disc')}}',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            }
            ,
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i> @lang("inscription.imprimer")',
                titleAttr: 'Imprimer',
                title: '{{trans('admin.etat_disc')}}',
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            }
        ]
    } );
    $(function () {
        //$('#date_debut').datepicker({ dateFormat: 'dd-mm-yy' });
        //$('#date_fin').datepicker({ dateFormat: 'dd-mm-yy' });
    });


</script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/discipline')}}">@lang('inscription.title_search')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection