<div class="form-group">
    <label for="{{ $prefix }}_source">Releve de notes</label>
    <select name="source_releve_id" id="{{ $prefix }}_source" class="form-control" required>
        <option value="">-- Selectionner une specialite et un niveau --</option>
        @foreach($sources_releves as $source)
            <option value="{{ $source->id }}" data-classe="{{ $source->id_classe }}" data-cycle="{{ $source->id_cycle }}" data-nniveau="{{ $source->id_nniveau }}" style="display:none;" disabled>
                Releve #{{ $source->id }} du {{ $source->created_at ? date('d/m/Y H:i', strtotime($source->created_at)) : '' }} ({{ $source->nb_releves }} etudiants)
            </option>
        @endforeach
    </select>
    <p class="help-block" id="{{ $prefix }}_source_help"></p>
</div>
<script>
    function updateAttestationReleveSources(prefix) {
        var classe = document.getElementById(prefix + '_classe');
        var cycle = document.getElementById(prefix + '_cycle');
        var niveau = document.getElementById(prefix + '_nniveau');
        var source = document.getElementById(prefix + '_source');
        var button = document.getElementById(prefix + '_btn');
        var help = document.getElementById(prefix + '_source_help');
        if (!classe || !cycle || !niveau || !source) return;
        var previous = source.value;
        var first = '';
        var found = false;
        for (var i = 1; i < source.options.length; i++) {
            var option = source.options[i];
            var matches = option.getAttribute('data-classe') === classe.value &&
                option.getAttribute('data-cycle') === cycle.value &&
                option.getAttribute('data-nniveau') === niveau.value;
            option.disabled = !matches;
            option.style.display = matches ? '' : 'none';
            if (matches) {
                found = true;
                if (!first) first = option.value;
            }
        }
        source.value = previous && source.querySelector('option[value="' + previous + '"]:not([disabled])') ? previous : first;
        if (button) button.disabled = !found;
        if (help) help.textContent = found ? '' : 'Aucun releve enregistre pour cette selection. Generez d abord le releve de notes.';
    }
</script>
