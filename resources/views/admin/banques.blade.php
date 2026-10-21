@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-arrow-left"></span> Tableau de bord</a>
        <a class="btn btn-primary" href="{{ url('/admin/caisses') }}"><span class="fa fa-briefcase"></span> Caisses</a>
        <a class="btn btn-info" href="{{ url('/admin/finances/etat') }}"><span class="fa fa-bar-chart"></span> Etat financier</a>
        <a class="btn btn-warning" href="{{ url('/admin/finances/transfert') }}"><span class="fa fa-exchange"></span> Transfert</a>
        <a class="btn btn-info" href="{{ url('/admin/finances/affectations') }}"><span class="fa fa-users"></span> Affectations</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/admin/banques/store') }}" class="panel panel-default">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"><label>Nom</label><input name="nom" class="form-control" required></div>
                <div class="col-md-3"><label>Numero de compte</label><input name="numero_compte" class="form-control"></div>
                <div class="col-md-2"><label>Ville</label><input name="ville" class="form-control"></div>
                <div class="col-md-2"><label>Localisation</label><input name="localisation" class="form-control"></div>
                <div class="col-md-2"><label>Solde</label><input type="number" step="0.01" name="solde" value="0" class="form-control"></div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-9"><label>Description</label><input name="description" class="form-control"></div>
                <div class="col-md-3"><label>&nbsp;</label><button class="btn btn-success form-control">Creer</button></div>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-striped table-condensed">
        <thead><tr><th>#</th><th>Nom</th><th>Compte</th><th>Ville</th><th>Solde</th><th>Description</th><th></th></tr></thead>
        <tbody>
        <?php $i = 1; ?>
        @foreach($banques as $b)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $b->nom }}</td>
                <td>{{ $b->numero_compte }}</td>
                <td>{{ $b->ville }}</td>
                <td>{{ number_format($b->solde, 0) }}</td>
                <td>{{ $b->description }}</td>
                <td>
                    <button type="button" class="btn btn-primary btn-xs edit-banque"
                            data-id="{{ $b->id }}"
                            data-nom="{{ $b->nom }}"
                            data-numero_compte="{{ $b->numero_compte }}"
                            data-ville="{{ $b->ville }}"
                            data-localisation="{{ $b->localisation }}"
                            data-solde="{{ $b->solde }}"
                            data-description="{{ $b->description }}">
                        Modifier
                    </button>
                    <a class="btn btn-danger btn-xs" href="{{ url('/admin/banques/destroy/'.$b->id) }}" onclick="return confirm('Supprimer cette banque ?')">Supprimer</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="paginate" style="text-align:center"><?php echo(str_replace('/?', '?', $banques->render())); ?></div>
</div>

<div class="modal fade" id="editBanqueModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="{{ url('/admin/banques/update') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="id" id="edit_banque_id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modifier la banque</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4"><label>Nom</label><input name="nom" id="edit_banque_nom" class="form-control" required></div>
                        <div class="col-md-4"><label>Numero de compte</label><input name="numero_compte" id="edit_banque_numero_compte" class="form-control"></div>
                        <div class="col-md-4"><label>Ville</label><input name="ville" id="edit_banque_ville" class="form-control"></div>
                    </div>
                    <div class="row" style="margin-top:10px">
                        <div class="col-md-4"><label>Localisation</label><input name="localisation" id="edit_banque_localisation" class="form-control"></div>
                        <div class="col-md-4"><label>Solde</label><input type="number" step="0.01" name="solde" id="edit_banque_solde" class="form-control"></div>
                        <div class="col-md-4"><label>Description</label><input name="description" id="edit_banque_description" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(function () {
        $('.edit-banque').on('click', function () {
            var button = $(this);
            $('#edit_banque_id').val(button.attr('data-id'));
            $('#edit_banque_nom').val(button.attr('data-nom'));
            $('#edit_banque_numero_compte').val(button.attr('data-numero_compte'));
            $('#edit_banque_ville').val(button.attr('data-ville'));
            $('#edit_banque_localisation').val(button.attr('data-localisation'));
            $('#edit_banque_solde').val(button.attr('data-solde'));
            $('#edit_banque_description').val(button.attr('data-description'));
            $('#editBanqueModal').modal('show');
        });
    });
</script>@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
