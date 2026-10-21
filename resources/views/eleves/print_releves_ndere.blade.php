@extends('skeleton')
@section('sub-content')
    @include('perso.functions')
    <?php
        $nderePageSize = (isset($page_size) && strtoupper($page_size) == 'A3') ? 'A3' : 'A4';
        $nderePageClass = strtolower($nderePageSize);
        $ndereQuery = \Input::all();
        $ndereQuery['page_size'] = 'a4';
        $ndereA4Url = \URL::current().'?'.http_build_query($ndereQuery);
        $ndereQuery['page_size'] = 'a3';
        $ndereA3Url = \URL::current().'?'.http_build_query($ndereQuery);
    ?>

    <style>
        .result-table thead,.result-table thead th
        {
            height: auto;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }
        .transcript-note { font-size: 9.5px; line-height: 1.25; margin: 10px 5px 0; text-align: left; }
        .ndere-identity-grid { display: table; width: 100%; font-size: 10.5px; line-height: 1.1; margin: 8px 0; }
        .ndere-identity-left, .ndere-identity-right { display: table-cell; vertical-align: top; width: 50%; padding: 0 8px; }
        .ndere-identity-right { padding-left: 18px; }
        .ndere-identity-row { display: table; width: 100%; margin: 0 0 5px; }
        .ndere-identity-label { display: table-cell; width: 39%; vertical-align: top; font-weight: bold; }
        .ndere-identity-value { display: table-cell; vertical-align: top; }
        .ndere-identity-label em { display: block; font-weight: normal; }
        .ndere-identity-date { display: table; width: 100%; }
        .ndere-identity-date > div { display: table-cell; width: 50%; vertical-align: top; }
        .sign-footer .jury,
        .sign-footer .dep { text-align: center; }
        .releve.ndere-page-a3 {
            font-size: 11.5px;
        }
        .releve.ndere-page-a3 .module-summary,
        .releve.ndere-page-a3 .ndere-semester-summary,
        .releve.ndere-page-a3 .ndere-diploma-summary,
        .releve.ndere-page-a3 .ndere-diploma-legend {
            font-size: 10px;
        }
        .ndere-export-table-wrap {
            position: absolute;
            left: -99999px;
            top: 0;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
    </style>
    <div class="btn-toolbar no-print" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/releves_ndere') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-list"></span> Page Ngaoundere
        </a>
        <a href="{{ url('/eleves/releves_ndere/historique') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-folder-open"></span> Historique
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
        <a href="{{ $ndereA4Url }}" class="btn {{ $nderePageSize == 'A4' ? 'btn-primary' : 'btn-default' }}">
            <span class="glyphicon glyphicon-file"></span> A4 paysage
        </a>
        <a href="{{ $ndereA3Url }}" class="btn {{ $nderePageSize == 'A3' ? 'btn-primary' : 'btn-default' }}">
            <span class="glyphicon glyphicon-file"></span> A3 paysage
        </a>
        <button type="button" class="btn btn-success" id="ndere-export-excel">
            <span class="glyphicon glyphicon-download-alt"></span> Export Excel
        </button>
        <a href="#" class="btn btn-lg btn-warning" onclick="window.print()">
            <span class="glyphicon glyphicon-print"></span> @lang('inscription.imprimer')
        </a>
    </div>

    <link rel="stylesheet"  href="{{URL::asset('css/releves.css')}}">
    <style type="text/css">
        @media print {
            .no-print {
                display: none !important;
            }
            .ndere-export-table-wrap {
                display: none !important;
            }
            @page {
                size: {{ $nderePageSize }} landscape;
                margin: 4mm 4mm 4mm 4mm;
            }
            html,
            body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .releve {
                font-size: {{ $nderePageSize == 'A3' ? '10.8px' : '8.8px' }};
                min-height: {{ $nderePageSize == 'A3' ? '287mm' : '200mm' }};
                height: {{ $nderePageSize == 'A3' ? '287mm' : '200mm' }};
                margin: 0;
                box-sizing: border-box;
                page-break-after: always;
                overflow: hidden;
            }
            .releve:last-child {
                page-break-after: auto;
            }
            .content,
            .module-summary,
            .ndere-semester-summary,
            .ndere-diploma-summary,
            .ndere-diploma-legend,
            .result-table {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse !important;
            }
            .module-summary th,
            .module-summary td,
            .ndere-semester-summary th,
            .ndere-semester-summary td,
            .ndere-diploma-summary td,
            .ndere-diploma-legend td,
            .result-table th,
            .result-table td {
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
            }
            .releve-semester-six {
                font-size: {{ $nderePageSize == 'A3' ? '10.2px' : '8.4px' }};
            }
            .releve-semester-six .titre {
                margin-top: 1mm;
                margin-bottom: 2mm;
            }
            .releve-semester-six .titre p {
                font-size: 14px !important;
            }
            .releve-semester-six .ndere-identity-grid {
                font-size: 8.8px;
                line-height: 1;
                margin: 2px 0;
            }
            .releve-semester-six .ndere-identity-row {
                margin-bottom: 2px;
            }
            .releve-semester-six .ndere-identity-left,
            .releve-semester-six .ndere-identity-right {
                padding-left: 4px;
                padding-right: 4px;
            }
            .releve-semester-six .ndere-identity-right {
                padding-left: 8px;
            }
            .releve-semester-six .module-summary,
            .releve-semester-six .ndere-semester-summary,
            .releve-semester-six .ndere-diploma-summary,
            .releve-semester-six .ndere-diploma-legend {
                margin-top: 2px !important;
                margin-bottom: 0 !important;
                font-size: {{ $nderePageSize == 'A3' ? '9.4px' : '7.8px' }};
                line-height: 1;
            }
            .releve-semester-six .module-summary {
                width: 100%;
                table-layout: fixed;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .releve-semester-six .module-summary td:nth-child(1) {
                width: 8%;
            }
            .releve-semester-six .module-summary td:nth-child(2) {
                width: 26%;
            }
            .releve-semester-six .module-summary td:nth-child(3) {
                width: 7%;
            }
            .releve-semester-six .module-summary td:nth-child(n+4) {
                width: 8%;
            }
            .releve-semester-six .module-summary th,
            .releve-semester-six .module-summary td,
            .releve-semester-six .ndere-semester-summary th,
            .releve-semester-six .ndere-semester-summary td,
            .releve-semester-six .ndere-diploma-summary td,
            .releve-semester-six .ndere-diploma-legend td {
                padding: 1px 2px !important;
                line-height: 1.05;
            }
            .releve-semester-six .module-summary th {
                font-size: {{ $nderePageSize == 'A3' ? '8.8px' : '7.2px' }};
            }
            .releve-semester-six .module-summary br {
                line-height: 1;
            }
            .releve-semester-six .ndere-diploma-legend td {
                font-size: {{ $nderePageSize == 'A3' ? '8.8px' : '7.2px' }} !important;
            }
            .sign-footer {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 24mm;
                margin: 0;
                min-height: 12mm;
                padding: 0 8mm;
                display: table;
                width: 100%;
                box-sizing: border-box;
            }
            .sign-footer .jury,
            .sign-footer .dep {
                display: table-cell;
                position: static;
                width: 50%;
                vertical-align: top;
                margin: 0;
                padding: 0 6mm;
                text-align: center;
            }
            .sign-footer .jury {
                text-align: center;
            }
            .sign-footer .dep {
                text-align: center;
            }
            .transcript-note {
                position: absolute;
                left: 5px;
                right: 5px;
                bottom: 0;
                margin: 0;
            }
        }
    </style>
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
        ?>
        <div class="releve ndere-page-{{ $nderePageClass }} {{ (int) $trimestre === 6 ? 'releve-semester-six' : '' }}">
            @include('perso.header_releve_tutelle')

            <div class="content">

                <div class="titre">
                    <p style="font-size: 18px;"><strong>RELEVE DE NOTES DU SEMESTRE {{$trimestre}} / SEMESTER {{$trimestre}} TRANSCRIPT</strong></p>
                    <strong>
                        <p class="big-traduction"><strong> </strong></p>
                    </strong>
                </div>
                <div class="ndere-identity-grid">
                    <div class="ndere-identity-left">
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Noms & Prénoms :<em>Name and Surname</em></div>
                            <div class="ndere-identity-value"><strong>{{$releve->eleve->nom.' '.$releve->eleve->prenom}}</strong></div>
                        </div>
                        <div class="ndere-identity-date">
                            <div class="ndere-identity-row">
                                <div class="ndere-identity-label">Date de Naissance :<em>Born on</em></div>
                                <div class="ndere-identity-value">{{(new DateTime($releve->eleve->date_naiss))->format('d/m/Y')}}</div>
                            </div>
                            <div class="ndere-identity-row">
                                <div class="ndere-identity-label">A :<em>At</em></div>
                                <div class="ndere-identity-value">{{$releve->eleve->lieu_naiss}}</div>
                            </div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Sexe :<em>Sex</em></div>
                            <div class="ndere-identity-value">{{isset($releve->eleve->sexe) ? $releve->eleve->sexe : ''}}</div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Matricule :<em>Registration Number</em></div>
                            <div class="ndere-identity-value">{{$releve->eleve->matricule}}</div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Année Académique :<em>Academic Year</em></div>
                            <div class="ndere-identity-value">{{session('year')}}</div>
                        </div>
                    </div>
                    <div class="ndere-identity-right">
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Domaine :<em>Area</em></div>
                            <div class="ndere-identity-value">{{isset($niveau->domaines) ? $niveau->domaines : ''}}</div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Filière :<em>Field of study</em></div>
                            <div class="ndere-identity-value">{{$niveau->nom}}</div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Spécialité :<em>Speciality</em></div>
                            <div class="ndere-identity-value">{{$classe->nom}}</div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Cycle :<em>Degree</em></div>
                            <div class="ndere-identity-value">{{$cycle->nom}}</div>
                        </div>
                        <div class="ndere-identity-row">
                            <div class="ndere-identity-label">Semestre :<em>Semester</em></div>
                            <div class="ndere-identity-value"><strong>: {{$trimestre}}</strong></div>
                        </div>
                    </div>
                </div>
                @include('eleves.partials.releve_ndere_modules')
                @include('eleves.partials.releve_ndere_semester_summary')
                <table class="table table-striped table-condensed table-inverse" style="{{ !empty($module_groups) ? 'display:none;' : '' }} margin-top: auto; margin-bottom: 1px;">
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
                            <?php
                                $matiereNote = isset($matiere->note) && is_numeric($matiere->note) ? (float) $matiere->note : null;
                                $matiereCredit = isset($matiere->credit) && is_numeric($matiere->credit) ? (float) $matiere->credit : 0;
                                if ($matiereNote !== null && $matiereCredit > 0) {
                                    $sNote += ($matiereNote * $matiereCredit);
                                    $subSnote += ($matiereNote * $matiereCredit);
                                    $sCoef += $matiereCredit;
                                    $subScoef += $matiereCredit;
                                }
                                $TMP++;
                            ?>
                            @if($matiereNote !== null && $matiereNote>=50)
                                <?php $TMV++; ?>
                            @endif
                            <td> {{$matiere->id_code}}</td>

                            <td> {{$matiere->altName}}</td>
                            <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                            <td style="text-align: center">{{$matiere->credit}}</td>
                            <!--  <td>{{$matiere->note}}</td>-->
                            <td style="text-align: center">{{ $matiereNote !== null ? round($matiereNote/5,6) : '' }} </td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getGrade($matiereNote)[1] : '' }}</td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getGrade($matiereNote)[0] : '' }}</td>

                            <td style="text-align: center">{{ $matiereNote !== null ? getMention($matiereNote,100) : '' }}</td>

                            </tr>

                        @endif
                    @endforeach
                    <?php $subSaverage = $subScoef > 0 ? $subSnote / $subScoef : null; ?>
                    <tr >
                        <td colspan="2"></td>
                        <td style="text-align: center;" >
                            <strong> Moy/Av </strong>
                        </td>
                        <td style="text-align: center;">{{$subScoef}}</td>
                        <td  style="text-align: center;">
                            <strong>{{ $subSaverage !== null ? round($subSaverage / 5, 6) : '' }} </strong>
                        </td>
                        <td style="text-align: center">{{ $subSaverage !== null ? getGrade($subSaverage)[1] : '' }}</td>
                        <td  style="text-align: center;">
                            <strong > {{ $subSaverage !== null ? getGrade($subSaverage)[0] : '' }}  </strong>
                        </td>
                        <td style="text-align: center">{{ $subSaverage !== null ? getMention($subSaverage, 100) : '' }}</td>

                    </tr>

                    <?php

                    $subSnote=0;
                    $subScoef=0;
                    ?>


                    <tr>
                        <td colspan="8"><strong style="font-size: 12px;">Unités d'enseignement Transversales\<span class="big-traductio">Common courses</span></strong></td>
                    </tr>
                    @foreach($releve->matieres as $matiere)
                        @if( ($matiere->type==1))
                            <?php
                                $matiereNote = isset($matiere->note) && is_numeric($matiere->note) ? (float) $matiere->note : null;
                                $matiereCredit = isset($matiere->credit) && is_numeric($matiere->credit) ? (float) $matiere->credit : 0;
                                if ($matiereNote !== null && $matiereCredit > 0) {
                                    $sNote += ($matiereNote * $matiereCredit);
                                    $subSnote += ($matiereNote * $matiereCredit);
                                    $sCoef += $matiereCredit;
                                    $subScoef += $matiereCredit;
                                }
                                $TMP++;
                            ?>
                            @if($matiereNote !== null && $matiereNote>=50)
                                <?php $TMV++; ?>
                            @endif
                            <td> {{$matiere->id_code}}</td>

                            <td> {{$matiere->altName}}</td>
                            <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                            <td style="text-align: center">{{$matiere->credit}}</td>
                            <!-- <td>{{$matiere->note}}</td>-->
                            <td style="text-align: center">{{ $matiereNote !== null ? round($matiereNote/5,6) : '' }} </td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getGrade($matiereNote)[1] : '' }}</td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getGrade($matiereNote)[0] : '' }}</td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getMention($matiereNote,100) : '' }}</td>
                            </tr>

                        @endif
                    @endforeach
                    <?php $subSaverage = $subScoef > 0 ? $subSnote / $subScoef : null; ?>
                    <tr >
                        <td colspan="2"></td>
                        <td style="text-align: center;" >
                            <strong> Moy/Av </strong>
                        </td>

                        <td style="text-align: center;">{{$subScoef}}</td>
                        <td  style="text-align: center;">
                            <strong>{{ $subSaverage !== null ? round($subSaverage / 5, 6) : '' }} </strong>
                        </td>
                        <td style="text-align: center">{{ $subSaverage !== null ? getGrade($subSaverage)[1] : '' }}</td>
                        <td  style="text-align: center;">
                            <strong > {{ $subSaverage !== null ? getGrade($subSaverage)[0] : '' }}  </strong>
                        </td>
                        <td style="text-align: center">{{ $subSaverage !== null ? getMention($subSaverage, 100) : '' }}</td>
                    </tr>




                    <?php

                    $subSnote=0;
                    $subScoef=0;
                    ?>
                    <tr>
                        <td colspan="8"><strong style="font-size: 12px;">Unités d'enseignements Professionnelles\<span class="big-traductio">Professional courses</span></strong></td>

                    </tr>
                    @foreach($releve->matieres as $matiere)
                        @if(($matiere->type==2))
                            <?php
                                $matiereNote = isset($matiere->note) && is_numeric($matiere->note) ? (float) $matiere->note : null;
                                $matiereCredit = isset($matiere->credit) && is_numeric($matiere->credit) ? (float) $matiere->credit : 0;
                                if ($matiereNote !== null && $matiereCredit > 0) {
                                    $sNote += ($matiereNote * $matiereCredit);
                                    $subSnote += ($matiereNote * $matiereCredit);
                                    $sCoef += $matiereCredit;
                                    $subScoef += $matiereCredit;
                                }
                                $TMP++;
                            ?>
                            @if($matiereNote !== null && $matiereNote>=50)
                                <?php $TMV++; ?>
                            @endif
                            <td> {{$matiere->id_code}}</td>

                            <td> {{$matiere->altName}}</td>
                            <td style="text-align: center">{{getSemestre($matiere->semestre)}}</td>
                            <td style="text-align: center">{{$matiere->credit}}</td>
                            <!-- <td>{{$matiere->note}}</td>-->
                            <td style="text-align: center">{{ $matiereNote !== null ? round($matiereNote/5,6) : '' }} </td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getGrade($matiereNote)[1] : '' }}</td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getGrade($matiereNote)[0] : '' }}</td>
                            <td style="text-align: center">{{ $matiereNote !== null ? getMention($matiereNote,100) : '' }}</td>
                            </tr>



                        @endif

                    @endforeach
                    <?php $subSaverage = $subScoef > 0 ? $subSnote / $subScoef : null; ?>
                    <tr >
                        <td colspan="2"></td>
                        <td style="text-align: center;" >
                            <strong> Moy/Av </strong>
                        </td>
                        <td style="text-align: center;">{{$subScoef}}</td>
                        <td  style="text-align: center;">
                            <strong>{{ $subSaverage !== null ? round($subSaverage / 5, 6) : '' }} </strong>
                        </td>
                        <td style="text-align: center">{{ $subSaverage !== null ? getGrade($subSaverage)[1] : '' }}</td>
                        <td  style="text-align: center;">
                            <strong > {{ $subSaverage !== null ? getGrade($subSaverage)[0] : '' }}  </strong>
                        </td>
                        <td style="text-align: center">{{ $subSaverage !== null ? getMention($subSaverage, 100) : '' }}</td>
                    <tr>

                    <tr>


                        <td colspan="4" style="text-align: center;">Total des crédits validés</td>
                        <td colspan="4">{{$sCoef}}</td>
                    </tr>

                    </tbody>
                </table>



                <table class="table result-table table-striped table-condensed table-inverse" style="{{ (int) $trimestre === 6 ? 'display:none;' : '' }} font-size: 11px;margin-top: auto;">
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
                        <?php $moy = $sCoef > 0 ? $sNote / $sCoef : null; ?>
                        <td rowspan="2">Moy.Annuelle/<span class="big-traductio">Annual Av.</span></td>
                        <td>/ 100 </td>
                        <td rowspan="2">{{$TMP}}</td>
                        <td rowspan="2">{{$TMV}}</td>
                        <td>{{ $moy !== null ? round($moy, 6) : '' }}</td>
                        <td rowspan="2">{{ $moy !== null ? round($moy / 25, 6) : '' }}</td>
                        <td rowspan="2">{{ $moy !== null ? getGrade($moy)[0] : '' }}</td>
                        <td colspan="2">{{ $moy !== null ? getMention($moy, 100) : '' }}</td>


                    </tr>
                    <tr>
                        <td>/ 20</td>
                        <td>{{ $moy !== null ? round($moy / 5, 6) : '' }}</td>
                        <td>Decision :</td>
                        <td><strong style="font-size: 13px">{{ $moy !== null ? getDecision(round($moy / 25, 6)) : '' }}</strong></td>
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
                    <p><strong><u>Le Directeur de l'Centre de Formation Hintel</u></strong></p>
                </div>
                <div class="dep">
                    <p><strong><u>Le Directeur de l'IUT</u></strong></p>
                </div>
            </div>
            <div class="releve-fait-le" style="font-size: 10px;display: none">
                <p>Fait le {{date('d-m-Y')}}</p>
            </div>
            <div class="transcript-note">
                <strong>NB :</strong> Il n'est délivré qu'un seul relevé de notes. Il appartient au titulaire d'établir lui-même et faire certifier conformes les copies qui peuvent lui être nécessaires.<br><br>
                <em>Une seule copie de ce relevé de notes sera délivrée. Il est dans l’intérêt du propriétaire de réaliser autant de copies certifiées conformes qu’il le souhaite.</em>
            </div>
        </div>
        <?php $ne++; ?>
    @endforeach

    <div class="ndere-export-table-wrap no-print">
        <table id="ndere-export-table" class="table table-bordered table-condensed">
            <thead>
            <tr>
                <th>Matricule</th>
                <th>Noms et prenoms</th>
                <th>Classe</th>
                <th>Cycle</th>
                <th>Niveau</th>
                <th>Semestre</th>
                <th>Code</th>
                <th>Intitule</th>
                <th>Credits</th>
                <th>Note /20</th>
                <th>Note /100</th>
                <th>Grade</th>
                <th>MGP</th>
                <th>Appreciation</th>
            </tr>
            </thead>
            <tbody>
            @foreach($PV as $exportReleve)
                @foreach($exportReleve->matieres as $exportMatiere)
                    <?php
                        $exportNote = isset($exportMatiere->note) && is_numeric($exportMatiere->note) ? (float) $exportMatiere->note : null;
                        $exportGrade = $exportNote !== null ? getGrade($exportNote) : array('', '');
                    ?>
                    <tr>
                        <td>{{ isset($exportReleve->eleve->matricule) ? $exportReleve->eleve->matricule : '' }}</td>
                        <td>{{ isset($exportReleve->eleve) ? trim($exportReleve->eleve->nom.' '.$exportReleve->eleve->prenom) : '' }}</td>
                        <td>{{ isset($classe->nom) ? $classe->nom : '' }}</td>
                        <td>{{ isset($cycle->nom) ? $cycle->nom : '' }}</td>
                        <td>{{ isset($nniveau->nom) ? $nniveau->nom : '' }}</td>
                        <td>{{ isset($exportMatiere->semestre) ? getSemestre($exportMatiere->semestre) : $trimestre }}</td>
                        <td>{{ isset($exportMatiere->id_code) ? $exportMatiere->id_code : '' }}</td>
                        <td>{{ isset($exportMatiere->altName) ? $exportMatiere->altName : (isset($exportMatiere->nom) ? $exportMatiere->nom : '') }}</td>
                        <td>{{ isset($exportMatiere->credit) ? $exportMatiere->credit : '' }}</td>
                        <td>{{ $exportNote !== null ? number_format($exportNote / 5, 2) : '' }}</td>
                        <td>{{ $exportNote !== null ? number_format($exportNote, 2) : '' }}</td>
                        <td>{{ $exportNote !== null ? $exportGrade[0] : '' }}</td>
                        <td>{{ $exportNote !== null ? $exportGrade[1] : '' }}</td>
                        <td>{{ $exportNote !== null ? getMention($exportNote, 100) : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $(function () {
            if (!$.fn.DataTable) {
                return;
            }

            var ndereExportTable = $('#ndere-export-table').DataTable({
                dom: 'Bfrtip',
                paging: false,
                searching: false,
                ordering: false,
                info: false,
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export Excel',
                        title: 'Releves Ngaoundere {{ session('year') }}'
                    }
                ]
            });

            $('#ndere-export-excel').on('click', function () {
                ndereExportTable.button('.buttons-excel').trigger();
            });
        });
    </script>

@endsection
