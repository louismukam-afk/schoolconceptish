<form method="GET" class="well">
    <div class="row">
        <div class="col-md-4">
            <label>Annee academique</label>
            <select name="annee_scolaire" class="form-control">
                @foreach($years as $year)
                    <option value="{{ $year }}" @if($selectedYear==$year) selected @endif>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label>&nbsp;</label>
            <button class="btn btn-primary form-control">Filtrer</button>
        </div>
    </div>
</form>
