@extends('skeleton')
@section('sub-content')
<div class="col-md-10">
    @if(isset($success))<div class="alert alert-success">{{ trans('admin.succes') }}</div>@endif
    <div class="btn-group" style="margin-bottom:10px">
        <a class="btn btn-default" href="{{ url('/admin/tutelles') }}"><span class="fa fa-arrow-left"></span> Tutelles</a>
        <a class="btn btn-primary" href="{{ url('/admin/campus') }}"><span class="fa fa-university"></span> Campus</a>
        <a class="btn btn-default" href="{{ url('/admin') }}"><span class="fa fa-home"></span> Tableau de bord</a>
    </div>
    <h3>{{ $titre }} : {{ $tutelle ? $tutelle->nom : '' }}</h3>
    <form method="post" action="{{ url('/admin/tutelles/specialites/store') }}">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="id_tutelle" value="{{ $tutelle ? $tutelle->id : '' }}">
        <table class="table table-bordered table-condensed">
            <thead><tr><th></th><th>Code</th><th>Specialite</th></tr></thead>
            <tbody>
            @foreach($classes as $c)
                <tr><td><input type="checkbox" name="classes[]" value="{{ $c->id }}" @if(in_array($c->id, $selected)) checked @endif></td><td>{{ $c->code }}</td><td>{{ $c->getNom() }}</td></tr>
            @endforeach
            </tbody>
        </table>
        <button class="btn btn-success">Enregistrer les affectations</button>
    </form>
</div>
@endsection

@section('breadcrumb')
<li><strong><a href="{{ url('admin') }}">@lang('admin.dashboard')</a></strong></li>
<li><strong><a href="{{ url('/admin/tutelles') }}">Tutelles academiques</a></strong></li>
<li class="active"><strong>{{ $titre }}</strong></li>
@endsection
