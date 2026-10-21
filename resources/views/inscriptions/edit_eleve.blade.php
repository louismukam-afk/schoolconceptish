@extends('skeleton')
@section('sub-content')

    <div class="col-md-6">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif


            <style>
                .icon-choose-image
                {
                    width: 150px;
                    height: 150px;
                    border: solid 1px;
                    border-radius: 3px;
                }
            </style>

        <form accept-charset="UTF-8" role="form" method="POST" id="enregistrer_eleve_form" action="{{URL::to('/inscriptions/enregistrement/update/'.$eleve->id)}}" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

            <fieldset>
                <legend style="margin-top:20px;margin-bottom:30px;"><strong> {{trans('inscription.eleve')}}</strong></legend>

                <div id="targetOuter">
                    <div id="targetLayer">
                        @if(isset($photoUrl))
                            <img src="{{URL::asset($photoUrl)}}"  class="icon-choose-image" />
                        @else
                            <img src="{{URL::asset('img/avatar.png')}}"  class="icon-choose-image" />
                        @endif
                    </div>
                    <div  >
                        <input name="photo" id="userImage" disabled type="file" class=" form-control" onChange="showPreview(this);" />
                    </div>
                </div>

                <div class="form-group">
                    <label for="nom">{{  trans('inscription.nom')  }} : <span class="required">*</span></label>
                    <input disabled class="form-control" id="nom" name="nom" type="text"  value="{{ $eleve->nom}}"  autofocus>

                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_naiss">{{  trans('inscription.date_naiss')  }} : <span class="required">*</span></label>
                            <input  disabled class="form-control" id="date_naiss" name="date_naiss" type="date"  value="{{ $eleve->date_naiss }}"  placeholder="yyyy/mm/dd">

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="lieu_naiss">{{  trans('inscription.lieu_naiss')  }} : <span class="required">*</span></label>

                            <input disabled class="form-control" id="lieu_naiss" name="lieu_naiss" type="text"  value="{{ $eleve->lieu_naiss }}"  >

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ne_vers">Né vers :</label>
                            <input  disabled  class="form-control" id="ne_vers" name="ne_vers" type="text"  value="{{ $eleve->ne_vers }}">

                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="sexe">{{  trans('inscription.sexe')  }} : </label>
                            <select disabled form="enregistrer_eleve_form" id="sexe" name="sexe" class="form-control">
                                <option value="M" @if(strcmp($eleve->sexe,'M')==0) selected @endif>{{trans('inscription.homme') }}</option>
                                <option value="F" @if(strcmp($eleve->sexe,'F')==0) selected @endif>{{trans('inscription.femme')}}</option>
                            </select>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nationalite">{{  trans('inscription.nationalite')  }} : <span class="required">*</span></label>
                            <input disabled class="form-control" id="nationalite" name="nationalite" type="text"  value="{{ $eleve->nationalite }}"  >

                        </div>
                    </div>
                    <div class="col-md-4">
                    <div class="form-group">
                    <label for="quartier">{{  trans('inscription.residence')  }} : <span class="required">*</span></label>
                    <input disabled class="form-control" id="quartier" name="quartier" type="text"  value="{{ $eleve->quartier }}"  required>
                    </div>
                </div>

                </div>
              

                <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="email">{{  trans('inscription.email')  }} : </label>
                        <input disabled class="form-control" id="email" name="email" type="text"  value="{{  $eleve->email }}"  >

                    </div>
                    </div>

                    <div class="col-md-4">
                            <div class="form-group">
                                <label for="telephone_wathsapp">{{  trans('inscription.telephone_wathsapp')  }} : </label>
                                <input disabled class="form-control" id="telephone_wathsapp_eleve" name="telephone_wathsapp_eleve" type="text"  value="{{ $eleve->telephone_wathsapp_eleve }}"  >
                            </div>
                        </div>
                        <div class="col-md-4">
                    <div class="form-group">
                        <label for="tel">{{  trans('inscription.tel')  }} : </label>
                        <input disabled class="form-control" id="tel" name="tel" type="text"  value="{{  $eleve->tel }}"  >

                    </div>
                </div>
                </div>

               

                <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="status_matrimonial">{{  trans('inscription.status_matrimonial')  }} : </label>
                        <input disabled class="form-control" id="status_matrimonial" name="status_matrimonial" type="text"  value="{{  $eleve->status_matrimonial }}"  >

                    </div>
                   </div>


                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="region_origine">{{  trans('inscription.region_origine')  }} : </label>
                            <input disabled class="form-control" id="region_origine" name="region_origine" type="text"  value="{{  $eleve->region_origine }}"  >

                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="departement_origine">{{  trans('inscription.departement_origine')  }} : </label>
                            <input disabled class="form-control" id="departement_origine" name="departement_origine" type="text"  value="{{  $eleve->departement_origine }}"  >

                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                        <label for="diplome_preinscription">{{  trans('inscription.diplome_preinscription')  }} : </label>
                        <input disabled class="form-control" id="diplome_preinscription" name="diplome_preinscription" type="text"  value="{{  $eleve->diplome_preinscription }}"  >

                    </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="serie">{{  trans('inscription.serie')  }} : </label>
                                <input  disabled class="form-control" id="serie" name="serie" type="text"  value="{{ $eleve->serie }}"  >
                            </div>
                         </div>
                     <div class="col-md-4">
                         <div class="form-group">
                                <label for="moyenne_generale">{{  trans('inscription.moyenne_generale')  }} : </label>
                                <input disabled class="form-control" step="0.0005" id="moyenne_generale" name="moyenne_generale" type="numeric"  value="{{ $eleve->moyenne_generale }}"  >
                            </div>
                         </div>

                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="annee_obtention">{{  trans('inscription.annee_obtention')  }} : </label>
                            <input disabled class="form-control" id="annee_obtention" name="annee_obtention" type="text"  value="{{  $eleve->annee_obtention }}"  >

                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="pays_obtention">{{  trans('inscription.pays_obtention')  }} : </label>
                            <input disabled class="form-control" id="pays_obtention" name="pays_obtention" type="text"  value="{{  $eleve->pays_obtention }}"  >

                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                                <label for="localisation">{{  trans('inscription.localisation')  }} : </label>
                                <input disabled class="form-control" id="localisation_ecole" name="localisation_ecole" type="text"  value="{{ $eleve->localisation_ecole }}"  >
                        </div>
                    </div>
                   
                </div>
              

                <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="dernier_etablissement">{{  trans('inscription.dernier_etablissement')  }} : </label>
                        <input disabled class="form-control" id="dernier_etablissement" name="dernier_etablissement" type="text"  value="{{  $eleve->dernier_etablissement }}"  >

                    </div>

                </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="annee_sortie">{{  trans('inscription.annee_sortie')  }} : </label>
                            <input disabled class="form-control" id="annee_sortie" name="annee_sortie" type="text"  value="{{  $eleve->annee_sortie }}"  >

                        </div>

                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="diplome_obtenu">{{  trans('inscription.diplome_obtenu')  }} : </label>
                            <input disabled class="form-control" id="diplome_obtenu" name="diplome_obtenu" type="text"  value="{{  $eleve->diplome_obtenu }}"  >

                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="premiere_langue">{{  trans('inscription.premiere_langue')  }} : </label>
                            <input disabled class="form-control" id="premiere_langue" name="premiere_langue" type="text"  value="{{  $eleve->premiere_langue }}"  >

                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="deuxieme_langue">{{  trans('inscription.deuxieme_langue')  }} : </label>
                            <input disabled class="form-control" id="deuxieme_langue" name="deuxieme_langue" type="text"  value="{{  $eleve->deuxieme_langue }}"  >

                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="autre_langue">{{  trans('inscription.autre_langue')  }} : </label>
                            <input disabled class="form-control" id="autre_langue" name="autre_langue" type="text"  value="{{  $eleve->autre_langue }}"  >

                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telephone_wathsapp_pere">Téléphone wathsapp du père : </label>
                            <input disabled class="form-control" id="telephone_wathsapp_pere" name="telephone_wathsapp_pere" type="text"  value="{{  $eleve->telephone_wathsapp_pere }}"  >

                        </div>
                     </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telephone_wathsapp_mere">Téléphone wathsapp de la mère : </label>
                            <input disabled class="form-control" id="telephone_wathsapp_mere" name="telephone_wathsapp_mere" type="text"  value="{{  $eleve->telephone_wathsapp_mere }}"  >

                        </div>

                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="telephone_wathsapp_tuteur">Téléphone wathsapp du tuteur : </label>
                            <input disabled class="form-control" id="telephone_wathsapp_tuteur" name="telephone_wathsapp_tuteur" type="text"  value="{{  $eleve->telephone_wathsapp_tuteur }}"  >

                        </div>

                    </div>
                </div>
                <div >
                    <div class="form-group">
                        <label for="profession">{{  trans('inscription.profession')  }} : </label>
                        <input disabled class="form-control" id="profession" name="profession" type="text"  value="{{  $eleve->profession }}"  >

                    </div>

                </div>

                <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                    <label for="source_financement">Sources potentielles de financement pour les études : </label>
                                    <select disabled form="enregistrer_eleve_form" id="source_financement" name="source_financement" class="form-control">
                                        <option value="bourse" @if(strcmp($eleve->source_financement,'bourse')==0) selected @endif>Bourses</option>
                                        <option value="pret" @if(strcmp($eleve->source_financement,'pret')==0) selected @endif>Prêt</option>
                                        <option value="soutient_familial" @if(strcmp($eleve->source_financement,'soutient_familial')==0) selected @endif>Soutient familliale</option>
                                        <option value="autre" @if(strcmp($eleve->source_financement,'autre')==0) selected @endif>Autres</option>
                                    </select>
                                </div>
                                 </div>

                                <div class="col-md-4">
                                <div class="form-group">
                                    <label for="canal_decouvert">Par quel canal avez-vous connu le Centre de Formation Hintel </label>
                                    <select  disabled form="enregistrer_eleve_form" id="canal_decouvert" name="canal_decouvert" class="form-control">
                                        <option value="depliant" @if(strcmp($eleve->canal_decouvert,'depliant')==0) selected @endif>Dépliant reçus en chemin</option>
                                        <option value="concour" @if(strcmp($eleve->canal_decouvert,'concour')==0) selected @endif>Après un concours</option>
                                        <option value="college" @if(strcmp($eleve->canal_decouvert,'college')==0) selected @endif>Au collège ou lycée</option>
                                        <option value="google" @if(strcmp($eleve->canal_decouvert,'google')==0) selected @endif>Google</option>
                                        <option value="connaissance" @if(strcmp($eleve->canal_decouvert,'connaissance')==0) selected @endif>Connaissance</option>
                                        <option value="facebook" @if(strcmp($eleve->canal_decouvert,'facebook')==0) selected @endif>Facebook</option>
                                        <option value="plaque" @if(strcmp($eleve->canal_decouvert,'plaque')==0) selected @endif>Plaques publicitaires</option>
                                        <option value="tele" @if(strcmp($eleve->canal_decouvert,'tele')==0) selected @endif>Télé (chaines)</option>
                                        <option value="radio" @if(strcmp($eleve->canal_decouvert,'radio')==0) selected @endif>Radio</option>
                                        <option value="pannaux" @if(strcmp($eleve->canal_decouvert,'pannaux')==0) selected @endif>Panneaux vidéo rond-point longkak</option>
                                        <option value="conseille" @if(strcmp($eleve->canal_decouvert,'conseille')==0) selected @endif>Par le suivi d'un conseillé de l'école</option>
                        
                                    </select>
                                    </div>
                                     </div>
                                    <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="numero_conseille"> Entrez le numéro de téléphone du conseillé d'orientation qui vous a suivie</label>
                                        <input  disabled class="form-control" id="numero_conseille" name="numero_conseille" type="text"  value="{{ $eleve->numero_conseille }}"  >
                                     </div>
                                     </div>
                                
                                </div>
                                <div class="form-group">
                                
                                <label for="chaine"> Entrez le nom de la chaine de télévision par lequel vous avez connu le Centre de Formation Hintel</label>
                                <input disabled class="form-control" id="chaine" name="chaine" type="text"  value="{{ $eleve->chaine }}"  >
                               
                               </div>
                               <legend>Données comportementales </legend>
                               <div class="row">
                                <div class="col-md-4">
                                <div class="form-group">
                                <label for="site_internet">Avez-vous déjà visité le site de le Centre de Formation Hintel ? </label>
                                    <select disabled form="enregistrer_eleve_form" id="site_internet" name="site_internet" class="form-control">
                                        <option value="oui" @if(strcmp($eleve->site_internet,'oui')==0) selected @endif>OUI</option>
                                        <option value="non" @if(strcmp($eleve->site_internet,'non')==0) selected @endif>NON</option>
                                    </select>
                                 </div>
                                 </div>
                                 <div class="col-md-4">
                                 <div class="form-group">
                                <label for="abonnement">Etes-vous abonnés à la page facebook ou autre canal de communication ?</label>
                                    <select disabled form="enregistrer_eleve_form" id="abonnement" name="abonnement" class="form-control">
                                        <option value="oui" @if(strcmp($eleve->abonnement,'oui')==0) selected @endif>OUI</option>
                                        <option value="non" @if(strcmp($eleve->abonnement,'non')==0) selected @endif>NON</option>
                                    </select>
                                 </div>
                                </div>
                                 <div class="col-md-4">
                                 <div class="form-group">
                                        <label for="abonnement_valide"> Si oui laquelle ? </label>
                                        <input disabled class="form-control" id="abonnement_valide" name="abonnement_valide" type="text"  value="{{ $eleve->abonnement_valide }}"  >
                                     </div>
                                     </div>
                                 </div>

