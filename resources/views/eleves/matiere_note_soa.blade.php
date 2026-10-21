@extends('skeleton')
@section('sub-content')
    <style>
        a:visited
        {
            color: #d58512;
        }

        a:hover
        {
            color: #d58512;
        }

    </style>
    <div class="col-md-12">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif
        @if(isset($erreur))

            <div class="alert alert-danger">
                {{  $erreur  }}
            </div>

        @endif
        <h4>{{trans('eleve.choix_mat')}}</h4>
        <hr>
        <div class="liste-classe col-md-9">
            <ul>
                @foreach($matieres as $m)
                    <li><a  data-toggle="modal" data-backdrop="false" href="#choose_seq" onclick="choix_seq({{$m->id}});this.style.color='#d58512'">{{(App::getLocale()=='en')?$m->nom_anglais:$m->nom}}</a></li>
                @endforeach
            </ul>
        </div>


        <div class="modal fade" id="choose_seq">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                    </div>
                    <div
                            class="modal-body">



                        <form accept-charset="UTF-8" role="form" method="POST" id="choix_seq_form" action="{{ url('/eleves/notes/create_soa/'.$id) }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="id" id="id" value=""/>
                            <input type="hidden" name="cycle" id="id_cycle" value="{{$id_cycle}}"/>
                            <input type="hidden" name="nniveau" id="id_nniveau" value="{{$id_nniveau}}"/>

                            <fieldset>
                                <div class="form-group">
                                    <label for="niveau">{{  trans('eleve.choix_seq')  }} : </label>
                                    <select  name="sequence" form="choix_seq_form" class="form-control" >
                                        <optgroup label="@lang('admin.semestre') 1">
                                            <option value="1">CC 1</option>
                                            <option value="3">@lang('admin.session_normale') 1</option>
                                            <option value="5">@lang('admin.rattrapage') 1</option>
                                        </optgroup>
                                        <optgroup label="@lang('admin.semestre') 2">
                                            <option value="2">CC 2</option>
                                            <option value="4">@lang('admin.session_normale') 2</option>
                                            <option value="6">@lang('admin.rattrapage') 2</option>
                                        </optgroup>

                                    </select>
                                </div>


                                <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}" autofocus>


                            </fieldset>
                        </form>


                    </div>





                </div>


            </div>
        </div>
    </div>

    <script>

    function chargerPvSoa(
        idClasse,
        sequence,
        cycle,
        nniveau
    ) {

        /*
        |--------------------------------------------------------------------------
        | Interface
        |--------------------------------------------------------------------------
        */

        $('#pv-erreur')
            .hide()
            .html('');

        $('#pv-resultat')
            .html('');

        $('#pv-loading')
            .show();


        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        $.ajax({

            url:
                "{{ url('/eleves/notes/print_note_soa') }}"
                + '/'
                + idClasse,

            type: 'GET',

            dataType: 'json',

            data: {

                sequence: sequence,

                cycle: cycle,

                nniveau: nniveau,

                ajax: 1
            },


            /*
            |--------------------------------------------------------------------------
            | SUCCES
            |--------------------------------------------------------------------------
            */

            success: function(response) {

                $('#pv-loading').hide();


                if (response.success) {

                    $('#pv-resultat')
                        .html(
                            response.html
                        );

                } else {

                    $('#pv-erreur')
                        .html(
                            response.message
                            ||
                            'Impossible de charger le PV.'
                        )
                        .show();
                }
            },


            /*
            |--------------------------------------------------------------------------
            | ERREUR
            |--------------------------------------------------------------------------
            */

            error: function(xhr) {

                $('#pv-loading').hide();


                var message =
                    'Une erreur est survenue lors du chargement du PV.';


                /*
                 * Si Laravel retourne du JSON
                 */

                if (
                    xhr.responseJSON
                    &&
                    xhr.responseJSON.message
                ) {

                    message =
                        xhr.responseJSON.message;
                }


                $('#pv-erreur')
                    .html(message)
                    .show();


                console.log(
                    xhr.responseText
                );
            }

        });
    }
      function choix_seq(id)
        {
            document.getElementById("id").value = id;
        }

</script>
  

@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('eleves')}}">@lang('menu.gestion_notes')</a></strong></li>
    <li><strong><a href="{{url('eleves/notes')}}">Notes</a></strong></li>

    <li><a href="{{url('eleves/notes/edit')}}"><strong>@lang('admin.choix_classe')</strong></a></li>
    <li class="active"><strong>@lang('admin.matiere')</strong></li>
@endsection
