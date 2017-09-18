@extends('skeleton')
@section('sub-content')
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
            <th>Role </th>
            <th>{{  trans('admin.actif')  }}</th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($utilisateurs as $u)
                <tr>
                    <td>{{$i++}}</td>
                    <td><strong >{{$u->name}}</strong></td>
                    <td> <strong>{{$u->fullname}}</strong></td>
                    <td>{{getRole($u->role)}}</td>
                    @if($u->active==1)
                    <td>{{trans('inscription.oui')}}</td>
                    <td>
                        <a  class="btn btn-danger btn-xs"   href="{{url('utilisateurs/desactiver/'.$u->id)}}"> <span class="glyphicon glyphicon-ban-circle"></span> {{  trans('admin.desactiver')  }}</a>
                        @else
                        <td>{{trans('inscription.non')}}</td>
                        <td>
                            <a  class="btn btn-success btn-xs"  data-toggle="modal" data-backdrop="false" href="#edit_classe" onclick="activer_utilisateur({{$u->id}})"><span class="glyphicon glyphicon-ok"  ></span> {{  trans('admin.activer')  }}</a>

                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>


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
                                    <label for="role">Role : </label>
                                    <select  name="role" form="activate_user_form" class="form-control">
                                            <option value="2">{{trans('admin.editeur')}}</option>
                                        <option value="17">{{trans('admin.g_personnel')}}</option>
                                        <option value="18">{{trans('admin.g_notes')}}</option>
                                        <option value="19">{{trans('admin.g_inscriptions')}}</option>
                                    </select>
                                </div>

                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>

    </div>

    <script>
        function activer_utilisateur(id)
        {
            document.getElementById("id_utilisateur").value = id;

        }
    </script>
<?php
    function getRole($n){
        if($n==2)
            return trans('admin.editeur');
        if($n==17)
            return trans('admin.g_personnel');
        if($n==18)
            return trans('admin.g_notes');
        if($n==19)
            return trans('admin.g_inscriptions');

            return trans('admin.aucun');
    }
?>
@endsection