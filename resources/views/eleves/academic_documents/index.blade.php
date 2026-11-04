@extends('skeleton')
@section('sub-content')
<h3>Notes, délibérations et documents par tutelle</h3>
@if(session('deliberation_selection') || !empty($selection['choisir_deliberation']))<div class="alert alert-info">Choisissez la tutelle et le contexte, puis ouvrez « Délibérations » ou « Rapport des délibérations ».</div>@endif
@if(session('anonymat_selection'))<div class="alert alert-info">Choisissez le contexte, puis cliquez sur « Gérer les anonymats ».</div>@endif
@if(isset($selection['choisir_tutelle']))<div class="alert alert-info">Cette formation utilise des sessions par tutelle. Choisissez la tutelle puis utilisez la saisie des notes de cet écran.</div>@endif
<p><strong>Campus :</strong> {{ $campus ? $campus->nom : '' }} · <strong>Année :</strong> {{ session('year') }}</p>
@if($errors->any())<div class="alert alert-danger">{{ implode(' · ', $errors->all()) }}</div>@endif
<p>Configurez d’abord la tutelle et ses spécialités, affectez les spécialités au campus, puis définissez les programmes et les sessions d’examen pour la tutelle et l’année choisies.</p>
<div class="btn-group" role="group" aria-label="Tutelles">
@foreach($profiles as $key => $profile)
    <button type="button" class="btn btn-primary academic-profile" data-profile="{{ $key }}">{{ $profile['label'] }}</button>
