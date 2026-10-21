<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Cycle</th>
        <th>Niveau</th>
        <th>Filiere</th>
        <th>Specialite</th>
        <th>Matiere / Cours</th>
        <th>Enseignant</th>
        <th>Semestre</th>
        <th>Periode</th>
        @if($avecFiche)<th>Fiches</th>@endif
    </tr>
    </thead>
    <tbody>
    @forelse($rows as $cours)
        <tr>
            <td>{{ $cours->cycle ? $cours->cycle->nom : '-' }}</td>
            <td>{{ $cours->nniveau ? $cours->nniveau->nom : '-' }}</td>
            <td>{{ $cours->filiere ? $cours->filiere->nom : '-' }}</td>
            <td>{{ $cours->classe ? $cours->classe->nom : '-' }}</td>
            <td>{{ $cours->matiere ? $cours->matiere->nom : '-' }}</td>
            <td>{{ $cours->personnel ? $cours->personnel->nom : '-' }}</td>
            <td>Semestre {{ $cours->semestre }}</td>
            <td>{{ $cours->date_debut }} - {{ $cours->date_fin }}</td>
            @if($avecFiche)
                <td>
                    @foreach($fichesParCours[$cours->id] as $fiche)
                        <div style="margin-bottom:5px">
                            {{ $fiche->fichier_original }}
                            @if($fiche->fichier_extension == 'pdf')
                                <button type="button" class="btn btn-info btn-xs js-preview-fiche no-print" data-target="preview-fiche-cours-{{ $fiche->id }}">Visualiser</button>
                            @endif
                            <a href="{{ route('fiches_progression_download', $fiche->id) }}" class="btn btn-primary btn-xs no-print">Telecharger</a>
                            @if($fiche->fichier_extension == 'pdf')
                                <div id="preview-fiche-cours-{{ $fiche->id }}" style="display:none;margin-top:8px">
                                    <iframe data-src="{{ route('fiches_progression_show', $fiche->id) }}" style="width:100%; height:420px; border:1px solid #ccc;"></iframe>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </td>
            @endif
        </tr>
    @empty
        <tr><td colspan="{{ $avecFiche ? 9 : 8 }}" class="text-center">Aucun cours trouve.</td></tr>
    @endforelse
    </tbody>
</table>
<script>
    (function(){
        var buttons = document.getElementsByClassName('js-preview-fiche');
        Array.prototype.forEach.call(buttons, function(button) {
            button.onclick = function() {
                var box = document.getElementById(button.getAttribute('data-target'));
                if (!box) return;
                var iframe = box.getElementsByTagName('iframe')[0];
                if (iframe && !iframe.getAttribute('src')) iframe.setAttribute('src', iframe.getAttribute('data-src'));
                box.style.display = box.style.display === 'none' ? '' : 'none';
            };
        });
    })();
</script>
