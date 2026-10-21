@extends('skeleton')
@section('sub-content')
<div class="ajax-list-fragment">
    @include('perso.functions')
    <div class="row">
        <div class="col-md-12">
            <a href="#" class="btn btn-warning btn-lg" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> {{trans('inscription.imprimer')}}</a>
        </div>
    </div>
    <link rel="stylesheet" media="print" href="{{URL::asset('css/print_table.css')}}">

    @if(\Auth::user()->canDoAction(\gesecole\Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE))
        <div class="row hide-in-print">
            <div class="col-md-12" style="margin-top: 10px">
                <div class="btn-group">
                     <span onclick="openChangeStatusModal('#')" class="btn btn-success"> @lang('admin.allow_student_access')</span>
                    <span onclick="editStudentAccess(0)" class="btn btn-danger"> @lang('admin.deny_student_access')</span>
                </div>
            </div>
        </div>
    @endif
    @include('perso.header')



    <div class="list-cont">
        <h2 style="font-size: 22px;text-align: center">{{  $titre_liste  }}</h2>

        <table  class="table table-bordered   table-striped table-condensed table-inverse" id="idTableListe" style="margin-top: 5px;">
        <thead>
        <th>#</th>
        <th>@lang('admin.matricule')</th>
        <th>{{  trans('admin.nom')  }}</th>
        <th>Date de Naissance</th>
        <th>{{  trans('inscription.lieu_naiss') }}</th>
       <th>pays d'origine</th>
	   <th>région d'origine</th>
	    <th  class="sexe">{{  trans('inscription.sexe')  }}</th>
	  <th>Contacts étudiants</th>
		<th>email</th>
        <th>Diplome préinscription</th>
		<th>Annee obtention</td>
        <th>Dernier établissement fréquenté</th>
       <th>Numéro téléphone Parents</th>
	  
        @if(isset($show_classe))<th class="classe">@lang('admin.classe')</th>@endif
        @if(isset($show_classe))<th>@lang('admin.nniveau')</th>@endif
        @if(isset($show_montant))<th>@lang('admin.montant')</th> <?php $sumAmount = 0 ?>@endif
        @if(isset($type) && $type != 1)<th>{{trans('inscription.reste')}}</th>@endif
        <th class="action"></th>

        </thead>
        <tbody>
        <?php $i=1; $elevesStringArray = ""; $sumReste = 0?>
        @foreach($eleves as $e)
            <?php
                if($i != 1) {
                    $elevesStringArray .= ', ';
                }
            $elevesStringArray .= $e->id;
            ?>

            <tr>
                <td>{{$i++}}</td>
                <td>{{$e->matricule}}</td>
                <td><strong >{{$e->nom.' '.$e->prenom}}</strong>
                    <div style="color: green">{{ isset($e->montant_bourse) ? ('(' . trans('admin.bourse') . ': ' . $e->montant_bourse . ')') : '' }}</div>

                </td>
                <!--<td>{{(new DateTime($e->date_naiss))->format('d-m-Y')}}</td>-->
				<td>{{$e->date_naiss}}</td>
                <td>{{$e->lieu_naiss}}</td>
				<td>{{$e->nationalite}}</td>
				<td>{{$e->region_origine}}</td>
				 <td style="width: 25px">{{$e->sexe}}</td>
                <td>{{$e->tel}}</td>
				 <td>{{$e->email}}</td>


                <td>{{$e->diplome_preinscription}}</td>
				<td>{{$e->annee_obtention}}</td>
                <td>{{$e->dernier_etablissement}}</td>
               <td>
                    @foreach($parents as $n)
                        @if($n->id_eleve == $e->id)
                            {{ $n->tel1 }}
                            <?php break; ?>
                        @endif
                    @endforeach
                </td>
				
                @if(isset($show_classe))<td>{{$e->code_classe}}</td>@endif
                @if(isset($show_classe))<td>{{$e->nniveau}}</td>@endif
                 @if(isset($show_montant))<td>{{$e->montant}}</td> <?php $sumAmount += $e->montant ?>@endif

                @if(isset($type) && $type != 1)<td>
                    {{ $e->reste - (isset($e->montant_bourse) && isset($last_installment) ? $e->montant_bourse : 0) }}
                    <!--<div style="color: green">{{ isset($e->montant_bourse) ? ('(-' . $e->montant_bourse . ')') : '' }}</div>-->
                    <?php $sumReste += $e->reste - (isset($e->montant_bourse) && isset($last_installment) ? $e->montant_bourse : 0)?>
                </td>
                @endif
                <td class="action">
                    <div class="btn-group">
                        <a href="#" class=" btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a style="color: white" href="{{ URL::to('/inscriptions/edit/').'/'.$e->id }}"   class="btn-primary"><span class="glyphicon glyphicon-edit"></span> {{  trans('admin.edit_inscription')  }}</a></li>
                            <li><a style="color: white" href="#" onclick="fill_delete_modal({{$e->id}}, '{{ URL::to('/inscriptions/remove/').'/'.$e->id }}', null, null)"   class="btn-danger"><span class="glyphicon glyphicon-trash"></span> {{  trans('admin.desinscrire')  }}</a></li>
                             <li><a style="color: white" href="#" onclick="allowStudentAccess({{ $e->id }})"   class="btn-success"><span class="glyphicon glyphicon-plus"></span> {{  trans('admin.allow_student_access')  }}</a></li>
                            <li><a style="color: white" href="{{url('/inscriptions/eleve/set-status?status=0&id='. $e->id)}}"   class="btn-danger"><span class="fa fa-ban"></span> {{  trans('admin.deny_student_access')  }}</a></li>
                        </ul>
                    </div>

                </td>
            </tr>

        @endforeach
        </tbody>
    </table>


        <div class="col-md-12"><strong>@lang('admin.total'): {{count($eleves)}}</strong></div>
        @if(isset($type) && $type != 1)
            <div class="col-md-12"><strong>@lang('admin.reste') @lang('admin.total'): {{$sumReste}} @lang('inscription.devise')</strong></div>

        @endif
           @if(isset($show_montant))
        <div class="col-md-12"><strong> @lang('admin.total'): {{$sumAmount}} @lang('inscription.devise')</strong></div>
        @endif


        <div id="dialog" title="@lang('admin.info')">
            <h3 id="dialog-text" class="alert"></h3>
        </div>
