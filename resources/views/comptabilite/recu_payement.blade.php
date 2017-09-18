
    @include('perso.header')
    <link href="{{URL::asset('css/recu_payement_salaire.css')}}" rel="stylesheet">
    <div class="titre-salaire">
        <strong>
            <p>RECU DE PAIEMENT DE SALAIRE</p>
            <p class="traduction">SALARY PAYMENT RECEIPT</p>
        </strong>
    <div class="numero-container">
            <p class="numero">N° {{$payement->numero}}</p>
    </div>
    </div>

    <div class="body-salaire">
        <p>Mr/Mme <strong>{{$personnel->nom}}</strong> atteste avoir recu paiement :</p>
        <p class="traduction">Mr/Mrs <strong class="traduction">{{$personnel->nom}}</strong> certifies having received payment: :</p>

        <p class="from" style="margin-top: 20px"><strong>DE :</strong> {{$user->fullname}}</p>
        <p class="traduction"><strong class="traduction">FROM :</strong> {{$user->fullname}}</p>

        <p style="margin-top: 20px"><strong>DU MONTANT DE :</strong> {{$payement->montant}} @lang('inscription.devise')</p>
        <p class="traduction"><strong class="traduction">OF THE AMOUNT OF :</strong> {{$payement->montant}} @lang('inscription.devise')</p>

        <p style="margin-top: 20px"><strong>POUR :</strong> Salaire</p>
        <p class="traduction"><strong class="traduction">FOR :</strong> Salary</p>

        <p style="margin-top: 20px"><strong>A LA DATE :</strong> {{(new DateTime($payement->date_p))->format('d/m/Y')}}</p>
        <p class="traduction"><strong class="traduction">AT THE DATE :</strong> {{(new DateTime($payement->date_p))->format('d/m/Y')}}</p>

        <p class="fait-le" style="margin-top: 40px"><strong>Fait le {{date('d/m/Y')}}</strong></p>
        <p class="traduction" ><strong class="traduction">Made on {{date('d/m/Y')}}</strong></p>

        <p class="signature" style="margin-top: 20px"><strong>Signature</strong></p>

    </div>
