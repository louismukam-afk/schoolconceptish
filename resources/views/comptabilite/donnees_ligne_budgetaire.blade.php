@extends('skeleton')
@section('sub-content')

    <div class="add-container col-md-12" style="margin-top: 25px">
        <div class="row">
            <h3 class="col-md-10">{{ $budget_line->libelle_ligne }} ({{ number_format($budget_line->total, 0, '.', ' ') }} @lang('inscription.devise'))</h3>

            <div class="col-md-2" style="display: none">
                <a href="#" class="btn-xs btn btn-danger"><span class="glyphicon glyphicon-trash"></span> @lang('admin.del')</a>

            </div>
        </div>

        <hr>
        <form accept-charset="UTF-8" role="form" method="POST" id="add_level_form" action="{{URL::to('/comptabilite/donnee/ligne/'. $budget_line->id)}}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <fieldset>
                <?php $i=0; ?>
                @foreach($budget_categories as $bc)
                <?php $selectedData = $budget_line->donneesLigneBudgetaire->where('id_categorie_budgetaire', $bc->id)->first(); ?>

                    <div class="row col-md-8" >

                        <div class="form-group col-md-6 ">
                            <input type="checkbox" name="categorie[]" value="{{$bc->id}}" onchange="onCheckedChange(this,'montant{{$i}}')" tabindex="-1" @if($selectedData) checked @endif>
                            <label >{{ $bc->libelle }} : </label>
                        </div>

                        <div class="form-group col-md-4 row">
                            <input type="number" id="montant{{$i}}" name="montant[]"  class="form-control" value="@if($selectedData){{ $selectedData->montant }}@endif" @if(!$selectedData) disabled @endif required>
                        </div>

                    </div>
                    <?php $i++; ?>
                @endforeach

            </fieldset>
            @if($i!=0)
            <div class="col-md-7">

                <input type="submit" value="{{trans('admin.confirmer')}}" class="btn btn-success center-block btn-lg">
                <h3 class="alert alert-info"> @lang('admin.allocated'): {{ number_format($allocated, 0, '.', ' ') }} @lang('inscription.devise')</h3>
                <h3 class="alert alert-danger"> @lang('admin.not_allocated'): {{ number_format($budget_line->total - $allocated, 0, '.', ' ') }} @lang('inscription.devise')</h3>

            </div>
                @else
                <div class="alert alert-info">
                    {{trans('admin.no_budget_cat')}}
                </div>
            @endif

        </form>
    </div>
    <script>
        function onCheckedChange(checkElt,input1) {
            if(checkElt.checked){

                document.getElementById(input1).disabled=false;

            }else{

                document.getElementById(input1).disabled=true;
            }
        }
    </script>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>
    <li><strong><a href="{{url('comptabilite/ligne')}}">@lang('admin.ligne_budget')</a></strong></li>
    <li class="active"><strong>{{$titre}}</strong></li>
@endsection