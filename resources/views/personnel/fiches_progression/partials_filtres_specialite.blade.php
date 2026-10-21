<form method="GET" class="well">
    <div class="row">
        <div class="col-md-2">
            <label>Annee academique</label>
            <select name="annee_scolaire" class="form-control">
                @foreach($years as $year)
                    <option value="{{ $year }}" @if($selectedYear==$year) selected @endif>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Cycle</label>
            <select name="id_cycle" id="fp_id_cycle" class="form-control">
                <option value="">Tous</option>
                @foreach($cycles as $cycle)
                    <option value="{{ $cycle->id }}" @if($selectedCycle==$cycle->id) selected @endif>{{ $cycle->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Niveau</label>
            <select name="id_nniveau" id="fp_id_nniveau" class="form-control">
                <option value="">Tous</option>
                @foreach($nniveaux as $niveau)
                    <option value="{{ $niveau->id }}" data-cycle="{{ $niveau->id_cycle }}" @if($selectedNniveau==$niveau->id) selected @endif>{{ $niveau->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>Filiere</label>
            <select name="id_niveau" id="fp_id_niveau" class="form-control">
                <option value="">Toutes</option>
                @foreach($niveaux as $filiere)
                    <option value="{{ $filiere->id }}" data-cycle="{{ isset($filiere->id_cycle) ? $filiere->id_cycle : '' }}" @if($selectedNiveau==$filiere->id) selected @endif>{{ $filiere->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label>Specialite</label>
            <select name="id_classe" id="fp_id_classe" class="form-control">
                <option value="">Toutes</option>
                @foreach($classes as $classe)
                    <option value="{{ $classe->id }}"
                            data-niveau="{{ $classe->id_niveau }}"
                            data-cycle="{{ isset($classe->id_cycle) ? $classe->id_cycle : ($classe->niveau && isset($classe->niveau->id_cycle) ? $classe->niveau->id_cycle : '') }}"
                            @if($selectedClasse==$classe->id) selected @endif>{{ $classe->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label>&nbsp;</label>
            <button class="btn btn-primary form-control">Filtrer</button>
        </div>
    </div>
</form>
<script>
    (function(){
        var cycle = document.getElementById('fp_id_cycle');
        var nniveau = document.getElementById('fp_id_nniveau');
        var filiere = document.getElementById('fp_id_niveau');
        var specialite = document.getElementById('fp_id_classe');

        function filterOptions(select, matcher) {
            Array.prototype.forEach.call(select.options, function(option) {
                if (option.value === '') {
                    option.style.display = '';
                    return;
                }
                option.style.display = matcher(option) ? '' : 'none';
                if (option.selected && option.style.display === 'none') {
                    select.value = '';
                }
            });
        }

        function applyFilters() {
            var cycleValue = cycle ? cycle.value : '';
            var filiereValue = filiere ? filiere.value : '';

            filterOptions(nniveau, function(option) {
                return !cycleValue || option.getAttribute('data-cycle') === cycleValue;
            });

            filterOptions(filiere, function(option) {
                var optionCycle = option.getAttribute('data-cycle');
                return !cycleValue || optionCycle === cycleValue || optionCycle === '';
            });

            filterOptions(specialite, function(option) {
                var optionCycle = option.getAttribute('data-cycle');
                var optionFiliere = option.getAttribute('data-niveau');
                var byCycle = !cycleValue || optionCycle === cycleValue || optionCycle === '';
                var byFiliere = !filiereValue || optionFiliere === filiereValue;
                return byCycle && byFiliere;
            });
        }

        if (cycle) cycle.onchange = applyFilters;
        if (filiere) filiere.onchange = applyFilters;
        if (nniveau) nniveau.onchange = applyFilters;
        applyFilters();
    })();
</script>
