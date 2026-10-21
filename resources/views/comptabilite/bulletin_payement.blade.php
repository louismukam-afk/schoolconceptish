    <link href="{{URL::asset('css/recu_payement_salaire.css')}}" rel="stylesheet">
    <table class="table table-bordered text-center">
        <tr>
            <td rowspan="2" class="cel-etab text-uppercase"><strong>{{$etab->valeur}}</strong></td>
            <td colspan="3">@lang('admin.periode'): {{$payement->periode}}</td>
            <td colspan="3">@lang('admin.fonctions') :  @foreach($fonctions as $f)
                    @foreach($fonctions2 as $f2)
                        @if($f2->id==$f->id_fonction)
                            / {{$f2->nom}}
                        @endif
                    @endforeach
                @endforeach</td>
        </tr>
        <tr>
            <td><p>@lang('admin.matricule')</p> {{$personnel->matricule}}</td>
            <td><p>@lang('admin.categorie')</p>{{$personnel->categorie}}</td>
            <td><p>@lang('admin.num_contribuable')</p>{{$personnel->num_contribuable}}</td>
            <td><p>@lang('admin.echelon')</p>{{$personnel->echelon}}</td>
            <td><p>@lang('admin.anciennete')</p> {{$diff->format('%y '.trans('admin.an').' %m '.trans('admin.mois'))}}</td>
            <td><p>@lang('admin.num_ss')</p> {{$personnel->num_contribuable}}</td>
        </tr>
    </table>
 <div class="bulletin-paie">
     <div class="titre">
         <div style="font-size: 23px"><strong>BULLETIN DE PAIE</strong></div>
         <strong class="traduction">PAYSLIP</strong>
     </div>
     <div class="content-mois">
         <div class="mois">
             <p style="font-size: 18px"><strong>{{date('d').' '.getMois($payement->mois).' '.date('Y')}}</strong></p>
             <strong class="traduction">{{date('d').' '.getMoisEng($payement->mois).' '.date('Y')}}</strong>
         </div>
     </div>
     <div class="identite">
         <p><strong>Nom : {{$personnel->nom}}</strong></p>
         <strong class="traduction">Name</strong>
         @if(isset($user) && $user)
             <p><strong>Agent / Caissier : {{$user->fullname ? $user->fullname : $user->name}}</strong></p>
         @endif


     </div>

     <table class="table table-bordered table-striped table-condensed table-inverse" style="margin-top: 15px;">
         <thead>
         <th></th>
         <th></th>
         <th><p><strong class="text-uppercase">gain</strong></p></th>
         <th><p><strong class="text-uppercase">@lang('admin.retenues')</strong></p></th>
         </thead>
         <tbody>
         <tr class="table-title"><td colspan="4" class="text-center" style="font-size: 12px;"><strong>@lang('admin.mensuel')</strong></td></tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.taux_horaire')</strong></p></td>
                 <td>{{$payement->taux.' '.trans('inscription.devise').' / '.trans('admin.heure')}}</td>
                 <td></td>
                 <td></td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.heure_realise')</strong></p></td>
                 <td>{{$payement->heure_realise}}</td>
                 <td></td>
                 <td></td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.solde_mensuel')</strong></p></td>
                 <td></td>
                 <td>{{$payement->taux*$payement->heure_realise.' '.trans('inscription.devise')}}</td>
                 <td></td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.salaire_base')</strong></p></td>
                 <td></td>
                 <td>{{$salaire->montant.' '.trans('inscription.devise')}}</td>
                 <td></td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.primes')</strong></p></td>
                 <td></td>
                 <td>{{$payement->primes.' '.trans('inscription.devise')}}</td>
                 <td></td>
             </tr>
         <tr class=" table-title"><td colspan="4" class="text-center" style="font-size: 12px"><strong>@lang('admin.impots')</strong></td></tr>
         <tr>
             <td><p><strong class="text-uppercase">IRPP</strong></p></td>
             <td></td>
             <td></td>
             <td>{{$payement->IRPP.' '.trans('inscription.devise')}}</td>
         </tr>
         <tr>
             <td><p><strong class="text-uppercase">CAC IRPP</strong></p></td>
             <td></td>
             <td></td>
             <td>{{$payement->CAC.' '.trans('inscription.devise')}}</td>
         </tr>
         <tr>
             <td><p><strong class="text-uppercase">@lang('admin.CFC')</strong></p></td>
             <td></td>
             <td></td>
             <td>{{$payement->CFC.' '.trans('inscription.devise')}}</td>
         </tr>

         <tr>
             <td><p><strong class="text-uppercase">CRTV</strong></p></td>
             <td></td>
             <td></td>
             <td>{{$payement->CRTV.' '.trans('inscription.devise')}}</td>
         </tr>
         <tr>
             <td><p><strong class="text-uppercase">@lang('admin.taxe_communale')</strong></p></td>
             <td></td>
             <td></td>
             <td>{{$payement->pension.' '.trans('inscription.devise')}}</td>
         </tr>
         <tr class=" table-title"><td colspan="4" class="text-center" style="font-size: 12px"><strong>@lang('admin.autre_retenues')</strong></td></tr>

         <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.acomptes')</strong></p></td>
                 <td></td>
                 <td></td>
                 <td>{{$payement->acomptes.' '.trans('inscription.devise')}}</td>
             </tr>
             <tr>
                 <td><p><strong>CNPS</strong></p></td>
                 <td></td>
                 <td></td>
                 <td>{{$payement->cnps.' '.trans('inscription.devise')}}</td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.cas_sociaux')</strong></p></td>
                 <td></td>
                 <td></td>
                 <td>{{$payement->cas_sociaux.' '.trans('inscription.devise')}}</td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.assurances')</strong></p></td>
                 <td></td>
                 <td></td>
                 <td>{{$payement->assurances.' '.trans('inscription.devise')}}</td>
             </tr>
             <tr>
                 <td><p><strong class="text-uppercase">@lang('admin.autre_retenues')</strong></p></td>
                 <td></td>
                 <td></td>
                 <td>{{$payement->autre_retenues.' '.trans('inscription.devise')}}</td>
             </tr>
             <tr>
                 <td>TOTAL</td>
                 <td></td>
                 <td>{{$payement->total.' '.trans('inscription.devise')}}</td>
                 <td>{{$payement->total_retenu.' '.trans('inscription.devise')}}</td>
             </tr>
         <tr>
             <td><p><strong class="text-uppercase">@lang('admin.net_a_payer')</strong></p></td>
             <td>{{$payement->net_a_payer.' '.trans('inscription.devise')}}</td>
         </tr>
         </tbody>
     </table>
 </div>



