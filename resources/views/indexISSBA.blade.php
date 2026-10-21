<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
      <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{!!  isset($page_title) ? $page_title : (isset($titre) ? $titre : session('big_title'))  !!}</title>
    <link rel="shortcut icon" href="{{URL::asset('img/log.png')}}" type="image/x-icon">
	<!-- BOOTSTRAP STYLES-->
    <link href="{{ URL::asset('dist/css/bootstrap.min.css')  }}" rel="stylesheet" />
     <!-- FONTAWESOME STYLES-->
    <link href="{{ URL::asset('css/font-awesome.css')  }}" rel="stylesheet" />
        <!-- CUSTOM STYLES-->
    <link href="{{ URL::asset('css/custom.css?202212051300')  }}" rel="stylesheet" media="screen"/>
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
    $nom_etab=$p->where('nom','nom_etablissement')->first();
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
                        <img src="{{URL::asset('img/school-concept.png')}}" />

                    </a>
                </div>
                <div class="nom-etab"><strong>{{session('nom_etab')}}</strong></div>


                <span class="logout-spn" >

                    <a href="{{ url('/auth/logout') }}">{{  trans('admin.logout')  }}</a>
                </span>
                <div ><span class="annee">{{  trans('admin.year')  }} <span>{{  session('year') }}</span> <a data-toggle="modal"  href="#change-year" class="btn btn-success btn-xs">{{  trans('admin.edit')  }}</a></span></div>
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

                    <li style="display: none;">
                        <a href="#"><i class="fa fa-bar-chart-o"></i>{{  trans('menu.bilan')  }}</a>
                    </li>

                    <li>
                        <a href="{{  URL::to('/admin/')  }}"><span class="glyphicon glyphicon-lock"></span> {{  trans('menu.admin')  }}</a>
                    </li>
                    @if(Auth::user()->isCreator())
                    <li>
                        <a href="{{  URL::to('/admin/blk')  }}"><span class="fa fa-ban"></span>BLK</a>
                    </li>

                        <li>
                            <a href="{{  URL::to('/admin/unblk')  }}"><span class="fa fa-ban"></span>UBLK</a>
                        </li>
                    @endif

                    
                </ul>
                            </div>

        </nav>
        <!-- /. NAV SIDE  -->
        <div id="page-wrapper" >
            <div id="page-inner">
                <div class="row hidden">
                    <div class="col-md-12">

                     <h2 class="big-title">{{  session('big_title')  }}</h2>
                    </div>
                </div>
                 <!-- /. ROW  -->
                  <hr style="margin-top: 0;margin-bottom: 0" />
                <div class="row-fluid">
                    <ul class="breadcrumb" style="margin-bottom: 0;padding: 2px 15px;font-size: 16px">

                        @yield('breadcrumb')
                    </ul>
                <div style="height: 10px; background: #214761" class="action"></div>
                    <div class="col-lg-12 ">
                     @if(isset($isHome))
                        <div class="alert alert-info">
                            @if (Auth::guest())
                            @else
                             <strong>{{trans('admin.bienvenue')}} {{$u->fullname}} ! </strong>
                                <a data-toggle="modal" data-backdrop="false" href="#edit_pass" class="pull-right" style="color: #BE5C00"><span class="fa fa-lock"></span> @lang('admin.modifier_pass')</a>

                                @if(isset($_SESSION['lr']))
                                    <?php $lr=$_SESSION['lr'];
                                    ?>
                                    @if($lr<40)
                                        <div class="text-danger text-uppercase" style="font-weight: 600;font-size: 25px">
                                            {{  trans('admin.exp_lic_g',['jours'=>$lr]) }}
                                        </div>
                                    @else
                                        <div class="text-warning text-uppercase" style="font-weight: 600;font-size: 20px">
                                            {{  trans('admin.exp_lic',['jours'=>$lr]) }}

                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>

                    @endif
                       
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
                                        <h5>{{ $error }}</h5>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success">
                                <ul>
                                    <h5>{{ session('success') }}</h5>
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