</div>

    <script>

     var elevesArray = [{{$elevesStringArray}}];

        $(function () {
            $('#change-status-form').on('submit', function(e) {
                console.log('event', e)
            e.preventDefault() //disable default submit behavior
            editStudentAccess(1)
        })
        })
         function editStudentAccess(status) {
            var jDText = $( "#dialog-text" );
            const date_debut = $('#change_status-date_debut').val()
            const date_fin = $('#change_status-date_fin').val()
            const montant = $('#change_status-montant').val()

            $.ajax({
                url : '{{url('inscriptions/eleve/set-multi-status')}}',
                type: 'POST',
                data: {
                    ids : elevesArray,
                    status : status,
                    date_debut,
                    date_fin,
                    montant,
                    _token : '{{csrf_token()}}'
                },
                beforeSend : function () {
                    showLoader();
                },
                success: function (data) {
                    console.log(data);
                    $( "#dialog" ).dialog( "open" );
                    jDText.html("@lang('admin.status_edited')");
                    jDText.removeClass('alert-danger');
                    jDText.addClass('alert-success');
                },
                error: function (details) {
                  console.error('details', details);
                    $( "#dialog" ).dialog( "open" );
                    jDText.html("@lang('admin.error_occured')");
                    jDText.removeClass('alert-success');
                    jDText.addClass('alert-danger');
                },
                complete: function () {
                    hideLoader();
                    $('#change-status-modal').modal('hide');
                }
            });
        }


        $('#idTableListe').dataTable( {
            dom: 'Bfrtip',
            pageLength:5000,
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: '{!! sanitize($titre_liste) !!}',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'PDF',
                    title: '{!! sanitize($titre_liste) !!}',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }

            ]
        } );

        jQuery.noConflict();
        $( "#dialog" ).dialog({
            autoOpen: false,
            modal: true
        });
    </script>
</div>
@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li><strong><a href="{{url('inscriptions/selection-listes')}}">@lang('inscription.listes')</a></strong></li>
@endsection
