@extends('skeleton')
@section('sub-content')
    @include('perso.functions')



    <style>
        .result-table thead,.result-table thead th
        {
            height: auto;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
    </style>
    <a href="#" class="btn btn-lg btn-warning" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> @lang('inscription.imprimer')</a>

    <link rel="stylesheet"  href="{{URL::asset('css/releves.css')}}">
    <?php $ne=0 ?>
    @foreach($PV as $releve)
        <?php
        $sCoef=0;
        $TMP=0;
        $TMV=0;
        $sNote=0;
        $subSnote=0;
        $subScoef1=0;
        $subScoef2=0;
        $subScoef3=0;
        $subScoef=0;
        $credits=0;
        $coefficientPondere=0;
        ?>
        <div class="releve">
            @include('perso.header_releve_tutelle')

            <div class="content">

                <div class="titre">
                    <p style="font-size: 18px;"><strong>RELEVE DE NOTES ANNUEL/ANNUAL TRANSCRIPT</strong></p>
                    <strong>
                        <p class="big-traduction"><strong> </strong></p>
                    </strong>
                </div>
                <div class="nom-matricule-container">
                    <div class="nom">
                        <p><strong>Nom et Prénoms / Surname and name : </strong>{{$releve->eleve->nom.' '.$releve->eleve->prenom}}</p>
                    </div>
                    <div class="matricule">
                        <p><strong>Matricule : </strong>{{$releve->eleve->matricule}}</p>

                    </div>
                </div>
                <div class="date-lieu-container">
                    <div class="date">
                        <p><strong>Né(e) le / Born on: </strong> {{(new DateTime($releve->eleve->date_naiss))->format('d/m/Y')}}</p>
                    </div>
                    <div class="lieu">
                        <p><strong>A / At :</strong> {{$releve->eleve->lieu_naiss}}</p>
                    </div>
                </div>
                <div class="cycle-filiere">
                    <div class="cycle">
                        <p><strong>Cycle : </strong> {{$cycle->nom}}</p>
                    </div>
                    <div class="filiere">
                        <p><strong>Filière/ Field : </strong> {{$niveau->nom}}</p>
                    </div>

                </div>

                <div class="specialite-annee">
                    <div class="specialite">
                        <p><strong>Spécialité/ Speciality : </strong> {{$classe->nom}} <strong>- Niveau/ level : </strong>{{$nniveau->nom}}</p>
                    </div>
                    <div class="annee_a">
                        <p><strong>Année académique/ Academic Year : </strong> {{session('year')}}</p>

                    </div>
                </div>
                <table class="table  table-striped table-condensed table-inverse" style="margin-top: auto; margin-bottom: 1px;">
                    <thead>
                    <th>Codes</th>
                    <th ><strong>Intitulé de l'UE/</strong> <span class="big-traductio">Course title</span></th>

                    <th class="note-column"><strong>Semestre <span class="big-traductio">Semester</span></strong>  </th>
                    <th class="note-column"><strong>Crédits </strong>  </th>
                    <!-- <th class="note-column"><strong>Note /100</strong>  </th>-->
                    <th class="note-column"><strong>Note /20</strong>  </th>
                    <th class="note-column"><strong>Qualité /4</strong>  </th>
                    <th class="note-column"><strong>Notation</strong> <span class="big-traductio">Grade</span></th>
                    <th class="note-column"><strong>Observations</strong></th>

                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="8"><strong style="font-size: 12px;">Unités d'enseignement Fondamentales\<span class="big-traductio">Common courses</span></strong></td>
                    </tr>

                    @foreach($releve->matieres as $matiere)
                        @if(($matiere->type==0))
                            <?php $sNote+= ($matiere->note);$subSnote+=$matiere->note; $sCoef+=$matiere->credit; $coefficientPondere+=$matiere->credit; $subScoef+=1;$TMP++; ?>
                            <!--                            --><?php //$sNote+= ($matiere->note*$matiere->credit);$subSnote+=$matiere->note; $sCoef+=$matiere->credit; $subScoef+=$matiere->credit;$TMP++; ?>
                            @if($matiere->note>=50)
                            <?php $TMV++; ?>
                        @endif
                        <td> {{$matiere->id_code}}</td>

                        <td> {{$matiere->altName}}</td>
                        <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                        <td style="text-align: center">{{$matiere->credit}}</td>
                        <!--  <td>{{$matiere->note}}</td>-->
                        <td style="text-align: center">{{round($matiere->note/5,6)}} </td>
                        <td style="text-align: center">{{getGrade($matiere->note)[1]}}</td>
                        <td style="text-align: center">{{getGrade($matiere->note)[0]}}</td>

                        <td style="text-align: center">{{getMention($matiere->note,100)}}</td>

                        </tr>

                        @endif
                    @endforeach
                    <tr >
                        <td colspan="2"></td>
                        <td style="text-align: center;" >
                            <strong> Moy/Av </strong>
                        </td>
                        <td style="text-align: center;">{{$coefficientPondere}}</td>
                        <td style="text-align: center;">
                            @if($subSnote<>null && $subScoef<>null)
                                <strong>{{ round(($subSnote/$subScoef)/5,3) }} </strong>
                            @else
                                <strong>0</strong>

                            @endif
                        </td>

                        <td style="text-align: center">
                            @if($subSnote<>null && $subScoef<>null)
                                {{getGrade($subSnote/$subScoef)[1]}}
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td  style="text-align: center;">
                            @if($subSnote<>null && $subScoef<>null)
                                <strong > {{getGrade(($subSnote/$subScoef))[0]}}  </strong>
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td style="text-align: center">
                            @if($subSnote<>null && $subScoef<>null)
                                {{getMention($subSnote/$subScoef,100)}}
                            @else
                                <strong>0</strong>

                            @endif
                        </td>

                    </tr>

                    <?php

                    $subSnote=0;
                    $subScoef=0;
                    $coefficientPondere=0;
                    ?>


                    <tr>
                        <td colspan="8"><strong style="font-size: 12px;">Unités d'enseignement Transversales\<span class="big-traductio">Common courses</span></strong></td>
                    </tr>
                    @foreach($releve->matieres as $matiere)
                        @if( ($matiere->type==1))
                            <!--                            --><?php //$sNote+= ($matiere->note*$matiere->credit);$subSnote+=$matiere->note*$matiere->credit; $sCoef+=$matiere->credit;$subScoef+=$matiere->credit;$TMP++; ?>
                            <!--                            --><?php //$sNote+= ($matiere->note*$matiere->credit);$subSnote+=$matiere->note*$matiere->credit; $sCoef+=$matiere->credit; $subScoef+=$matiere->credit;$TMP++; ?>
                            <?php $sNote+= ($matiere->note);$subSnote+=$matiere->note; $sCoef+=$matiere->credit; $coefficientPondere+=$matiere->credit; $subScoef+=1;$TMP++; ?>

                            @if($matiere->note>=50)
                                <?php $TMV++; ?>
                            @endif
                            <td> {{$matiere->id_code}}</td>

                            <td> {{$matiere->altName}}</td>
                            <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                            <td style="text-align: center">{{$matiere->credit}}</td>
                            <!-- <td>{{$matiere->note}}</td>-->
                            <td style="text-align: center">{{round($matiere->note/
                5,6)}} </td>
                            <td style="text-align: center">{{getGrade($matiere->note)[1]}}</td>
                            <td style="text-align: center">{{getGrade($matiere->note)[0]}}</td>
                            <td style="text-align: center">{{getMention($matiere->note,100)}}</td>
                            </tr>

                        @endif
                    @endforeach
                    <tr >
                        <td colspan="2"></td>
                        <td style="text-align: center;" >
                            <strong> Moy/Av </strong>
                        </td>

                        <td style="text-align: center;">{{$coefficientPondere}}</td>
                        <td  style="text-align: center;">
                            @if($subSnote<>null && $subScoef<>null)
                                <strong>{{ round(($subSnote/$subScoef)/5,3) }} </strong>
                            @else
                                <strong>0</strong>

                            @endif
                        </td>

                        <td style="text-align: center">
                            @if($subSnote<>null && $subScoef<>null)
                                {{getGrade($subSnote/$subScoef)[1]}}
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td  style="text-align: center;">
                            @if($subSnote<>null && $subScoef<>null)
                                <strong > {{getGrade(($subSnote/$subScoef))[0]}}  </strong>
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td style="text-align: center">
                            @if($subSnote<>null && $subScoef<>null)
                                {{getMention($subSnote/$subScoef,100)}}
                            @else
                                <strong>0</strong>

                            @endif
                        </td>


                    </tr>




                    <?php

                    $subSnote=0;
                    $subScoef=0;
                    $coefficientPondere=0;
                    ?>
                    <tr>
                        <td colspan="8"><strong style="font-size: 12px;">Unités d'enseignements Professionnelles\<span class="big-traductio">Professional courses</span></strong></td>

                    </tr>
                    @foreach($releve->matieres as $matiere)
                        @if(($matiere->type==2))
                            <!--                            --><?php //$sNote+= ($matiere->note*$matiere->credit);$subSnote+=$matiere->note*$matiere->credit; $sCoef+=$matiere->credit;$subScoef+=$matiere->credit;$TMP++; ?>
                            <!--                            --><?php //$sNote+= ($matiere->note*$matiere->credit);$subSnote+=$matiere->note*$matiere->credit; $sCoef+=$matiere->credit; $subScoef+=$matiere->credit;$TMP++; ?>
                            <?php $sNote+= ($matiere->note);$subSnote+=$matiere->note; $sCoef+=$matiere->credit; $coefficientPondere+=$matiere->credit; $subScoef+=1;$TMP++; ?>

                            @if($matiere->note>=50)
                                <?php $TMV++; ?>
                            @endif
                            <td> {{$matiere->id_code}}</td>

                            <td> {{$matiere->altName}}</td>
                            <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                            <td style="text-align: center">{{$matiere->credit}}</td>
                            <!-- <td>{{$matiere->note}}</td>-->
                            <td style="text-align: center">{{round($matiere->note/5,6)}} </td>
                            <td style="text-align: center">{{getGrade($matiere->note)[1]}}</td>
                            <td style="text-align: center">{{getGrade($matiere->note)[0]}}</td>
                            <td style="text-align: center">{{getMention($matiere->note,100)}}</td>
                            </tr>



                        @endif

                    @endforeach
                    <tr >
                        <td colspan="2"></td>
                        <td style="text-align: center;" >
                            <strong> Moy/Av </strong>
                        </td>
                        <td style="text-align: center;">{{$coefficientPondere}}</td>

                        <td  style="text-align: center;">
                            @if($subSnote<>null && $subScoef<>null)
                                <strong>{{ round(($subSnote/$subScoef)/5,3) }} </strong>
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td style="text-align: center">
                            @if($subSnote<>null && $subScoef<>null)
                                {{getGrade($subSnote/$subScoef)[1]}}
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td  style="text-align: center;">
                            @if($subSnote<>null && $subScoef<>null)
                                <strong > {{getGrade(($subSnote/$subScoef))[0]}}  </strong>
                            @else
                                <strong>0</strong>

                            @endif
                        </td>
                        <td style="text-align: center">
                            @if($subSnote<>null && $subScoef<>null)
                                {{getMention($subSnote/$subScoef,100)}}
                            @else
                                <strong>0</strong>

                            @endif
                        </td>

                    <tr>

                    <tr>


                        <td colspan="4" style="text-align: center;">Total des crédits validés</td>
                        <td colspan="4">{{$sCoef}}</td>
                    </tr>

                    </tbody>
                </table>



                <table class="table result-table table-striped table-condensed table-inverse" style="font-size: 11px;margin-top: auto;">
                    <thead>
                    <th colspan="2">Travail annuel</th>
                    <th>T.M.P</th>
                    <th>T.M.V</th>
                    <th>Moyenne</th>
                    <th>MGP</th>
                    <th>Cote</th>
                    <th colspan="2">Mention</th>
                    </thead>
                    <tbody style="text-align: center">
                    <tr>
                        <?php $moy=$sNote/$sCoef ?>
                        <td rowspan="2">Moy.Annuelle/<span class="big-traductio">Annual Av.</span></td>
                        <td>/ 100 </td>
                        <td rowspan="2">{{$TMP}}</td>
                        <td rowspan="2">{{$TMV}}</td>
                        <td>{{round($releve->mgp,3)}}</td>
                        <td rowspan="2">{{round($releve->mgp/25,3)}}</td>
                        <td rowspan="2">{{getGrade($releve->mgp)[0]}}</td>
                        <td colspan="2">{{getMention($releve->mgp,100)}}</td>


                    </tr>
                    <tr>
                        <td>/ 20</td>
                        <td>{{round($releve->mgp/5,3)}}</td>
                        <td>Decision :</td>
                        <td><strong style="font-size: 13px">{{getDecision(round($releve->mgp,3))}}</strong></td>
                    </tr>
                    <tr>
                        <td colspan="9">
                            <strong class="big-traductio">T.M.P: </strong> <em class="big-traductio">Total des modules prévus</em>
                            &emsp;
                            <strong class="big-traductio">T.M.V: </strong> <em class="big-traductio">Total des modules Validés</em>


                        </td>
                    </tr>
                    <tr> <td colspan="9" style="text-align: center;"><(Note: A+(90-100)=Excellent; A(80-89)=Très Bien; A-(75-79)=Bien; B(65-69)=Assez Bien; B-(60-64)=Passable; C+(55-59)=Passable; C(50-54)=Passable; C-(45-49)=Médiocre; D+(40-44)=Faible; D(35-39);E(30-34)=Echec; F(0-29)=Echec</td></tr>



                    </tbody>



                </table>

            </div>




            <div class="sign-footer">
                <div class="jury">
                    <p><strong class="big-traductio"></strong></p>
                    <P></P>
                    <p><strong><u>La Directrice des Affaires Académiques</u></strong></p>

                </div>
                <div class="dep">
                    <p></p>
                    <p><strong>YAOUNDE Le ..........................</strong></p>
                    <p class="doyen" style="margin-top: 5%"><u><strong>Le Doyen de la FSEG</strong></u></p>
                </div>
            </div>
            <div class="releve-fait-le" style="font-size: 10px;display: none">
                <p>Fait le {{date('d-m-Y')}}</p>
            </div>
            @if($ne>0)
                <div class="copy col-md-12" >
                    &copy;  2017 SchoolConcept | by: <a href="http://youth-concept.com" style="color:#fff;" target="_blank">youth-concept</a>
                </div>
            @endif
        </div>
        <?php $ne++; ?>
    @endforeach

@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/releve_estic')}}">@lang('admin.choix_classe')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
