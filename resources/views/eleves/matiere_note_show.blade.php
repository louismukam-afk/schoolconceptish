@extends('skeleton')

@section('sub-content')
    <div class="col-md-12">
        {{-- Messages flash --}}
        @if(isset($success))
            <div class="alert alert-success">
                {{ trans('admin.succes') }}
            </div>
        @endif

        @if(isset($erreur))
            <div class="alert alert-danger">
                {{ $erreur }}
            </div>
        @endif

<?php
    $pvTutellesLicence = array(
        'licence_soa' => 'SOA',
        'licence_bamenda' => 'Bamenda',
        'licence_ebolowa' => 'Ebolowa / ESTIC',
        'licence_ngaoundere' => 'Ngaoundere'
    );
    $pvResultSequences = array(
        3 => 'S1',
        4 => 'S2',
        9 => 'annuelle'
    );
    $pvResultLists = array(
        'admis' => 'Liste admis - ordre alphabetique',
        'merite' => 'Liste admis - ordre de merite',
        'non_admis' => 'Liste echouer / non admis'
    );
    $pvRattrapageVariants = array(
        '' => 'concernes',
        '&only_done=1' => 'effectue',
        '&global=1' => 'global'
    );
    $soaPrintPath = URL::to('/eleves/releves_soa/print');
    $esticPrintPath = URL::to('/eleves/releve_estic/print');
    $bamendaPrintPath = URL::to('/eleves/releves_bamenda/print');
?>

