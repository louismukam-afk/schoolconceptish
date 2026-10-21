@extends('skeleton')
@section('sub-content')

    <style>
        .panel-success>.panel-heading {
            background-color: #3c763d;
            color:white;
            border:solid 1px #3c763d;
        }
        .panel-success {

            border:solid 1px #3c763d;
        }
        #ajax-list-result {
            clear: both;
            margin-top: 20px;
            min-height: 80px;
        }
        .ajax-list-loading {
            padding: 25px;
            text-align: center;
            background: #f7f7f7;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .ajax-list-error {
            margin-top: 15px;
        }
    </style>
    <div id="monaccordeon" class="panel-group col-md-12">
        <h3>{{trans('inscription.choix_liste')}}</h3>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item1" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('inscription.eleves_inscrits')}}
                    </a>
                </h3>
            </div>

          


            <div id="item1"
                 class="panel-collapse collapsing">
                <div
                        class="panel-body">
                    <li class="col-md-12"><a  href="{{URL::to('/inscriptions/listes/show?id=0&nniveau=0')}}">{{trans('inscription.all_eleves')}}</a></li>
                    @foreach($nniveaux as $nn)
                        <li class="col-md-12"><a href="{{URL::to('/inscriptions/listes/show?id=0&nniveau='.$nn->id)}}">{{trans('inscription.all_eleves')}} - {{$nn->nom}}</a></li>
                    @endforeach


                    @foreach($niveaux as $n)
                        <div class="col-md-6">
                            <h4 class="page-header"> {{$n->getNom()}}</h4>

                            @foreach($classes as $c)
                                <div class="col-md-12">
                                    @if($c->id_niveau==$n->id)
                                        <li class="col-md-12"><a data-toggle="modal" data-backdrop="false" href="#choose_level" onclick="choose_level({{$c->id}}, '{{URL::to('/inscriptions/listes/show')}}', '')">{{$c->getNom()}}</a></li>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach


                </div>

            </div>
        </div>
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item12" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        Listes des étudiants avec contacts parents
                    </a>
                </h3>
            </div>

            <div id="item12"
                 class="panel-collapse collapsing">
                <div
                        class="panel-body">
                    <li class="col-md-12"><a  href="{{URL::to('/inscriptions/listes/showParents?id=0&nniveau=0')}}">{{trans('inscription.all_eleves')}}</a></li>
                    @foreach($nniveaux as $nn)
                        <li class="col-md-12"><a href="{{URL::to('/inscriptions/listes/showParents?id=0&nniveau='.$nn->id)}}">{{trans('inscription.all_eleves')}} - {{$nn->nom}}</a></li>
                    @endforeach


                    @foreach($niveaux as $n)
                        <div class="col-md-6">
                            <h4 class="page-header"> {{$n->getNom()}}</h4>

                            @foreach($classes as $c)
                                <div class="col-md-12">
                                    @if($c->id_niveau==$n->id)
                                        <li class="col-md-12"><a data-toggle="modal" data-backdrop="false" href="#choose_level" onclick="choose_level({{$c->id}}, '{{URL::to('/inscriptions/listes/showParents')}}', '')">{{$c->getNom()}}</a></li>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach


                </div>

            </div>

        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item2" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('admin.scolarite_pi')}}
                    </a>
                </h3>
            </div>

            <div id="item2"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    <form class="form-horizontal" id="check_list_pay_form" method="get" action="{{route('show_sco_liste')}}">
                        <div class="">

                            <div class="row">
                                <div class="col-md-5" >
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>@lang('admin.tranche'): </label>
                                        <select form="check_list_pay_form" id="option" name="option" class="form-control" required="required">
                                            <option value="" disabled>-- @lang('admin.selectionner') --</option>
                                            <option value="1"> @lang('admin.tranche1') </option>
                                            <option value="2"> @lang('admin.tranche2') </option>
                                            <option value="3"> @lang('admin.tranche3') </option>
                                            <option value="4"> @lang('admin.tranche4') </option>
                                            <option value="5"> @lang('admin.tranche5') </option>
                                            <option value="6"> @lang('admin.schoolfees') </option>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-md-5" >
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>Type: </label>
                                        <select form="check_list_pay_form" id="type_pay" name="type" class="form-control" required>
                                            <option value="0">@lang('admin.impaye')</option>
                                            <option value="1">@lang('admin.paye')</option>
                                        </select>

                                    </div>
                                </div>
                                <div class="col-md-5" >
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>Profil de scolarite : </label>
                                        <select form="check_list_pay_form" name="scolarite_style" class="form-control">
                                            <option value="">Tous les profils</option>
                                            <option value="0">Ancien profil</option>
                                            <option value="1">Nouveau profil</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">


                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>@lang('admin.specialites')  : </label>

                                        <select form="check_list_pay_form" id="select_classe_for_pay" name="id" class="form-control autocomplete" onchange="classForPayChange()" required>
                                            <?php $n=0;$i=0; ?>
                                            <option value="" disabled> -- @lang('admin.choix_classe') --</option>
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($classes_list as $c)
                                                @if($i=0)
                                                    <optgroup label="<{{$c->niveau->getNom()}}">
                                                        <?php  $i++; ?>
                                                        @endif
                                                        @if($c->id_niveau!=$n)
                                                            <?php $n = $c->id_niveau ?>
                                                    </optgroup>
                                                    <optgroup label="{{$c->niveau->getNom()}}">
                                                        @endif
                                                        <option id="{{$c->niveau->id}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                                                        @endforeach
                                                    </optgroup>
                                        </select>

                                    </div>
                                </div>



                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>  @lang('admin.nniveau') </label>
                                        <select form="check_list_pay_form"  name="nniveau" class="form-control" id="select_nniveau_for_pay">
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($nniveaux as $nn)
                                                <option value="{{$nn->id}}"  @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>
                            </div>


                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >

                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>




        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item3" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('admin.fiche_scolarite')}}
                    </a>
                </h3>
            </div>

            <div id="item3"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    @foreach($niveaux as $n)
                        <div class="col-md-6">
                            <h4 class="page-header"> {{$n->getNom()}}</h4>

                            @foreach($classes as $c)
                                <div class="col-md-12">
                                    @if($c->id_niveau==$n->id)
                                        <li class="col-md-12"><a data-toggle="modal" data-backdrop="false" href="#choose_level" onclick="choose_level({{$c->id}}, '{{URL::to('/inscriptions/fiche-scolarite')}}', '')">{{$c->getNom()}}</a></li>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach


                </div>

            </div>
        </div>






        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item8" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('admin.frais_pi')}}
                    </a>
                </h3>
            </div>



            <div id="item8"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    <form class="form-horizontal" id="check_list_form" method="get" action="{{route('show_frais_liste')}}">
                        <div class="">

                           <div class="row">
                                   <div class="col-md-5" >
                                       <div class="form-group" style="padding: 5px 10px">
                                           <label>@lang('admin.frais'): </label>
                                           <select form="check_list_form" id="frais" name="frais" class="form-control" required="required">
                                               <option value="">-- @lang('admin.selectionner') --</option>
                                               <option value="0">@lang('admin.inscriptions')</option>
                                               @foreach($frais as $f)
                                                   <option value="{{$f->id}}"> {{$f->nom}} </option>
                                               @endforeach
                                           </select>

                                       </div>
                                   </div>

                               <div class="col-md-5" >
                                   <div class="form-group" style="padding: 5px 10px">
                                       <label>Type: </label>
                                       <select form="check_list_form" id="type" name="type" class="form-control" required>
                                           <option value="0">@lang('admin.impaye')</option>
                                           <option value="1">@lang('admin.paye')</option>
                                       </select>

                                   </div>
                               </div>
                               <div class="col-md-5" >
                                   <div class="form-group" style="padding: 5px 10px">
                                       <label>Profil de scolarite : </label>
                                       <select form="check_list_form" name="scolarite_style" class="form-control">
                                           <option value="">Tous les profils</option>
                                           <option value="0">Ancien profil</option>
                                           <option value="1">Nouveau profil</option>
                                       </select>
                                   </div>
                               </div>
                           </div>

                            <div class="row">


                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>@lang('admin.specialites')  : </label>

                                        <select form="check_list_form" id="select_classe_for_frais" name="classe" class="form-control autocomplete" onchange="classForFraisChange()" required>
                                            <?php $n=0;$i=0; ?>
                                            <option value="" disabled> -- @lang('admin.choix_classe') --</option>
                                                <option value="0"> @lang('admin.tous') </option>
                                            @foreach($classes_list as $c)
                                                @if($i=0)
                                                    <optgroup label="<{{$c->niveau->getNom()}}">
                                                        <?php  $i++; ?>
                                                        @endif
                                                        @if($c->id_niveau!=$n)
                                                            <?php $n=$c->id_niveau ?>
                                                    </optgroup>
                                                    <optgroup label="{{$c->niveau->getNom()}}">
                                                        @endif
                                                        <option id="{{$c->niveau->id}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                                                        @endforeach
                                                    </optgroup>
                                        </select>

                                    </div>
                                </div>



                            </div>
                            <div class="row">
                                  <div class="col-md-5">
                                      <div class="form-group" style="padding: 5px 10px">
                                          <label>  @lang('admin.nniveau') </label>
                                          <select form="check_list_form"  name="nniveau" class="form-control" id="select_nniveau_for_frais">
                                              <option value="0"> @lang('admin.tous') </option>
                                              @foreach($nniveaux as $nn)
                                                  <option value="{{$nn->id}}"  @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                                              @endforeach
                                          </select>

                                      </div>
                                  </div>
                            </div>






                        </div>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >

                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>

        <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item9" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('admin.bourse')}}
                    </a>
                </h3>
            </div>



            <div id="item9"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    <form class="form-horizontal" id="check_list_form_bourse" method="get" action="{{route('show_bourses_liste')}}">
                        <div class="">

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>@lang('admin.specialites')  : </label>

                                        <select form="check_list_form_bourse" id="select_classe_for_bourses" name="classe" class="form-control autocomplete" onchange="classForBoursesChange()" required>
                                            <?php $n=0;$i=0; ?>
                                            <option value="" disabled> -- @lang('admin.choix_classe') --</option>
                                                <option value="0"> @lang('admin.tous') </option>
                                            @foreach($classes_list as $c)
                                                @if($i=0)
                                                    <optgroup label="<{{$c->niveau->getNom()}}">
                                                        <?php  $i++; ?>
                                                        @endif
                                                        @if($c->id_niveau!=$n)
                                                            <?php $n=$c->id_niveau ?>
                                                    </optgroup>
                                                    <optgroup label="{{$c->niveau->getNom()}}">
                                                        @endif
                                                        <option id="{{$c->niveau->id}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                                                        @endforeach
                                                    </optgroup>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>  @lang('admin.nniveau') </label>
                                        <select form="check_list_form_bourse"  name="nniveau" class="form-control" id="select_nniveau_for_bourses">
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($nniveaux as $nn)
                                                <option value="{{$nn->id}}"  @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                            </div>







                        </div>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >

                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>


             <div class="panel panel-danger">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item10" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        {{trans('admin.acces')}}
                    </a>
                </h3>
            </div>



            <div id="item10"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    <form class="form-horizontal" id="check_list_form_acces" method="get" action="{{route('show_acces_liste')}}">
                        <div class="">

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>@lang('admin.specialites')  : </label>

                                        <select form="check_list_form_acces" id="select_classe_for_acces" name="classe" class="form-control autocomplete" onchange="classForaccesChange()" required>
                                            <?php $n=0;$i=0; ?>
                                            <option value="" disabled> -- @lang('admin.choix_classe') --</option>
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($classes_list as $c)
                                                @if($i=0)
                                                    <optgroup label="<{{$c->niveau->getNom()}}">
                                                        <?php  $i++; ?>
                                                        @endif
                                                        @if($c->id_niveau!=$n)
                                                            <?php $n=$c->id_niveau ?>
                                                    </optgroup>
                                                    <optgroup label="{{$c->niveau->getNom()}}">
                                                        @endif
                                                        <option id="{{$c->niveau->id}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                                                        @endforeach
                                                    </optgroup>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>  @lang('admin.nniveau') </label>
                                        <select form="check_list_form_acces"  name="nniveau" class="form-control" id="select_nniveau_for_acces">
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($nniveaux as $nn)
                                                <option value="{{$nn->id}}"  @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                            </div>







                        </div>

        



                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >

                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>



        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item11" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        Liste des accès autorisés
                    </a>
                </h3>
            </div>



            <div id="item11"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    <form class="form-horizontal" id="check_list_form_accesok" method="get" action="{{route('show_accesok_liste')}}">
                        <div class="">

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>@lang('admin.specialites')  : </label>

                                        <select form="check_list_form_accesok" id="select_classe_for_accesok" name="classe" class="form-control autocomplete" onchange="classForaccesokChange()" required>
                                            <?php $n=0;$i=0; ?>
                                            <option value="" disabled> -- @lang('admin.choix_classe') --</option>
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($classes_list as $c)
                                                @if($i=0)
                                                    <optgroup label="<{{$c->niveau->getNom()}}">
                                                        <?php  $i++; ?>
                                                        @endif
                                                        @if($c->id_niveau!=$n)
                                                            <?php $n=$c->id_niveau ?>
                                                    </optgroup>
                                                    <optgroup label="{{$c->niveau->getNom()}}">
                                                        @endif
                                                        <option id="{{$c->niveau->id}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                                                        @endforeach
                                                    </optgroup>
                                        </select>

                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="form-group" style="padding: 5px 10px">
                                        <label>  @lang('admin.nniveau') </label>
                                        <select form="check_list_form_accesok"  name="nniveau" class="form-control" id="select_nniveau_for_accesok">
                                            <option value="0"> @lang('admin.tous') </option>
                                            @foreach($nniveaux as $nn)
                                                <option value="{{$nn->id}}"  @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                                            @endforeach
                                        </select>

                                    </div>
                                </div>

                            </div>







                        </div>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >

                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>


        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <a class="accordion-toggle" href="#item13" dataparent="#monaccordeon"
                       data-toggle="collapse">
                        Liste des etudiants desinscrits
                    </a>
                </h3>
            </div>

            <div id="item13"
                 class="panel-collapse collapsing">
                <div class="panel-body">
                    <form class="form-horizontal" id="check_list_form_desinscrits" method="get" action="{{route('show_desinscrits_liste')}}">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group" style="padding: 5px 10px">
                                    <label>@lang('admin.specialites')  : </label>

                                    <select form="check_list_form_desinscrits" id="select_classe_for_desinscrits" name="classe" class="form-control autocomplete" onchange="classForDesinscritsChange()" required>
                                        <?php $n=0;$i=0; ?>
                                        <option value="" disabled> -- @lang('admin.choix_classe') --</option>
                                        <option value="0"> @lang('admin.tous') </option>
                                        @foreach($classes_list as $c)
                                            @if($i=0)
                                                <optgroup label="<{{$c->niveau->getNom()}}">
                                                    <?php  $i++; ?>
                                                    @endif
                                                    @if($c->id_niveau!=$n)
                                                        <?php $n=$c->id_niveau ?>
                                                </optgroup>
                                                <optgroup label="{{$c->niveau->getNom()}}">
                                                    @endif
                                                    <option id="{{$c->niveau->id}}" value="{{$c->id}}" @if(old('classe')==$c->id) selected @endif>{{$c->getNom()}}</option>
                                                    @endforeach
                                                </optgroup>
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group" style="padding: 5px 10px">
                                    <label>  @lang('admin.nniveau') </label>
                                    <select form="check_list_form_desinscrits"  name="nniveau" class="form-control" id="select_nniveau_for_desinscrits">
                                        <option value="0"> @lang('admin.tous') </option>
                                        @foreach($nniveaux as $nn)
                                            <option value="{{$nn->id}}"  @if(old('nniveau')==$nn->id) selected @endif>{{$nn->nom}}</option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 col-md-offset-3">
                                <input type="submit" class="btn btn-success form-control" value="{{trans('admin.confirmer')}}" >
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>



    </div>

    <div id="ajax-list-result"></div>





    <script>

        $(function (){
            classForFraisChange()
            classForPayChange()
            classForBoursesChange()
            classForaccesChange()
            classForaccesokChange()
            classForDesinscritsChange()
            bindAjaxLists()
        })

        function bindAjaxLists()
        {
            $(document).off('click.ajaxLists', 'a').on('click.ajaxLists', 'a', function (e) {
                var href = $(this).attr('href');
                if (!href || href === '#' || href.indexOf('/inscriptions/listes/') === -1) {
                    return;
                }

                e.preventDefault();
                loadListAjax(href);
            });

            $(document).off('submit.ajaxLists', 'form').on('submit.ajaxLists', 'form', function (e) {
                var action = $(this).attr('action');
                if (!action || action.indexOf('/inscriptions/listes/') === -1) {
                    return;
                }

                e.preventDefault();
                var separator = action.indexOf('?') === -1 ? '?' : '&';
                loadListAjax(action + separator + $(this).serialize());
                $('#choose_level').modal('hide');
            });
        }

        function loadListAjax(url)
        {
            var container = $('#ajax-list-result');

            container.html('<div class="ajax-list-loading">Chargement de la liste...</div>');
            $('html, body').animate({ scrollTop: container.offset().top - 80 }, 250);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                beforeSend: function () {
                    if (typeof showLoader === 'function') {
                        showLoader();
                    }
                },
                success: function (html) {
                    var response = $('<div>').append($.parseHTML(html, document, true));
                    var fragment = response.find('.ajax-list-fragment').first();

                    if (!fragment.length) {
                        fragment = response.find('.list-cont').first();
                    }

                    if (!fragment.length) {
                        container.html(html);
                        return;
                    }

                    container.html(fragment.html());
                    executeAjaxListScripts(fragment);

                    if (window.history && window.history.pushState) {
                        window.history.pushState({ajaxListUrl: url}, '', url);
                    }
                },
                error: function () {
                    container.html('<div class="alert alert-danger ajax-list-error">Impossible de charger la liste. Veuillez reessayer.</div>');
                },
                complete: function () {
                    if (typeof hideLoader === 'function') {
                        hideLoader();
                    }
                }
            });
        }

        function executeAjaxListScripts(fragment)
        {
            fragment.find('script').each(function () {
                var scriptContent = $(this).html();
                if (scriptContent) {
                    scriptContent = scriptContent.replace(/jQuery\.noConflict\(\);?/g, '');
                    $.globalEval(scriptContent);
                }
            });
        }

        function classForFraisChange()
        {
            selectNNiveau = $('#select_nniveau_for_frais');
            selectNNiveau.removeAttr('disabled');
        }
        function classForaccesChange()
        {
            selectNNiveau = $('#select_nniveau_for_acces');
            selectNNiveau.removeAttr('disabled');
        }


        function classForaccesokChange()
        {
            selectNNiveau = $('#select_nniveau_for_accesok');
            selectNNiveau.removeAttr('disabled');
        }
        function classForBoursesChange()
        {
            selectNNiveau = $('#select_nniveau_for_bourses');
            selectNNiveau.removeAttr('disabled');
        }

        function classForPayChange()
        {
            selectNNiveau = $('#select_nniveau_for_pay');
            selectNNiveau.removeAttr('disabled');
        }
        function classForDesinscritsChange()
        {
            selectNNiveau = $('#select_nniveau_for_desinscrits');
            selectNNiveau.removeAttr('disabled');
        }
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
