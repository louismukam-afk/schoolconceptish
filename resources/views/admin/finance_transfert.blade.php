@extends('skeleton')
@section('sub-content')
<div class="col-md-10">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin/finances/etat') }}"><span class="fa fa-arrow-left"></span> Etat financier</a>
        <a class="btn btn-primary" href="{{ url('/admin/caisses') }}"><span class="fa fa-briefcase"></span> Caisses</a>
        <a class="btn btn-primary" href="{{ url('/admin/banques') }}"><span class="fa fa-bank"></span> Banques</a>
        <a class="btn btn-info" href="{{ url('/admin/finances/affectations') }}"><span class="fa fa-users"></span> Affectations</a>
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-home"></span> Tableau de bord</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/admin/finances/transfert/store') }}" class="panel panel-default">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="form-group">
                <label>Type de transfert</label>
                <select name="type_transfert" id="type_transfert" class="form-control" onchange="toggleTransferFields()" required>
                    <option value="caisse_caisse">Caisse vers caisse</option>
                    <option value="caisse_banque">Caisse vers banque</option>
                    <option value="banque_banque">Banque vers banque</option>
                </select>
            </div>
            <div class="form-group transfer-field" id="caisse_source_group">
                <label>Caisse source</label>
                <select name="id_caisse_source" class="form-control">
                    @foreach($caisses as $c)
                        <option value="{{ $c->id }}">{{ $c->nom }} - {{ $c->type_caisse }} - {{ number_format($c->solde, 0) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group transfer-field" id="caisse_destination_group">
                <label>Caisse destination</label>
                <select name="id_caisse_destination" class="form-control">
                    @foreach($caisses_sortie as $c)
                        <option value="{{ $c->id }}">{{ $c->nom }} - {{ number_format($c->solde, 0) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group transfer-field" id="banque_source_group">
                <label>Banque source</label>
                <select name="id_banque_source" class="form-control">
                    @foreach($banques as $b)
                        <option value="{{ $b->id }}">{{ $b->nom }} {{ $b->numero_compte }} - {{ number_format($b->solde, 0) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group transfer-field" id="banque_destination_group">
                <label>Banque destination</label>
                <select name="id_banque_destination" class="form-control">
                    @foreach($banques as $b)
                        <option value="{{ $b->id }}">{{ $b->nom }} {{ $b->numero_compte }} - {{ number_format($b->solde, 0) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group"><label>Montant</label><input type="number" step="0.01" name="montant" class="form-control" required></div>
            <div class="form-group"><label>Date</label><input type="date" name="date_operation" value="{{ date('Y-m-d') }}" class="form-control" required></div>
            <div class="form-group"><label>Reference</label><input name="reference" class="form-control"></div>
            <div class="form-group"><label>Motif</label><input name="motif" class="form-control" value="Transfert financier"></div>
            <button class="btn btn-success"><span class="fa fa-exchange"></span> Effectuer le transfert</button>
        </div>
    </form>
    <script>
        function toggleTransferFields() {
            var type = $('#type_transfert').val();
            $('.transfer-field').addClass('hidden');
            if(type == 'banque_banque') {
                $('#banque_source_group').removeClass('hidden');
                $('#banque_destination_group').removeClass('hidden');
            }
            else if(type == 'caisse_banque') {
                $('#caisse_source_group').removeClass('hidden');
                $('#banque_destination_group').removeClass('hidden');
            }
            else {
                $('#caisse_source_group').removeClass('hidden');
                $('#caisse_destination_group').removeClass('hidden');
            }
        }
        $(document).ready(function() {
            toggleTransferFields();
        });
    </script>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li><strong><a href="{{ url('/admin/finances/etat') }}">Etat financier</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