{{-- Proces-verbaux tutelles Licence --}}
<div class="panel panel-primary">
    <div class="panel-heading">
        <strong>Proces-verbaux tutelles</strong>
    </div>
    <div class="panel-body">
        <div class="row">
            @foreach($pvTutellesLicence as $pvTypeLicence => $pvLibelleTutelle)
                <div class="col-md-4">
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <strong>{{ $pvLibelleTutelle }}</strong>
                        </div>
                        <div class="panel-body">
                            <strong>PV / releves</strong>
                            <a class="btn btn-success btn-block"
                               href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=3&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&pv_type='.$pvTypeLicence) }}">
                                <span class="glyphicon glyphicon-list-alt"></span> Semestre 1
                            </a>
                            <a class="btn btn-success btn-block"
                               href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=4&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&pv_type='.$pvTypeLicence) }}">
                                <span class="glyphicon glyphicon-list-alt"></span> Semestre 2
                            </a>
                            <a class="btn btn-info btn-block"
                               href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=9&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&pv_type='.$pvTypeLicence) }}">
                                <span class="glyphicon glyphicon-stats"></span> Bilan annuel
                            </a>
                            <hr>
                            <strong>Listes des resultats</strong>
                            @foreach($pvResultLists as $listeType => $listeLibelle)
                                @foreach($pvResultSequences as $sequencePv => $sequenceLibelle)
                                    <a class="btn btn-default btn-block"
                                       href="{{ url('/eleves/notes/print_note/'.$id.'?sequence='.$sequencePv.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&pv_type='.$pvTypeLicence.'&liste='.$listeType) }}">
                                        <span class="glyphicon glyphicon-list"></span> {{ $listeLibelle }} {{ $sequenceLibelle }}
                                    </a>
                                @endforeach
                            @endforeach
                            <hr>
                            <strong>Listes de rattrapage</strong>
                            @foreach(array(1 => 'S1', 2 => 'S2') as $semestreRattrapage => $semestreLibelle)
                                @foreach($pvRattrapageVariants as $variantQuery => $variantLibelle)
                                    <a class="btn btn-warning btn-block"
                                       href="{{ url('/eleves/notes/pv/rattrapages/'.$id.'?semestre='.$semestreRattrapage.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&pv_type='.$pvTypeLicence.'&page_size=a4'.$variantQuery) }}">
                                        <span class="glyphicon glyphicon-refresh"></span> Rattrapage {{ $variantLibelle }} {{ $semestreLibelle }}
                                    </a>
                                @endforeach
                            @endforeach
                            <hr>
                            <strong>Releves de notes</strong>
                            @if($pvTypeLicence == 'licence_soa')
                                <a class="btn btn-success btn-block"
                                   href="{{ url('/eleves/attestations_reussite_soa/') }}">
                                    <span class="glyphicon glyphicon-certificate"></span> Attestations de reussite SOA
                                </a>
                                <a class="btn btn-primary btn-block"
                                   href="{{ $soaPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a4' }}"
                                   onclick="return generateSOAReleves(this, event);">
                                    <span class="glyphicon glyphicon-file"></span> Relevés SOA A4 portrait
                                </a>
                                <a class="btn btn-info btn-block"
                                   href="{{ $soaPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a3' }}"
                                   onclick="return generateSOAReleves(this, event);">
                                    <span class="glyphicon glyphicon-file"></span> Relevés SOA A3 portrait
                                </a>
                            @elseif($pvTypeLicence == 'licence_ebolowa')
                                <a class="btn btn-success btn-block"
                                   href="{{ url('/eleves/attestations_reussite_ebolowa/') }}">
                                    <span class="glyphicon glyphicon-certificate"></span> Attestations de reussite Ebolowa
                                </a>
                                <a class="btn btn-primary btn-block"
                                   href="{{ $esticPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a4&page_orientation=portrait' }}">
                                    <span class="glyphicon glyphicon-file"></span> Releves Ebolowa A4 portrait
                                </a>
                                <a class="btn btn-info btn-block"
                                   href="{{ $esticPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a3&page_orientation=portrait' }}">
                                    <span class="glyphicon glyphicon-file"></span> Releves Ebolowa A3 portrait
                                </a>
                            @elseif($pvTypeLicence == 'licence_bamenda')
                                <a class="btn btn-primary btn-block"
                                   href="{{ $bamendaPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a4&page_orientation=portrait' }}">
                                    <span class="glyphicon glyphicon-file"></span> Bamenda transcripts A4 portrait
                                </a>
                                <a class="btn btn-info btn-block"
                                   href="{{ $bamendaPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a3&page_orientation=portrait' }}">
                                    <span class="glyphicon glyphicon-file"></span> Bamenda transcripts A3 portrait
                                </a>
                            @elseif($pvTypeLicence == 'licence_ngaoundere')
                                <a class="btn btn-primary btn-block"
                                   href="{{ url('/eleves/releves_ndere/') }}">
                                    <span class="glyphicon glyphicon-file"></span> Relevés de notes Ngaoundere
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    function generateSOAReleves(link, event) {
        event.preventDefault();
        var button = link;
        var originalText = button.innerHTML;
        button.classList.add('disabled');
        button.innerHTML = '<span class="glyphicon glyphicon-refresh"></span> Génération en cours...';

        fetch(link.href, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Erreur HTTP ' + response.status);
                }
                return response.text();
            })
            .then(function (html) {
                var output = window.open('', '_blank');
                if (!output) {
                    throw new Error('La fenêtre de résultat a été bloquée par le navigateur.');
                }
                output.document.open();
                output.document.write(html);
                output.document.close();
            })
            .catch(function (error) {
                window.alert('La génération des relevés a échoué : ' + error.message);
            })
            .then(function () {
                button.classList.remove('disabled');
                button.innerHTML = originalText;
            });
        return false;
    }
</script>

{{-- Options de generation des PV --}}
<div class="panel panel-default">
    <div class="panel-heading">
        <strong>Generation des proces-verbaux</strong>
    </div>
    <div class="panel-body">
        <div class="row">
            <form method="GET" action="{{ url('/eleves/notes/print_note/'.$id) }}" class="col-md-6">
                <input type="hidden" name="cycle" value="{{ $id_cycle }}">
                <input type="hidden" name="nniveau" value="{{ $id_nniveau }}">

                <h4>Visualiser / generer un PV</h4>
                <div class="form-group">
                    <label>Semestre</label>
                    <select name="sequence" class="form-control">
                        <option value="3">Semestre 1</option>
                        <option value="4">Semestre 2</option>
                        <option value="9">Bilan annuel</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Format</label>
                    <select name="pv_type" class="form-control">
                        <option value="bts">BTS / Francophone</option>
                        <option value="hnd">HND / Anglophone</option>
                        <option value="licence_soa">Licence / SOA</option>
                        <option value="licence_bamenda">Licence / Bamenda</option>
                        <option value="licence_ebolowa">Licence / Ebolowa</option>
                        <option value="licence_ngaoundere">Licence / Ngaoundere</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Date debut</label>
                        <input type="date" name="date_debut" class="form-control">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-success btn-block">
                    <span class="glyphicon glyphicon-list-alt"></span> Generer le PV
                </button>
            </form>

            <form method="GET" action="{{ url('/eleves/notes/pv/rattrapages/'.$id) }}" class="col-md-6">
                <input type="hidden" name="cycle" value="{{ $id_cycle }}">
                <input type="hidden" name="nniveau" value="{{ $id_nniveau }}">

                <h4>PV rattrapages</h4>
                <div class="form-group">
                    <label>Semestre</label>
                    <select name="semestre" class="form-control">
                        <option value="1">Semestre 1</option>
                        <option value="2">Semestre 2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Format</label>
                    <select name="pv_type" class="form-control">
                        <option value="bts">BTS / Francophone</option>
                        <option value="hnd">HND / Anglophone</option>
                        <option value="licence_soa">Licence / SOA</option>
                        <option value="licence_bamenda">Licence / Bamenda</option>
                        <option value="licence_ebolowa">Licence / Ebolowa</option>
                        <option value="licence_ngaoundere">Licence / Ngaoundere</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-sm-6 form-group">
                        <label>Date debut</label>
                        <input type="date" name="date_debut" class="form-control">
                    </div>
                    <div class="col-sm-6 form-group">
                        <label>Date fin</label>
                        <input type="date" name="date_fin" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Format papier</label>
                    <select name="page_size" class="form-control">
                        <option value="a4">A4 portrait - un etudiant par page</option>
                        <option value="a3">A3 paysage - un etudiant par page</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="glyphicon glyphicon-file"></span> Liste PDF des rattrapages
                </button>
            </form>
        </div>
    </div>
</div>

<div class="panel panel-info">
    <div class="panel-heading"><strong>Deliberation par etudiant</strong></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-sm-3"><a class="btn btn-info btn-block" href="{{ url('/eleves/notes/deliberations/'.$id.'?semestre=1&mode=semestre&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">Semestre 1</a></div>
            <div class="col-sm-3"><a class="btn btn-info btn-block" href="{{ url('/eleves/notes/deliberations/'.$id.'?semestre=2&mode=semestre&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">Semestre 2</a></div>
            <div class="col-sm-3"><a class="btn btn-warning btn-block" href="{{ url('/eleves/notes/deliberations/'.$id.'?semestre=1&mode=rattrapage&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">Rattrapage 1</a></div>
            <div class="col-sm-3"><a class="btn btn-warning btn-block" href="{{ url('/eleves/notes/deliberations/'.$id.'?semestre=2&mode=rattrapage&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">Rattrapage 2</a></div>
        </div>
    </div>
</div>
        {{-- Contexte général --}}
        <p>
            <strong>Année académique :</strong>
            {{ isset($annee) ? $annee : session('year') }}
        </p>

        {{-- Liens de PV globaux (classe entière) --}}
        <ul>
            <li>
                <a href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=1&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">
                    <h4>Procès-verbaux des notes de contrôle continu semestre 1</h4>
                </a>
            </li>
            <li>
                <a href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=2&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">
                    <h4>Procès-verbaux des notes de contrôle continu semestre 2</h4>
                </a>
            </li>
            <li>
                <a href="{{ url('/eleves/notes/pv/rattrapages/'.$id.'?semestre=1&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a4') }}">
                    <h4>Procès-verbaux des notes de rattrapages semestre 1</h4>
                </a>
            </li>
            <li>
                <a href="{{ url('/eleves/notes/pv/rattrapages/'.$id.'?semestre=2&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a4') }}">
                    <h4>Procès-verbaux des notes de rattrapages semestre 2</h4>
                </a>
            </li>
            <li>
                <a href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=3&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">
                    <h4>{{ trans('admin.PV').' '.trans('admin.semestre').' 1' }}</h4>
                </a>
            </li>
            <li>
                <a href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=4&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">
                    <h4>{{ trans('admin.PV').' '.trans('admin.semestre').' 2' }}</h4>
                </a>
            </li>
            <li>
                <a href="{{ url('/eleves/notes/print_note/'.$id.'?sequence=9&cycle='.$id_cycle.'&nniveau='.$id_nniveau) }}">
                    <h4>@lang('admin.bilan_annuel')</h4>
                </a>
            </li>

            {{-- Délibérations des notes --}}
            <li style="list-style: none; margin: 15px 0;">
                <form method="GET" action="{{ url('/eleves/notes/deliberations/'.$id) }}" class="well well-sm">
                    <input type="hidden" name="cycle" value="{{ $id_cycle }}">
                    <input type="hidden" name="nniveau" value="{{ $id_nniveau }}">

                    <h4>Délibérer les notes</h4>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Type</label>
                            <select name="mode" class="form-control">
                                <option value="semestre">Semestriel</option>
                                <option value="rattrapage">Rattrapage</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Semestre</label>
                            <select name="semestre" class="form-control">
                                <option value="1">Semestre 1</option>
                                <option value="2">Semestre 2</option>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Note cible /20</label>
                            <input type="number" step="0.01" min="0" max="20" name="target_note" value="10" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-info btn-block">
                        <span class="glyphicon glyphicon-edit"></span> Ouvrir le formulaire de délibération
                    </button>
                </form>
            </li>  
        
         <a href="{{ url('/eleves/notes/pv/historique/'.$id
    .'?cycle='.$id_cycle
    .'&nniveau='.$id_nniveau) }}"
   class="btn btn-warning">

    <span class="glyphicon glyphicon-folder-open"></span>
    Historique des PV

</a>
        
              </ul>

        {{-- Choix de la matière --}}
        <h4>{{ trans('eleve.choix_mat') }}</h4>
        <hr>

        <div class="liste-classe col-md-9">
            <ul>
                @foreach($matieres as $m)
                    <li>
                        <a data-toggle="modal"
                           data-backdrop="false"
                           href="#choose_seq2"
                           onclick="choix_seq({{ $m->id }})">
                            {{ (App::getLocale()=='en') ? $m->nom_anglais : $m->nom }}
                        </a>
                        @if(empty($m->id_session_exam))
                            <small class="text-muted">(session non configuree, notes recuperables)</small>
                        @endif
                    </li>
                @endforeach

               
            </ul>
        </div>

        {{-- Modal choix séquence --}}
        <div class="modal fade" id="choose_seq2">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button"
                                class="close"
                                data-dismiss="modal">x</button>
                        <h4 class="modal-title">{{ trans('admin.remplir_champs') }}</h4>
                    </div>

                    <div class="modal-body">
                        <form accept-charset="UTF-8"
                              role="form"
                              method="POST"
                              id="choix_seq_form2"
                              action="{{ url('/eleves/notes/print_note/'.$id) }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"/>
                            <input type="hidden" name="id" id="id" value=""/>
                            <input type="hidden" name="cycle" id="id_cycle" value="{{ $id_cycle }}"/>
                            <input type="hidden" name="nniveau" id="id_nniveau" value="{{ $id_nniveau }}"/>

                            <fieldset>
                                <div class="form-group">
                                    <label for="sequence">{{ trans('eleve.choix_seq') }} : </label>
                                    <select name="sequence"
                                            form="choix_seq_form2"
                                            class="form-control">
                                        <optgroup label="@lang('admin.semestre') 1">
                                            <option value="1">CC 1</option>
                                            <option value="3">@lang('admin.session_normale') 1</option>
                                            <option value="5">@lang('admin.rattrapage') 1</option>
                                            <option value="7">TP / TPE 1</option>
                                        </optgroup>
                                        <optgroup label="@lang('admin.semestre') 2">
                                            <option value="2">CC 2</option>
                                            <option value="4">@lang('admin.session_normale') 2</option>
                                            <option value="6">@lang('admin.rattrapage') 2</option>
                                            <option value="8">TP / TPE 2</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <input class="btn btn-success"
                                       type="submit"
                                       value="{{ trans('admin.confirmer') }}"
                                       autofocus>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div> {{-- /modal --}}
    </div>

    <script>
        function choix_seq(id) {
            document.getElementById("id").value = id;
        }
    </script>
@endsection

@section('breadcrumb')
    <li>
        <strong><a href="{{ url('eleves') }}">@lang('menu.gestion_notes')</a></strong>
    </li>
    <li>
        <strong><a href="{{ url('eleves/notes') }}">Notes</a></strong>
    </li>
    <li>
        <a href="{{ url('eleves/notes/show') }}">
            <strong>@lang('admin.choix_classe')</strong>
        </a>
    </li>
    <li class="active">
        <strong>@lang('admin.matiere')</strong>
    </li>
@endsection
