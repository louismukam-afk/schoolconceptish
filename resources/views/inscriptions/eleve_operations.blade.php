@extends('skeleton')
@section('sub-content')
    @if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>


    @endif

    @if(isset($custom_error))

        <div class="alert alert-danger">
            {{  $custom_error  }}
        </div>

    @endif



        <div class="col-md-8">
            <form class="form-horizontal" id="eleve_op_form" method="get" action="{{URL::to('/inscriptions/find/')}}">
                <input type="hidden" name="id_eleve" value="{{session('inscription_new_record')}}">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search">{{  trans('inscription.recherche')  }} : </label>
                        <div class="input-group">
                            <input type="search" name="search" placeholder="{{trans('inscription.nom')}} ou Matricule" class="form-control" autofocus>
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary" ><span class="fa fa-search"> </span>{{trans('inscription.search')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>

        <div class="col-md-10">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
                    <thead>
                    <caption style="font-size: 22px">{{  trans('inscription.search_result')  }}</caption>
                    <th>#</th>
                    <th>Matricule</th>
                    <th>{{  trans('admin.nom')  }}</th>
                    <th>{{  trans('inscription.date_naiss')  }}</th>
                    <th>{{  trans('inscription.lieu_naiss')  }}</th>
                    <th>{{  trans('inscription.sexe')  }}</th>
                    <th>Actions</th>

                    </thead>
                    <tbody>
                    <?php $i=1; ?>
                    @foreach($eleves as $e)
                        <tr>
                            <td>{{$i++}}</td>
                            <td>{{$e->matricule}}</td>
                            <td><strong >{{$e->nom.' '.$e->prenom}}</strong></td>
                            <td>{{$e->date_naiss}}</td>
                            <td>{{$e->lieu_naiss}}</td>
                            <td>{{$e->sexe}}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="#" class=" btn btn-primary dropdown-toggle" data-toggle="dropdown">Actions <b class="caret"></b></a>
                                    <ul class="dropdown-menu">
                                        <li><a style="" href="{{ URL::to('/inscriptions/eleve/status').'?id='.$e->id }}"   class="btn-default"><span class="glyphicon glyphicon-cloud-upload"></span> {{  trans('admin.status_eleve')  }}</a></li>
                                        @if(isset($inscriptions[$e->id]) && !$inscriptions[$e->id]->desinscrire)
                                            @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_UPDATE))
                                                <li><a href="{{ URL::to('/inscriptions/edit/'.$e->id) }}"><span class="glyphicon glyphicon-pencil"></span> Modifier l'inscription</a></li>
                                            @endif
                                            @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE))
                                                <li><a href="#" onclick="fill_delete_modal({{$e->id}}, '{{ URL::to('/inscriptions/remove/'.$e->id) }}', null, 'Confirmer la desinscription de cet etudiant ?'); return false;"><span class="glyphicon glyphicon-ban-circle"></span> {{ trans('admin.desinscrire') }}</a></li>
                                            @endif
                                        @endif
                                        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE))
                                            <li><a style="color: white" href="{{ URL::to('/inscriptions/create/').'/'.$e->id }}"   class="btn-success"><span class="glyphicon glyphicon-cloud-upload"></span> {{  trans('inscription.inscrire')  }}</a></li>
                                        @endif
                                        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE))
                                            <li><a style="color: white" class="btn-primary"   href="{{ URL::to('/inscriptions/print-certificate/').'/'.$e->id }}"><span class="fa fa-file-text-o"></span> {{  trans('admin.certificat_scolarite')  }}</a></li>
                                        @endif


                                        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_CREATE))
                                            <li><a href="{{URL::to('/inscriptions/payement/create/'.$e->id)}}" style="color: white" class="btn-success" ><span class="fa fa-money"></span> {{trans('inscription.add_payement')}}</a></li>
                                            <li><a href="{{URL::to('/inscriptions/payement/create-frais/'.$e->id)}}" style="color: white" class="btn-primary" ><span class="fa fa-money"></span> {{trans('admin.paiement_frais')}}</a></li>
                                        @endif

                                        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT))
                                            <li><a href="{{ URL::to('/inscriptions/payement/list/'.$e->id)}}"  style="color: white" class="btn-warning"><span class="glyphicon glyphicon-list"></span> {{  trans('inscription.liste_paiement')  }}</a></li>
                                        @endif

                                        <li><a style="color: white" class="btn-primary"  data-toggle="modal" data-backdrop="false" href="{{URL::to('/inscriptions/enregistrement/edit/'.$e->id)}}"><span class="glyphicon glyphicon-pencil"  ></span> {{  trans('admin.see_edit')  }}</a></li>
                                        @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_ADMIN_STUDENT_DELETE))
                                            <li><a style="color: white" class="btn-danger"   href="#" onclick="fill_delete_modal({{$e->id}}, '{{ route('destroy_eleve',$e->id) }}', null, '@lang('admin.confirm_delete_student')')"><span class="glyphicon glyphicon-trash"></span> {{  trans('admin.del')  }}</a></li>
                                        @endif
                                    </ul>
                                </div>
                                @if(isset($inscriptions[$e->id]) && $inscriptions[$e->id]->desinscrire)
                                    <span class="text-muted">Desinscrit</span>
                                @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
                <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $eleves->render()) ); ?></div>


        </div>

    <script>
        function getScolarite() {

            var cycle=$('#cycle').val();
            var el=$('#classe')[0];
            var niveau= el.options[el.selectedIndex].id;
            var token='{{csrf_token()}}';

            if(cycle.length>0 && niveau.length>0)
            {
                $.ajax({
                    url:'{{route('get_scolarite')}}',
                    type:'POST',
                    dataType:'html',
                    data:'cycle='+cycle+'&niveau='+niveau+'&_token='+token,
                    complete: function (res,stat) {
                        var obj=JSON.parse(res.responseText);
                        if(obj.inscription<=0)
                        {
                            $('#show_montant').addClass('hidden');
                            $('#montant-group').removeClass('hidden');
                            var montant=$('#montant').removeClass('disabled');
                            montant.attr('readonly',false);
                            montant.val('');

                        }
                        else {
                            $('#show_montant').removeClass('hidden');
                            $('#show_montant_value').html(obj.inscription);
                            $('#montant-group').addClass('hidden');
                            var montant=$('#montant').addClass('disabled');
                            montant.val(obj.inscription);



                        }


                    }
                })
            }
            else {
                $('#show_montant').addClass('hidden');
                $('#montant-group').addClass('hidden');
                $('#montant').val('');

            }


        }
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
