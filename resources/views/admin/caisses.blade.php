@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-arrow-left"></span> Tableau de bord</a>
        <a class="btn btn-primary" href="{{ url('/admin/banques') }}"><span class="fa fa-bank"></span> Banques</a>
        <a class="btn btn-info" href="{{ url('/admin/finances/etat') }}"><span class="fa fa-bar-chart"></span> Etat financier</a>
        <a class="btn btn-warning" href="{{ url('/admin/finances/transfert') }}"><span class="fa fa-exchange"></span> Transfert</a>
        <a class="btn btn-info" href="{{ url('/admin/finances/affectations') }}"><span class="fa fa-users"></span> Affectations</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/admin/caisses/store') }}" class="panel panel-default">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3"><label>Nom</label><input name="nom" class="form-control" required></div>
                <div class="col-md-2"><label>Type de caisse</label><select name="type_caisse" class="form-control"><option value="entree">Entree</option><option value="sortie">Sortie</option></select></div>
                <div class="col-md-2"><label>Solde</label><input type="number" step="0.01" name="solde" value="0" class="form-control"></div>
                <div class="col-md-4"><label>Description</label><input name="description" class="form-control"></div>
                <div class="col-md-1"><label>&nbsp;</label><button class="btn btn-success form-control">OK</button></div>
            </div>
        </div>
    </form>
    <table class="table table-bordered table-striped table-condensed">
        <thead><tr><th>#</th><th>Nom</th><th>Type</th><th>Solde</th><th>Description</th><th></th></tr></thead>
        <tbody>
        <?php $i = 1; ?>
        @foreach($caisses as $c)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $c->nom }}</td>
                <td>{{ $c->type_caisse }}</td>
                <td>{{ number_format($c->solde, 0) }}</td>
                <td>{{ $c->description }}</td>
                <td>
                    <button type="button" class="btn btn-primary btn-xs edit-caisse"
                            data-id="{{ $c->id }}"
                            data-nom="{{ $c->nom }}"
                            data-type_caisse="{{ $c->type_caisse }}"
                            data-solde="{{ $c->solde }}"
                            data-description="{{ $c->description }}">
                        Modifier
                    </button>
                    <a class="btn btn-danger btn-xs" href="{{ url('/admin/caisses/destroy/'.$c->id) }}" onclick="return confirm('Supprimer cette caisse ?')">Supprimer</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="paginate" style="text-align:center"><?php echo(str_replace('/?', '?', $caisses->render())); ?></div>
</div>

<div class="modal fade" id="editCaisseModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="{{ url('/admin/caisses/update') }}">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="id" id="edit_caisse_id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Modifier la caisse</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6"><label>Nom</label><input name="nom" id="edit_caisse_nom" class="form-control" required></div>
                        <div class="col-md-6"><label>Type de caisse</label><select name="type_caisse" id="edit_caisse_type_caisse" class="form-control"><option value="entree">Entree</option><option value="sortie">Sortie</option></select></div>
                    </div>
                    <div class="row" style="margin-top:10px">
                        <div class="col-md-6"><label>Solde</label><input type="number" step="0.01" name="solde" id="edit_caisse_solde" class="form-control"></div>
                        <div class="col-md-6"><label>Description</label><input name="description" id="edit_caisse_description" class="form-control"></div>
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
        $('.edit-caisse').on('click', function () {
            var button = $(this);
            $('#edit_caisse_id').val(button.attr('data-id'));
            $('#edit_caisse_nom').val(button.attr('data-nom'));
            $('#edit_caisse_type_caisse').val(button.attr('data-type_caisse'));
            $('#edit_caisse_solde').val(button.attr('data-solde'));
            $('#edit_caisse_description').val(button.attr('data-description'));
            $('#editCaisseModal').modal('show');
        });
    });
</script>@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