@endforeach
</div>
<p style="margin-top:12px"><a href="{{ url('/eleves/documents-tutelles/historique') }}">Consulter tous les historiques du campus et de l’année sélectionnés</a></p>
<hr>
<form id="academic-context" method="get" action="{{ url('/eleves/documents-tutelles/apercu') }}" target="_blank">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="row">
        <div class="col-md-4 form-group"><label>Modèle</label><select name="modele" id="academic-profile" class="form-control" required>
            @foreach($profiles as $key => $profile)<option value="{{ $key }}" {{ isset($selection['modele']) && $selection['modele'] === $key ? 'selected' : '' }}>{{ $profile['label'] }}</option>@endforeach
        </select></div>
        <div class="col-md-4 form-group"><label>Tutelle configurée</label><select name="id_tutelle" id="academic-tutelle" class="form-control" required>
            <option value="">Sélectionnez la tutelle</option>
            @foreach($tutelles as $t)<option value="{{ $t->id }}" data-profile="{{ $t->modele_documents }}" {{ isset($selection['id_tutelle']) && $selection['id_tutelle'] == $t->id ? 'selected' : '' }}>{{ $t->nom }}</option>@endforeach
        </select></div>
        <div class="col-md-4 form-group"><label>Spécialité du campus</label><select name="id_classe" id="academic-class" class="form-control" required><option value="">Sélectionnez la spécialité</option>
            @foreach($classes as $c)<option value="{{ $c->id }}" data-tutelles="{{ isset($affectations[$c->id])?implode(',',$affectations[$c->id]):'' }}" {{ isset($selection['id_classe']) && $selection['id_classe'] == $c->id ? 'selected' : '' }}>{{ $c->nom }} · {{ $c->code }} · {{ $c->filiere_nom }}</option>@endforeach
        </select></div>
    </div>
    <div class="row">
        <div class="col-md-3 form-group"><label>Cycle</label><select name="id_cycle" id="academic-cycle" class="form-control" required><option value="">Choisir</option>
            @foreach($cycles as $c)<option value="{{ $c->id }}" {{ isset($selection['id_cycle']) && $selection['id_cycle'] == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>@endforeach
        </select></div>
        <div class="col-md-3 form-group"><label>Niveau</label><select name="id_nniveau" id="academic-level" class="form-control" required><option value="">Choisir</option>
            @foreach($nniveaux as $n)<option value="{{ $n->id }}" data-cycle="{{ $n->id_cycle }}" {{ isset($selection['id_nniveau']) && $selection['id_nniveau'] == $n->id ? 'selected' : '' }}>{{ $n->nom }}</option>@endforeach
        </select></div>
        <div class="col-md-3 form-group"><label>Période</label><select name="periode" id="academic-period" class="form-control"><option value="annuel">Année complète</option><option value="s1">Semestre impair (S1 / S3 / S5…)</option><option value="s2">Semestre pair (S2 / S4 / S6…)</option></select></div>
        <div class="col-md-3 form-group"><label>Résultats</label><select name="session_mode" class="form-control"><option value="final">Avec rattrapage</option><option value="normal">Session normale uniquement</option></select></div>
    </div>
    <div class="row">
        <div class="col-md-6 form-group"><label>Document à produire</label><select name="document" id="academic-document" class="form-control">@foreach($documents as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2 form-group"><label>Format papier</label><select name="page_size" class="form-control"><option value="a4" {{ !isset($selection['page_size']) || $selection['page_size']!=='a3'?'selected':'' }}>A4</option><option value="a3" {{ isset($selection['page_size']) && $selection['page_size']==='a3'?'selected':'' }}>A3</option></select><small>Textes et tableaux adaptés au format choisi.</small></div>
        <div class="col-md-4 form-group"><label>Date de soutenance (attestation)</label><input type="date" name="date_soutenance" class="form-control"></div>
    </div>
    <div id="academic-attestation-options" style="display:none">
        <p class="help-block">Attestations SOA, ESSTIC, ISABEE, Ngaoundéré et Polytechnique : présentation bilingue encadrée, en paysage. Pour une référence individuelle, sélectionnez un seul étudiant. Seuls les étudiants admis sont édités.</p>
        <div class="row">
            <div class="col-md-4 form-group"><label>Référence de l’attestation (facultatif)</label><input name="attestation_reference" maxlength="80" class="form-control" disabled></div>
            <div class="col-md-4 form-group"><label>Date du procès-verbal du jury (facultatif)</label><input type="date" name="attestation_jury_date" class="form-control" disabled><small>Non renseignée : emplacement à compléter.</small></div>
            <div class="col-md-4 form-group"><label>Date de délivrance (facultatif)</label><input type="date" name="attestation_issue_date" class="form-control" disabled><small>Par défaut : date du jour.</small></div>
        </div>
    </div>
    <div id="academic-anonymat-options" class="row" style="display:none">
        <div class="col-md-6 form-group"><label>Évaluation de la fiche d’anonymat</label><select name="sequence" class="form-control" disabled><option value="3">Session normale impaire</option><option value="4">Session normale paire</option><option value="5">Rattrapage impair</option><option value="6">Rattrapage pair</option></select></div>
        <div class="col-md-6 form-group"><label>Codes à utiliser</label><select name="mode_generation" class="form-control" disabled><option value="matiere">Codes par matière</option><option value="examen">Codes communs au semestre</option></select></div>
    </div>
    <p class="help-block">Pour une fiche de présence, d’anonymat ou de report, choisissez la matière concernée. Les PV, listes d’admission et attestations tiennent toujours compte du programme complet.</p>
    <div class="row"><div class="col-md-6 form-group"><label>Matière (facultatif)</label><select name="id_session" id="academic-session" class="form-control"><option value="">Toutes les matières</option></select></div><div class="col-md-6 form-group"><label>Étudiant (facultatif)</label><select name="id_eleve" id="academic-student" class="form-control"><option value="">Tous les étudiants</option></select><small id="academic-student-status"></small></div></div>
    <p class="help-block">Les modèles « PV par modules » et « Relevés par modules » affichent les UV puis la moyenne de leur groupe de matières. Configurez les regroupements dans les programmes, pour le niveau et le semestre concernés ; une UV sans groupe reste affichée séparément. Les anciens modèles restent disponibles.</p>
    <div id="academic-rules" class="alert alert-info"></div>
    <div id="academic-context-status" class="alert alert-warning" role="status" style="display:none"></div>
    <button class="btn btn-default" type="submit">Afficher l’aperçu</button>
    <button class="btn btn-default" type="submit" formaction="{{ url('/eleves/documents-tutelles/export') }}">Exporter en CSV / Excel</button>
    @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU))
        <button class="btn btn-success" type="submit" formmethod="post" formaction="{{ url('/eleves/documents-tutelles/generer') }}">Générer et archiver</button>
        <button class="btn btn-primary" type="submit" formaction="{{ url('/eleves/documents-tutelles/notes') }}" formtarget="_self">Créer / modifier / supprimer des notes</button>
    @endif
    @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU))
    <button class="btn btn-warning" type="submit" formaction="{{ url('/eleves/notes/anonymats') }}" formtarget="_self">Gérer les anonymats</button>
    @endif
    @if(Auth::user()->canDoAction(\gesecole\Action::ACTION_STUDENT_NOTE_CU) && !Auth::user()->isTeacher())
    <button class="btn btn-success" type="submit" formaction="{{ url('/eleves/notes/deliberations') }}" formtarget="_self">Délibérations</button>
    <button class="btn btn-info" type="submit" formaction="{{ url('/eleves/notes/deliberations/rapport') }}" formtarget="_self" formnovalidate data-context-optional="true">Rapport des délibérations</button>
    @endif
    <button class="btn btn-info" type="submit" formaction="{{ url('/eleves/documents-tutelles/historique') }}" formtarget="_self" formnovalidate data-context-optional="true">Historique de cette tutelle</button>
