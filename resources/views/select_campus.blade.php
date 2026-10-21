@extends('skeleton')
@section('sub-content')
<div class="col-md-6 col-md-offset-3">
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/') }}"><span class="fa fa-arrow-left"></span> Accueil</a>
        <a class="btn btn-primary" href="{{ url('/admin/campus') }}"><span class="fa fa-university"></span> Campus</a>
    </div>
    <h3>{{ $titre }}</h3>
    <form method="post" action="{{ url('/select-campus') }}" class="panel panel-default">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="panel-body">
            <div class="form-group">
                <label>Campus</label>
                <select name="id_campus" class="form-control" required>
                    @foreach($campus as $cp)
                        <option value="{{ $cp->id }}">
                            {{ $cp->nom }}
                            @if($cp->sigle) - {{ $cp->sigle }} @endif
                            @if($cp->ville || $cp->localisation)
                                ({{ trim(($cp->ville ? $cp->ville : '').($cp->ville && $cp->localisation ? ' - ' : '').($cp->localisation ? $cp->localisation : '')) }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="table-responsive" style="margin-bottom:15px">
                <table class="table table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Campus</th>
                        <th>Sigle</th>
                        <th>Ville</th>
                        <th>Localisation</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($campus as $cp)
                        <tr>
                            <td>{{ $cp->nom }}</td>
                            <td>{{ $cp->sigle }}</td>
                            <td>{{ $cp->ville }}</td>
                            <td>{{ $cp->localisation }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-success form-control">Continuer</button>
        </div>
    </form>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('/') }}">Accueil</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
