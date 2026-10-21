@extends('index')

@section('content')
    <?php
        $roles=\Illuminate\Support\Facades\Auth::user()->roles->fetch('value')->toArray();
        $canAdmin=\gesecole\Functions::pp_exists($roles,2);
        $canCompta=\gesecole\Functions::pp_exists($roles,1) || \gesecole\Functions::contain($roles,16);
        $items=array(
            array('href'=>URL::to('/personnel/list/'), 'icon'=>'fa-users', 'label'=>'1. Personnel', 'show'=>true),
            array('href'=>URL::to('/emploi-du-temps/personnel-entites/'), 'icon'=>'fa-sitemap', 'label'=>'2. Affectation entites', 'show'=>true),
            array('href'=>route('plages_horaires'), 'icon'=>'fa-clock-o', 'label'=>'3. Plages horaires', 'show'=>$canAdmin),
            array('href'=>route('emplois_permanents'), 'icon'=>'fa-briefcase', 'label'=>'4. Emplois permanents', 'show'=>true),
            array('href'=>URL::to('/emploi-du-temps/salaires-permanents/'), 'icon'=>'fa-credit-card', 'label'=>'5. Salaires permanents', 'show'=>$canAdmin),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/#rubriques-salaire'), 'icon'=>'fa-list', 'label'=>'6. Rubriques salaire', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/#affectations-rubriques'), 'icon'=>'fa-link', 'label'=>'7. Affecter rubriques', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/#baremes-irpp'), 'icon'=>'fa-percent', 'label'=>'8. Baremes IRPP', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/#acomptes-personnel'), 'icon'=>'fa-minus-circle', 'label'=>'9. Acomptes', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/biometrie-permanents/'), 'icon'=>'fa-id-card', 'label'=>'10. Biometrie permanents', 'show'=>true),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/'), 'icon'=>'fa-calculator', 'label'=>'11. Generer paie', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/#bulletins-paie'), 'icon'=>'fa-list-alt', 'label'=>'12. Bulletins de paie', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/paie-permanents/#etats-paie'), 'icon'=>'fa-table', 'label'=>'13. Etats de paie', 'show'=>$canCompta),
            array('href'=>URL::to('/emploi-du-temps/discipline-personnels/'), 'icon'=>'fa-exclamation-triangle', 'label'=>'Discipline personnel', 'show'=>true),
            array('href'=>URL::to('/emploi-du-temps/matieres/'), 'icon'=>'fa-book', 'label'=>'Matieres', 'show'=>$canAdmin),
            array('href'=>URL::to('/emploi-du-temps/programmes-specialites/'), 'icon'=>'fa-book', 'label'=>'Programmes specialite', 'show'=>$canAdmin),
            array('href'=>route('cours_enseignants'), 'icon'=>'fa-calendar', 'label'=>'Cours enseignants', 'show'=>true),
            array('href'=>route('fiches_progression_enseignants'), 'icon'=>'fa-file-text-o', 'label'=>'Fiches de progression', 'show'=>true),
            array('href'=>route('emploi_temps_specialites'), 'icon'=>'fa-table', 'label'=>'Emplois specialites', 'show'=>true),
            array('href'=>route('emploi_temps_enseignants'), 'icon'=>'fa-user', 'label'=>'Emplois enseignants', 'show'=>true),
            array('href'=>URL::to('/emploi-du-temps/volumes-specialites'), 'icon'=>'fa-bar-chart', 'label'=>'Volumes horaires', 'show'=>true),
            array('href'=>route('decompte_heures'), 'icon'=>'fa-calculator', 'label'=>'Decompte heures', 'show'=>true),
            array('href'=>URL::to('/emploi-du-temps/salles/'), 'icon'=>'fa-building', 'label'=>'Salles', 'show'=>$canAdmin),
            array('href'=>URL::to('/emploi-du-temps/taux-horaires/'), 'icon'=>'fa-money', 'label'=>'Taux horaires', 'show'=>$canAdmin),
            array('href'=>URL::to('/personnel/dispenses/'), 'icon'=>'fa-table', 'label'=>'Ancien emploi du temps', 'show'=>$canAdmin),
        );
    ?>

    <div class="page-header">
        <h3>{{ $titre }}</h3>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="panel panel-primary text-center"><div class="panel-heading"><strong>{{ trans('admin.personnel') }}</strong></div><div class="panel-body"><span style="font-size: 28px">{{ $stats['personnel'] }}</span></div></div></div>
        <div class="col-md-3 col-sm-6"><div class="panel panel-info text-center"><div class="panel-heading"><strong>{{ trans('admin.enseignant') }}</strong></div><div class="panel-body"><span style="font-size: 28px">{{ $stats['enseignants'] }}</span></div></div></div>
        <div class="col-md-3 col-sm-6"><div class="panel panel-success text-center"><div class="panel-heading"><strong>Permanents</strong></div><div class="panel-body"><span style="font-size: 28px">{{ $stats['permanents'] }}</span></div></div></div>
        <div class="col-md-3 col-sm-6"><div class="panel panel-warning text-center"><div class="panel-heading"><strong>Vacataires</strong></div><div class="panel-body"><span style="font-size: 28px">{{ $stats['vacataires'] }}</span></div></div></div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6"><div class="alert alert-info"><strong>Specialites :</strong> {{ $stats['classes'] }}</div></div>
        <div class="col-md-3 col-sm-6"><div class="alert alert-info"><strong>Matieres :</strong> {{ $stats['matieres'] }}</div></div>
        <div class="col-md-3 col-sm-6"><div class="alert alert-success"><strong>Cours :</strong> {{ $stats['cours'] }}</div></div>
        <div class="col-md-3 col-sm-6"><div class="alert alert-warning"><strong>Plages :</strong> {{ $stats['horaires'] }}</div></div>
    </div>

    <div class="row text-center pad-top">
        @foreach($items as $item)
            @if($item['show'])
                <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6">
                    <div class="div-square">
                        <a href="{{ $item['href'] }}">
                            <i class="fa {{ $item['icon'] }} fa-5x"></i>
                            <h4>{{ $item['label'] }}</h4>
                        </a>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="row" style="margin-top: 15px">
        <div class="col-md-6"><div class="alert alert-info"><strong>{{ trans('admin.heure_prevue') }} :</strong> {{ $stats['heures_prevues'] }}</div></div>
        <div class="col-md-6"><div class="alert alert-success"><strong>{{ trans('admin.compte_heure_') }} :</strong> {{ $stats['heures_realisees'] }}</div></div>
    </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{ url('/') }}">{{ trans('menu.accueil') }}</a></strong></li>
    <li class="active"><strong>{{ $titre }}</strong></li>
@endsection
