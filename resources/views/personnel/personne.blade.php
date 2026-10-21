@extends('skeleton')
@section('sub-content')
    <div class="col-md-10">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif

        @if(isset($import_success))

            <div class="alert alert-success">
                {{  $import_success  }}
            </div>

        @endif

        @if($errors->any())

            <div class="alert alert-danger">
                {{  $errors->first()  }}
            </div>

        @endif

            <button class="btn btn-primary" data-toggle="modal" data-backdrop="false" href="#add_cycle"
            ><span class="glyphicon glyphicon-plus"></span> {{  trans('admin.ajouter_personnel')  }}</button>
            <a class="btn btn-success" href="{{ route('personnel_import_template') }}">
                <span class="fa fa-download"></span> Telecharger le template Excel
            </a>
            <button class="btn btn-warning" data-toggle="modal" data-backdrop="false" href="#import_personnel">
                <span class="fa fa-file-excel-o"></span> Importer le personnel
            </button>
            <span class="col-md-12"><strong>Total : {{count($personnel)}}</strong></span>

            <h4 style="font-size: 22px">{{  trans('admin.personnels').' : '.trans('admin.actif')  }}</h4>

            <table id="table-personnel" class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 5px;">
            <thead>
            <th>#</th>
            <th>@lang('inscription.nom')</th>
            <th>@lang('inscription.lieu_naiss_full')</th>
            <th>{{trans('inscription.sexe')}}</th>
            <th>Tel</th>
            <th></th>
            </thead>
            <tbody>
            <?php $i=1; ?>
            @foreach($personnel as $c)
                @if($c->actif==1)
                    <tr>
                        <td>{{$i++}}</td>
                        <td><a href="{{ URL::to('/personnel/edit/').'/'.$c->id }}" > <span class="glyphicon glyphicon-edit"></span> <strong >{{$c->nom}}</strong></a></td>
                        <td><strong >{{$c->lieu_naiss}}</strong></td>
                        <td><strong >{{$c->sexe}}</strong></td>
                        <td><strong >{{$c->tel1}}</strong></td>

                        <td><a href="{{route('deactivate_personnel',$c->id)}}"   class="btn btn-danger btn-xs pull-right"><span class="glyphicon glyphicon-ban-circle"></span> {{trans('admin.desactiver')}}</a></td>
                    </tr>
                @endif

            @endforeach
            </tbody>
        </table>

            <hr>
            <h4 style="font-size: 22px">{{  trans('admin.personnels').' : '.trans('admin.non_actif')  }}</h4>

            <table id="table-personnel2" class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 5px;">
                <thead>
                <th>#</th>
                <th>@lang('inscription.nom')</th>
                <th>@lang('inscription.lieu_naiss_full')</th>
                <th>{{trans('inscription.sexe')}}</th>
                <th>Tel</th>
                <th></th>
                </thead>
                <tbody>
                <?php $i=1; ?>
                @foreach($personnel as $c)
                    @if($c->actif==0)
                        <tr>
                            <td>{{$i++}}</td>
                            <td><a href="{{ URL::to('/personnel/edit/').'/'.$c->id }}" > <span class="glyphicon glyphicon-edit"></span> <strong >{{$c->nom}}</strong></a></td>
                            <td><strong >{{$c->lieu_naiss}}</strong></td>
                            <td><strong >{{$c->sexe}}</strong></td>
                            <td><strong >{{$c->tel1}}</strong></td>

                            <td><a href="{{route('activate_personnel',$c->id)}}"   class="btn btn-success btn-xs pull-right"><span class="glyphicon glyphicon-check"></span> {{trans('admin.activer')}}</a></td>
                        </tr>
                    @endif

                @endforeach
                </tbody>
            </table>

            <div class="modal fade" id="import_personnel">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">Importer le personnel</h4>
                        </div>
                        <div class="modal-body">
                            <form accept-charset="UTF-8" role="form" method="POST" action="{{ route('personnel_import') }}" enctype="multipart/form-data">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <fieldset>
                                    <div class="form-group">
                                        <label for="fichier_personnel">Fichier Excel / CSV : <span class="required">*</span></label>
                                        <input class="form-control" id="fichier_personnel" name="fichier_personnel" type="file" accept=".csv,.txt,.xlsx" required>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-condensed table-striped">
                                            <thead>
                                            <tr>
                                                <th>Colonne</th>
                                                <th>Obligatoire</th>
                                                <th>Comment remplir</th>
                                                <th>Valeur par defaut</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr><td>nom</td><td>Oui</td><td>Nom complet du personnel.</td><td>-</td></tr>
                                            <tr><td>date_naiss</td><td>Oui</td><td>Date au format AAAA-MM-JJ ou JJ/MM/AAAA.</td><td>-</td></tr>
                                            <tr><td>lieu_naiss</td><td>Oui</td><td>Lieu de naissance.</td><td>-</td></tr>
                                            <tr><td>nationalite</td><td>Oui</td><td>Nationalite du personnel.</td><td>-</td></tr>
                                            <tr><td>sexe</td><td>Oui</td><td>H pour homme ou F pour femme.</td><td>-</td></tr>
                                            <tr><td>tel1</td><td>Oui</td><td>Telephone principal.</td><td>-</td></tr>
                                            <tr><td>adresse</td><td>Oui</td><td>Adresse ou quartier.</td><td>-</td></tr>
                                            <tr><td>type</td><td>Oui</td><td>0 pour enseignant, 1 pour autre.</td><td>-</td></tr>
                                            <tr><td>date_rec</td><td>Non</td><td>Date de recrutement au format AAAA-MM-JJ ou JJ/MM/AAAA.</td><td>Date du jour</td></tr>
                                            <tr><td>diplome</td><td>Non</td><td>Diplome le plus eleve ou qualification.</td><td>Vide</td></tr>
                                            <tr><td>email</td><td>Non</td><td>Adresse email.</td><td>Vide</td></tr>
                                            <tr><td>tel2</td><td>Non</td><td>Telephone secondaire.</td><td>Vide</td></tr>
                                            <tr><td>type_personnel</td><td>Non</td><td>enseignant, permanent ou vacataire.</td><td>enseignant</td></tr>
                                            <tr><td>mode_horaire</td><td>Non</td><td>strict ou souple.</td><td>strict</td></tr>
                                            <tr><td>categorie_horaire</td><td>Non</td><td>standard, conseil_administration, chef_service ou coordination.</td><td>standard</td></tr>
                                            <tr><td>horaire_travail</td><td>Non</td><td>permanent_jour, permanent_soir, enseignant_jour ou vacataire_soir.</td><td>Vide</td></tr>
                                            <tr><td>matieres</td><td>Non</td><td>Matieres enseignees ou affectations.</td><td>Vide</td></tr>
                                            <tr><td>autre_information</td><td>Non</td><td>Informations complementaires.</td><td>Vide</td></tr>
                                            <tr><td>num_contribuable</td><td>Non</td><td>Numero contribuable.</td><td>Vide</td></tr>
                                            <tr><td>num_ss</td><td>Non</td><td>Numero securite sociale.</td><td>Vide</td></tr>
                                            <tr><td>categorie</td><td>Non</td><td>Categorie administrative.</td><td>Vide</td></tr>
                                            <tr><td>echelon</td><td>Non</td><td>Echelon administratif.</td><td>Vide</td></tr>
                                            <tr><td>taux</td><td>Non</td><td>Taux horaire numerique, sans espace ni devise.</td><td>Aucun taux cree</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a class="btn btn-default" href="{{ route('personnel_import_template') }}">
                                        <span class="fa fa-download"></span> Telecharger le template
                                    </a>
                                    <input class="btn btn-success pull-right" type="submit" value="{{  trans('admin.confirmer')  }}">
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="add_cycle">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">x</button>
                            <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                        </div>
                        <div class="modal-body">


                        <form accept-charset="UTF-8" role="form" id="select_sexe" method="POST" action="{{ url('/personnel/store/') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <fieldset>

                    <div class="form-group">
                        <label for="nom">{{  trans('admin.nom')  }} : <span class="required">*</span></label>
                        <input class="form-control" id="nom" name="nom" type="text"  value="{{ old('nom') }}" required autofocus>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date">{{  trans('inscription.date_naiss')  }} : <span class="required">*</span></label>
                                <input class="form-control" id="nom" name="date_naiss" type="date"  value="{{ old('date_naiss') }}" placeholder="jj/mm/aaaa" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lieu_naiss">{{  trans('inscription.lieu_naiss')  }} : <span class="required">*</span></label>
                                <input class="form-control" id="nom" name="lieu_naiss" type="text"  value="{{ old('lieu_naiss') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nationalite">{{  trans('inscription.nationalite')  }} : <span class="required">*</span></label>
                                <input class="form-control" id="nationalite" name="nationalite" type="text"  value="{{ old('nationalite') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="sexe">{{  trans('inscription.sexe')  }} : <span class="required">*</span></label>
                                <select name="sexe" form="select_sexe" class="form-control">
                                    <option value="H">{{  trans('inscription.homme')  }}</option>
                                    <option value="F">{{  trans('inscription.femme')  }}</option>
                                </select>
                            </div>
                        </div>


                        <div class="row">

                            <div class="col-md-6">
                                <label for="diplome">{{  trans('admin.diplome')  }} : <span class="required">*</span></label>
                                <input class="form-control" id="diplome" name="diplome" type="text"  value="{{ old('diplome') }}" required >
                            </div>
                            <div class="col-md-6">
                                <label for="num_contribuable">{{  trans('admin.num_contribuable')  }} :</label>
                                <input class="form-control" id="num_contribuable" name="num_contribuable" type="text"  value="{{ old('num_contribuable') }}"  >

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="num_ss">{{  trans('admin.num_ss')  }} :</label>
                                <input class="form-control" id="num_ss" name="num_ss" type="text"  value="{{ old('num_ss') }}" >
                            </div>
                            <div class="col-md-6">
                                <label for="categorie">{{  trans('admin.categorie')  }} :</label>
                                <input class="form-control" id="categorie" name="categorie" type="text"  value="{{ old('categorie') }}" >
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="echelon">{{  trans('admin.echelon')  }} :</label>
                                <input class="form-control" id="echelon" name="echelon" type="text"  value="{{ old('echelon') }}" >
                            </div>
                            <div class="col-md-6">
                                <label for="date_rec">{{  trans('admin.date_rec')  }} :</label>
                                <input class="form-control" id="date_rec" name="date_rec" type="date"  value="{{ old('date_rec') }}" >
                            </div>
                        </div>
                        <label >Type :</label>
                        <select name="type" form="select_sexe" class="form-control">
                            <option value="0">@lang('admin.enseignant')</option>
                            <option value="1">@lang('admin.autre')</option>
                        </select>

                        <div class="row">
                            <div class="col-md-3">
                                <label for="type_personnel">Type personnel :</label>
                                <select name="type_personnel" id="type_personnel" form="select_sexe" class="form-control">
                                    <option value="enseignant">Enseignant</option>
                                    <option value="permanent">Permanent</option>
                                    <option value="vacataire">Vacataire</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="mode_horaire">Mode horaire :</label>
                                <select name="mode_horaire" id="mode_horaire" form="select_sexe" class="form-control">
                                    <option value="strict">Strict</option>
                                    <option value="souple">Souple</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="categorie_horaire">Categorie horaire :</label>
                                <select name="categorie_horaire" id="categorie_horaire" form="select_sexe" class="form-control">
                                    <option value="standard">Standard</option>
                                    <option value="conseil_administration">Conseil administration</option>
                                    <option value="chef_service">Chef service</option>
                                    <option value="coordination">Coordination</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="horaire_travail">Horaire travail :</label>
                                <select name="horaire_travail" id="horaire_travail" form="select_sexe" class="form-control">
                                    <option value="">--</option>
                                    <option value="permanent_jour">Permanent jour</option>
                                    <option value="permanent_soir">Permanent soir</option>
                                    <option value="enseignant_jour">Enseignant jour</option>
                                    <option value="vacataire_soir">Vacataire soir</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="adresse">{{  trans('admin.adresse')  }} : <span class="required">*</span></label>
                                <input class="form-control" id="adresse" name="adresse" type="text"  value="{{ old('adresse') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email">{{  trans('admin.email')  }} :</label>
                                <input class="form-control" id="email" name="email" type="text"  value="{{ old('email') }}" >
                            </div>
                        </div>







                        <div class="row">
                            <div class="col-md-6">
                                <label for="tel1">{{  trans('admin.tel1')  }} : <span class="required">*</span></label>
                                <input class="form-control" id="tel1" name="tel1" type="text"  value="{{ old('tel1') }}" required >
                            </div>

                            <div class="col-md-6">
                                <label for="tel2">{{  trans('admin.tel2')  }} :</label>
                                <input class="form-control" id="nom" name="tel2" type="text"  value="{{ old('tel2') }}">
                            </div>
                        </div>






                        <label for="matieres">{{  trans('admin.titre_matiere')  }} :</label>
                        <textarea  class="form-control" id="matieres" name="matieres">{{ old('matieres') }}</textarea>

                        <label for="autre_information">{{  trans('admin.autre')  }} informations:</label>
                        <textarea  class="form-control" id="autre_information" name="autre_information">{{ old('autre_information') }}</textarea>
                        <hr>

                        <div class="form-group">
                                <label >{{  trans('admin.taux_horaire')  }} :</label>
                                <input class="form-control" id="taux" name="taux" type="number"   value="{{ old('taux') }}">
                        </div>
                    </div>
                    <input class="btn  btn-success pull-right" type="submit" value="{{  trans('admin.confirmer')  }}">


                </fieldset>
            </form>

    </div>
</div>
</div>
    </div>
    </div>

    <script>
        $('#table-personnel').dataTable( {
            dom: 'Bfrtip',
            pageLength:50,

            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: 'Liste personnel actif',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'PDF',
                    title: 'Liste personnel actif',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> @lang("inscription.imprimer")',
                    titleAttr: 'Imprimer',
                    title: 'Liste personnel actif',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ]
        } );

        $('#table-personnel2').dataTable( {
            dom: 'Bfrtip',
            pageLength:50,

            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    titleAttr: 'Export to Excel',
                    title: 'Liste personnel inactif',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    titleAttr: 'PDF',
                    title: 'Liste personnel inactif',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
                ,
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> @lang("inscription.imprimer")',
                    titleAttr: 'Imprimer',
                    title: 'Liste personnel inactif',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ]
        } );


        $(function () {
            //$('#date_debut').datepicker({ dateFormat: 'dd-mm-yy' });
            //$('#date_fin').datepicker({ dateFormat: 'dd-mm-yy' });
        });


    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('personnel')}}">@lang('menu.gestion_personnel')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
