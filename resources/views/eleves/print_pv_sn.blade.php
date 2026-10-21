@extends('skeleton')
@section('sub-content')
    @include('perso.functions')
    <?php $standartLimite=7;
    $current_matiere=0;
    $current_note=0;
    $admis=0;
    $echec=0;
    ?>


      <style media="screen">
        .table-note
        {
            display: none;
        }
        div.table
        {
            overflow-x: scroll;
        }

    </style>
    <style media="print">
        #note-excel,.dt-buttons,#note-excel_filter,#note-excel_info,#note-excel_paginate
        {
            display: none;
        }

    </style>
    <a href="#" class="btn btn-lg btn-warning" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> @lang('inscription.imprimer')</a>
    @include('perso.header')
    <link rel="stylesheet"  href="{{URL::asset('css/pv.css')}}">
    <div class="titre">
        <p style="font-size: 19px"><strong>Résultats  des Examens du semestre {{$semestre}}</strong></p>
        <strong class="traduction">Semester {{$semestre}} exam results</strong>
    </div>
    <div class="classe">
        <div class="specialite">
            <p><strong>Spécialité : {{$classe->nom}} - Niveau  {{$nniveau->nom}}</strong></p>
            <strong class="traduction">Speciality : {{$classe->nom_anglais}} - Level  {{$nniveau->nom}}</strong>
        </div>

        <div class="filiere">
            <p ><strong>Filière: {{\gesecole\niveau::find($classe->id_niveau)->nom}}</strong></p>
            <strong class="traduction">Path : {{\gesecole\niveau::find($classe->id_niveau)->nom_anglais}}</strong>
        </div>

    </div>


     <div class="table">
        <table id="note-excel" class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 5px;">
            <thead>
            <th class="number-column">#</th>
            <th class="spec-column">Matricule/Matricle</th>
            <th class="nom-column">Nom/Name</th>
            @foreach($matieres as $m)

                <th class="matiere-column"><p>{{(App::getLocale()=='en')?$m->nom_anglais:$m->nom}}</p></th>

            @endforeach
            <th class="note-column">Moy/20</th>
            <th class="note-column">MGP</th>

            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($eleves as $e)
                <?php $total=0; ?>
                <tr>
                    <td class="number-column">{{$i++}}</td>
                    <td class="mat-column">{{$e->matricule}}</td>
                    <td>{{$e->nom.' '.$e->prenom}}</td>

                    @foreach($matieres as $m)

                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                           
                                             {{round(($mat->normal)/2.5,2)}}
                
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                @endif
                                @if($finded)
                                    <?php break; ?>
                                @endif
                            @endforeach

                        </td>

                    @endforeach

                    <?php $mgp=0;?>
                    <td class="note-column">
                        @foreach($PV as $pv)
                            @if($pv->id_eleve==$e->id)
                                {{round(($pv->mgp)/2.5,2)}}
                                <?php $mgp=round(($pv->mgp)/12.5,2);
                                if($mgp>=2)
                                {$admis++;}
                                else{$echec++;}
                                break; ?>
                           
                             
                            @endif

                        @endforeach

                    </td>

                    <td class="note-column">{{$mgp}}</td>




                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@while($current_matiere<$nb_matieres)
    <div class="table-matiere">
        <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 250px;">
            <thead>

            <?php $j=$current_matiere;$limite=$current_matiere+$standartLimite;$k=0; ?>
            @foreach($matieres as $m)
                @if($k>=$current_matiere)

                    @if($j>=$limite)
                        <?php break; ?>
                    @endif
                    <th class="matiere-column"><p>{{(App::getLocale()=='en')?$m->nom_anglais:$m->nom}}</p></th>
                    <?php $j++;
                    $current_matiere=$j;
                    ?>
                @endif
                <?php $k++; ?>

            @endforeach
            @if($current_matiere==$nb_matieres)
                <th class="note-column">Moy/20</th>
                <th class="note-column">MGP</th>
            @endif

            </thead>
            <tbody>
            <?php $i=1;$t=0; ?>
            @foreach($eleves as $e)
                <?php $total=0; ?>
                <tr>
                    <?php $j=$current_note;$limite=$current_note+$standartLimite;$k=0; ?>
                    @foreach($matieres as $m)
                        @if($k>=$current_note)

                            @if($j>=$limite)
                                <?php break; ?>
                            @endif
                                <td class="note-column">
                                    <?php $finded=false; ?>
                                    @foreach($PV as $pv)
                                        @if($pv->id_eleve==$e->id)
                                            <?php $finded=true; ?>
                                            @foreach($pv->matieres as $mat)
                                                @if($mat->id_matiere==$m->id )
                                                  {{round(($mat->normal)/2.5,2)}}
                                                    <?php break; ?>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if($finded)
                                            <?php break; ?>
                                        @endif
                                    @endforeach

                                </td>
                            <?php $j++;
                            $t=$j;
                            ?>
                        @endif
                        <?php $k++; ?>

                    @endforeach

            <?php $j=$current_note; ?>
                        @if($current_matiere==$nb_matieres)

                            <td class="note-column">
                                @foreach($PV as $pv)
                                    @if($pv->id_eleve==$e->id)
                                        {{round(($pv->mgp)/12.5,2)}}
                                        <?php $mgp=round(($pv->mgp)/12.5,2);

                                        break; ?>
                                    @endif

                                @endforeach

                            </td>

                            <td class="note-column">{{$mgp}}</td>

                        @endif
                </tr>
            @endforeach
            <?php $current_note=$t; ?>

            </tbody>
        </table>
    </div>
@endwhile
  <script>

        $('#note-excel').dataTable( {
            dom: 'Bfrtip',
            pageLength:2000,
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: 'Notes {{$classe->nom}}'

                }

            ]
        } );


    </script>
@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/notes')}}">Notes</a></strong></li>

    <li><a href="{{url('eleves/notes/show')}}"><strong>@lang('admin.choix_classe')</strong></a></li>
    <li><a href="{{url('eleves/notes/matieres/show').'?id='.$classe->id.'&cycle='.$id_cycle.'&nniveau='.$nniveau->id}}"><strong>@lang('admin.titre_matiere')</strong></a></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection