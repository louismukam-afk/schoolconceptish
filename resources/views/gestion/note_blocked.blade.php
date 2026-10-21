<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ isset($page_title) ? $page_title : 'Notes' }}</title>
    <link rel="shortcut icon" href="{{URL::asset('img/log.png')}}" type="image/x-icon">
    <link href="{{ URL::asset('dist/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('css/font-awesome.css') }}" rel="stylesheet" />
    <link href="{{ URL::asset('css/custom.css') }}" rel="stylesheet" media="screen"/>
</head>
<body>
<div class="container" style="margin-top: 40px;">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title"><span class="fa fa-lock"></span> Acces temporairement ferme</h3>
                </div>
                <div class="panel-body">
                    <h3>{{ $portal_lock->objectif ? $portal_lock->objectif : 'Consultation des notes suspendue' }}</h3>
                    <p class="lead">
                        {{ $portal_lock->message ? $portal_lock->message : 'La plateforme de consultation des notes est momentanement fermee pour permettre les operations academiques.' }}
                    </p>
                    <hr>
                    <p><strong>Etudiant :</strong> {{ $eleve->nom }} {{ $eleve->prenom }}</p>
                    <p><strong>Annee academique :</strong> {{ $selected_inscription->annee_scolaire }}</p>
                    <p><strong>Specialite :</strong> {{ $selected_inscription->classe ? $selected_inscription->classe->getNom() : '' }}</p>
                    <p><strong>Niveau :</strong> {{ $selected_inscription->nniveau ? $selected_inscription->nniveau->nom : '' }}</p>
                    @if($portal_lock->ends_at)
                        <p><strong>Reouverture prevue :</strong> {{ date('d/m/Y H:i', strtotime($portal_lock->ends_at)) }}</p>
                    @endif
                    <a href="{{ url('/gestion/login/etudiant/annees') }}" class="btn btn-default">
                        <span class="fa fa-arrow-left"></span> Retour aux inscriptions
                    </a>
                    <a href="{{ url('/gestion/login/etudiant/disconnect_user') }}" class="btn btn-danger pull-right">
                        {{ trans('admin.logout') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
