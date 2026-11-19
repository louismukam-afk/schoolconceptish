@extends('skeleton')
@section('sub-content')
    <style>
        .user-action {
            margin-left: 15px;
            font-weight: 600;
            font-size: 11px;
        }

        fieldset h5 {
            color: black;
        }
    </style>
        <div class="col-md-12">
            @include('perso.functions')
            @if(isset($success))

                <div class="alert alert-success">
                    {{  trans('admin.succes')  }}
                </div>

            @endif

            <form action="{{route('update_actions')}}" class="form" method="POST" id="update-actions-form">
                <input type="hidden" name="id" value="{{$user->id}}">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <legend class="text-uppercase" style="margin-top: 15px">@lang('menu.admin')</legend>
                   <div class="col-md-12 alert alert-warning">
                       <div class="col-md-6">
                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.cycle') / @lang('admin.filiere') / @lang('admin.classe') / @lang('admin.salle') / @lang('admin.nniveau') / @lang('admin.matiere') / @lang('admin.programme')</strong></h5>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_CLASS_SETTINGS}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_CLASS_SETTINGS, $actions_array)) checked @endif> @lang('admin.read')</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_CLASS_SETTINGS_CU}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_CLASS_SETTINGS_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_CLASS_SETTINGS_DELETE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_CLASS_SETTINGS_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                               </div>

                           </fieldset>

                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.schoolfees') / @lang('admin.frais')</strong></h5>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_FEES_SETTINGS}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_FEES_SETTINGS, $actions_array)) checked @endif> @lang('admin.read')</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_FEES_SETTINGS_CU}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_FEES_SETTINGS_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_FEES_SETTINGS_DELETE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_FEES_SETTINGS_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                               </div>

                           </fieldset>

                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.verrou_note')</strong></h5>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_NOTE_LOCK}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_NOTE_LOCK, $actions_array)) checked @endif> @lang('admin.read')</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_NOTE_LOCK_UPDATE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, $actions_array)) checked @endif> @lang('admin.edit')</span>
                               </div>

                           </fieldset>

                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.horaire') / @lang('admin.fonctions')</strong></h5>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STAFF_SETTINGS}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STAFF_SETTINGS, $actions_array)) checked @endif> @lang('admin.read')</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STAFF_SETTINGS_CU}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STAFF_SETTINGS_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STAFF_SETTINGS_DELETE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STAFF_SETTINGS_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                               </div>

                           </fieldset>
                       </div>
                       <div class="col-md-6">

                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.salaire') / @lang('admin.taux_horaire')</strong></h5>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS, $actions_array)) checked @endif> @lang('admin.read')</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_CU}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_DELETE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                               </div>

                           </fieldset>


                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.utilisateurs')</strong></h5>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_USER}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_USER, $actions_array)) checked @endif> @lang('admin.read')</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_USER_ACTION}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_USER_ACTION, $actions_array)) checked @endif> @lang('admin.read') {{trans_choice('admin.action', 2)}}</span>
                                   @if(\Illuminate\Support\Facades\Auth::user()->isAdmin())
                                       <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_USER_ACTIVATE_DEACTIVATE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_USER_ACTIVATE_DEACTIVATE, $actions_array)) checked @endif> @lang('admin.activer')/@lang('admin.desactiver')</span>
                                       <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_USER_ACTION_CU}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_USER_ACTION_CU, $actions_array)) checked @endif> @lang('admin.edit') {{trans_choice('admin.action', 2)}}</span>
                                   @endif
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_TEACHER}}" @if(in_array(\gesecole\Action::ACTION_TEACHER, $actions_array)) checked @endif> {{trans('admin.enseignant')}}</span>
                               </div>

                           </fieldset>

                           <fieldset >
                               <div >
                                   <h5 class="text-uppercase"><strong>@lang('admin.autre')</strong></h5>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE, $actions_array)) checked @endif> @lang('admin.edit') @lang('admin.montant') @lang('admin.payment') </span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STUDENT_DELETE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STUDENT_DELETE, $actions_array)) checked @endif> @lang('admin.del') @lang('inscription.eleve')</span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE, $actions_array)) checked @endif> @lang('admin.del') {{trans('admin.inscriptions')}}</span>
                                   <span class="user-action admin-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, $actions_array)) checked @endif> @lang('admin.edit') {{trans('admin.status_eleve')}}</span>
                                   <span class="user-action admin-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ADMIN_SCHOOL_YEAR_CU}}" @if(in_array(\gesecole\Action::ACTION_ADMIN_SCHOOL_YEAR_CU, $actions_array)) checked @endif> @lang('admin.edit') {{trans('admin.year')}}</span>
                               </div>

                           </fieldset>
                       </div>
                   </div>




                <legend class="text-uppercase " style="margin-top: 15px">@lang('menu.gestion_inscriptions')</legend>
                <div class="col-md-12 alert alert-warning">
                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('inscription.eleve') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_STUDENT_CU}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_STUDENT_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                            </div>

                        </fieldset>


                        <fieldset>
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.inscriptions') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE, $actions_array)) checked @endif> @lang('admin.create')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_UPDATE}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_UPDATE, $actions_array)) checked @endif> @lang('admin.edit')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE, $actions_array)) checked @endif> @lang('admin.certificat_scolarite')</span>
                            </div>

                        </fieldset>

                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.payment') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_CREATE}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_CREATE, $actions_array)) checked @endif> @lang('admin.create')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT, $actions_array)) checked @endif> @lang('admin.print_receipt')</span>
                            </div>

                        </fieldset>
                    </div>
                    <div class="col-md-6">
                        <fieldset>
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.listes') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_LIST}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_LIST, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_LIST_TUITION_SHEET}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_LIST_TUITION_SHEET, $actions_array)) checked @endif> @lang('admin.fiche_scolarite')</span>
                            </div>

                        </fieldset>

                        <fieldset>
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.photo')  </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_STUDENT_PHOTO}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_STUDENT_PHOTO, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_STUDENT_PHOTO_CU}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_STUDENT_PHOTO_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                            </div>

                        </fieldset>

                        <fieldset>
                            <div >
                                <h5 class="text-uppercase"><strong>{{trans_choice('admin.carte_e', 1)}} </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_STUDENT_CARD}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_STUDENT_CARD, $actions_array)) checked @endif> @lang('inscription.imprimer')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_REGISTRATION_SCAN_CARD}}" @if(in_array(\gesecole\Action::ACTION_REGISTRATION_SCAN_CARD, $actions_array)) checked @endif> @lang('admin.scan_carte')</span>
                            </div>

                        </fieldset>
                    </div>




                </div>



                <legend class="text-uppercase " style="margin-top: 15px">@lang('admin.discipline')</legend>
                <div class="col-md-12 alert alert-warning">
                    <div class="col-md-12">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.discipline') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_DISCIPLINE}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_DISCIPLINE, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_DISCIPLINE_CU}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                <div><strong>Appel et etats disciplinaires (disciplineetd)</strong></div>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_CREATE, $actions_array)) checked @endif> Saisir l'appel</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_UPDATE, $actions_array)) checked @endif> Modifier l'appel</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_DISCIPLINE_ETD_REPORT, $actions_array)) checked @endif> Afficher et exporter les etats</span>
                            </div>

                        </fieldset>
                    </div>
                </div>

                <legend class="text-uppercase " style="margin-top: 15px">@lang('menu.gestion_notes')</legend>
                <div class="col-md-12 alert alert-warning">
                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.notes') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_NOTE}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_NOTE, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_NOTE_CU}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_NOTE_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                            </div>

                        </fieldset>
                    </div>


                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.releve_notes') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STUDENT_REPORT_NOTE}}" @if(in_array(\gesecole\Action::ACTION_STUDENT_REPORT_NOTE, $actions_array)) checked @endif> @lang('inscription.imprimer')</span>
                            </div>

                        </fieldset>
                    </div>



                </div>


                <legend class="text-uppercase " style="margin-top: 15px">@lang('menu.gestion_personnel')</legend>
                <div class="col-md-12 alert alert-warning">
                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.personnel') / @lang('admin.fonctions') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_WORKER}}" @if(in_array(\gesecole\Action::ACTION_STAFF_WORKER, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_WORKER_CU}}" @if(in_array(\gesecole\Action::ACTION_STAFF_WORKER_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                <span class="user-action inscription-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_WORKER_DELETE}}" @if(in_array(\gesecole\Action::ACTION_STAFF_WORKER_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                            </div>
                        </fieldset>

                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.emploi_temps')  </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_TIMETABLE}}" @if(in_array(\gesecole\Action::ACTION_STAFF_TIMETABLE, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_TIMETABLE_CU}}" @if(in_array(\gesecole\Action::ACTION_STAFF_TIMETABLE_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                <span class="user-action inscription-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_TIMETABLE_DELETE}}" @if(in_array(\gesecole\Action::ACTION_STAFF_TIMETABLE_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                            </div>
                        </fieldset>
                    </div>



                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.discipline')  </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_DISCIPLINE}}" @if(in_array(\gesecole\Action::ACTION_STAFF_DISCIPLINE, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_DISCIPLINE_CU}}" @if(in_array(\gesecole\Action::ACTION_STAFF_DISCIPLINE_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                            </div>
                        </fieldset>

                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.compte_heure')  </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_HOUR_COUNT}}" @if(in_array(\gesecole\Action::ACTION_STAFF_HOUR_COUNT, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_HOUR_COUNT_CU}}" @if(in_array(\gesecole\Action::ACTION_STAFF_HOUR_COUNT_CU, $actions_array)) checked @endif> @lang('admin.create_update')</span>
                                <span class="user-action inscription-check text-danger"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_STAFF_HOUR_COUNT_DELETE}}" @if(in_array(\gesecole\Action::ACTION_STAFF_HOUR_COUNT_DELETE, $actions_array)) checked @endif> @lang('admin.del')</span>
                            </div>
                        </fieldset>
                    </div>



                </div>



                <legend class="text-uppercase " style="margin-top: 15px">@lang('admin.comptabilite')</legend>
                <div class="col-md-12 alert alert-warning">
                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.retrait') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_WITHDRAWAL}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_WITHDRAWAL, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_WITHDRAWAL_CREATE}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_WITHDRAWAL_CREATE, $actions_array)) checked @endif> @lang('admin.create')</span>
                            </div>
                        </fieldset>


                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.pay_salaire') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_STAFF_PAYMENT}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_STAFF_PAYMENT, $actions_array)) checked @endif> @lang('admin.read')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_STAFF_PAYMENT_CU}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_STAFF_PAYMENT_CU, $actions_array)) checked @endif> @lang('admin.create')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_STAFF_PAYMENT_PRINT_RECEIPT}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_STAFF_PAYMENT_PRINT_RECEIPT, $actions_array)) checked @endif> @lang('inscription.imprimer') @lang('admin.pay_bull')</span>
                            </div>
                        </fieldset>

                    </div>



                    <div class="col-md-6">
                        <fieldset >
                            <div >
                                <h5 class="text-uppercase"><strong>@lang('admin.bilan_annuel') </strong></h5>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_ENTRY_BALANCE}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_ENTRY_BALANCE, $actions_array)) checked @endif> @lang('admin.bilan_entrees')</span>
                                <span class="user-action inscription-check text-primary"><input name="actions[]" type="checkbox" value="{{\gesecole\Action::ACTION_ACCOUNTING_OUTPUT_BALANCE}}" @if(in_array(\gesecole\Action::ACTION_ACCOUNTING_OUTPUT_BALANCE, $actions_array)) checked @endif> @lang('admin.bilan_sorties')</span>
                            </div>
                        </fieldset>

                    </div>



                </div>

            <div class="row">
                <div class="col-md-12">
                    <input type="submit" class="btn btn-primary"  value="@lang('admin.confirmer')" >
                </div>
            </div>

            </form>
        </div>
@endsection

@section('breadcrumb')
    <li><strong><a href="{{url('admin')}}">@lang('admin.dashboard')</a></strong></li>
    <li><strong><a href="{{url('utilisateurs')}}">@lang('admin.utilisateurs')</a></strong></li>

    <li class="active"><strong>{{$titre}}</strong></li>
@endsection
