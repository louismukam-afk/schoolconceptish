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

    <link rel="stylesheet"  href="{{URL::asset('css/diplome.css')}}">
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
            @include('perso.diplome_header_soa')

            <div class="content" style="background-image: url('../img/fond_diplome.PNG')!important; background-size: 100%;  ">

                <div class="titre">
                    <p style="font-size: 18px;"><strong> LICENCE PROFESSIONNELLE  N° 002/ISH/yde ii-SOA</strong></p>
                    <p style="font-size: 18px;"><strong> BACHELOR’S DEGREE SUCCESS TESTIMONIAL </strong></p>
                    <br>

                </div>

                        <p class="texte1">Le DOYEN DE LA FACULTE DES SCIENCES ECONOMIQUES ET GESTION de<strong> l’UNIVERSITÉ DE YAOUNDE II -SOA</strong> et
                                le Président du Conseil d’Etablissement de <strong>l’CENTRE DE FORMATION HINTEL </strong>certifient que: / The DEAN OF THE FACULTY OF ECONOMICS AND MANAGEMENT  of the <strong>UNIVERSITY OF YAOUNDE II  -SOA</strong>
                                and the Chairman of the <strong> HINTEL TRAINING CENTER </strong> hereby certify that: </p>

                <div class="nom-matricule-container">
                    <div class="nom">
                        <p><strong>M. /Mlle: </strong>{{$releve->eleve->nom.' '.$releve->eleve->prenom}}</p>
                    </div>
                    <div class="matricule">
                        <p><strong>Matricule : </strong>{{$releve->eleve->matricule}}</p>

                    </div>
                </div>
                <div class="date-lieu-container" >
                    <div class="date">
                        <p><strong>Né(e) le / Born on: </strong> {{(new DateTime($releve->eleve->date_naiss))->format('d/m/Y')}}</p>
                    </div>
                    <div class="lieu">
                        <p><strong>A / At :</strong> {{$releve->eleve->lieu_naiss}}</p>
                    </div>
                </div>
                <div class="proces">
                    <p><strong>Vu le procès-verbal du jury en date du 28 Juillet 2021/ Mindful of the official report of the jury of July 28th  2021</strong></p>
                </div>
                <br>
                <div class="texte">
                  <p>  A suivi avec succès la formation pour l’obtention du diplôme de <strong>{{$cycle->nom}} </strong> , mention <strong>  {{$niveau->nom}} </strong>, parcours <strong> {{$classe->nom}} </strong>
                      / Has successfully completed the course work for award of a Bachelor’s degree in PROFESSIONAL LICENSE Specialization <strong> {{$classe->nom}} </strong>.</p><br>
                        <p><strong>Année académique : </strong> {{session('year')}}
                          <strong>  Mention Globale :</strong>

                            {{getMention($releve->mgp,100)}}

                            </p>
                        <p><strong class="traduction">Academic year                                        Overall Grade : </strong></p>

                 <p>  En foi de quoi, la présente attestation lui est délivrée pour servir et valoir ce que de droit. / This certificate is, therefore, issued to the above named to serve the purposes for which it is intented.</p>
                </div>
                    <p style="text-align: right;"><strong>YAOUNDE Le ..........................</strong></p>
                    <div class="signature">
                    <div class="dep1">
                        <p><strong><u>Le Directeur de l'Centre de Formation Hintel</u></strong></p>
                        <p><strong class="traduction">The Director of the Hintel Training Center</strong></p>


                    </div>
                    <div class="dep">
                        <p class="doyen"><u><strong>Le Doyen de la Faculté des Sciences Economiques et Gestion</strong></u></p>
                        <p><strong class="traduction">         The Dean of the Faculty of Economics and Management</strong></p>
                    </div>
                        </div>

                    <div class="sign-footer " style="text-align: center; font-family: 'Arial Black';font-size: small" >
                        <p>Il n’est délivré qu’un seul exemplaire de cette attestation de réussite. Le titulaire peut établir et faire certifier des copies conformes /
                            Only one success certificate is issued. The owner can establish and certify sample copies</p>
                        <!--  <p>Only one transcript shall be delivered. It is the owners interest to make certified true copies.</p>-->
                    </div>



    @endforeach
            </div>
        </div>
@endsection
@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/diplome_soa')}}">@lang('admin.choix_classe')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection