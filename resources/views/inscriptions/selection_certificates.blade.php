@extends('skeleton')
@section('sub-content')
    <div class="panel panel-primary col-md-8">
        <div class="panel-heading">
            <h3 class="panel-title">@lang('admin.certificat_scolarite')</h3>
        </div>
        <div class="panel-body">
            @if($campus)
                <div class="alert alert-info">
                    Campus : <strong>{{ $campus->nom }}</strong>
                </div>
            @endif

            <form accept-charset="UTF-8" role="form" method="GET" action="{{ URL::to('/inscriptions/print-certificates') }}" target="_blank">
                <fieldset>
                    <div class="form-group">
                        <label for="cycle">Cycle :</label>
                        <select name="cycle" id="cycle" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            @foreach($cycles as $cy)
                                <option value="{{ $cy->id }}">{{ $cy->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="niveau">Fili&egrave;re :</label>
                        <select name="niveau" id="niveau" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            @foreach($niveaux as $n)
                                <option value="{{ $n->id }}" data-cycle="{{ $n->id_cycle }}">{{ $n->getNom() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="classe">Sp&eacute;cialit&eacute; :</label>
                        <select name="classe" id="classe" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c->id }}" data-niveau="{{ $c->id_niveau }}" data-cycle="{{ $c->id_cycle }}">{{ $c->getNom() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nniveau">Niveau :</label>
                        <select name="nniveau" id="nniveau" class="form-control" required>
                            <option value="">-- Choisir --</option>
                            @foreach($nniveaux as $nn)
                                <option value="{{ $nn->id }}" data-cycle="{{ $nn->id_cycle }}">{{ $nn->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="btn btn-success" type="submit">
                        <span class="fa fa-print"></span> Generer les certificats
                    </button>
                </fieldset>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var allNiveaux = $('#niveau option').clone();
            var allClasses = $('#classe option').clone();
            var allNniveaux = $('#nniveau option').clone();

            function filterNiveaux() {
                var cycle = $('#cycle').val();
                $('#niveau').empty().append(allNiveaux.filter(function () {
                    return !$(this).val() || !$(this).data('cycle') || $(this).data('cycle') == cycle;
                }));
                filterClasses();
            }

            function filterClasses() {
                var niveau = $('#niveau').val();
                var cycle = $('#cycle').val();
                $('#classe').empty().append(allClasses.filter(function () {
                    var sameNiveau = $(this).data('niveau') == niveau;
                    var sameCycle = !$(this).data('cycle') || $(this).data('cycle') == cycle;
                    return !$(this).val() || (sameNiveau && sameCycle);
                }));
            }

            function filterNniveaux() {
                var cycle = $('#cycle').val();
                $('#nniveau').empty().append(allNniveaux.filter(function () {
                    return !$(this).val() || $(this).data('cycle') == cycle;
                }));
            }

            $('#niveau').on('change', filterClasses);
            $('#cycle').on('change', function () {
                filterNiveaux();
                filterNniveaux();
            });
            filterNiveaux();
            filterNniveaux();
        })();
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('inscriptions')}}">@lang('menu.gestion_inscriptions')</a></strong></li>
    <li class="active"><strong>@lang('admin.certificat_scolarite')</strong></li>
@endsection
