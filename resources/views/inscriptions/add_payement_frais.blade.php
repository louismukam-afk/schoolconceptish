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

        <p style="font-size: 18px"> <strong>Matricule : {{$e->matricule}} </strong></p>
        <p style="font-size: 18px"> <strong>{{trans('admin.nom')}} : {{$e->nom.' '.$e->prenom}} </strong></p>

        <div class="col-md-8">
            <form class="form-horizontal" id="add_inscription_form" method="post" action="{{URL::to('/inscriptions/payement/store/'.$e->id)}}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                <input type="hidden" name="id_eleve" value="{{$e->id}}">
                <input type="hidden" name="montant" id="montant" required/>

                <div class="col-md-6">
                    
                     <div class="form-group">
                        <label for="frais">@lang('admin.fee_type')</label>
                            <select name="type"  class="form-control" id="typeSelect" form="add_inscription_form" onchange="changeTypeAction()" style="font-size:20px; height: 50px; text-align: center; border:solid orange 2px" required="required">
                                <option value=""> -- @lang('admin.selectionner') -- </option>
                                @foreach($frais as $f)
                                    <option value="{{$f->id}}" montant="{{$f->montant}}" type="{{ $f->type }}">{{$f->nom}}</option>
                                @endforeach
                            </select>
                    </div>



                    <div class="form-group">
                        <label for="motif">{{trans('inscription.motif')}} : </label>
                            <input type="text" name="motif" id="motif" placeholder="EX: 1ère tranche" class="form-control" required value="{{old('motif')}}">

                    </div>
                    <div class="form-group">
                        <label>Campus : <span class="required">*</span></label>
                        @if(isset($campus_actif) && $campus_actif)
                            <input type="hidden" name="id_campus" value="{{ $campus_actif->id }}">
                            <p class="form-control-static">
                                <strong>{{ $campus_actif->nom }}</strong>
                                @if($campus_actif->sigle) - {{ $campus_actif->sigle }} @endif
                                @if($campus_actif->localisation) | {{ $campus_actif->localisation }} @endif
                            </p>
                        @else
                            <select name="id_campus" class="form-control" required>
                                <option value=""> -- Choisir --</option>
                                @if(isset($campus))
                                    @foreach($campus as $cp)
                                        <option value="{{ $cp->id }}" @if(old('id_campus')==$cp->id) selected @endif>{{ $cp->nom }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @endif
                    </div>
                    <div class="form-group">
                        <label>Montant en lettre</label>
                        <input type="text" name="lettre"  class="form-control"  value="{{old('lettre')}}">

                    </div>
                    <div class="form-group">
                        <label for="mode_paye">{{trans('inscription.detail')}} : </label>
                        <select form="add_inscription_form" id="mode_paye" name="mode_paye" class="form-control">
                            <option value="bancaire">{{trans('inscription.bancaire') }}</option>
                            <option value="orange money">{{trans('inscription.om')}}</option>
                            <option value="espece">{{trans('inscription.espece')}}</option>

                        </select>

                    </div>
                    <div class="form-group">
                        <label>Type d'operation : <span class="required">*</span></label>
                        <select name="operation_source" id="operation_source" class="form-control" onchange="toggleOperationSource()" required>
                            <option value="caisse" @if(old('operation_source')=='caisse') selected @endif>Espece / Caisse</option>
                            <option value="banque" @if(old('operation_source')=='banque') selected @endif>Banque</option>
                        </select>
                    </div>
                    <div class="form-group" id="caisse_group">
                        <label>Caisse d'entree</label>
                        <select name="id_caisse" class="form-control">
                            @if(isset($caisses))
                                @foreach($caisses as $caisse)
                                    <option value="{{ $caisse->id }}" @if(old('id_caisse')==$caisse->id) selected @endif>{{ $caisse->nom }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div id="banque_group" class="hidden">
                        <div class="form-group">
                            <label>Banque</label>
                            <select name="id_banque" class="form-control">
                                @if(isset($banques))
                                    @foreach($banques as $banque)
                                        <option value="{{ $banque->id }}" @if(old('id_banque')==$banque->id) selected @endif>{{ $banque->nom }} {{ $banque->numero_compte }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Numero de versement</label>
                            <input type="text" name="numero_versement" class="form-control" value="{{ old('numero_versement') }}">
                        </div>
                        <div class="form-group">
                            <label>Reference</label>
                            <input type="text" name="reference_operation" class="form-control" value="{{ old('reference_operation') }}">
                        </div>
                        <div class="form-group">
                            <label>Demande</label>
                            <input type="text" name="demande_operation" class="form-control" value="{{ old('demande_operation') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{trans('inscription.ref')}} </label>
                        <input type="text" name="ref_versement"  class="form-control"  value="{{old('ref_versement')}}">

                    </div>
                    <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >
                </div>
            </form>



            <div class="row">

                <div class="col-md-6 hidden" id="amountAlert" >

                    <div>
                        <div class="alert alert-info">
                            <p class="text-center" style="font-size: 28px;font-weight: 600"><span id="amount"></span> @lang('inscription.devise')</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6" id="amountInput" >
                    <div >
                        <div class="form-group">
                            <label for="montant">{{trans('inscription.montant')}} : </label>
                            <div class="input-group">
                                <input type="number" name="montant2" id="montant2" placeholder="EX: 15000" class="form-control" required value="{{old('montant2')}}" onkeyup="fillMontant()">
                                <span class="input-group-addon"> {{trans('inscription.devise')}}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>




    <script>

        $(function () {
            changeTypeAction();
        });
        function changeTypeAction() {
            ts=$('#typeSelect')[0];
            montant=ts.options[ts.selectedIndex].getAttribute("montant");
            id=ts.options[ts.selectedIndex].getAttribute("id");
            aa=$('#amountAlert');
            ai=$('#amountInput');
            mo=$('#montant');
            mo2=$('#montant2');
            a=$('#amount');

            if(montant){
                mo.val(montant);
                mo2.val(montant);
                a.html(montant);
                ai.val('');

                //ai.addClass('hidden');
                aa.removeClass('hidden');

            }
           /* else if(id){
                getLeftInscription(id);
            }*/
            else {
                mo.val('');
                aa.addClass('hidden');
                //ai.removeClass('hidden');
            }
        }



        function fillMontant(){
            mo2=$('#montant2');
            mo=$('#montant');
            console.log(mo2.val());
            mo.val(mo2.val());
        }

        function submitForm() {
            form=$('#add_inscription_form');
            form.submit();
        }

        function toggleOperationSource() {
            var source = $('#operation_source').val();
            if(source == 'banque') {
                $('#banque_group').removeClass('hidden');
                $('#caisse_group').addClass('hidden');
            } else {
                $('#banque_group').addClass('hidden');
                $('#caisse_group').removeClass('hidden');
            }
        }

        $(document).ready(function() {
            toggleOperationSource();
        });
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li><strong><a href="{{url('inscriptions/payement/search')}}">@lang('inscription.title_search')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection

