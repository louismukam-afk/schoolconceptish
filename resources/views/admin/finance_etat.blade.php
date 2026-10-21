@extends('skeleton')
@section('sub-content')
<div class="col-md-12">
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-arrow-left"></span> Tableau de bord</a>
        <a class="btn btn-primary" href="{{ url('/admin/caisses') }}"><span class="fa fa-briefcase"></span> Caisses</a>
        <a class="btn btn-primary" href="{{ url('/admin/banques') }}"><span class="fa fa-bank"></span> Banques</a>
        <a class="btn btn-warning" href="{{ url('/admin/finances/transfert') }}"><span class="fa fa-exchange"></span> Transfert caisse</a>
    </div>
    <h3>{{ $titre }}</h3>
    @if(isset($campus_only) && $campus_only && isset($campus_actif) && $campus_actif)
        <div class="alert alert-success">
            Campus : <strong>{{ $campus_actif->nom }}</strong>
            @if($campus_actif->sigle) - {{ $campus_actif->sigle }} @endif
            @if($campus_actif->localisation) | {{ $campus_actif->localisation }} @endif
        </div>
    @endif
    <form method="get" action="{{ (isset($campus_only) && $campus_only) ? url('/inscriptions/etat-caisse') : url('/admin/finances/etat') }}" class="panel panel-default">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-2"><label>Du</label><input type="date" name="date_debut" value="{{ $date_debut }}" class="form-control"></div>
                <div class="col-md-2"><label>Au</label><input type="date" name="date_fin" value="{{ $date_fin }}" class="form-control"></div>
                @if($can_filter_admin)
                    <div class="col-md-3">
                        <label>Campus</label>
                        <select name="id_campus" class="form-control">
                            @foreach($campus as $cp)
                                <option value="{{ $cp->id }}" @if($id_campus == $cp->id) selected @endif>{{ $cp->nom }} @if($cp->sigle) - {{ $cp->sigle }} @endif @if($cp->localisation) | {{ $cp->localisation }} @endif</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Utilisateur</label>
                        <select name="id_user" class="form-control">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @if($id_user == $u->id) selected @endif>{{ $u->fullname ? $u->fullname : $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label>Source</label>
                    <select name="source" class="form-control">
                        <option value="all" @if($source == 'all') selected @endif>Toutes</option>
                        <option value="caisse" @if($source == 'caisse') selected @endif>Caisse</option>
                        <option value="banque" @if($source == 'banque') selected @endif>Banque</option>
                    </select>
                </div>
            </div>
            <div class="row" style="margin-top:10px">
                <div class="col-md-4">
                    <label>Caisse</label>
                    <select name="id_caisse" class="form-control">
                        <option value="">Toutes les caisses autorisees</option>
                        @foreach($caisses as $caisse)
                            <option value="{{ $caisse->id }}" @if($id_caisse == $caisse->id) selected @endif>{{ $caisse->nom }} - {{ $caisse->type_caisse }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Banque</label>
                    <select name="id_banque" class="form-control">
                        <option value="">Toutes les banques autorisees</option>
                        @foreach($banques as $banque)
                            <option value="{{ $banque->id }}" @if($id_banque == $banque->id) selected @endif>{{ $banque->nom }} {{ $banque->numero_compte }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4"><label>&nbsp;</label><button class="btn btn-primary form-control">Afficher</button></div>
            </div>
        </div>
    </form>
    <div class="alert alert-info">
        Entrees : <strong>{{ number_format($total_entree, 0) }}</strong>
        &nbsp;&nbsp; Sorties : <strong>{{ number_format($total_sortie, 0) }}</strong>
        &nbsp;&nbsp; Solde periode : <strong>{{ number_format($total_entree - $total_sortie, 0) }}</strong>
    </div>
    <h4>Soldes par caisse / banque</h4>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
                <th>Type</th>
                <th>Nom</th>
                <th>Nature / Compte</th>
                <th>Entrees periode</th>
                <th>Sorties periode</th>
                <th>Solde periode</th>
                <th>Solde actuel</th>
            </tr>
        </thead>
        <tbody>
        @foreach($summaries as $summary)
            <tr>
                <td>{{ $summary['type'] }}</td>
                <td>{{ $summary['nom'] }}</td>
                <td>{{ $summary['nature'] }}</td>
                <td>{{ number_format($summary['entree'], 0) }}</td>
                <td>{{ number_format($summary['sortie'], 0) }}</td>
                <td><strong>{{ number_format($summary['solde_periode'], 0) }}</strong></td>
                <td>{{ number_format($summary['solde_actuel'], 0) }}</td>
            </tr>
        @endforeach
        @if(count($summaries) <= 0)
            <tr><td colspan="7" class="text-center">Aucune caisse ou banque autorisee pour ce filtre.</td></tr>
        @endif
        </tbody>
    </table>
    <h4>Details des mouvements</h4>
    <table class="table table-bordered table-striped table-condensed">
        <thead><tr><th>Date</th><th>Operation</th><th>Source</th><th>Motif</th><th>Montant</th><th>Reference</th><th>Demande</th><th>Agent</th></tr></thead>
        <tbody>
        @foreach($mouvements as $m)
            <tr><td>{{ $m->date_operation }}</td><td>{{ $m->type_operation }}</td><td>{{ $m->source }}</td><td>{{ $m->motif }}</td><td>{{ number_format($m->montant, 0) }}</td><td>{{ $m->reference }}</td><td>{{ $m->demande }}</td><td>@if($m->creator) {{ $m->creator->fullname ? $m->creator->fullname : $m->creator->name }} @endif</td></tr>
        @endforeach
        </tbody>
    </table>
    <div class="paginate" style="text-align:center"><?php echo(str_replace('/?', '?', $mouvements->render())); ?></div>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
