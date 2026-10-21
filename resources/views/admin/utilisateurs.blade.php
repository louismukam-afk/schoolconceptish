@extends('skeleton')
@section('sub-content')
    <div class="row">
        <div class="col-md-8">
            <form class="form-horizontal" id="search_user_form" method="get" action="{{URL::to('/utilisateurs')}}">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search">{{  trans('inscription.recherche')  }} : </label>
                        <div class="input-group">
                            <input id="search" type="search" name="search" placeholder="{{trans('inscription.nom')}}" class="form-control" autofocus>
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary" ><span class="fa fa-search"> </span>{{trans('inscription.search')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
    <div class="col-md-10">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif

        <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
            <thead>
            <th>#</th>
            <th>{{  trans('admin.username')  }}</th>
            <th>{{  trans('admin.fullname')  }}</th>
            <th>@lang('inscription.nom') @lang('admin.personnel')</th>
            <th>Role </th>

            <th>{{  trans('admin.actif')  }}</th>
            <th>Actions</th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($utilisateurs as $u)
                <tr>
                    <td>{{$i++}}</td>
                    <td><strong >{{$u->name}}</strong></td>
                    <td> <strong>{{$u->fullname}}</strong></td>
                    <td>
                        @if($u->personnel)
                            {{$u->personnel->nom}}
                        @endif
                    </td>
                    <td><a href="{{route('action_management',$u->id)}}" class="btn btn-warning btn-xs"><span class="glyphicon glyphicon-lock"></span> {{trans_choice('admin.action',1)}}</a></td>

                    @if($u->active==1)
                        <td>{{trans('inscription.oui')}}</td>
                        <td>
                            <a class="btn btn-danger btn-xs" href="{{url('utilisateurs/desactiver/'.$u->id)}}"><span class="glyphicon glyphicon-ban-circle"></span> {{ trans('admin.desactiver') }}</a>
                            <a class="btn btn-primary btn-xs" data-toggle="modal" data-backdrop="false" href="#reset_password_user" data-username="{{$u->name}}" onclick="reset_password_utilisateur({{$u->id}}, this.getAttribute('data-username'))"><span class="glyphicon glyphicon-refresh"></span> Mot de passe</a>
                        </td>
                    @else
                        <td>{{trans('inscription.non')}}</td>
                        <td>
                            <a class="btn btn-success btn-xs" data-toggle="modal" data-backdrop="false" href="#edit_classe" onclick="activer_utilisateur({{$u->id}})"><span class="glyphicon glyphicon-ok"></span> {{ trans('admin.activer') }}</a>
                            <a class="btn btn-primary btn-xs" data-toggle="modal" data-backdrop="false" href="#reset_password_user" data-username="{{$u->name}}" onclick="reset_password_utilisateur({{$u->id}}, this.getAttribute('data-username'))"><span class="glyphicon glyphicon-refresh"></span> Mot de passe</a>
                        </td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
            <div class="paginate" style="text-align: center;"> <?php echo(str_replace('/?', '?', $utilisateurs->render()) ); ?></div>



            <div class="modal fade" id="edit_classe">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="activate_user_form" action="{{ url('/utilisateurs/activer/') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="id" id="id_utilisateur" value=""/>
                            <fieldset>

                                <div class="form-group">
                                    <label >@lang('admin.personnel') : </label>
                                    <select  name="personnel" form="activate_user_form" class="form-control autocomplete">
                                        <option value="0">{{trans('admin.aucun')}}</option>
                                        @foreach($personnels as $p)
                                            <option value="{{$p->id}}">{{$p->nom}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Campus autorises : </label>
                                    @if(isset($campus))
                                        @foreach($campus as $cp)
                                            <div class="checkbox">
                                                <label><input type="checkbox" name="campus[]" value="{{ $cp->id }}"> {{ $cp->nom }}</label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>

    </div>

        <div class="modal fade" id="reset_password_user">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">Modifier le mot de passe</h4>
                    </div>
                    <div class="modal-body">
                        <form accept-charset="UTF-8" role="form" method="POST" id="reset_password_user_form" action="{{ url('/utilisateurs/reset-password') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="id" id="id_reset_password_utilisateur" value=""/>
                            <fieldset>
                                <div class="alert alert-info">
                                    Utilisateur : <strong id="reset_password_username"></strong>
                                </div>
                                <div class="form-group">
                                    <label>Nouveau mot de passe : </label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                </div>
                                <div class="form-group">
                                    <label>Confirmer le mot de passe : </label>
                                    <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                                </div>
                                <input class="btn btn-primary" type="submit" value="Enregistrer">
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <script>
        function activer_utilisateur(id)
        {
            document.getElementById("id_utilisateur").value = id;

        }

        function reset_password_utilisateur(id, username)
        {
            document.getElementById("id_reset_password_utilisateur").value = id;
            document.getElementById("reset_password_username").innerHTML = username;
        }
    </script>
<?php
    function getRole($n){
        if($n==2)
            return trans('admin.editeur');
        if($n==16)
            return trans('admin.g_comptabilite');
        if($n==17)
            return trans('admin.g_personnel');
        if($n==18)
            return trans('admin.g_notes');
        if($n==18.5)
            return trans('admin.enseignant');
        if($n==19)
            return trans('admin.g_inscriptions');

            return trans('admin.aucun');
    }
?>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
