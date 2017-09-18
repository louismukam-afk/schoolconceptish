<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
      <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SchoolConcept</title>
    <link rel="shortcut icon" href="{{URL::asset('favicon.ico')}}" type="image/x-icon">
	<!-- BOOTSTRAP STYLES-->
    <link href="{{ URL::asset('css/bootstrap.css')  }}" rel="stylesheet" />
     <!-- FONTAWESOME STYLES-->
    <link href="{{ URL::asset('css/font-awesome.css')  }}" rel="stylesheet" />
        <!-- CUSTOM STYLES-->
    <link href="{{ URL::asset('css/custom.css')  }}" rel="stylesheet" media="screen"/>
     <!-- GOOGLE FONTS-->

</head>
<body>

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
                        <img src="{{URL::asset('img/school-concept.png')}}" />

                    </a>
                    
                </div>
              
                <span class="logout-spn" >

                    <a href="{{ url('/auth/logout') }}">{{  trans('admin.logout')  }}</a>
                </span>
                <div ><span class="annee">{{  trans('admin.year')  }} <span>{{  session('year') }}</span> <a data-toggle="modal"  href="#change-year" class="btn btn-success btn-xs">{{  trans('admin.edit')  }}</a></span></div>

            </div>
        </div>
        <div class="col-md-4">
            <div class="modal fade" id="change-year">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div
                                class="modal-body">



                            <form accept-charset="UTF-8" role="form" method="POST" id="edit_classe_form" action="{{ url('/update-school-year/') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

                                <div class="form-group">
                                    <div class="row" style="text-align: center">
                                        <div class="col-md-2"><input type="text" name="first_year" placeholder="2017" class="form-control"></div> <span class="col-md-1 " style="font-size: 20px;font-weight: 600;"> - </span><div class="col-md-2"><input class="form-control" type="text" name="second_year" placeholder="2018" ></div>
                                    </div>

                                </div>
                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </form>


                        </div>





                    </div>


                </div>
            </div>
        </div>

        <!-- /. NAV TOP  -->
        <nav class="navbar-default navbar-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav" id="main-menu">
                 


                    <li class="active-link">
                        <a href="#" ><i class="fa fa-desktop "></i>{{  trans('menu.titre')  }}</a>
                    </li>

                    <li>
                        <a href="{{  URL::to('/')  }}"><i class="fa fa-home "></i>{{  trans('menu.accueil')  }}</a>
                    </li>

                    <li>
                        <a href="{{URL::to('/inscriptions/')}}"><i class="fa fa-table "></i>{{  trans('menu.gestion_inscriptions')  }}</a>
                    </li>
                    <li>
                        <a href="{{URL::to('/eleves/')}}"><i class="fa fa-edit "></i>{{  trans('menu.gestion_notes')  }}</a>
                    </li>


                    <li>
                        <a href="{{URL::to('/personnel/')}}"><i class="fa fa-steam   "></i>{{  trans('menu.gestion_personnel')  }}</a>
                    </li>

                    <li>
                        <a href="{{URL::to('/comptabilite/')}}"><i class="fa fa-money"></i>{{  trans('admin.comptabilite')  }}</a>
                    </li>

                    <li>
                        <a href="#"><i class="fa fa-bar-chart-o"></i>{{  trans('menu.bilan')  }}</a>
                    </li>

                    <li>
                        <a href="{{  URL::to('/admin/')  }}"><span class="glyphicon glyphicon-lock"></span> {{  trans('menu.admin')  }}</a>
                    </li>

                    
                </ul>
                            </div>

        </nav>
        <!-- /. NAV SIDE  -->
        <div id="page-wrapper" >
            <div id="page-inner">
                <div class="row">
                    <div class="col-lg-12">
                     <h2 class="big-title">{{  session('big_title')  }}</h2>
                    </div>
                </div>              
                 <!-- /. ROW  -->
                  <hr />
                <div class="row">
                    <div class="col-lg-12 ">
                        <div class="alert alert-info">
                            @if (Auth::guest())
                            @else
                             <strong>{{trans('admin.bienvenue')}} {{Auth::user()->fullname}} ! </strong>
                            @endif
                        </div>

                       
                    </div>

                    </div>
                <div class="row">
                    <div class="col-md-12">
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
                <div class="col-lg-12 copy" >
                    &copy;  2017 SchoolConcept | by: <a href="http://youth-concept.com" style="color:#fff;" target="_blank">youth-concept</a>
                </div>
            </div>
        </div>
          

     <!-- /. WRAPPER  -->
    <!-- SCRIPTS -AT THE BOTOM TO REDUCE THE LOAD TIME-->
    <!-- JQUERY SCRIPTS -->
    <script src="{{ URL::asset('js/jquery.min.js')  }}"></script>
      <!-- BOOTSTRAP SCRIPTS -->
    <script src="{{ URL::asset('js/bootstrap.min.js')  }}"></script>
      <!-- CUSTOM SCRIPTS -->
    <script src="{{ URL::asset('js/custom.js')  }}"></script>

    
   
</body>
</html>
