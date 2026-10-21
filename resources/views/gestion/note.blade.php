<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{  isset($page_title) ? $page_title : (isset($titre) ? $titre : session('big_title'))  }}</title>
    <link rel="shortcut icon" href="{{URL::asset('img/log.png')}}" type="image/x-icon">
    <!-- BOOTSTRAP STYLES-->
    <link href="{{ URL::asset('dist/css/bootstrap.min.css')  }}" rel="stylesheet" />
    <!-- FONTAWESOME STYLES-->
    <link href="{{ URL::asset('css/font-awesome.css')  }}" rel="stylesheet" />
    <!-- CUSTOM STYLES-->
    <link href="{{ URL::asset('css/custom.css')  }}" rel="stylesheet" media="screen"/>
    <link href="{{ URL::asset('js/jquery-ui-1.12.1/jquery-ui.css')  }}" rel="stylesheet" media="screen"/>
    <link href="{{ URL::asset('js/datatables/datatables.min.css')  }}" rel="stylesheet"/>
    <link href="{{ URL::asset('js/chosen_v1.8.7/chosen.min.css')  }}" rel="stylesheet"/>


    <!-- GOOGLE FONTS-->

    <script src="{{ URL::asset('js/jquery.min.js')  }}"></script>

    <script src="{{ URL::asset('js/jquery-ui-1.12.1/jquery-ui.js')  }}"></script>
    <script src="{{ URL::asset('js/chosen_v1.8.7/chosen.jquery.min.js')}}"></script>
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="{{ URL::asset('dist/js/bootstrap.min.js')  }}"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="{{ URL::asset('js/custom.js')  }}"></script>

    <script src="{{ URL::asset('js/datatables/datatables.min.js')  }}"></script>


    <script>
        function startLoading() {
            // $('.loading-back').css('display','inline');
            $('.loading_icon').css('display','inline');
        }
        function stopLoading() {
            //$('.loading-back').css('display','none');
            $('.loading_icon').css('display','none');
        }

        function showLoader() {
            $('.loader').removeClass('hidden');
        }
        function hideLoader() {
            $('.loader').addClass('hidden');

        }
    </script>

    <style>
        #loader {
            display: none;
        }

        .loader{
            position: fixed;
            width: 100%;
            height: 100%;
            z-index: 100000;
            background-color: rgba(0, 0, 0, 0.6);
            top: 0;
            left: 0;
        }
        .loader img {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 150px;
            transform: translate(-50%, -50%);
        }
        #qrcode-input {
            height: 0;
        }
    </style>
</head>



<body>
<div  class="loader hidden">
    <img src="{{asset('img/loading3.gif')}}" alt="">
</div>
<div class="loading-back">  </div>
<div class="col-md-12" >
    <a href="#"><img class="back-button" src="{{URL::asset('img/back_button.png')}}" onclick="history.back()" width="50px" height="50px"></a>

</div>
<?php $u=\Illuminate\Support\Facades\Auth::user();
if(session('nom_etab')==null){
    $p=\gesecole\parametres::all();
    $nom_etab=$p->where('nom','nom_etablissement',false)->first();
    session(['nom_etab'=>$nom_etab->valeur]);
}
?>

<div class="loading_icon" style="
    position: fixed;
    Left: calc(50% - 75px);
    top: calc(50% - 37.5px);
    z-index: 10000;
     width: 150px;
     height: 75px;
         box-shadow: 0 10px 20px rgba(0, 0, 0, .5);
     display: none;
">
    <img src="{{URL::asset('img/loading.gif')}}" alt="" style="width: inherit;height: inherit">
</div>

