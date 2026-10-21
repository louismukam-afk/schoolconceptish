@extends('index')

@section('content')
    <div class="alert alert-info">
        <span class="fa fa-3x fa-users"> {{ $nb_eleves.' '.trans('admin.inscrits')}}</span>
    </div>
    <div class="row text-center pad-top">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/enregistrement/create')}}" >
                    <i class="fa fa-save fa-5x"></i>
                    <h4>{{trans('inscription.title_register')}}</h4>
                </a>
            </div>


        </div>

        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_STUDENT_CU))
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                <div class="div-square">
                    <a href="{{route('students_import')}}" >
                        <i class="fa fa-file-excel-o fa-5x"></i>
                        <h4 style="overflow-wrap: anywhere">Importer les etudiants</h4>
                    </a>
                </div>
            </div>
        @endif

        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE))
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                <div class="div-square">
                    <a href="{{route('inscriptions_import')}}" >
                        <i class="fa fa-file-excel-o fa-5x"></i>
                        <h4 style="overflow-wrap: anywhere">Importer les inscriptions</h4>
                    </a>
                </div>
            </div>
        @endif

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/find-inscription')}}" >
                    <i class="fa fa-user fa-5x"></i>
                    <h4 style="overflow-wrap: anywhere">{{trans('inscription.title_inscription')}}</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/payement/')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>{{trans('inscription.title_paiement')}}</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/find')}}" >
                    <i class="fa fa-user fa-5x"></i>
                    <h4 style="overflow-wrap: anywhere">{{trans('admin.op_eleve')}}</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{route('inscriptions_historique_etudiant')}}" >
                    <i class="fa fa-history fa-5x"></i>
                    <h4 style="overflow-wrap: anywhere">Historique etudiant</h4>
                </a>
            </div>
        </div>

        <!--<div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/payement/')}}" >
                    <i class="fa fa-money fa-5x"></i>
                    <h4>{{trans('inscription.title_paiement')}}</h4>
                </a>
            </div>


        </div>-->




    </div>
    <div class="row text-center pad-top">
        <!--
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/eleves/search')}}" >
                    <i class="fa fa-search fa-5x"></i>
                    <h4>{{trans('admin.see_edit').' '.trans('inscription.eleve')}}</h4>
                </a>
            </div>


        </div>-->

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                <div class="div-square">
                    <a href="{{URL::to('/inscriptions/selection-listes/')}}" >
                        <i class="fa fa-list-alt fa-5x"></i>
                        <h4>{{trans('inscription.listes')}}</h4>
                    </a>
                </div>


            </div>

            @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_LIST))
                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                    <div class="div-square">
                        <a href="{{route('inscriptions_effectifs')}}" >
                            <i class="fa fa-table fa-5x"></i>
                            <h4 style="overflow-wrap: anywhere">Effectifs par cycle</h4>
                        </a>
                    </div>
                </div>
            @endif

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                <div class="div-square">
                    <a href="{{URL::to('/select/classe').'?url='.URL::to('/inscriptions/photos')}}" >
                        <i class="fa fa-image fa-5x"></i>
                        <h4>{{trans('admin.add_photo_to').' '.trans('inscription.eleve')}}</h4>
                    </a>
                </div>


            </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="#choix_carte" data-toggle="modal"  >
                    <i class="fa fa-credit-card fa-5x"></i>
                    <h4>{{trans_choice('admin.carte_e',2)}}</h4>
                </a>
            </div>


        </div>

            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                <div class="div-square">
                    <a href="{{URL::to('/inscriptions/eleve/scan-carte')}}" >
                        <i class="fa fa-qrcode fa-5x"></i>
                        <h4>{{trans('admin.scan_carte')}} </h4>
                    </a>
                </div>

            </div>


    </div>


    <div class="modal fade" id="choix_carte">
        <div class="modal-dialog" style="width: 300px">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">x</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <ul>
                        <li><strong><a href="{{URL::to('/select/classe').'?url='.route('show_carte')}}">@lang('admin.selectionner_classe')</a></strong></li>
                        <li><strong><a href="{{route('show_carte_eleve')}}">@lang('admin.selectionner_eleve')</a></strong></li>
                    </ul>
                </div>
            </div>


        </div>
    </div>
     <div class="row text-center pad-top">

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{route('index_bourses')}}" >
                    <i class="fa fa-archive fa-5x"></i>
                    <h4>{{trans('admin.bourse')}}</h4>
                </a>
            </div>

        </div>

          <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('inscriptions/fiches/')}}" >
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>@lang('admin.fiches')</h4>
                </a>
            </div>


        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/inscriptions/etat-caisse')}}" >
                    <i class="fa fa-bank fa-5x"></i>
                    <h4>Mon etat de caisse</h4>
                </a>
            </div>

        </div>

        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE))
            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
                <div class="div-square">
                    <a href="{{URL::to('/inscriptions/certificates')}}" >
                        <i class="fa fa-file-text-o fa-5x"></i>
                        <h4 style="overflow-wrap: anywhere">@lang('admin.certificat_scolarite')</h4>
                    </a>
                </div>
            </div>
        @endif

    </div>

<!-- 
    <div class="row text-center pad-top">
          <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/fiche/selection-listes/')}}" >
                    <i class="fa fa-list-alt fa-5x"></i>
                    <h4>Imprimer les  fiche de report des notes de controles continus</h4>
                </a>
            </div>


        </div>

          <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
              <div class="div-square">
                  <a href="{{URL::to('/fiches/selection-listes/')}}" >
                      <i class="fa fa-list-alt fa-5x"></i>
                      <h4>Imprimer les  fiche de report des notes de l'examen</h4>
                  </a>
              </div>


          </div>

              <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
              <div class="div-square">
                  <a href="{{URL::to('/fichesblanc/selection-listes/')}}" >
                      <i class="fa fa-list-alt fa-5x"></i>
                      <h4>Imprimer les  fiche de report des notes de l'examen blanc</h4>
                  </a>
              </div>


          </div>
          <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
              <div class="div-square">
                  <a href="{{URL::to('/fichepresence/selection_liste/')}}" >
                      <i class="fa fa-list-alt fa-5x"></i>
                      <h4>Fiches de présences aux examens</h4>
                  </a>
              </div>


          </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="div-square">
                <a href="{{URL::to('/rattrappages/selection-listes/')}}" >
                    <i class="fa fa-print fa-5x"></i>
                    <h4>Imprimer les fiches de report des notes de rattrapages</h4>
                </a>
            </div>


        </div> -->
        
    </div>
@endsection

@section('breadcrumb')
    <li class="active"><strong>@lang('menu.gestion_inscriptions')</strong></li>

@endsection