<!-- 
                <div class="col-md-5"> -->
                    <div class="form-group">
                        <label for="lien_tuteur">{{  trans('inscription.lien_tuteur')  }} : </label>
                        <input disabled class="form-control" id="lien_tuteur" name="lien_tuteur" type="text"  value="{{ $eleve->lien_tuteur }}"  >

                    </div>
                <!-- </div> -->


            </fieldset>
            <input  style="display: none;" id="submit"  class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">

        </form>

    </div>
    <div class="col-md-1">
        <div id="change_eleve_editable" class="btn btn-primary btn-xs"> <span class="glyphicon glyphicon-edit"></span> @lang('admin.modifier')</div>
    </div>
 <div class="parent col-md-offset-1 col-md-5">

    @if($inscription == null)
        <h3 class="alert alert-danger">
            @lang('inscription.pas_inscrit')
        </h3>
    @endif
    @if(count($inscriptions) > 0)
        <h3>@lang('admin.inscriptions')</h3>
        @foreach($inscriptions as $i)
            <ul style="list-style-type: circle">
                <li><h4><span class="text-primary"> {{$i->annee_scolaire}} </span> : {{ $i->classe->getNom() . ' - ' . $i->nniveau->nom}}</h4></li>
            </ul>
        @endforeach

    @endif


        <h3>{{trans('inscription.parent')}}</h3>
    @foreach($parents as $p)
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">
                    <h4>
                        @if($p->type==0)
                            {{trans('inscription.mere')}}
                        @elseif($p->type==1)
                            {{trans('inscription.pere')}}
                        @else
                            {{trans('inscription.tuteur')}}
                        @endif
                    </h4>
                </div>

            </div>
            <div class="panel-body" >
                <div>{{trans('admin.nom')}} : <strong>{{$p->nom}}</strong></div>
                <div>{{trans('inscription.quartier')}} : <strong>{{$p->quartier}}</strong></div>
                <div>{{trans('inscription.ville')}} : <strong>{{$p->ville}}</strong></div>
                <div>Tel1 : <strong>{{$p->tel1}}</strong></div>
                <div>Tel2 : <strong>{{$p->tel2}}</strong></div>
            </div>
        </div>



    @endforeach
