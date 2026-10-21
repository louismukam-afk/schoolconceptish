@extends('skeleton')
@section('sub-content')
<div class="col-md-10">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin/finances/transfert') }}"><span class="fa fa-arrow-left"></span> Transferts</a>
        <a class="btn btn-primary" href="{{ url('/admin/caisses') }}"><span class="fa fa-briefcase"></span> Caisses</a>
        <a class="btn btn-primary" href="{{ url('/admin/banques') }}"><span class="fa fa-bank"></span> Banques</a>
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-home"></span> Tableau de bord</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/admin/finances/affectations/store') }}" class="panel panel-default">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="form-group">
                <label>Utilisateur</label>
                <select name="id_user" id="id_user" class="form-control" onchange="refreshAssignments()" required>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->fullname ? $u->fullname : $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>Caisses autorisees</label>
                    <select name="caisses[]" id="caisses" class="form-control" multiple size="10">
                        @foreach($caisses as $c)
                            <option value="{{ $c->id }}">{{ $c->nom }} - {{ $c->type_caisse }} - {{ number_format($c->solde, 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Banques autorisees</label>
                    <select name="banques[]" id="banques" class="form-control" multiple size="10">
                        @foreach($banques as $b)
                            <option value="{{ $b->id }}">{{ $b->nom }} {{ $b->numero_compte }} - {{ number_format($b->solde, 0) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-top:15px">
                <button class="btn btn-success"><span class="fa fa-save"></span> Enregistrer les affectations</button>
            </div>
        </div>
    </form>
    <script>
        var selectedCaisses = {!! json_encode($selected_caisses) !!};
        var selectedBanques = {!! json_encode($selected_banques) !!};

        function selectValues(selectId, values) {
            values = values || [];
            $(selectId).find('option').each(function() {
                $(this).prop('selected', values.indexOf(parseInt($(this).val())) >= 0);
            });
        }

        function refreshAssignments() {
            var userId = $('#id_user').val();
            selectValues('#caisses', selectedCaisses[userId]);
            selectValues('#banques', selectedBanques[userId]);
        }

        $(document).ready(function() {
            refreshAssignments();
        });
    </script>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li><strong><a href="{{ url('/admin/finances/transfert') }}">Transferts</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
