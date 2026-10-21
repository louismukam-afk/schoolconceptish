<?php
    $selectionCycleLabel = isset($selectionCycleLabel) ? $selectionCycleLabel : 'Cycle';
    $selectionLevelLabel = isset($selectionLevelLabel) ? $selectionLevelLabel : 'Niveau';
    $selectionFiliereLabel = isset($selectionFiliereLabel) ? $selectionFiliereLabel : 'Filiere';
    $selectionCyclePlaceholder = isset($selectionCyclePlaceholder) ? $selectionCyclePlaceholder : '-- Selectionner un cycle --';
    $selectionLevelFirstPlaceholder = isset($selectionLevelFirstPlaceholder) ? $selectionLevelFirstPlaceholder : "-- Selectionner un cycle d'abord --";
    $selectionLevelPlaceholder = isset($selectionLevelPlaceholder) ? $selectionLevelPlaceholder : '-- Selectionner un niveau --';
    $selectionFiliereFirstPlaceholder = isset($selectionFiliereFirstPlaceholder) ? $selectionFiliereFirstPlaceholder : '-- Selectionner un niveau --';
    $selectionFilierePlaceholder = isset($selectionFilierePlaceholder) ? $selectionFilierePlaceholder : '-- Selectionner une filiere --';
    $selectionEmptyMessage = isset($selectionEmptyMessage) ? $selectionEmptyMessage : "Selectionnez d'abord le cycle, le niveau et la filiere pour afficher les specialites.";
    $selectionAlertMessage = isset($selectionAlertMessage) ? $selectionAlertMessage : "Veuillez selectionner le cycle, le niveau et la filiere d'abord.";
?>