<div id="wrapper">
    <div class="navbar navbar-inverse navbar-fixed-top">
        <div class="adjust-nav">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand logo" href="#">
                    <?php
                        $campusHeader = $selected_inscription->campus;
                        $campusLogo = 'img/school-concept.png';
                        if($campusHeader && $campusHeader->logo) {
                            $campusLogo = $campusHeader->logo;
                        }
                    ?>
                    <img src="{{ URL::asset($campusLogo) }}" />

                </a>
            </div>
            <div class="nom-etab">
                <strong>
                    {{ $selected_inscription->campus ? $selected_inscription->campus->nom : session('nom_etab') }}
                </strong>
            </div>


            <span class="logout-spn" >

                    <a href="{{ url('/gestion/login/etudiant/disconnect_user') }}">{{  trans('admin.logout')  }}</a>
                </span>
            <div ><span class="annee">Annee academique <span>{{  $selected_inscription->annee_scolaire }}</span></span></div>
            <div class="langage ">
                <div class="lang dropdown"><a href="#" data-toggle="dropdown" class="dropdown-toggle"><strong style="color: white">@lang('admin.langage') :</strong> @if(App::getLocale()=='fr')Francais @else English @endif <span class="caret"></span></a>
                    <ul class="dropdown-menu navbar-inverse">
                        <li><a href="{{url('language/french')}}"><strong>Français</strong></a></li>
                        <li><a href="{{url('language/english')}}"><strong>English</strong></a></li>
                    </ul>
                </div>

            </div>

        </div>
    </div>



    <div id="page-wrapper" >
        <div id="page-inner">
            <div class="row hidden">
                <div class="col-md-12">

                    <h2 class="big-title">{{  session('big_title')  }}</h2>
                </div>
            </div>
            <div class="col-md-12">
                <a  class="btn btn-warning btn-lg" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> Imprimer</a>

            </div>
            <link rel="stylesheet" href="{{URL::asset('css/certificat.css')}}">

            <!-- /. ROW  -->
            <hr style="margin-top: 0;margin-bottom: 0" />
            <div class="row-fluid">
                <ul class="breadcrumb" style="margin-bottom: 0;padding: 2px 15px;font-size: 16px">

                    @yield('breadcrumb')
                </ul>
                <h3 style="text-align:center;" > {{  trans('admin.notess')  }}  <strong> {{$eleve->nom}} </strong></h3>
                <h4>
                    <strong>Campus</strong> :
                    {{ $selected_inscription->campus ? $selected_inscription->campus->nom : session('nom_etab') }}
                    @if($selected_inscription->campus && $selected_inscription->campus->sigle)
                        ({{ $selected_inscription->campus->sigle }})
                    @endif
                </h4>
                <h4><strong>Annee academique</strong> : {{$selected_inscription->annee_scolaire}}</h4>
                <h4 >   <strong>{{  trans('admin.cycle')  }}</strong> : {{$selected_inscription->cycle->nom}} </h4>
                <h4 >  <strong> {{  trans('admin.special')  }}</strong> : {{$selected_inscription->classe->getNom()}} </h4>
                <h4 ><strong>{{  trans('admin.niveauss')  }}</strong>  : {{$selected_inscription->nniveau->nom }} </h4>

                <div class="row" style="margin-top: 15px;">
                    @foreach(array(1, 2) as $semestre)
                        <div class="col-md-6">
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <strong>Semestre {{ $semestre }}</strong>
                                </div>
                                <div class="panel-body">
                                    <h4>
                                        Credits valides :
                                        <strong>{{ isset($credits_stats[$semestre]) ? $credits_stats[$semestre]['valides'] : 0 }}</strong>
                                        /
                                        <strong>{{ isset($credits_stats[$semestre]) ? $credits_stats[$semestre]['prevus'] : 0 }}</strong>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="panel panel-success">
                    <div class="panel-heading">
                        <strong>Matieres de la specialite avec notes publiees ou examens composes</strong>
                    </div>
                    <div class="panel-body">
                        @foreach(array(1, 2) as $semestre)
                            <h4>Semestre {{ $semestre }}</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('admin.matiere') }}</th>
                                        <th>Credit</th>
                                        <th>CC</th>
                                        <th>SN</th>
                                        <th>Rattrapage</th>
                                        <th>TPE</th>
                                        <th>Total normal /100</th>
                                        <th>Total retenu /100</th>
                                        <th>Decision</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i=1; ?>
                                    @forelse($matieres_avec_notes_par_semestre[$semestre] as $row)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td><strong>{{ $row['matiere']->getNom() }}</strong></td>
                                            <td>{{ $row['credit'] }}</td>
                                            <td>{{ $row['cc'] !== null ? $row['cc'] : '' }}</td>
                                            <td>{{ $row['sn'] !== null ? $row['sn'] : '' }}</td>
                                            <td>
                                                {{ $row['rattrapage'] !== null ? $row['rattrapage'] : '' }}
                                                @if($row['rattrapage'] !== null && $row['rattrapage_retenu'])
                                                    <span class="label label-info">retenue</span>
                                                @endif
                                            </td>
                                            <td>{{ $row['tpe'] !== null ? $row['tpe'] : '' }}</td>
                                            <td>{{ $row['final_compose'] ? $row['normal_total'] : '' }}</td>
                                            <td><strong>{{ $row['final_compose'] ? $row['total'] : '' }}</strong></td>
                                            <td>
                                                @if($row['is_validated'])
                                                    <span class="label label-success">Validee</span>
                                                @elseif($row['final_compose'] || $row['class_has_notes'])
                                                    <span class="label label-warning">A rattraper</span>
                                                @else
                                                    <span class="label label-default">En attente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">Aucune note publiee pour ce semestre.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <strong>Matieres de la specialite sans notes pour la classe</strong>
                    </div>
                    <div class="panel-body">
                        @foreach(array(1, 2) as $semestre)
                            <h4>Semestre {{ $semestre }}</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ trans('admin.matiere') }}</th>
                                        <th>Credit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $j=1; ?>
                                    @forelse($matieres_sans_notes_par_semestre[$semestre] as $row)
                                        <tr>
                                            <td>{{ $j++ }}</td>
                                            <td><strong>{{ $row['matiere']->getNom() }}</strong></td>
                                            <td>{{ $row['credit'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Toutes les matieres de ce semestre ont au moins une note enregistree.</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <strong>Liste des matieres a rattraper par semestre</strong>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            @foreach(array(1, 2) as $semestre)
                                <div class="col-md-6">
                                    <h4>Semestre {{ $semestre }}</h4>
                                    <ul class="list-group">
                                        @forelse($rattrapages_semestre[$semestre] as $row)
                                            <li class="list-group-item">
                                                <strong>{{ $row['matiere']->getNom() }}</strong>
                                                <span class="pull-right">{{ $row['final_compose'] ? $row['total'].'/100' : 'Note manquante' }}</span>
                                            </li>
                                        @empty
                                            <li class="list-group-item">Aucune matiere a rattraper.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>


            <div class="row" >
                <div class="col-md-12">
                    <div class="alert alert-danger print-error-msg" style="display: none">
                        <ul> </ul>
                    </div>
                    <div class="alert alert-success print-success-msg" style="display: none">
                        <ul> </ul>
                    </div>
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

            </div>
            <!-- /. ROW  -->

        @yield('content')
        <!-- /. ROW  -->

            <!-- /. ROW  -->
        </div>
        <!-- /. PAGE INNER  -->
    </div>
    <!-- /. PAGE WRAPPER  -->
</div>


<div class="footer">


    <div class="row">
        <div class="col-md-12 copy" >
            &copy;  2017 SchoolConcept | by: <a href="http://youth-concept.com" style="color:#fff;" target="_blank">youth-concept</a>
        </div>
    </div>
</div>






</body>




</html>
