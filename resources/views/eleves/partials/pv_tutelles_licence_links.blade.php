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
                                    <span class="glyphicon glyphicon-file"></span> Releves SOA A4 portrait
                                </a>
                                <a class="btn btn-info btn-block"
                                   href="{{ $soaPrintPath.'?id='.$id.'&cycle='.$id_cycle.'&nniveau='.$id_nniveau.'&page_size=a3' }}"
                                   onclick="return generateSOAReleves(this, event);">
                                    <span class="glyphicon glyphicon-file"></span> Releves SOA A3 portrait
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
                                    <span class="glyphicon glyphicon-file"></span> Releves de notes Ngaoundere
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
        button.innerHTML = '<span class="glyphicon glyphicon-refresh"></span> Generation en cours...';

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
                    throw new Error('La fenetre de resultat a ete bloquee par le navigateur.');
                }
                output.document.open();
                output.document.write(html);
                output.document.close();
            })
            .catch(function (error) {
                window.alert('La generation des releves a echoue : ' + error.message);
            })
            .then(function () {
                button.classList.remove('disabled');
                button.innerHTML = originalText;
            });
        return false;
    }
</script>