<div class="panel panel-default">
    <div class="panel-heading"><strong>{{ isset($selectionTitle) ? $selectionTitle : 'Selection de la classe' }}</strong></div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="cycle_filter">{{$selectionCycleLabel}}</label>
                    <select id="cycle_filter" class="form-control">
                        <option value="">{{$selectionCyclePlaceholder}}</option>
                        @foreach($cycles as $cycle)
                            <option value="{{$cycle->id}}">{{$cycle->nom}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="nniveau_filter">{{$selectionLevelLabel}}</label>
                    <select id="nniveau_filter" class="form-control" disabled>
                        <option value="">{{$selectionLevelFirstPlaceholder}}</option>
                        @foreach($nniveaux as $nniveau)
                            <option value="{{$nniveau->id}}" data-cycle="{{$nniveau->id_cycle}}">{{$nniveau->nom}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="filiere_filter">{{$selectionFiliereLabel}}</label>
                    <select id="filiere_filter" class="form-control" disabled>
                        <option value="">{{$selectionFiliereFirstPlaceholder}}</option>
                        @foreach($niveaux as $n)
                            <option value="{{$n->id}}" data-nniveau="{{isset($n->id_niveau) ? $n->id_niveau : ''}}">{{$n->getNom()}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="specialite_empty" class="alert alert-info">
    {{$selectionEmptyMessage}}
</div>

<script>
    (function () {
        var baseUrl = "{{ isset($selectionBaseUrl) ? $selectionBaseUrl : '' }}";
        var levelFirstPlaceholder = "{{ addslashes($selectionLevelFirstPlaceholder) }}";
        var levelPlaceholder = "{{ addslashes($selectionLevelPlaceholder) }}";
        var filiereFirstPlaceholder = "{{ addslashes($selectionFiliereFirstPlaceholder) }}";
        var filierePlaceholder = "{{ addslashes($selectionFilierePlaceholder) }}";
        var alertMessage = "{{ addslashes($selectionAlertMessage) }}";

        function get(id) {
            return document.getElementById(id);
        }

        function updateChosen(select) {
            if (window.jQuery && select) {
                window.jQuery(select).trigger('chosen:updated');
            }
        }

        function getSpecialiteList() {
            return get('specialite_list') || document.querySelector('.liste-classe > ul');
        }

        function setListVisible(visible) {
            var list = getSpecialiteList();
            var empty = get('specialite_empty');
            if (list) list.style.display = visible ? '' : 'none';
            if (empty) empty.style.display = visible ? 'none' : '';
        }

        function resetSelect(select, placeholder, disabled) {
            if (!select) return;
            select.value = '';
            select.disabled = !!disabled;
            if (select.options.length) {
                select.options[0].text = placeholder;
            }
            updateChosen(select);
        }

        function refreshNiveaux() {
            var cycle = get('cycle_filter');
            var niveau = get('nniveau_filter');
            var filiere = get('filiere_filter');
            var cycleId = cycle ? cycle.value : '';

            resetSelect(niveau, cycleId ? levelPlaceholder : levelFirstPlaceholder, !cycleId);
            resetSelect(filiere, filiereFirstPlaceholder, true);

            if (niveau) {
                for (var i = 0; i < niveau.options.length; i++) {
                    var option = niveau.options[i];
                    if (!option.value) {
                        option.style.display = '';
                        continue;
                    }
                    option.style.display = option.getAttribute('data-cycle') === cycleId ? '' : 'none';
                }
            }
            refreshSpecialites();
        }

        function refreshFilieres() {
            var niveau = get('nniveau_filter');
            var filiere = get('filiere_filter');
            var niveauId = niveau ? niveau.value : '';

            resetSelect(filiere, niveauId ? filierePlaceholder : filiereFirstPlaceholder, !niveauId);

            if (filiere) {
                for (var i = 0; i < filiere.options.length; i++) {
                    var option = filiere.options[i];
                    if (!option.value) {
                        option.style.display = '';
                        continue;
                    }
                    var optionNiveau = option.getAttribute('data-nniveau');
                    option.style.display = (!optionNiveau || optionNiveau === niveauId) ? '' : 'none';
                }
            }
            refreshSpecialites();
        }

        function refreshSpecialites() {
            var filiere = get('filiere_filter');
            var filiereId = filiere ? filiere.value : '';
            var cycle = get('cycle_filter');
            var cycleId = cycle ? cycle.value : '';
            var blocks = document.getElementsByClassName('niveau-block');
            var hasVisible = false;

            for (var i = 0; i < blocks.length; i++) {
                var blockCycle = blocks[i].getAttribute('data-cycle');
                if (blocks[i].getAttribute('data-filiere') === filiereId && (!blockCycle || blockCycle === cycleId)) {
                    blocks[i].style.display = '';
                    hasVisible = true;
                } else {
                    blocks[i].style.display = 'none';
                }
            }

            setListVisible(!!filiereId && hasVisible);
        }

        window.selectionCycleNiveauFiliere = {
            cycleValue: function () {
                var cycle = get('cycle_filter');
                return cycle ? cycle.value : '';
            },
            nniveauValue: function () {
                var niveau = get('nniveau_filter');
                return niveau ? niveau.value : '';
            },
            hasSelection: function () {
                var filiere = get('filiere_filter');
                return !!(this.cycleValue() && this.nniveauValue() && filiere && filiere.value);
            },
            setModalValues: function (cycleSelector, niveauSelector) {
                var cycle = document.querySelector(cycleSelector);
                var niveau = document.querySelector(niveauSelector);
                if (cycle) {
                    cycle.value = this.cycleValue();
                    if (window.jQuery) window.jQuery(cycle).trigger('change');
                }
                if (niveau) {
                    niveau.disabled = false;
                    niveau.value = this.nniveauValue();
                    updateChosen(niveau);
                }
            }
        };

        function openSpecialite(event) {
            var link = event.currentTarget;
            var idClasse = link.getAttribute('data-classe');
            if (!window.selectionCycleNiveauFiliere.hasSelection()) {
                alert(alertMessage);
                event.preventDefault();
                return false;
            }
            if (baseUrl && idClasse) {
                event.preventDefault();
                window.location.href = baseUrl + '?id=' + encodeURIComponent(idClasse) + '&cycle=' + encodeURIComponent(window.selectionCycleNiveauFiliere.cycleValue()) + '&nniveau=' + encodeURIComponent(window.selectionCycleNiveauFiliere.nniveauValue());
                return false;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            var cycle = get('cycle_filter');
            var niveau = get('nniveau_filter');
            var filiere = get('filiere_filter');
            var links = document.getElementsByClassName('cycle-niveau-specialite-link');

            if (cycle) cycle.addEventListener('change', refreshNiveaux);
            if (niveau) niveau.addEventListener('change', refreshFilieres);
            if (filiere) filiere.addEventListener('change', refreshSpecialites);
            for (var i = 0; i < links.length; i++) {
                links[i].addEventListener('click', openSpecialite);
            }
            setListVisible(false);
        });
    })();
</script>
