@extends('skeleton')
@section('sub-content')

    <div class="col-md-10">
        @if(isset($success))

            <div class="alert alert-success">
                {{  trans('admin.succes')  }}
            </div>

        @endif
    </div>
    @if($salaire!=null)
    <div class="col-md-8">
        <div class="panel panel-success">
            <div class="panel-heading">
                <div class="panel-title">
                   <h3>{{trans('admin.resume')}}</h3>
                </div>
            </div>
            <div class="panel-body">
                <h4><strong>Nom :</strong> {{$personnel->nom}}</h4>
                @if(isset($dernier_payement))
                    <h4>{{trans('admin.date_dernier_payement').': '.$dernier_payement->date_p}}</h4>
                @endif
                <h5 class="alert alert-info"><strong>{{trans('admin.depuis_dernier_payement')}}</strong></h5>
                <p><strong>{{trans('admin.abscences')}} :</strong>

                    <?php $s=0; ?>
                    @foreach($discipline as $d)
                        @if($d->type==0)
                            <?php $s+=$d->nombre; ?>
                        @endif
                    @endforeach
                    {{$s.' '.trans('admin.heure')}}
                </p>
                <p><strong>{{trans('admin.retards')}} :</strong>  {{$discipline->where('type',1,false)->count()}}</p>

                <div class="alert alert-warning"><strong>{{trans('admin.salaire')}}</strong>
                    <p><strong>{{$salaire->montant.' '.trans('inscription.devise')}}</strong></p>

                </div>
                <form accept-charset="UTF-8" role="form" method="POST" action="{{ url('/comptabilite/payement-salaire/store/') }}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="{{$personnel->id }}">
                    <fieldset>
                        <div class="form-group">
                            <label>{{  trans('admin.montant_variations')  }} : </label>
                            <div class="input-group col-md-3">
                                <input class="form-control" name="variations" type="number"  value="{{ old('variations') }}">
                                <span class="input-group-addon"> {{trans('inscription.devise')}}</span>
                            </div>

                        </div>

                        <input class="btn  btn-success " type="submit" value="{{  trans('admin.confirmer')  }}">


                    </fieldset>
                </form>
            </div>

        </div>
    </div>
    @else
        <div class="alert alert-danger">{{trans('admin.salaire_non_defini')}}</div>
    @endif


@endsection