@extends('skeleton')
@section('sub-content')
    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ url('/eleves/releves_ndere/historique') }}" class="btn btn-warning">
            <span class="glyphicon glyphicon-folder-open"></span> Historique des relevés Ngaoundéré
        </a>
        <a href="javascript:history.back();" class="btn btn-info">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
    </div>
    <h4>{{trans('eleve.choix_classe')}}</h4>
    <hr>
    @include('eleves.partials.cycle_niveau_filiere_filter')
    <div class="liste-classe col-md-12">
        <ul>


            @foreach($cycles as $cy)
                <h3 class="page-header col-md-12">{{$cy->nom}}</h3>

                @foreach($niveaux as $n)
                    <div class="col-md-6 niveau-block" data-cycle="{{$cy->id}}" data-filiere="{{$n->id}}" style="display:none;">
                        <h4 class="page-header"> {{(App::getLocale()=='en')?$n->nom_anglais:$n->nom}}</h4>

                        @foreach($classes as $c)
                            <div class="col-md-12">
                                @if($c->id_niveau==$n->id)
                                    <li class="col-md-12">
                                        <a data-toggle="modal" data-backdrop="false" href="#choose_level" onclick="getNniveau({{$cy->id}});choix_lvl({{$c->id}},{{$cy->id}})">{{(App::getLocale()=='en')?$c->nom_anglais:$c->nom}}</a>
                                        <a href="#generate_releves_ndere" data-toggle="modal" data-backdrop="false" class="btn btn-xs btn-success" data-classe="{{$c->id}}" data-cycle="{{$cy->id}}" data-niveau="{{$n->id}}" onclick="prepareNdereGeneration(this);">
                                            Générer et sauvegarder
                                        </a>
                                    </li>
                                @endif
                            </div>

                        @endforeach
                    </div>
                @endforeach

            @endforeach


        </ul>
    </div>

    <div class="modal fade" id="generate_releves_ndere">
        <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header"><button type="button" class="close" data-dismiss="modal">x</button><h4 class="modal-title">Générer les relevés Ngaoundéré</h4></div>
            <div class="modal-body">
                <form method="POST" id="generate_releves_ndere_form" action="{{URL::to('/eleves/releves_ndere/generate/')}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" id="ndere_generation_classe">
                    <input type="hidden" name="ajax" value="1">
                    <p><strong>Spécialité :</strong> <span id="ndere_specialite"></span></p>
                    <div class="form-group"><label>Cycle</label><select name="cycle" id="ndere_generation_cycle" class="form-control" required><option value="">-- Sélectionner --</option>@foreach($cycles as $cycle)<option value="{{$cycle->id}}">{{$cycle->nom}}</option>@endforeach</select></div>
                    <div class="form-group"><label>Niveau</label><select name="nniveau" id="ndere_generation_nniveau" class="form-control" required disabled><option value="">-- Sélectionner un cycle d'abord --</option>@foreach($nniveaux as $nn)<option value="{{$nn->id}}" data-cycle="{{$nn->id_cycle}}">{{$nn->nom}}</option>@endforeach</select></div>
                    <div class="form-group"><label for="ndere_generation_semestre">Semestre</label><select name="trimestre" id="ndere_generation_semestre" class="form-control" required></select></div>
                    <div class="form-group"><label>Début de période</label><input type="date" name="periode_debut" class="form-control"></div>
                    <div class="form-group"><label>Fin de période</label><input type="date" name="periode_fin" class="form-control"></div>
                    <button type="submit" class="btn btn-success" id="ndere_generation_btn">Générer</button>
                    <span id="ndere_generation_status" class="text-muted"></span>
                </form>
                <div id="ndere_generation_message" class="alert" style="display:none;margin-top:10px;"></div>
            </div>
        </div></div>
    </div>


    <div class="modal fade" id="choose_level">
        <div class="modal-dialog" style="width: 20%">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">x</button>
                    <h4 class="modal-title">{{  trans('admin.remplir_champs')  }}</h4>
                </div>
                <div
                        class="modal-body">



                    <form accept-charset="UTF-8" role="form" method="GET" id="choix_level_form" action="{{URL::to('/eleves/releves_ndere/print/')}}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                        <input type="hidden" name="id" id="id" value=""/>
                        <input type="hidden" name="cycle" id="id_cycle" value=""/>
                        <div class="form-group">
                            <label for="trimestre">Semestre</label>
                            <select name="trimestre" id="trimestre" class="form-control" required>
                                <option value="1">Semestre 1 (séquence 5)</option>
                                <option value="2">Semestre 2 (séquence 6)</option>
                            </select>
                        </div>

                        <fieldset>
                            <div class="form-group">
                                <label for="niveau">{{  trans('admin.nniveau')  }} : </label>
                                <select  name="nniveau" id="nniveau" form="choix_level_form" class="form-control" required>
                                    @foreach($nniveaux as $nn)
                                        <option value="{{$nn->id}}">{{$nn->nom}}</option>
                                    @endforeach
                                </select>
                            </div>


                            <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}" autofocus>


                        </fieldset>
                    </form>


                </div>





            </div>


        </div>
    </div>

    <script>

        function choix_lvl(id,id_cycle)
        {
            document.getElementById("id").value = id;
            document.getElementById("id_cycle").value = id_cycle;
        }
        function prepareNdereGeneration(link) {
            document.getElementById('ndere_generation_classe').value = link.getAttribute('data-classe');
            document.getElementById('ndere_specialite').innerHTML = link.innerHTML.replace(/<[^>]+>/g, '');
            var cycle = document.getElementById('ndere_generation_cycle');
            cycle.value = link.getAttribute('data-cycle');
            cycle.onchange();
        }
        document.getElementById('ndere_generation_cycle').onchange = function () {
            var cycleId = this.value, niveau = document.getElementById('ndere_generation_nniveau');
            niveau.value = ''; niveau.disabled = !cycleId;
            for (var i = 0; i < niveau.options.length; i++) {
                var option = niveau.options[i];
                option.hidden = option.value && option.getAttribute('data-cycle') !== cycleId;
            }
        };
        document.getElementById('ndere_generation_nniveau').onchange = function () {
            var level = (this.options[this.selectedIndex].text.match(/[1-5]/) || [1])[0];
            var pairs = {1:[1,2], 2:[3,4], 3:[5,6], 4:[7,8], 5:[8,9]};
            var semesters = pairs[level] || pairs[1], select = document.getElementById('ndere_generation_semestre');
            select.innerHTML = '';
            for (var i = 0; i < semesters.length; i++) {
                var option = document.createElement('option');
                option.value = semesters[i];
                option.text = 'Semestre ' + semesters[i];
                select.appendChild(option);
            }
        };
        document.getElementById('generate_releves_ndere_form').onsubmit = function (event) {
            event.preventDefault();
            var form = this, button = document.getElementById('ndere_generation_btn'), status = document.getElementById('ndere_generation_status');
            button.disabled = true; status.innerHTML = ' Génération en cours...';
            var xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.onreadystatechange = function () {
                if (xhr.readyState !== 4) return;
                button.disabled = false;
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300 && data.success) window.location.href = data.url;
                    else status.innerHTML = ' Erreur : ' + (data.message || 'génération impossible');
                } catch (e) {
                    status.innerHTML = ' Réponse serveur invalide. ' + (xhr.status ? 'HTTP ' + xhr.status : '');
                }
            };
            xhr.send(new FormData(form));
        };
    </script>
@endsection
