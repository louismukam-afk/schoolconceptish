@extends('skeleton')
@section('sub-content')
    <link rel="stylesheet" href="{{asset('css/carte.css')}}">
    @if(isset($inscription))
        @if($inscription== 0)
            <div class="alert alert-danger">
                @lang('inscription.pas_inscrit')
            </div>
        @endif
        @else
        <div class="container">
            <a href="#" class="btn btn-warning btn-lg" onclick="window.print()"><span class="glyphicon glyphicon-print"></span> {{trans('inscription.imprimer')}}</a>


        </div>
        <?php $k=0;$k1=0 ?>

        @for($i=0;$i<$n;$i++)
            <div class="card-group">

                @for($j=0;$j<5;$j++)

                    <div class="row card-row">

                        @if(isset($eleves[$k]))
                            <?php $e=$eleves[$k] ?>
                            <div class="card-e left">
                                <div class="card-e-box">
                                    <div class="card-header">
                                        <div class="logo">
                                        </div>
                                        <div class="school text-uppercase" style="text-align: center !important; margin-left: 10%;">
                                            <span>
                                                <div class="element">{{$nom_e}}</div>
                                                <div class="element" style="font-size: 8px">{{trans('admin.year'). ' ' . session('year')}}</div>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="titre text-center text-uppercase">
                                        {{trans_choice('admin.carte_e',1)}}
                                    </div>
                                    <div class="card-body">
                                        <div class="filigrane"></div>
                                        <div class="card-infos">
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title">{{trans('admin.matricule')}}</div>
                                                <div>{{$e->matricule}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title ">{{trans('inscription.nom')}}</div>
                                                <div>{{$e->nom.' '.$e->prenom}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title ">{{trans('admin.nee_a')}}</div>
                                                <div>{{$e->lieu_naiss.', ' . trans('admin.le') . ' ' .(new DateTime($e->date_naiss))->format('d/m/Y')}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title">{{trans('admin.filiere')}}</div>
                                                <div>{{$niveau->nom}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title">{{trans('admin.classe')}}</div>
                                                <div>{{$classe->nom}} - {{$nniveau->nom}}</div>
                                            </div>



                                        </div>
                                        <?php $url='img/avatar.png' ?>
                                        @foreach($photos as $p)
                                            @if($p->eleve_id==$e->id)

                                                <?php $url=$p->url ;break; ?>
                                            @endif

                                        @endforeach
                                        <div class="card-aside">
                                            <div class="card-photo">
                                                <img src="{{asset($url)}}" alt="">
                                            </div>
                                            <div class="card-sign text-uppercase">
                                                La Direction
                                            </div>
                                            <div class="sign">
                                              <!--  <img src="{{asset('/img/signature.png')}}" alt="">-->

                                            </div>
                                            <div class="cachet">
                                                 <img src="{{asset('/img/cachet.png')}}" alt="">
                                            </div>

                                        </div>
                                        <div class="text-uppercase fait-le">
                                            <div class="field-title "><span class="fa fa-facebook-square"></span>Centre de Formation Hintel</div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        @endif
                        <?php $k++ ?>

                        @if(isset($eleves[$k]))
                            <?php $e=$eleves[$k] ?>


                            <div class="card-e right">

                                <div class="card-e-box">
                                    <div class="card-header">
                                        <div class="logo">
                                        </div>
                                        <div class="school text-uppercase"  style="text-align: center !important; margin-left: 10%;">
                                             <span>
                                                <div class="element">{{$nom_e}}</div>
                                                <div class="element" style="font-size: 8px">{{trans('admin.year'). ' ' . session('year')}}</div>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="titre text-center text-uppercase">
                                        {{trans_choice('admin.carte_e',1)}}
                                    </div>
                                    <div class="card-body">
                                        <div class="filigrane"></div>
                                        <div class="card-infos">
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title">{{trans('admin.matricule')}}</div>
                                                <div>{{$e->matricule}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title ">{{trans('admin.nom')}}</div>
                                                <div>{{$e->nom.' '.$e->prenom}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title ">{{trans('admin.nee_a')}}</div>
                                                <div>{{$e->lieu_naiss.', ' . trans('admin.le') . ' ' .(new DateTime($e->date_naiss))->format('d/m/Y')}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title">{{trans('admin.filiere')}}</div>
                                                <div>{{$niveau->nom}}</div>
                                            </div>
                                            <div class="infos-group text-uppercase">
                                                <div class="field-title">{{trans('admin.classe')}}</div>
                                                <div>{{$classe->nom}} - {{$nniveau->nom}}</div>
                                            </div>



                                        </div>
                                        <?php $url='img/avatar.png' ?>
                                        @foreach($photos as $p)
                                            @if($p->eleve_id==$e->id)

                                                <?php $url=$p->url ;break; ?>
                                            @endif

                                        @endforeach
                                        <div class="card-aside">
                                            <div class="card-photo">
                                                <img src="{{asset($url)}}" alt="">
                                            </div>
                                            <div class="card-sign text-uppercase">
                                                La Direction
                                            </div>
                                             <div class="sign">
                                             <!-- <img src="{{asset('/img/signature.png')}}" alt=""> -->
                                            </div>
                                            <div class="cachet">
                                                 <img src="{{asset('/img/cachet.png')}}" alt="">
                                            </div>
                                        </div>
                                        <div class="text-uppercase fait-le">
                                            <div class="field-title "><span class="fa fa-facebook-square"></span> Centre de Formation Hintel</div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endif
                        <?php $k++ ?>


                    </div>
                @endfor


            </div>


            <div class="card-group">

                @for($j=0; $j<5; $j++)

                    <div class="row card-row">
					   @if(isset($eleves[$k1]))
                                <?php $e=$eleves[$k1] ?>

                            <div class="card-e card-back right">
                                    <div class="qrcode-container">
                                        <img class="qrcode" src="data:image/png;base64, {!! base64_encode(\QrCode::format('png')->errorCorrection('H')->size(200)->generate($e->id)) !!}" />
                                    </div>
                            </div>
                        @endif

                      

                        <?php $k1++ ?>

                       @if(isset($eleves[$k1]))
                            <?php $e=$eleves[$k1] ?>
                            <div class="card-e card-back left">
                                    <div class="qrcode-container">
                                        <img class="qrcode" src="data:image/png;base64, {!! base64_encode(\QrCode::format('png')->errorCorrection('H')->size(200)->generate($e->id)) !!}" />
                                    </div>
                            </div>
                        @endif
                        <?php $k1++ ?>


                    </div>
                @endfor


            </div>
        @endfor






    @endif

@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection