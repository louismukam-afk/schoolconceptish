<form method="get" action="{{ url('/eleves/notes/anonymats') }}">
@foreach(array('modele','id_tutelle','id_classe','id_cycle','id_nniveau','periode')as $key)
<input type="hidden" name="{{ $key }}" value="{{ $context[$key] }}">
@endforeach
<div class="row"><div class="col-md-5 form-group"><label>Matière / session d’examen</label><select name="id_session" class="form-control" required><option value="">Choisir</option>
@foreach($sessions as $s)
<option value="{{ $s->id }}" {{ $selected && $selected->id==$s->id?'selected':'' }}>{{ $s->nom }} · S{{ $s->semestre }} · session {{ $s->id }}</option>
@endforeach
</select></div><div class="col-md-3 form-group"><label>Évaluation</label><select name="sequence" class="form-control">
@foreach(array(3=>'Session normale impaire',4=>'Session normale paire',5=>'Rattrapage impair',6=>'Rattrapage pair')as $id=>$name)
<option value="{{ $id }}" {{ $sequence==$id?'selected':'' }}>{{ $name }}</option>
@endforeach
</select></div><div class="col-md-3 form-group"><label>Attribution des codes</label><select name="mode_generation" class="form-control"><option value="matiere" {{ $mode==='matiere'?'selected':'' }}>Un code par UV / matière</option><option value="examen" {{ $mode==='examen'?'selected':'' }}>Un code commun aux UV du semestre</option></select></div><div class="col-md-1"><br><button class="btn btn-primary">Afficher</button></div></div>
</form>