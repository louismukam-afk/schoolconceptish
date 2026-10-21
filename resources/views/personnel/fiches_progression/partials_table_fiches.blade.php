<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Date</th>
        <th>Annee</th>
        <th>Enseignant</th>
        <th>Cours</th>
        <th>Fichier</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse($fiches as $fiche)
        <tr>
            <td>{{ $fiche->created_at }}</td>
            <td>{{ $fiche->annee_scolaire }}</td>
            <td>{{ $fiche->personnel ? $fiche->personnel->nom : '' }}</td>
            <td>
                @if($fiche->cours)
                    {{ $fiche->cours->matiere ? $fiche->cours->matiere->nom : 'Matiere' }}
                    / {{ $fiche->cours->classe ? $fiche->cours->classe->nom : '' }}
                    {{ $fiche->cours->nniveau ? ' - '.$fiche->cours->nniveau->nom : '' }}
                @endif
            </td>
            <td>{{ $fiche->fichier_original }} <span class="label label-default">{{ strtoupper($fiche->fichier_extension) }}</span></td>
            <td>
                @if($fiche->fichier_extension == 'pdf')
                    <button type="button" class="btn btn-info btn-xs js-preview-fiche" data-target="preview-fiche-simple-{{ $fiche->id }}">Visualiser</button>
                @endif
                <a href="{{ route('fiches_progression_download', $fiche->id) }}" class="btn btn-primary btn-xs">Telecharger</a>
            </td>
        </tr>
        @if($fiche->fichier_extension == 'pdf')
            <tr id="preview-fiche-simple-{{ $fiche->id }}" style="display:none">
                <td colspan="6">
                    <iframe data-src="{{ route('fiches_progression_show', $fiche->id) }}" style="width:100%; height:520px; border:1px solid #ccc;"></iframe>
                </td>
            </tr>
        @endif
    @empty
        <tr><td colspan="6" class="text-center">Aucune fiche trouvee.</td></tr>
    @endforelse
    </tbody>
</table>
<script>
    (function(){
        var buttons = document.getElementsByClassName('js-preview-fiche');
        Array.prototype.forEach.call(buttons, function(button) {
            button.onclick = function() {
                var row = document.getElementById(button.getAttribute('data-target'));
                if (!row) return;
                var iframe = row.getElementsByTagName('iframe')[0];
                if (iframe && !iframe.getAttribute('src')) iframe.setAttribute('src', iframe.getAttribute('data-src'));
                row.style.display = row.style.display === 'none' ? '' : 'none';
            };
        });
    })();
</script>
