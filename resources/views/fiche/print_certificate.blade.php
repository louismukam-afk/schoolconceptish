@extends('index')
@section('content')

    @if($inscription==null)
        <div class="alert alert-danger">
            @lang('inscription.pas_inscrit')
        </div>
    @else
        <a href="" onclick="window.print()" class="btn btn-warning btn-lg">@lang('inscription.imprimer')</a>
        @include('perso.header1')
        <link rel="stylesheet" href="{{URL::asset('css/certificat.css')}}">
        <div class="titre">
            @if(App::getLocale()!="en")
            <p><strong>CERTIFICAT DE SCOLARITE</strong> </p>
            @else
                <p><strong >SCHOOL CERTFICATE</strong></p>
                @endif

        </div>

        <div class="body">
            @if(App::getLocale()!="en")
            <p>Le Directeur du {{$nom_etablissement}} (CEF HINTEL) atteste que l'étudiant(e) <strong>{{$eleve->nom.' '.$eleve->prenom}}</strong>
                Né(e) le {{$eleve->date_naiss}} à {{$eleve->lieu_naiss}} de matricule <strong>{{$eleve->matricule}}
                </strong>
                est régulièrement inscrit(e) pour l'année académique <strong>{{session('year')}}</strong> en filière  <strong>{{$niveau->nom}}</strong> spécialité <strong>{{$classe->nom}}</strong>
                au niveau  <strong>{{$nniveau->nom}}</strong> .<br>En foi de quoi le présent certificat lui est délivré pour servir et valoir ce que de droit.
            </p>
            @else
            <p>
                The manager of {{$nom_etablissement}}, {{$nom_directeur}} certifies that the student <strong> {{$eleve->nom. ' '. $eleve->prenom}} </strong> Born on {{$eleve->date_naiss}} at {{$eleve->lieu_naiss}} with registration number <strong> {{$eleve->matricule}}</Strong>
                is regularly registered for the academic year <strong> {{session ('year')}} </strong> In <strong> {{$niveau->nom}} </strong> speciality <strong> {{$classe->nom}} </strong> at level {{$nniveau->nom}}
                .This certificate is issued to him.
            </p>
            @endif

                @if(App::getLocale()!="en")
            <div class="fait" style="margin-top: 90px; margin-left: 450px;">
                <div>Yaoundé le {{date('d/m/Y') }}</div>

                <div class="sign" >Le Directeur</div>
            </div>

                @else
            <div class="fait" style="margin-top: 90px">
                <div>Made on {{date('d/m/Y') }}</div>

                <div class="sign">Le Directeur</div>
            </div>
                    @endif
        </div>


    @endif
@endsection