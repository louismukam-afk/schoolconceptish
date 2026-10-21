@extends('skeleton')
@section('sub-content')
    @include('perso.functions')
    <?php $standartLimite=7;
    $standartLimite1=7;
    $current_group_matiere=0;
    $current_matiere=0;
    $current_matiere1=0;
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
    <link rel="stylesheet"  href="{{URL::asset('css/pv.css')}}">

    <?php $pv_type = 'licence_soa'; $pv_title_mode = 'annuel'; ?>
    @include('eleves.partials.pv_tutelle_header')

    <div class="titre">
        <p style="font-size: 19px"><strong>Bilan annuel </strong></p>
        <strong class="traduction">Annual review</strong>
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
            <tr>
                <th class="number-column" rowspan="2">#</th>
                <th class="spec-column" rowspan="2">Matricule/Matricle</th>
                <th class="nom-column" rowspan="2">Nom/Name</th>
                <?php  $j=0; ?>
                @foreach($matieres as $m)
                    @if($j>=$standartLimite)
                        <?php break; ?>
                    @endif

                    <th class="matiere-column" colspan="3"><p>{{$m->nom}}</p></th>
                    <?php $j++;
                    $current_matiere=$j;
                    ?>
                @endforeach
                @if($current_matiere==$nb_matieres)
                    <th class="note-column" rowspan="2">Moy/20</th>
                    <th class="note-column" rowspan="2">MGP</th>
                    <th class="note-column" rowspan="2">Decision</th>
            </tr>
            <tr>
                @foreach($matieres as $m)
                    <th>CC</th>
                    <th>SN</th>
                    <th>Total/100  </th>

                @endforeach
                @endif
            </tr>


            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($eleves as $e)
                <?php $total=0; ?>
                <tr>
                    <td class="number-column">{{$i++}}</td>
                    <td class="mat-column">{{$e->matricule}}</td>
                    <td>{{$e->nom.' '.$e->prenom}}</td>

                    <?php $j=0; ?>
                    @foreach($matieres as $m)
                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            {{$mat->controle}}
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                @endif
                                @if($finded)
                                    <?php break; ?>
                                @endif
                            @endforeach

                        </td>
                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            {{$mat->normal}}
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                @endif
                                @if($finded)
                                    <?php break; ?>
                                @endif
                            @endforeach

                        </td>


                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            {{$mat->note}}
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
                    <td class="note-column">
                        @foreach($PV as $pv)
                            @if($pv->id_eleve==$e->id)
                                {{round(($pv->mgp)/5,2)}}
                                <?php $mgp=round(($pv->mgp)/25,2);
                                if($mgp>=2)
                                {$admis++;}
                                else{$echec++;}
                                break; ?>
                            @endif

                        @endforeach

                    </td>

                    <td class="note-column">{{$mgp}}</td>
                    <td class="note-column">
                        {{getDecision($mgp)}}
                    </td>
                </tr>

            @endforeach
            <tr style="font-size: 13px">
                <td>{{$i}}</td>
                <td></td>
                <td><b>%  validation</b></td>
                <td class="note-column">
                    <?php  $nbValid=0;$nbNotes=0;  ?>
                    @foreach($PV as $pv)

                        @foreach($pv->matieres as $mat)
                            @if($mat->id_matiere==$m->id )
                                <?php $nbNotes++ ?>
                                @if(floatval($mat->controle)>=15)
                                    <?php $nbValid++; ?>
                                @endif
                                <?php break; ?>
                            @endif
                        @endforeach

                    @endforeach
                    <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                </td>
                <td class="note-column">
                    <?php  $nbValid=0;$nbNotes=0;  ?>
                    @foreach($PV as $pv)

                        @foreach($pv->matieres as $mat)
                            @if($mat->id_matiere==$m->id )
                                <?php $nbNotes++ ?>
                                @if(floatval($mat->normal)>=35)
                                    <?php $nbValid++; ?>
                                @endif
                                <?php break; ?>
                            @endif
                        @endforeach

                    @endforeach
                    <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                </td>


                <td class="note-column">
                    <?php  $nbValid=0;$nbNotes=0;  ?>
                    @foreach($PV as $pv)

                        @foreach($pv->matieres as $mat)
                            @if($mat->id_matiere==$m->id )
                                <?php $nbNotes++ ?>
                                @if(floatval($mat->note)>=50)
                                    <?php $nbValid++; ?>
                                @endif
                                <?php break; ?>
                            @endif
                        @endforeach

                    @endforeach
                    <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                </td>

                <?php $j++;
                $current_note=$j;
                ?>
                <td ></td>
                <td></td>
                <td></td>

            </tr>
            </tbody>
        </table>
    </div>





    <div class="">
        <table class="table table-note table-bordered table-striped table-condensed table-inverse" style="margin-top: 5px;">
            <thead>
            <tr>
                <th class="number-column" rowspan="3">#</th>
                <th class="spec-column" rowspan="3">Matricule/Matricle</th>
                <th class="nom-column" rowspan="3">Nom/Name</th>
                <?php $j=0;$j2=0; ?>
                @foreach($groupe_matieres as $m)
                    @if($j2>=$standartLimite)
                        <?php break; ?>
                    @endif
                    <th class="matiere-column" colspan="{{$m->nbComposes*3}}"><p>{{$m->nom}}</p></th>
                    <?php $j++;$j2+=$m->nbComposes;
                    $current_matiere=$j2;
                    $current_group_matiere=$j;

                    ?>
                @endforeach
                @if($current_matiere==$nb_matieres)
                    <th class="note-column" rowspan="3">Moy/20</th>
                    <th class="note-column" rowspan="3">MGP</th>
                    <th class="note-column" rowspan="3">Decision</th>
                @endif

            </tr>
            <tr>
                <?php $j=0;
                $e=0;
                ?>

                @foreach($matieres as $m)
                    @if($j>=$j2)
                        <?php break; ?>
                    @endif
                    <th colspan="3">{{$m->nom}}</th>

                    <?php $j++;
                    //$current_matiere=$j;
                    ?>
                @endforeach
            </tr>
            <tr>
                <?php $j=0; ?>

                @foreach($matieres as $m)
                    @if($j>=$j2)
                        <?php break; ?>
                    @endif
                    <th>CC</th>
                    <th>SN</th>
                    <th>Total/100  </th>
                    <?php $j++;
                    //$current_matiere=$j;
                    ?>

                @endforeach
            </tr>


            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($eleves as $e)
                <?php $total=0; ?>
                <tr>
                    <td class="number-column">{{$i++}}</td>
                    <td class="mat-column">{{$e->matricule}}</td>
                    <td>{{$e->nom.' '.$e->prenom}}</td>

                    <?php $j=0; ?>
                    @foreach($matieres as $m)
                        @if($j>=$j2)
                            <?php break; ?>
                        @endif

                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            {{$mat->controle}}
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                @endif
                                @if($finded)
                                    <?php break; ?>
                                @endif
                            @endforeach

                        </td>
                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            {{$mat->normal}}
                                            <?php break; ?>
                                        @endif
                                    @endforeach
                                @endif
                                @if($finded)
                                    <?php break; ?>
                                @endif
                            @endforeach

                        </td>

                        <td class="note-column">
                            <?php $finded=false; ?>
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    <?php $finded=true; ?>
                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            {{$mat->note}}
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
                        $current_note=$j;
                        ?>
                    @endforeach
                    @if($current_matiere==$nb_matieres)
                        <?php $mgp=0;?>
                        <td class="note-column">
                            @foreach($PV as $pv)
                                @if($pv->id_eleve==$e->id)
                                    {{round(($pv->mgp)/5,2)}}
                                    <?php $mgp=round(($pv->mgp)/25,2);
                                    if($mgp>=2)
                                    {$admis++;}
                                    else{$echec++;}
                                    break; ?>
                                @endif

                            @endforeach

                        </td>

                        <td class="note-column">{{$mgp}}</td>
                        <td class="note-column">
                            {{getDecision($mgp)}}
                        </td>
                    @endif

                </tr>

            @endforeach
            <tr style="font-size: 13px">
                <td></td>
                <td></td>
                <td><b>%  validation</b></td>

                <?php $j=0;   ?>
                @foreach($matieres as $m)
                    @if($j>=$j2)
                        <?php break; ?>
                    @endif
                    <td class="note-column">
                        <?php  $nbValid=0;$nbNotes=0;  ?>
                        @foreach($PV as $pv)

                            @foreach($pv->matieres as $mat)
                                @if($mat->id_matiere==$m->id )
                                    <?php $nbNotes++ ?>
                                    @if(floatval($mat->controle)>=15)
                                        <?php $nbValid++; ?>
                                    @endif
                                    <?php break; ?>
                                @endif
                            @endforeach

                        @endforeach
                        <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                    </td>
                    <td class="note-column">
                        <?php  $nbValid=0;$nbNotes=0;  ?>
                        @foreach($PV as $pv)

                            @foreach($pv->matieres as $mat)
                                @if($mat->id_matiere==$m->id )
                                    <?php $nbNotes++ ?>
                                    @if(floatval($mat->normal)>=35)
                                        <?php $nbValid++; ?>
                                    @endif
                                    <?php break; ?>
                                @endif
                            @endforeach

                        @endforeach
                        <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                    </td>


                    <td class="note-column">
                        <?php  $nbValid=0;$nbNotes=0;  ?>
                        @foreach($PV as $pv)

                            @foreach($pv->matieres as $mat)
                                @if($mat->id_matiere==$m->id )
                                    <?php $nbNotes++ ?>
                                    @if(floatval($mat->note)>=50)
                                        <?php $nbValid++; ?>
                                    @endif
                                    <?php break; ?>
                                @endif
                            @endforeach

                        @endforeach
                        <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                    </td>
                    <?php $j++;
                    $current_note=$j;
                    ?>
                @endforeach


            </tr>
            </tbody>
        </table>
    </div>

    <?php $standartLimite=9;$group_limited=$current_group_matiere; ?>


    @while($current_matiere<$nb_matieres)

        <div class="table-matiere table-note">
            <div class="titre">
                <p style="font-size: 19px"><strong>Bilan annuel </strong></p>
                <strong class="traduction">Annual review</strong>
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

            <table class="table table-bordered table-striped table-condensed table-inverse" style="">
                <thead>

                <?php $j=$current_matiere;$limite=$current_matiere+$standartLimite;$k=0; ?>
                <?php $j2=$group_limited ;?>
                <?php $j_matieres=0 ;?>

                <?php $j0=$j;$current_matiere0=$current_matiere;$k0=$k;$limite0=$limite ?>
                <?php $j1=$j;$current_matiere1=$current_matiere;$k1=$k;$limite1=$limite ?>
                <?php $current_matiere3=$current_matiere; ?>


                <tr>
                    @foreach($groupe_matieres as $m)

                        @if($k>=$group_limited)

                            @if($j>=$limite)
                                <?php break; ?>
                            @endif

                            <th class="matiere-column" colspan="{{$m->nbComposes*3}}"><p>{{$m->nom}}</p></th>
                            <?php
                            $j+=$m->nbComposes;

                            $j_matieres=$j;
                            $j2++;
                            ?>
                        @endif
                        <?php $k++; ?>


                    @endforeach
                    <?php $group_limited=$j2; ?>


                    @if($j_matieres==$nb_matieres)
                        <th class="note-column" rowspan="3">Moy/20</th>
                        <th class="note-column" rowspan="3">MGP</th>
                        <th class="note-column" rowspan="3">Decision</th>
                    @endif

                </tr>
                <tr>

                    @foreach($matieres as $m)
                        @if($k1>=$current_matiere)

                            @if($j1>=$j_matieres)
                                <?php break; ?>
                            @endif
                            <th colspan="3">{{$m->nom}}</th>

                            <?php $j1++;
                            $current_matiere=$j1;

                            ?>
                        @endif
                        <?php $k1++; ?>

                    @endforeach
                </tr>

                <tr>

                    @foreach($matieres as $m)
                        @if($k0>=$current_matiere3)

                            @if($j0>=$j_matieres)
                                <?php break; ?>
                            @endif
                            <th>CC</th>
                            <th>SN</th>
                            <th>Total/100  </th>
                            <?php $j0++;
                            $current_matiere0=$j0;
                            ?>
                        @endif
                        <?php $k0++; ?>

                    @endforeach
                </tr>

                </thead>
                <tbody>
                <?php $i=1;$t=0; ?>
                @foreach($eleves as $e)
                    <?php $total=0; ?>
                    <tr>
                        <?php $j=$current_matiere3;$limite=$j_matieres;$k=0;$current_note2=$current_matiere3; ?>
                        @foreach($matieres as $m)
                            @if($k>=$current_matiere3)

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
                                                    {{$mat->controle}}
                                                    <?php break; ?>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if($finded)
                                            <?php break; ?>
                                        @endif
                                    @endforeach

                                </td>
                                <td class="note-column">
                                    <?php $finded=false; ?>
                                    @foreach($PV as $pv)
                                        @if($pv->id_eleve==$e->id)
                                            <?php $finded=true; ?>
                                            @foreach($pv->matieres as $mat)
                                                @if($mat->id_matiere==$m->id )
                                                    {{$mat->normal}}
                                                    <?php break; ?>
                                                @endif
                                            @endforeach
                                        @endif
                                        @if($finded)
                                            <?php break; ?>
                                        @endif
                                    @endforeach

                                </td>


                                <td class="note-column">
                                    <?php $finded=false; ?>
                                    @foreach($PV as $pv)
                                        @if($pv->id_eleve==$e->id)
                                            <?php $finded=true; ?>
                                            @foreach($pv->matieres as $mat)
                                                @if($mat->id_matiere==$m->id )
                                                    {{$mat->note}}
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

                        <?php $j=$current_matiere3; ?>
                        @if($current_matiere==$nb_matieres)
                            <td class="note-column">
                                <?php $mgp=0 ?>
                                @foreach($PV as $pv)
                                    @if($pv->id_eleve==$e->id)
                                        {{round(($pv->mgp)/5,2)}}
                                        <?php $mgp=round(($pv->mgp)/25,2);
                                        if($mgp>=2)
                                        {$admis++;}
                                        else{$echec++;}
                                        break; ?>
                                    @endif

                                @endforeach

                            </td>

                            <td class="note-column">{{$mgp}}</td>
                            <td class="note-column">{{getDecision($mgp)}}</td>
                        @endif
                    </tr>

                @endforeach
                <tr>
                    <?php $j=$current_matiere3;$limite=$j_matieres;$k=0; ?>
                    @foreach($matieres as $m)
                        @if($k>=$current_note)

                            @if($j>=$limite)
                                <?php break; ?>
                            @endif

                            <td class="note-column">
                                <?php  $nbValid=0;$nbNotes=0;  ?>
                                @foreach($PV as $pv)

                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            <?php $nbNotes++ ?>
                                            @if(floatval($mat->controle)>=15)
                                                <?php $nbValid++; ?>
                                            @endif
                                            <?php break; ?>
                                        @endif
                                    @endforeach

                                @endforeach
                                <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                            </td>
                            <td class="note-column">
                                <?php  $nbValid=0;$nbNotes=0;  ?>
                                @foreach($PV as $pv)

                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            <?php $nbNotes++ ?>
                                            @if(floatval($mat->normal)>=35)
                                                <?php $nbValid++; ?>
                                            @endif
                                            <?php break; ?>
                                        @endif
                                    @endforeach

                                @endforeach
                                <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>


                            </td>


                            <td class="note-column">
                                <?php  $nbValid=0;$nbNotes=0;  ?>
                                @foreach($PV as $pv)

                                    @foreach($pv->matieres as $mat)
                                        @if($mat->id_matiere==$m->id )
                                            <?php $nbNotes++ ?>
                                            @if(floatval($mat->note)>=50)
                                                <?php $nbValid++; ?>
                                            @endif
                                            <?php break; ?>
                                        @endif
                                    @endforeach

                                @endforeach
                                <b>{{round(($nbValid/$nbNotes)*100,2)}} %</b>

                            </td>
                            <?php $j++;
                            //$t=$j;
                            ?>
                        @endif
                        <?php $k++; ?>

                    @endforeach
                </tr>
                <?php $current_note=$t; ?>

                </tbody>
            </table>
        </div>
    @endwhile
    @if($admis==0 && $echec== 0)
        <h4><strong>@lang('admin.admis') : </strong> 0 %</h4>
        <h4><strong>@lang('admin.echec') : </strong> 0 %</h4>
    @else
        <h4><strong>@lang('admin.admis') : </strong> {{round(($admis/($admis+$echec))*100,2)}} %</h4>
        <h4><strong>@lang('admin.echec') : </strong> {{round(($echec/($admis+$echec))*100,2)}} %</h4>
    @endif


    <h3 style="text-align: right;">Responsable des Affaires Academiques </h3>


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