<div class="modal fade" id="edit_pass">
    <div class="modal-dialog">
        <div class="modal-content col-md-6">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
            </div>
            <div
                    class="modal-body">



                <form accept-charset="UTF-8" role="form" method="POST" action="#">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <fieldset>

                        <div class="form-group">
                            <label for="ancien_mot_de_passe">{{  trans('admin.ancien_pass')  }} : <span class="required">*</span></label>
                            <input class="form-control" id="ancien_mot_de_passe" name="ancien_mot_de_passe" type="password"  value="{{ old('ancien_mot_de_passe') }}" required autofocus>

                        </div>
                        <div class="form-group">
                            <label for="nouveau_mot_de_passe">{{  trans('admin.nouveau_pass')  }} : <span class="required">*</span></label>
                            <input class="form-control" id="nouveau_mot_de_passe" name="nouveau_mot_de_passe" type="password"  value="{{ old('nouveau_mot_de_passe') }}" required >

                        </div>
                        <div class="form-group">
                            <label for="password_again">{{  trans('admin.password_again')  }} : <span class="required">*</span></label>
                            <input class="form-control" id="password_again" name="password_again" type="password"  value="{{ old('password_again') }}" required >

                        </div>
                        <a id="submit_new_pass" class="btn  btn-success" data-dismiss="modal">{{  trans('admin.confirmer')  }}</a>


                    </fieldset>
                </form>


            </div>





        </div>


    </div>
</div>
    <div class="footer">
      
    
            <div class="row">
                <div class="col-md-12 copy" >
                    &copy;  2017 SchoolConcept | by: <a href="http://youth-concept.com" style="color:#fff;" target="_blank">youth-concept</a>
                </div>
            </div>
        </div>
          

     <!-- /. WRAPPER  -->
    <!-- SCRIPTS -AT THE BOTOM TO REDUCE THE LOAD TIME-->
    <!-- JQUERY SCRIPTS -->


<div class="modal fade" id="choose_level">
    <div class="modal-dialog" style="width: 20%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
            </div>
            <div class="modal-body">



                <form accept-charset="UTF-8" role="form" method="GET" id="choix_level_form" action="">
                    <input type="hidden" name="id" id="id_select_classe" value=""/>
                    <input type="hidden" name="option" id="option_select_classe" value=""/>

                    <fieldset>
                        <div class="form-group">
                            <label for="cycle">{{  trans('admin.cycle')  }} : </label>
                            <select  name="cycle" id="cycle_select_classe" form="choix_level_form" class="form-control" required>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nniveau">{{  trans('admin.nniveau')  }} : </label>
                            <select  name="nniveau" id="nniveau_select_classe" form="choix_level_form" class="form-control" required>
                                <option value="" disabled>@lang('admin.select_item')</option>
                            </select>
                        </div>

                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                        <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}" autofocus>


                    </fieldset>

                </form>


            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="confirm_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #d9534f">
                <button type="button" class="close" data-dismiss="modal">x</button>
                <h4 class="modal-title" style="color: white">{{  trans('admin.remplir_champs')  }}</h4>
            </div>
            <div class="modal-body">

                <form accept-charset="UTF-8" role="form" method="GET" id="confirm_delete_form" action="">
                    <input type="hidden" name="id" id="confirm_delete_item_id" value=""/>

                    <fieldset>
                        <h4 id="confirm_delete_text">@lang('admin.confirm_delete')</h4>
                        <div class="btn  btn-primary pull-right" style="margin-left: 10px"  class="close" data-dismiss="modal">@lang('inscription.non')</div>
                        <input class="btn  btn-danger pull-right" type="submit" value="{{  trans('inscription.oui')  }}">


                    </fieldset>

                </form>


            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="change-status-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" onclick="toggleScanStatus()">x</button>
                <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
            </div>
            <div class="modal-body">

                <form id="change-status-form" accept-charset="UTF-8" role="form" method="POST" action="{{ url('/inscriptions/eleve/set-status?status=1&id=') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">



                    <div class="form-group">
                        <label for="date_debut">{{  trans('admin.date_debut')  }} : <span class="required">*</span></label>
                        <input class="form-control" id="change_status-date_debut" name="date_debut" type="date"  value="{{ old('date_debut') }}" required autofocus />

                    </div>

                    <div class="form-group">
                        <label for="date_fin">{{  trans('admin.date_fin')  }} : <span class="required">*</span></label>
                        <input class="form-control" id="change_status-date_fin" name="date_fin" type="date"   value="{{ old('date_fin') }}" required />

                    </div>
                    <div class="form-group">
                        <label for="montant">{{  trans('admin.montant')  }} : </label>
                        <input class="form-control" id="change_status-montant" name="montant" type="number"  value="{{ old('montant') }}" disable>

                    </div>

                    <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">

                </form>


            </div>

        </div>

    </div>
