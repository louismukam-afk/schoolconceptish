@extends('skeleton')

@section('sub-content')
    <div class="btn-toolbar" style="margin-bottom:15px;">
        <a href="{{ url('/eleves') }}" class="btn btn-default">
            <span class="glyphicon glyphicon-home"></span> Accueil eleves
        </a>
        <a href="{{ $back_url }}" class="btn btn-default">
            <span class="glyphicon glyphicon-arrow-left"></span> Retour
        </a>
        <button type="button" class="btn btn-warning" onclick="printPvFrame()">
            <span class="glyphicon glyphicon-print"></span> Imprimer
        </button>
    </div>
    <div class="panel panel-primary">
        <div class="panel-heading">
            <strong>Chargement du proces-verbal</strong>
        </div>
        <div class="panel-body">
            <p id="pv-loading-message" class="text-info">
                Chargement du PV en cours...
            </p>

            <div id="pv-error-message" class="alert alert-danger" style="display:none;">
                Le PV n'a pas pu etre charge. Rechargez la page ou relancez l'impression.
            </div>

            <iframe
                id="pv-frame"
                src="about:blank"
                data-src="{{ $pv_url }}"
                style="width:100%; min-height:850px; border:1px solid #ddd; background:#fff;">
            </iframe>

            <div class="text-right" style="margin-top:10px;">
                <a href="{{ $back_url }}" class="btn btn-default">
                    Retour
                </a>
                <button type="button" class="btn btn-warning" onclick="printPvFrame()">
                    <span class="glyphicon glyphicon-print"></span>
                    Imprimer
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var frame = document.getElementById('pv-frame');
            var loading = document.getElementById('pv-loading-message');
            var error = document.getElementById('pv-error-message');

            frame.onload = function () {
                loading.style.display = 'none';
            };

            frame.onerror = function () {
                loading.style.display = 'none';
                error.style.display = '';
            };

            window.setTimeout(function () {
                frame.src = frame.getAttribute('data-src');
            }, 50);
        })();

        function printPvFrame() {
            var frame = document.getElementById('pv-frame');
            if (frame && frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
        }
    </script>
@endsection
