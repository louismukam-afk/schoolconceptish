@extends('skeleton')
@section('sub-content')
    @if(isset($success))

        <div class="alert alert-success">
            {{  trans('admin.succes')  }}
        </div>

    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <strong>Acces etudiant aux notes par campus</strong>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('student_portal_lock_store') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Campus</label>
                            <select name="id_campus" class="form-control" required>
                                @foreach($campus as $camp)
                                    <option value="{{ $camp->id }}" @if($camp->id == $current_campus_id) selected @endif>{{ $camp->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Date de fermeture</label>
                            <input type="datetime-local" name="starts_at" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Date de reouverture</label>
                            <input type="datetime-local" name="ends_at" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label>Objectif</label>
                            <input type="text" name="objectif" class="form-control" value="Publication des notes en cours" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <label>Message affiche aux etudiants</label>
                        <textarea name="message" class="form-control" rows="3" required>La plateforme de consultation des notes est temporairement fermee afin de permettre la saisie et le controle des nouvelles notes. Merci de revenir apres la reouverture annoncee.</textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <span class="glyphicon glyphicon-ban-circle"></span> Bloquer l acces etudiant
                    </button>
                </form>

                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered table-condensed">
                        <thead>
                        <tr>
                            <th>Campus</th>
                            <th>Objectif</th>
                            <th>Debut</th>
                            <th>Fin</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($student_portal_locks as $lock)
                            <tr>
                                <td>{{ $lock->id_campus }}</td>
                                <td>{{ $lock->objectif }}</td>
                                <td>{{ $lock->starts_at }}</td>
                                <td>{{ $lock->ends_at }}</td>
                                <td>
                                    @if($lock->is_active)
                                        <span class="label label-danger">Bloque</span>
                                    @else
                                        <span class="label label-success">Ouvert</span>
                                    @endif
                                </td>
                                <td>
                                    @if($lock->is_active)
                                        <a class="btn btn-xs btn-success" href="{{ route('student_portal_lock_open', $lock->id) }}">
                                            <span class="glyphicon glyphicon-ok"></span> Rouvrir
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Aucun blocage etudiant configure.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <h3 class="page-header  alert alert-warning">@lang('semestre') 1</h3>
        <ul>
            <li><strong class="col-md-6">CC</strong>
                <?php $finded=false; ?>
            @foreach($verrous as $v)
                @if($v->sequence==1)
                        <a href="{{url('/admin/verrou-note/activate/1')}}" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok"></span> @lang('admin.activer')</a>
                    <?php $finded=true;break; ?>
                    @endif
                @endforeach
                @if(!$finded)
                    <a href="{{url('/admin/verrou-note/deactivate/1')}}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> @lang('admin.desactiver')</a>
                @endif
            </li>
            <li><strong class="col-md-6">@lang('admin.session_normale')</strong>
                <?php $finded=false; ?>
                @foreach($verrous as $v)
                    @if($v->sequence==3)
                        <a href="{{url('/admin/verrou-note/activate/3')}}" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok"></span> @lang('admin.activer')</a>
                        <?php $finded=true;break; ?>
                    @endif
                @endforeach
                @if(!$finded)
                    <a href="{{url('/admin/verrou-note/deactivate/3')}}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> @lang('admin.desactiver')</a>
                @endif</li>
            <li><strong class="col-md-6">@lang('admin.rattrapage')</strong>
                <?php $finded=false; ?>
                @foreach($verrous as $v)
                    @if($v->sequence==5)
                        <a href="{{url('/admin/verrou-note/activate/5')}}" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok"></span> @lang('admin.activer')</a>
                        <?php $finded=true;break; ?>
                    @endif
                @endforeach
                @if(!$finded)
                    <a href="{{url('/admin/verrou-note/deactivate/5')}}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> @lang('admin.desactiver')</a>
                @endif</li>
        </ul>
    </div>
<div class="col-md-6">
    <h3 class="page-header  alert alert-warning">@lang('semestre') 2</h3>
    <ul>
        <li><strong class="col-md-6">CC</strong>
            <?php $finded=false; ?>
            @foreach($verrous as $v)
                @if($v->sequence==2)
                    <a href="{{url('/admin/verrou-note/activate/2')}}" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok"></span> @lang('admin.activer')</a>
                    <?php $finded=true;break; ?>
                @endif
            @endforeach
            @if(!$finded)
                <a href="{{url('/admin/verrou-note/deactivate/2')}}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> @lang('admin.desactiver')</a>
            @endif</li>
        <li><strong class="col-md-6">@lang('admin.session_normale')</strong>
            <?php $finded=false; ?>
            @foreach($verrous as $v)
                @if($v->sequence==4)
                    <a href="{{url('/admin/verrou-note/activate/4')}}" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok"></span> @lang('admin.activer')</a>
                    <?php $finded=true;break; ?>
                @endif
            @endforeach
            @if(!$finded)
                <a href="{{url('/admin/verrou-note/deactivate/4')}}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> @lang('admin.desactiver')</a>
            @endif</li>
        <li><strong class="col-md-6">@lang('admin.rattrapage')</strong>
            <?php $finded=false; ?>
            @foreach($verrous as $v)
                @if($v->sequence==6)
                    <a href="{{url('/admin/verrou-note/activate/6')}}" class="btn btn-xs btn-success"><span class="glyphicon glyphicon-ok"></span> @lang('admin.activer')</a>
                    <?php $finded=true;break; ?>
                @endif
            @endforeach
            @if(!$finded)
                <a href="{{url('/admin/verrou-note/deactivate/6')}}" class="btn btn-xs btn-danger"><span class="glyphicon glyphicon-ban-circle"></span> @lang('admin.desactiver')</a>
            @endif</li>
    </ul>
</div>


@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