<?php
    function getMois($mois){
        if($mois==1) return 'Janvier';
        if($mois==2) return 'Fevrier';
        if($mois==3) return 'Mars';
        if($mois==4) return 'Avril';
        if($mois==5) return 'Mai';
        if($mois==6) return 'Juin';
        if($mois==7) return 'Juillet';
        if($mois==8) return 'Aout';
        if($mois==9) return 'Septembre';
        if($mois==10) return 'Octobre';
        if($mois==11) return 'Novembre';
        if($mois==12) return 'Décembre';
    }
function getMoisEng($mois){
    if($mois==1) return 'January';
    if($mois==2) return 'February';
    if($mois==3) return 'March';
    if($mois==4) return 'April';
    if($mois==5) return 'Mai';
    if($mois==6) return 'June';
    if($mois==7) return 'July';
    if($mois==8) return 'August';
    if($mois==9) return 'September';
    if($mois==10) return 'October';
    if($mois==11) return 'November';
    if($mois==12) return 'Décember';
}
?>

    @section('breadcrumb')
        <li><strong><a href="{{url('comptabilite')}}">@lang('admin.comptabilite')</a></strong></li>
        <li><strong><a href="{{url('comptabilite/payement-salaire')}}">@lang('admin.search_personnel')</a></strong></li>

        <li class="active"><strong>Bulletin de paie</strong></li>
    @endsection
