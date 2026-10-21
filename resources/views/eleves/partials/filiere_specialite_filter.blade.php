<div class="panel panel-primary">
    <div class="panel-heading">
        <strong>Filtrer les specialites</strong>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="filiere_filter">Filiere</label>
                <select id="filiere_filter" class="form-control">
                    <option value="">-- Selectionner une filiere --</option>
                    @foreach($niveaux as $n)
                        <option value="{{ $n->id }}">
                            {{ (App::getLocale() == 'en' && isset($n->nom_anglais) && $n->nom_anglais) ? $n->nom_anglais : $n->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <p class="text-muted" style="margin-bottom:0;">
            Choisir une filiere pour afficher uniquement ses specialites.
        </p>
    </div>
</div>

<div id="specialite_empty" class="alert alert-info">
    Selectionnez une filiere pour afficher les specialites.
</div>

<script>
    (function () {
        function refreshSpecialitesByFiliere() {
            var select = document.getElementById('filiere_filter');
            var empty = document.getElementById('specialite_empty');
            var blocks = document.getElementsByClassName('niveau-block');
            if (!select || !blocks.length) return;

            var selected = select.value;
            var hasVisible = false;
            for (var i = 0; i < blocks.length; i++) {
                var visible = selected && blocks[i].getAttribute('data-filiere') == selected;
                blocks[i].style.display = visible ? '' : 'none';
                if (visible) hasVisible = true;
            }
            if (empty) {
                empty.style.display = hasVisible ? 'none' : '';
            }
        }

        if (window.jQuery) {
            window.jQuery(function ($) {
                $('#filiere_filter').on('change', refreshSpecialitesByFiliere);
                refreshSpecialitesByFiliere();
            });
        } else if (document.addEventListener) {
            document.addEventListener('DOMContentLoaded', function () {
                var select = document.getElementById('filiere_filter');
                if (select) select.onchange = refreshSpecialitesByFiliere;
                refreshSpecialitesByFiliere();
            });
        }
    })();
</script>