</form>
<script>
(function(){
    var profiles = {!! json_encode($profiles) !!}, choices = {!! json_encode($contextChoices) !!};
    var profile=document.getElementById('academic-profile'), tutelle=document.getElementById('academic-tutelle'), cycle=document.getElementById('academic-cycle'), level=document.getElementById('academic-level'), classe=document.getElementById('academic-class'), session=document.getElementById('academic-session');
    var student=document.getElementById('academic-student'), studentStatus=document.getElementById('academic-student-status'), studentRequest=0;
    function chooseSingle(select){var options=[].filter.call(select.options,function(o){return o.value && !o.disabled;});if(!options.some(function(o){return o.value===select.value;}))select.value='';if(!select.value && options.length===1)select.value=options[0].value;}
    function refresh(){
        [].forEach.call(tutelle.options,function(o){o.hidden=o.value && o.dataset.profile!==profile.value;o.disabled=o.hidden;});
        chooseSingle(tutelle);
        [].forEach.call(classe.options,function(o){o.hidden=o.value && o.dataset.tutelles.split(',').indexOf(tutelle.value)<0;o.disabled=o.hidden;});chooseSingle(classe);
        var available=choices.filter(function(c){return String(c.id_tutelle)===tutelle.value && String(c.id_classe)===classe.value;});
        [].forEach.call(cycle.options,function(o){o.hidden=o.value && !available.some(function(c){return String(c.id_cycle)===o.value;});o.disabled=o.hidden;});chooseSingle(cycle);
        [].forEach.call(level.options,function(o){o.hidden=o.value && (o.dataset.cycle!==cycle.value || !available.some(function(c){return String(c.id_nniveau)===o.value;}));o.disabled=o.hidden;});chooseSingle(level);
        var status=document.getElementById('academic-context-status');
        status.textContent=tutelle.value && classe.value && !available.length?'Aucune session d’examen n’est configurée pour cette tutelle et cette spécialité dans l’année sélectionnée. Configurez les sessions ou choisissez une autre année.':(!cycle.value || !level.value?'Choisissez le cycle et le niveau pour ouvrir les notes ou produire un document.':'');
        status.style.display=status.textContent?'':'none';
        var selectedSession=session.value;while(session.options.length>1)session.remove(1);
        available.filter(function(c){return String(c.id_cycle)===cycle.value && String(c.id_nniveau)===level.value;}).forEach(function(c){var o=document.createElement('option');o.value=c.id;o.textContent=c.nom+' · S'+c.semestre;session.appendChild(o);});session.value=selectedSession;if(session.selectedIndex<0)session.selectedIndex=0;
        var request=++studentRequest;while(student.options.length>1)student.remove(1);studentStatus.textContent='';
        if(tutelle.value && classe.value && cycle.value && level.value){
            var query=new URLSearchParams({modele:profile.value,id_tutelle:tutelle.value,id_classe:classe.value,id_cycle:cycle.value,id_nniveau:level.value});studentStatus.textContent='Chargement des étudiants…';
            fetch({!! json_encode(url('/eleves/documents-tutelles/etudiants')) !!}+'?'+query.toString(),{headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){if(!r.ok)throw Error();return r.json();}).then(function(rows){if(request!==studentRequest)return;rows.forEach(function(s){var o=document.createElement('option');o.value=s.id;o.textContent=s.label;student.appendChild(o);});studentStatus.textContent=rows.length+' étudiant(s) inscrit(s).';}).catch(function(){if(request===studentRequest)studentStatus.textContent='Liste indisponible. Vérifiez le contexte choisi.';});
        }
        var p=profiles[profile.value];
        document.getElementById('academic-rules').textContent='Règles du modèle : validation UE à 10/20 ; crédits capitalisés à partir de '+p.credit_threshold+'/20 ; '+(p.admission==='credits'?'admission à '+p.credits_required+' crédits.':'admission pour une moyenne strictement supérieure à '+p.average_required+'/20 ; égalité à délibérer.')+' Une note manquante laisse le dossier à délibérer.';
    }
    [].forEach.call(document.querySelectorAll('.academic-profile'),function(b){b.addEventListener('click',function(){profile.value=b.dataset.profile;refresh();});});
    [profile,tutelle,classe,cycle,level].forEach(function(e){e.addEventListener('change',refresh);});refresh();
    var certificateOptions=document.getElementById('academic-attestation-options');
    document.getElementById('academic-document').addEventListener('change',function(){var active=this.value==='attestation';certificateOptions.style.display=active?'':'none';[].forEach.call(certificateOptions.querySelectorAll('input'),function(s){s.disabled=!active;});});
    var anonOptions=document.getElementById('academic-anonymat-options');
    document.getElementById('academic-document').addEventListener('change',function(){var active=['anonymat_codes','anonymat_report','anonymat_presence','anonymat_fiches'].indexOf(this.value)>=0;anonOptions.style.display=active?'':'none';[].forEach.call(anonOptions.querySelectorAll('select'),function(s){s.disabled=!active;});});
    document.getElementById('academic-document').addEventListener('change',function(){var annual=['admis','merite','non_admis','a_deliberer','attestation'].indexOf(this.value)>=0,period=document.getElementById('academic-period');if(annual)period.value='annuel';[].forEach.call(period.options,function(o){o.disabled=annual&&o.value!=='annuel';});});
    document.getElementById('academic-context').addEventListener('formdata',function(e){[].forEach.call(this.elements,function(el){if(el.name && el.value==='')e.formData.delete(el.name);});});
    var submitter=null,form=document.getElementById('academic-context');
    [].forEach.call(form.querySelectorAll('button[type="submit"]'),function(button){button.addEventListener('click',function(){submitter=button;});});
    form.addEventListener('submit',function(e){
        var button=e.submitter || submitter;submitter=null;
        if(button && button.dataset.contextOptional==='true')return;
        var invalid=[profile,tutelle,classe,cycle,level].filter(function(select){var option=select.options[select.selectedIndex];return select.disabled || !option || !option.value || option.disabled;});
        if(invalid.length){e.preventDefault();var status=document.getElementById('academic-context-status');status.textContent='Sélectionnez la tutelle, la spécialité, le cycle et le niveau. Si aucun choix n’est disponible, vérifiez les sessions d’examen et l’année sélectionnée.';status.style.display='';invalid[0].focus();}
    });
})();
</script>
@endsection
