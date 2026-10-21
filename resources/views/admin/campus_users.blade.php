@extends('skeleton')
@section('sub-content')
<div class="col-md-10">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin/campus') }}"><span class="fa fa-arrow-left"></span> Campus</a>
        @if($campus)
            <a class="btn btn-primary" href="{{ url('/admin/campus/'.$campus->id.'/specialites') }}"><span class="fa fa-graduation-cap"></span> Specialites</a>
        @endif
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-home"></span> Tableau de bord</a>
    </div>
    <h3>{{ $titre }} : {{ $campus ? $campus->nom : '' }}</h3>
    <form method="post" action="{{ url('/admin/campus/users/store') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id_campus" value="{{ $campus ? $campus->id : '' }}">
        <table class="table table-bordered table-condensed">
            <thead><tr><th></th><th>Utilisateur</th><th>Nom complet</th><th>Actif</th></tr></thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td><input type="checkbox" name="users[]" value="{{ $u->id }}" @if(in_array($u->id, $selected)) checked @endif></td>
                    <td>{{ $u->name }}</td><td>{{ $u->fullname }}</td><td>{{ $u->active ? 'Oui' : 'Non' }}</td>
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