</div>
  
    <script>
        $(function () {
            $('#change_eleve_editable').click(function () {
                $('#userImage').removeAttr('disabled');
                $('#nom').removeAttr('disabled');
                $('#date_naiss').removeAttr('disabled');
                $('#lieu_naiss').removeAttr('disabled');
                $('#ne_vers').removeAttr('disabled');
                $('#sexe').removeAttr('disabled');
                $('#nationalite').removeAttr('disabled');
                $('#quartier').removeAttr('disabled');
                $('#email').removeAttr('disabled');
                $('#tel').removeAttr('disabled');
                $('#adresse').removeAttr('disabled');
                $('#type').removeAttr('disabled');
                $('#matieres').removeAttr('disabled');
                $('#status_matrimonial').removeAttr('disabled');
                $('#region_origine').removeAttr('disabled');
                $('#departement_origine').removeAttr('disabled');
                $('#diplome_preinscription').removeAttr('disabled');
                $('#annee_obtention').removeAttr('disabled');
                $('#pays_obtention').removeAttr('disabled');
                $('#dernier_etablissement').removeAttr('disabled');
                $('#diplome_obtenu').removeAttr('disabled');
                $('#annee_sortie').removeAttr('disabled');
                $('#premiere_langue').removeAttr('disabled');
                $('#deuxieme_langue').removeAttr('disabled');
                $('#autre_langue').removeAttr('disabled');
                $('#profession').removeAttr('disabled');
                $('#lien_tuteur').removeAttr('disabled');
                $('#telephone_wathsapp_eleve').removeAttr('disabled');
                $('#moyenne_generale').removeAttr('disabled');
                $('#serie').removeAttr('disabled');
                $('#localisation_ecole').removeAttr('disabled');
                $('#source_financement').removeAttr('disabled');
                $('#canal_decouvert').removeAttr('disabled');
                $('#site_internet').removeAttr('disabled');
                $('#abonnement').removeAttr('disabled');
                $('#telephone_wathsapp_pere').removeAttr('disabled');
                $('#telephone_wathsapp_mere').removeAttr('disabled');
                $('#telephone_wathsapp_tuteur').removeAttr('disabled');
                $('#abonnement_valide').removeAttr('disabled');
                $('#chaine').removeAttr('disabled');
                $('#numero_conseille').removeAttr('disabled');
                $('#submit').css('display','block');

            });
        });

        function showPreview(objFileInput) {
            if (objFileInput.files[0]) {
                var fileReader = new FileReader();
                fileReader.onload = function (e) {
                    $('#blah').attr('src', e.target.result);
                    $("#targetLayer").html('<img src="'+e.target.result+'" width="200px" height="200px" class="icon-choose-image" />');
                    $("#targetLayer").css('opacity','0.8');
                };
                startLoading();
                fileReader.readAsDataURL(objFileInput.files[0]);
                stopLoading();
            }
        }
    </script>

@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li><strong><a href="{{url('inscriptions/find ')}}">@lang('inscription.title_search')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection