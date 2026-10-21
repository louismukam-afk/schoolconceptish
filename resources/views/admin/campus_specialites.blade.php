@extends('skeleton')
@section('sub-content')
<div class="col-md-10">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin/campus') }}"><span class="fa fa-arrow-left"></span> Campus</a>
        @if($campus)
            <a class="btn btn-info" href="{{ url('/admin/campus/'.$campus->id.'/users') }}"><span class="fa fa-users"></span> Utilisateurs</a>
        @endif
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-home"></span> Tableau de bord</a>
    </div>
    <h3>{{ $titre }} : {{ $campus ? $campus->nom : '' }}</h3>
    <div class="panel panel-info">
        <div class="panel-heading"><strong>Specialites deja affectees</strong></div>
        <div class="panel-body">
            <table class="table table-bordered table-condensed">
                <thead><tr><th>Code</th><th>Specialite</th><th></th></tr></thead>
                <tbody>
                @if(isset($specialites_affectees))
                    @foreach($specialites_affectees as $sp)
                        <tr>
                            <td>{{ $sp->code }}</td>
                            <td>{{ $sp->getNom() }}</td>
                            <td>
                                <a class="btn btn-danger btn-xs" href="{{ url('/admin/campus/'.$campus->id.'/specialites/'.$sp->id.'/detach') }}" onclick="return confirm('Desaffecter cette specialite du campus ?')">
                                    Desaffecter
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <form method="post" action="{{ url('/admin/campus/specialites/store') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id_campus" value="{{ $campus ? $campus->id : '' }}">
        <table class="table table-bordered table-condensed">
            <thead><tr><th></th><th>Code</th><th>Specialite</th></tr></thead>
            <tbody>
            @foreach($classes as $c)
                <tr>
                    <td><input type="checkbox" name="classes[]" value="{{ $c->id }}" @if(in_array($c->id, $selected)) checked @endif></td>
                    <td>{{ $c->code }}</td><td>{{ $c->getNom() }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-success">Enregistrer les affectations</button>
    </form>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li><strong><a href="{{ url('/admin/campus') }}">Campus</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