</div>

</body>
<script>
    var scanActivated = false;
    function enableScan() {
        scanActivated = true;

        console.log('Scan active')

        if ($('#qrcode-input')[0]) {
            $('#qrcode-input')[0].addEventListener('focusout', giveFocus)
        }
    }

    function disableScan() {
        scanActivated = false;
        console.log('Scan desactive')
        if ($('#qrcode-input')[0]) {
            $('#qrcode-input')[0].removeEventListener('focusout', giveFocus)
        }
    }

    function giveFocus(e) {
        e.target.focus();
    }

    $(function () {
        $('#submit_new_pass').click(function () {
            //$('#edit_pass').modal('hide');
            $.post('{{url('/user-profile/edit-password')}}',{ _token:'{{ csrf_token() }}',ancien_mot_de_passe:$("#ancien_mot_de_passe").val(),nouveau_mot_de_passe:$("#nouveau_mot_de_passe").val(),password_again:$("#password_again").val() },function (data) {
                $(".print-success-msg").find("ul").html('');
                $(".print-error-msg").find("ul").html('');
                $(".print-success-msg").css('display', 'none');
                $(".print-error-msg").css('display', 'none');
                if(data.success)
                    printSuccessMsg(data);
                else
                {
                	console.log(data);
                    printErrorMsg(data);
                }
                	

            });
        });
        function printErrorMsg (msg) {

            
            if(msg)
            $(".print-error-msg").css('display', 'block');

            $.each(msg, function (key, value) {
                $(".print-error-msg").find("ul").append('<li>' + value + '</li>');
            });

        }
        function printSuccessMsg (msg) {

            if(msg)
                $(".print-success-msg").css('display', 'block');

            $.each(msg, function (key, value) {
                $(".print-success-msg").find("ul").append('<li>' + value + '</li>');
            });

        }

            jQuery.noConflict();
            $( "#date_naiss, #date, #date_rec, .datepicker" ).datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: 'yy/mm/dd',
                yearRange: '1900:c+10'
            });

            $('#cycle_select_classe').on('change', function (e) {
                if(e.target.value) {
                    getNniveau(e.target.value);
                }
            })
    });


    function export_to_excel(idTable)
    {

        var tab_text="<table ><tr >";
        var textRange; var j=0;
        tab = document.getElementById(idTable); // id of table

        for(j = 0 ; j < (tab.rows.length) ; j++)
        {
            tab_text=tab_text+tab.rows[j].innerHTML+"</tr>";
            //tab_text=tab_text+"</tr>";
        }

        tab_text=tab_text+"</table>";
        tab_text= tab_text.replace(/<A[^>]*>|<\/A>/g, "");//remove if u want links in your table
        tab_text= tab_text.replace(/<img[^>]*>/gi,""); // remove if u want images in your table
        tab_text= tab_text.replace(/<input[^>]*>|<\/input>/gi, ""); // reomves input paras
        tab_text= tab_text.replace(/<a[^>]*>|<\/a>/gi, "");
        tab_text= tab_text.replace(/<div[^>]*>|<\/div>/gi, "");
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf("MSIE ");

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./))      // If Internet Explorer
        {
            txtArea1.document.open("txt/html","replace");
            txtArea1.document.write(tab_text);
            txtArea1.document.close();
            txtArea1.focus();
            sa=txtArea1.document.execCommand("SaveAs",true,"dataExport.xls");
        }
        else                 //other browser not tested on IE 11
            sa = window.open('data:application/vnd.ms-excel,' + encodeURIComponent(tab_text));


        return (sa);
    }

    function getAllCycles() {
        var jElement = $('#cycle_select_classe');
        jElement.empty();
        $('.loading_icon').css('display','inline');
        $.ajax({
            type: 'GET',
            url: '{{route('get_all_cycles')}}',
            timeout: 20000,
            success: function (data) {
                jElement.append('<option value="" disabled>'+ '@lang('admin.select_item')' +'</option>');
                var i = 0;
                var selectedValue = -1;
                $.each(data,function (key,val) {
                    jElement.append('<option value="'+val.id+'">'+val.nom+'</option>');
                    i++;
                    if(i == 1) {
                        selectedValue = val.id;
                    }
                })

                //$('.loading_icon').css('display','none');
                if(selectedValue != -1) {
                    getNniveau(selectedValue);
                }
            },
            error: function (data) {
                $('.loading_icon').css('display','none');
            }


        });
    }

      function getNniveau(cycle) {
            var jElement = $('#nniveau_select_classe');
            jElement.empty();
            $('.loading_icon').css('display','inline');
            $.ajax({
                type: 'GET',
                url: '{{url('/admin/nniveaux/get/')}}/'+cycle,
                timeout: 20000,
                success: function (data) {
                    jElement.append('<option value="" disabled>'+ '@lang('admin.select_item')' +'</option>');
                    $.each(data,function (key,val) {
                        jElement.append('<option value="'+val.id+'">'+val.nom+'</option>');
                    })
                    $('.loading_icon').css('display','none');
                },
                error: function (data) {
                    $('.loading_icon').css('display','none');
                }


            });
        }

    function choose_level(id, actionUrl, option)
    {
        document.getElementById("id_select_classe").value = id;
        document.getElementById("option_select_classe").value = option;
        $('#choix_level_form').attr('action', actionUrl);
        getAllCycles();
    }

    function fill_delete_modal(id, actionUrl, method, text)
    {
        var jForm = $('#confirm_delete_form');
        if(id) {
            document.getElementById("confirm_delete_item_id").value = id;
        }
        jForm.attr('action', actionUrl);
        if(method) {
            jForm.attr('method', method);
        } else {
            jForm.attr('method', 'GET');
        }
        if(text) {
            $('#confirm_delete_text').html(text);
        } else {
            $('#confirm_delete_text').html('@lang('admin.confirm_delete')');

        }

        $('#confirm_delete_modal').modal('show');
    }

    $(function (){
        $('#qrcode-input').on('change', function (e){
            console.log('scanned', e.target.value);
            var scanned = e.target.value;
            e.target.value = "";
            $('#scan-error-message').addClass('hidden');

            $.ajax({
                url : '{{url('/inscriptions/eleve/process-scan')}}',
                type:'GET',
                dataType:'html',
                data:'scanned='+scanned,
                beforeSend: function () {
                    showLoader();
                },
                success: function (res) {

                    console.log(res);


                    if(res == 0) {
                        hideLoader();
                        $('#scan-error-message').removeClass('hidden');
                        $('#status-container').addClass('hidden');


                    } else {

                        location.href = '{{url('/inscriptions/eleve/status')}}' + '?id=' + res + "&show_access=1"
                    }
                },

                error: function (xhr) {
                    console.error(xhr);
                    hideLoader();
                }

            })
        })


        enableScan();
        $('.autocomplete').chosen({width : '100%'});
    })

    function openChangeStatusModal(action) {
        $('#change-status-form')[0].action = action
        $('#change-status-modal').modal('show');
    }

    function allowStudentAccess(id) {
        const action =  `{{ url('/inscriptions/eleve/set-status') }}?status=1&id=${id}`
        openChangeStatusModal(action)
    }

</script>
</html>
