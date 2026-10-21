<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use \gesecole\Action;


//allo

Route::group(['middleware'=>'auth'],function (){
    Route::group(['middleware'=>'dc'],function (){
        Route::get('/', 'WelcomeController@index');
        Route::get('home', 'WelcomeController@index');
        Route::get('/select-campus', 'WelcomeController@selectCampus');
        Route::post('/select-campus', 'WelcomeController@setCampus');
        Route::get('/admin/nniveaux/get/{id}', 'admin\nniveauController@get');
        Route::get('/admin/cycles/get-all', ['uses' => 'admin\cycleController@getAll', 'as' => 'get_all_cycles']);

        Route::post('/admin/scolarite/get', ['as'=>'get_scolarite','uses'=>'admin\scolariteController@getScolarite']);

        Route::get('/not-authorized',function(){
            return view('not_auth',['titre'=>'']);
        });
            //pour les recherches

            Route::get('/search/personnel', 'searchController@personnel');
            Route::get('/search/eleve', 'searchController@eleve');
            Route::any('/select/classe', 'searchController@select_classe');
            Route::post('/update-school-year/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SCHOOL_YEAR_CU, 'uses' => 'WelcomeController@editYear']);

            //Pour la langue
            Route::any('/language/french', 'WelcomeController@fr_locale');
            Route::any('/language/english','WelcomeController@en_locale');

            //modifier le mot de passe utilisateur
            Route::any('/user-profile/edit-password','WelcomeController@change_password');


            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ALL],function (){
            Route::get('/admin/blk/', 'WelcomeController@blk');
            Route::get('/admin/unblk/', 'WelcomeController@unblk');

            });


            //pour les utilisateurs
            Route::get('/utilisateurs', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER, 'uses' => 'user\userController@index']);
            Route::post('/utilisateurs/activer', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTIVATE_DEACTIVATE, 'uses' => 'user\userController@activate']);
            Route::post('/utilisateurs/reset-password', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER, 'uses' => 'user\userController@resetPassword']);
            Route::get('/utilisateurs/desactiver/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTIVATE_DEACTIVATE,'uses' =>'user\userController@deactivate']);

            Route::get('/access-control', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER, 'uses' => 'admin\AccessControlController@index', 'as' => 'access_control']);
            Route::post('/access-control/roles', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU, 'uses' => 'admin\AccessControlController@storeRole', 'as' => 'access_roles_store']);
            Route::get('/access-control/roles/{id}/permissions', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION, 'uses' => 'admin\AccessControlController@editRolePermissions', 'as' => 'access_roles_permissions_edit']);
            Route::post('/access-control/roles/{id}/permissions', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU, 'uses' => 'admin\AccessControlController@updateRolePermissions', 'as' => 'access_roles_permissions_update']);
            Route::post('/access-control/roles/{id}/sync-actions', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU, 'uses' => 'admin\AccessControlController@syncActions', 'as' => 'access_roles_actions_sync']);
            Route::post('/access-control/users/{id}/roles', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU, 'uses' => 'admin\AccessControlController@updateUserRoles', 'as' => 'access_users_roles']);
            Route::get('/access-control/roles/delete/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU, 'uses' => 'admin\AccessControlController@destroyRole', 'as' => 'access_roles_delete']);

            //routes pour les roles
            Route::get('admin/roles/{id}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION,'uses'=>'admin\RoleController@index','as'=>'role_management']);
            Route::post('admin/roles/store',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU,'uses'=>'admin\RoleController@store','as'=>'store_role']);
            Route::get('admin/roles/delete/{id}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU,'uses'=>'admin\RoleController@destroy','as'=>'delete_role']);

              Route::get('admin/actions/{id}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION,'uses'=>'admin\actionController@index','as'=>'action_management']);
            Route::post('admin/actions/update',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU,'uses'=>'admin\actionController@update','as'=>'update_actions']);


            Route::post('/inscriptions/payement/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE,'uses'=>'inscriptions\payementController@update','as'=>'update_payement']);
            Route::get('/inscriptions/enregistrement/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_DELETE,'uses'=>'inscriptions\enregistrementController@destroy','as'=>'destroy_eleve']);
            Route::get('/inscriptions/remove/{id_eleve}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE, 'uses' => 'inscriptions\inscriptionController@destroy']);
            Route::get('/inscriptions/restore/{id_eleve}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE, 'uses' => 'inscriptions\inscriptionController@restore']);



        //Tableau de bord d'admin
        Route::any('/inscriptions/eleve/set-status',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'uses' => 'inscriptions\enregistrementController@set_status']);
        Route::post('/inscriptions/eleve/set-multi-status',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'uses' => 'inscriptions\enregistrementController@set_multi_status']);
           /* Route::get('/inscriptions/eleve/set-status',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'uses' => 'inscriptions\enregistrementController@set_status']);
            Route::post('/inscriptions/eleve/set-multi-status',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'uses' => 'inscriptions\enregistrementController@set_multi_status']);
 */

            Route::get('/admin/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN, 'uses' => 'admin\adminController@index']);
            Route::get('/audit', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_AUDIT, 'uses' => 'admin\AuditController@index', 'as' => 'audit']);
            //routes pour le verrou des notes
            Route::get('/admin/verrou-note/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK, 'uses' => 'admin\verrou_noteController@index']);
            Route::get('/admin/verrou-note/activate/{sequence}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'uses' => 'admin\verrou_noteController@activate']);
            Route::get('/admin/verrou-note/deactivate/{sequence}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'uses' => 'admin\verrou_noteController@deactivate']);
            Route::get('/admin/verrou-note/portail-etudiant', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK, 'uses' => 'admin\verrou_noteController@index']);
            Route::post('/admin/verrou-note/portail-etudiant', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'as' => 'student_portal_lock_store', 'uses' => 'admin\verrou_noteController@storeStudentPortalLock']);
            Route::get('/admin/verrou-note/portail-etudiant/open/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'as' => 'student_portal_lock_open', 'uses' => 'admin\verrou_noteController@openStudentPortal']);
            //deliberer les notes

        Route::get('/admin/deliber/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK, 'uses' => 'admin\verrou_noteController@index']);
        Route::get('/admin/deliberer/activate/{sequence}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'uses' => 'admin\verrou_noteController@deliberer']);
        Route::get('/admin/deliberer/deactivate/{sequence}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'uses' => 'admin\verrou_noteController@deactivate']);



        Route::get('/admin/cycles/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CYCLE_READ, 'uses' => 'admin\cycleController@index']);
        Route::post('/admin/cycles/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CYCLE_CREATE, 'uses' => 'admin\cycleController@store']);
        Route::post('/admin/cycles/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CYCLE_UPDATE, 'uses' => 'admin\cycleController@update']);
        Route::get('/admin/cycles/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CYCLE_DELETE, 'uses' => 'admin\cycleController@destroy']);

        Route::get('/admin/nniveaux/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NNIVEAU_READ, 'uses' => 'admin\nniveauController@index']);
        Route::post('/admin/nniveaux/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NNIVEAU_CREATE, 'uses' => 'admin\nniveauController@store']);
        Route::post('/admin/nniveaux/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NNIVEAU_UPDATE, 'uses' => 'admin\nniveauController@update']);
        Route::get('/admin/nniveaux/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NNIVEAU_DELETE, 'uses' => 'admin\nniveauController@destroy']);

        Route::get('/admin/salles/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALLE_READ, 'uses' => 'admin\salleController@index']);
        Route::post('/admin/salles/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALLE_CREATE, 'uses' => 'admin\salleController@store']);
        Route::post('/admin/salles/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALLE_UPDATE, 'uses' => 'admin\salleController@update']);
        Route::get('/admin/salles/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALLE_DELETE, 'uses' => 'admin\salleController@destroy']);

        Route::get('/admin/niveaux/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FILIERE_READ, 'uses' => 'admin\niveauController@index']);
        Route::post('/admin/niveaux/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FILIERE_CREATE, 'uses' => 'admin\niveauController@store']);
        Route::post('/admin/niveaux/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FILIERE_UPDATE, 'uses' => 'admin\niveauController@update']);
        Route::get('/admin/niveaux/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FILIERE_DELETE, 'uses' => 'admin\niveauController@destroy']);

        Route::get('/admin/classes/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SPECIALITE_READ, 'uses' => 'admin\classeController@index']);
        Route::post('/admin/classes/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SPECIALITE_CREATE, 'uses' => 'admin\classeController@store']);
        Route::post('/admin/classes/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SPECIALITE_UPDATE, 'uses' => 'admin\classeController@update']);
        Route::get('/admin/classes/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SPECIALITE_DELETE, 'uses' => 'admin\classeController@destroy']);

        Route::get('/admin/campus/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_READ, 'uses' => 'admin\campusController@index']);
        Route::get('/admin/campus/{id}/users', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_READ, 'uses' => 'admin\campusController@users']);
        Route::get('/admin/campus/{id}/specialites', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_READ, 'uses' => 'admin\campusController@specialites']);
        Route::post('/admin/campus/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_CREATE, 'uses' => 'admin\campusController@store']);
        Route::post('/admin/campus/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_UPDATE, 'uses' => 'admin\campusController@update']);
        Route::post('/admin/campus/users/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_UPDATE, 'uses' => 'admin\campusController@storeUsers']);
        Route::post('/admin/campus/specialites/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_UPDATE, 'uses' => 'admin\campusController@storeSpecialites']);
        Route::get('/admin/campus/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_DELETE, 'uses' => 'admin\campusController@destroy']);
        Route::get('/admin/campus/{idCampus}/specialites/{idClasse}/detach', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAMPUS_DELETE, 'uses' => 'admin\campusController@detachSpecialite']);

        Route::get('/admin/tutelles/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TUTELLE_READ, 'uses' => 'admin\tutelleAcademiqueController@index']);
        Route::get('/admin/tutelles/{id}/specialites', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TUTELLE_READ, 'uses' => 'admin\tutelleAcademiqueController@specialites']);
        Route::post('/admin/tutelles/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TUTELLE_CREATE, 'uses' => 'admin\tutelleAcademiqueController@store']);
        Route::post('/admin/tutelles/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TUTELLE_UPDATE, 'uses' => 'admin\tutelleAcademiqueController@update']);
        Route::post('/admin/tutelles/specialites/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TUTELLE_UPDATE, 'uses' => 'admin\tutelleAcademiqueController@storeSpecialites']);
        Route::get('/admin/tutelles/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TUTELLE_DELETE, 'uses' => 'admin\tutelleAcademiqueController@destroy']);

        Route::get('/admin/matieres/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_MATIERE_READ, 'uses' => 'admin\matiereController@index']);
        Route::post('/admin/matieres/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_MATIERE_CREATE, 'uses' => 'admin\matiereController@store']);
        Route::post('/admin/matieres/update/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_MATIERE_UPDATE, 'uses' => 'admin\matiereController@update']);
        Route::get('/admin/matieres/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_MATIERE_DELETE, 'uses' => 'admin\matiereController@destroy']);

        Route::get('/admin/programmes/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_READ, 'uses' => 'admin\programmeController@index']);
        Route::get('/admin/programmes/selection-matieres/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_READ, 'uses' => 'admin\programmeController@create']);
        Route::get('/admin/programmes/groupe/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_READ, 'uses' => 'admin\groupeController@create']);
        Route::post('/admin/programmes/store/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_CREATE, 'uses' => 'admin\programmeController@store']);
        Route::post('/admin/programmes/update/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_UPDATE, 'uses' => 'admin\programmeController@update']);
        Route::post('/admin/programmes/groupe/store/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_UPDATE, 'uses' => 'admin\groupeController@store']);
        Route::get('/admin/programmes/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_DELETE, 'uses' => 'admin\programmeController@destroy']);
        Route::get('/admin/programmes/groupe/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_PROGRAMME_DELETE, 'uses' => 'admin\groupeController@destroy']);

        Route::group(['prefix' => 'admin/sessions', 'namespace' => 'admin'], function () {
            Route::get('/global', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SESSION_EXAM_READ, 'uses' => 'sessionsController@globalIndex']);
            Route::post('/global/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SESSION_EXAM_CREATE, 'uses' => 'sessionsController@globalStore']);
            Route::get('/{id_classe_matiere}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SESSION_EXAM_READ, 'uses' => 'sessionsController@index']);
            Route::post('/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SESSION_EXAM_CREATE, 'uses' => 'sessionsController@store']);
            Route::post('/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SESSION_EXAM_UPDATE, 'uses' => 'sessionsController@update']);
            Route::get('/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SESSION_EXAM_DELETE, 'uses' => 'sessionsController@destroy']);
        });


            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FEES_SETTINGS], function () {
                Route::get('/admin/frais/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FRAIS_READ, 'uses' => 'admin\FraisController@index']);
                Route::get('/admin/scolarites/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SCOLARITE_READ, 'uses' => 'admin\scolariteController@index']);
                Route::get('/admin/banques/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_BANQUE_READ, 'uses' => 'admin\banqueController@index']);
                Route::get('/admin/caisses/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAISSE_READ, 'uses' => 'admin\caisseController@index']);
                Route::get('/admin/finances/etat', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FINANCE_READ, 'uses' => 'admin\financeController@etat']);
                Route::get('/admin/finances/transfert', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FINANCE_READ, 'uses' => 'admin\financeController@transfert']);
                Route::get('/admin/finances/affectations', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FINANCE_READ, 'uses' => 'admin\financeController@affectations']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FEES_SETTINGS_CU], function () {
                Route::post('/admin/frais/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FRAIS_CREATE, 'uses' => 'admin\FraisController@store']);
                Route::post('/admin/frais/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FRAIS_UPDATE, 'uses' => 'admin\FraisController@update']);
                Route::post('/admin/scolarite/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SCOLARITE_UPDATE, 'uses' => 'admin\scolariteController@update']);
                Route::post('/admin/scolarite/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SCOLARITE_CREATE, 'uses' => 'admin\scolariteController@store']);
                Route::post('/admin/banques/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_BANQUE_CREATE, 'uses' => 'admin\banqueController@store']);
                Route::post('/admin/banques/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_BANQUE_UPDATE, 'uses' => 'admin\banqueController@update']);
                Route::post('/admin/caisses/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAISSE_CREATE, 'uses' => 'admin\caisseController@store']);
                Route::post('/admin/caisses/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAISSE_UPDATE, 'uses' => 'admin\caisseController@update']);
                Route::post('/admin/finances/transfert/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FINANCE_CREATE, 'uses' => 'admin\financeController@storeTransfert']);
                Route::post('/admin/finances/affectations/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FINANCE_CREATE, 'uses' => 'admin\financeController@storeAffectations']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FEES_SETTINGS_DELETE], function () {
                Route::get('/admin/frais/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FRAIS_DELETE, 'uses' => 'admin\FraisController@destroy']);
                Route::get('/admin/scolarite/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SCOLARITE_DELETE, 'uses' => 'admin\scolariteController@destroy']);
                Route::get('/admin/banques/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_BANQUE_DELETE, 'uses' => 'admin\banqueController@destroy']);
                Route::get('/admin/caisses/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CAISSE_DELETE, 'uses' => 'admin\caisseController@destroy']);

            });



            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_SETTINGS], function () {
                Route::get('/admin/fonctions/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FONCTION_READ, 'uses' => 'admin\fonctionController@index']);
                Route::get('/admin/horaires/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_HORAIRE_READ, 'uses' => 'admin\horaireController@index']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_SETTINGS_CU], function () {
                Route::post('/admin/fonctions/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FONCTION_CREATE, 'uses' => 'admin\fonctionController@store']);
                Route::post('/admin/fonctions/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FONCTION_UPDATE, 'uses' => 'admin\fonctionController@update']);
                Route::post('/admin/horaires/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_HORAIRE_CREATE, 'uses' => 'admin\horaireController@store']);
                Route::post('/admin/horaires/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_HORAIRE_UPDATE, 'uses' => 'admin\horaireController@update']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_SETTINGS_DELETE], function () {
                Route::get('/admin/fonctions/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FONCTION_DELETE, 'uses' => 'admin\fonctionController@destroy']);
                Route::get('/admin/horaires/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_HORAIRE_DELETE, 'uses' => 'admin\horaireController@destroy']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS], function () {
                Route::get('/admin/salaire/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALAIRE_READ, 'uses' => 'admin\salaireController@index']);
                Route::get('/admin/taux/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TAUX_READ, 'uses' => 'admin\tauxController@index']);
                Route::get('/admin/taux/create/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TAUX_CREATE, 'uses' => 'admin\tauxController@create']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_CU], function () {
                Route::post('/admin/salaire/store', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALAIRE_CREATE, 'uses' => 'admin\salaireController@store']);
                Route::post('/admin/salaire/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALAIRE_UPDATE, 'uses' => 'admin\salaireController@update']);
                Route::post('/admin/taux/store/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TAUX_CREATE, 'uses' => 'admin\tauxController@store']);


            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_DELETE], function () {
                Route::get('/admin/salaire/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_SALAIRE_DELETE, 'uses' => 'admin\salaireController@destroy']);
                Route::get('/admin/taux/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_TAUX_DELETE, 'uses' => 'admin\tauxController@destroy']);
            });




            //Inscriptions
            Route::get('/inscriptions/', ['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION, 'uses' => 'inscriptions\inscriptionController@index']);
            Route::get('/inscriptions/etat-caisse', ['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION, 'uses' => 'admin\financeController@etatCampus']);



            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION], function () {
                Route::get('/inscriptions/new', 'inscriptions\inscriptionController@search');
                Route::get('/inscriptions/eleves/search', 'inscriptions\enregistrementController@index');
                Route::get('/inscriptions/find', 'inscriptions\inscriptionController@find');
                Route::get('/inscriptions/find-inscription', 'inscriptions\inscriptionController@find_inscription');
                Route::get('/inscriptions/historique-etudiant', ['as' => 'inscriptions_historique_etudiant', 'uses' => 'inscriptions\inscriptionController@historiqueEtudiant']);
                Route::get('/inscriptions/historique-etudiant/{id}', ['as' => 'inscriptions_historique_etudiant_show', 'uses' => 'inscriptions\inscriptionController@historiqueEtudiantShow']);
                Route::get('/inscriptions/payement/', 'inscriptions\payementController@index');
                Route::get('/inscriptions/payement/search', 'inscriptions\payementController@search');
                Route::get('/inscriptions/enregistrement/edit/{id}', 'inscriptions\enregistrementController@edit');
                Route::get('/inscriptions/eleve/status','inscriptions\enregistrementController@status_eleve');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_STUDENT_CU], function () {

                Route::get('/inscriptions/enregistrement/create/', 'inscriptions\enregistrementController@create');
                Route::post('/inscriptions/enregistrement/store/', 'inscriptions\enregistrementController@store');
                Route::post('/inscriptions/enregistrement/update/{id}', 'inscriptions\enregistrementController@update');
                Route::get('/inscriptions/import-etudiants', ['as' => 'students_import', 'uses' => 'inscriptions\enregistrementController@importPage']);
                Route::get('/inscriptions/import-etudiants/template', ['as' => 'students_import_template', 'uses' => 'inscriptions\enregistrementController@downloadStudentImportTemplate']);
                Route::post('/inscriptions/import-etudiants', ['as' => 'students_import_store', 'uses' => 'inscriptions\enregistrementController@importStudentsFromExcel']);
            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE], function () {
                Route::get('/inscriptions/create/{id}', 'inscriptions\inscriptionController@create');
                Route::post('/inscriptions/store', 'inscriptions\inscriptionController@store');
                Route::get('/inscriptions/import-inscriptions', ['as' => 'inscriptions_import', 'uses' => 'inscriptions\inscriptionController@importPage']);
                Route::get('/inscriptions/import-inscriptions/template', ['as' => 'inscriptions_import_template', 'uses' => 'inscriptions\inscriptionController@downloadInscriptionImportTemplate']);
                Route::post('/inscriptions/import-inscriptions', ['as' => 'inscriptions_import_store', 'uses' => 'inscriptions\inscriptionController@importInscriptionsFromExcel']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_UPDATE], function () {
                Route::get('/inscriptions/edit/{id}', 'inscriptions\inscriptionController@edit');
                Route::post('/inscriptions/update', 'inscriptions\inscriptionController@update');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT], function () {
                Route::get('/inscriptions/payement/list/{id}','inscriptions\payementController@index_liste');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_CREATE], function () {
                Route::get('/inscriptions/payement/create/{id}', 'inscriptions\payementController@create');
                Route::get('/inscriptions/payement/create-frais/{id}', 'inscriptions\payementController@create_frais');
                Route::post('/inscriptions/payement/store/{id}', 'inscriptions\payementController@store');
            });


            //Route::get('/inscriptions/payement/get/inscription', ['uses'=>'inscriptions\payementController@getLeftInscription','as'=>'get_left_inscription']);
            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_LIST], function () {
                Route::get('/inscriptions/effectifs', ['as'=>'inscriptions_effectifs','uses'=>'inscriptions\inscriptionController@effectifs']);
                Route::get('/inscriptions/selection-listes','inscriptions\listesController@index');
                Route::get('/inscriptions/listes/show',['as'=>'show_sco_liste','uses'=>'inscriptions\listesController@show']);
                Route::get('/inscriptions/listes/showParents',['as'=>'show_parents_liste','uses'=>'inscriptions\listesController@showParents']);
                Route::get('/inscriptions/listes/desinscrits/show', ['as'=>'show_desinscrits_liste','uses'=>'inscriptions\listesController@showDesinscrits']);
                Route::get('/inscriptions/listes/frais/show', ['as'=>'show_frais_liste','uses'=>'inscriptions\listesController@showFrais']);
                Route::get('/inscriptions/listes/bourses/show', ['as'=>'show_bourses_liste','uses'=>'inscriptions\listesController@showBourses']);
                Route::get('/inscriptions/listes/acces/show', ['as'=>'show_acces_liste','uses'=>'inscriptions\listesController@liste_acces']);
                Route::get('/inscriptions/listes/accesok/show', ['as'=>'show_accesok_liste','uses'=>'inscriptions\listesController@liste_acces_autirise']);



            });
            Route::get('/inscriptions/fiche-scolarite',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_LIST_TUITION_SHEET,'uses' => 'inscriptions\listesController@fiche_scolarite']);
            Route::get('/inscriptions/print/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT, 'uses' => 'inscriptions\inscriptionController@print_receipe']);
            Route::get('/inscriptions/payement/print/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT, 'uses' => 'inscriptions\payementController@print_receipe']);


            //Route::get('/inscriptions/recu','inscriptions\inscriptionController@show');
            Route::get('/inscriptions/certificates',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE, 'uses' => 'inscriptions\inscriptionController@certificateSelection']);
            Route::get('/inscriptions/print-certificates',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE, 'uses' => 'inscriptions\inscriptionController@certificatesByClasse']);
            Route::get('/inscriptions/print-certificate/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE, 'uses' => 'inscriptions\inscriptionController@certificate']);
            Route::any('/inscriptions/photos/',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_STUDENT_PHOTO, 'uses' => 'inscriptions\photosController@create']);
            Route::any('/inscriptions/photos/store',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_STUDENT_PHOTO_CU, 'as'=>'store_photo','uses'=>'inscriptions\photosController@store']);


            Route::get('/inscriptions/eleve/scan-carte',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCAN_CARD, 'uses' => 'inscriptions\inscriptionController@index_scan_carte']);
            Route::get('/inscriptions/eleve/process-scan',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCAN_CARD, 'uses' => 'inscriptions\inscriptionController@process_scan']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_STUDENT_CARD], function () {
                Route::any('/inscriptions/cartes/',['as'=>'show_carte','uses'=>'inscriptions\photosController@show_carte']);
                Route::any('/inscriptions/cartes/eleve/',['as'=>'show_carte_eleve','uses'=>'inscriptions\photosController@search_carte_eleve']);
            });

            Route::get('inscriptions/fiches/', ['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_REPORTS, 'uses' => 'fiche\listesController@home']);
             Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_REPORTS_PRINT], function () {

          //liste des etudiants
         Route::get('/fiche/selection-listes','fiche\listesController@index');
         Route::get('/fiche/listes/show',['as'=>'show_ex_liste','uses'=>'fiche\listesController@show']);

          //fiches session normales

            Route::get('/fiches/selection-listes','fiches\listesController@index');
            Route::get('/fiches/listes/show',['as'=>'show_cc_liste','uses'=>'fiches\listesController@show']);


            //fiches rattrapages
                Route::get('/rattrappages/selection-listes','rattrapages\listesController@index');
                Route::get('/rattrappages/listes/show',['as'=>'show_rattrap_liste','uses'=>'rattrapages\listesController@show']);

        //fiches bts blanc


        Route::get('/fichesblanc/selection-listes','fichesblanc\listesController@index');
        Route::get('/fichesblanc/listes/show/','fichesblanc\listesController@show');
        Route::get('/fichesblanc/selection-listes','fichesblanc\listesController@index');

        ///fiche presence d'examen

       // Route::get('/fichepresence/selection-listes','fiche\listeController@index');
        Route::get('/fichepresence/listes/show/','fichepresence\listesController@show');
        Route::get('/fichepresence/selection_liste','fichepresence\listesController@index');

});
                
      /*  //liste des etudiants
         Route::get('/fiche/selection-listes','fiche\listesController@index');
         Route::get('/fiche/listes/show',['as'=>'show_ex_liste','uses'=>'fiche\listesController@show']);

          //fiches session normales

            Route::get('/fiches/selection-listes','fiches\listesController@index');
            Route::get('/fiches/listes/show',['as'=>'show_cc_liste','uses'=>'fiches\listesController@show']);


            //fiches rattrapages
                Route::get('/rattrappages/selection-listes','rattrapages\listesController@index');
                Route::get('/rattrappages/listes/show',['as'=>'show_rattrap_liste','uses'=>'rattrapages\listesController@show']);

        //fiches bts blanc


        Route::get('/fichesblanc/selection-listes','fichesblanc\listesController@index');
        Route::get('/fichesblanc/listes/show/','fichesblanc\listesController@show');
        Route::get('/fichesblanc/selection-listes','fichesblanc\listesController@index');

        ///fiche presence d'examen

       // Route::get('/fichepresence/selection-listes','fiche\listeController@index');
        Route::get('/fichepresence/listes/show/','fichepresence\listesController@show');
        Route::get('/fichepresence/selection_liste','fichepresence\listesController@index');
*/


            });

    Route::group(['prefix' => 'admin/jours'], function () {

        Route::get('/', 'admin\jourController@index');
        Route::post('/store', 'admin\jourController@store');
        Route::post('/update', 'admin\jourController@update');
        Route::get('/destroy/{id}', 'admin\jourController@destroy');

    });

 Route::get('/inscriptions/bourses',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCHOLARSHIP_GRANT,'uses' => 'inscriptions\bourseController@index' ,'as'=>'index_bourses']);
            Route::post('/inscriptions/bourses/store',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCHOLARSHIP_GRANT_CREATE,'uses' => 'inscriptions\bourseController@store', 'as'=>'store_bourse']);
            Route::post('/inscriptions/bourses/update',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCHOLARSHIP_GRANT_UPDATE, 'uses' => 'inscriptions\bourseController@update', 'as'=>'update_bourse']);
            Route::get('/inscriptions/bourses/delete/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCHOLARSHIP_GRANT_DELETE, 'uses' => 'inscriptions\bourseController@destroy', 'as'=>'delete_bourse']);


            //Les ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©lÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¨ves

           Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_DISCIPLINE], function () {
                Route::get('/eleves/discipline/', 'eleves\disciplineController@index');
                Route::get('/eleves/discipline/search', 'eleves\disciplineController@search');
                Route::get('/eleves/discipline/show/{id}', 'eleves\disciplineController@create');
                Route::get('/eleves/discipline/show/precision/{id}', 'eleves\disciplineController@show');
                Route::any('/eleves/discipline/index_injustifier/{id}', 'eleves\disciplineController@index_injustifier');
                Route::any('/eleves/discipline/index_justifier/{id}', 'eleves\disciplineController@index_justifier');
                Route::any('/eleves/discipline/state/', ['as'=>'etat_disciplinaire','uses'=>'eleves\disciplineController@state']);
                Route::get('/eleves/discipline/show_presnce/{id}', 'eleves\disciplineController@show_presnce');
                Route::get('/eleves/discipline/justifi_pesence/{id}/{id_eleve}', 'eleves\disciplineController@justifi_pesence');


                Route::any('/eleves/discipline/state1/', ['as'=>'etat_disciplinaire','uses'=>'eleves\disciplineController@state']);
                Route::get('/eleves/appel', ['as' => 'appel.index', 'uses' => 'eleves\disciplineControlleretd@index3']);
                Route::get('/eleves/appel/specialite/{classe}', ['as' => 'appel.specialite', 'uses' => 'eleves\disciplineControlleretd@choisirCycleNiveau']);
                Route::get('/eleves/appel/niveaux/{cycle}', ['as' => 'appel.niveaux', 'uses' => 'eleves\disciplineControlleretd@getNiveauxByCycle']);

// AprÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¨s choix cycle + niveau ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¬ÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¾ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â¢ liste classes
                Route::post('/eleves/appel/classes', ['as' => 'appel.classes', 'uses' => 'eleves\disciplineControlleretd@classes']);
              // Route::get('/eleves/appel/classes', ['as' => 'appel.classesok', 'uses' => 'eleves\disciplineControlleretd@classes']);
               Route::get(
                   '/eleves/appel/classesok/{cycle}/{niveau}',
                   [
                       'as' => 'appel.classesok',
                       'uses' => 'eleves\disciplineControlleretd@classesok'
                   ]
               );
// Retour liste des classes (GET sÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©curisÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©)
               Route::get('/eleves/appel/classesokk/{cycle}/{niveau}/{n_niveau}', [
                   'as' => 'appel.classesok.get',
                   'uses' => 'eleves\disciplineControlleretd@classesokGet'
               ]);
// SÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©lection jour
               // Route::get('/eleves/appel/selection/{classe}', ['as' => 'appel.selection', 'uses' => 'eleves\disciplineControlleretd@selectionJour']);
//             Route::post('/eleves/appel/selection-jour/', ['as' => 'appel.selection.jour', 'uses' => 'eleves\disciplineControlleretd@selectionJour']);


// POST depuis le formulaire choix_cycle_niveau
               Route::post('/eleves/appel/selection-jour', [
                   'as'   => 'appel.selection.jour',
                   'uses' => 'eleves\disciplineControlleretd@selectionJourPost'
               ]);

// GET pour afficher selection_jour
               Route::get('/eleves/appel/selection/{classe}', [
                   'as'   => 'appel.selection',
                   'uses' => 'eleves\disciplineControlleretd@selectionJour'
               ]);
               Route::post('/eleves/appel/formulaire', [
                   'as'   => 'appel.formulaire',
                   'uses' => 'eleves\disciplineControlleretd@formulaire'
               ]);
               Route::post('/eleves/appel/store', [
                   'as'   => 'appel.store',
                   'uses' => 'eleves\disciplineControlleretd@storePresence'
               ]);

              /* Route::post('/eleves/appel/selection-jour', [
                   'as'   => 'appel.selection.jour',
                   'uses' => 'eleves\disciplineControlleretd@selectionJourPost'
               ]);*/

               /*
                  *
                               Route::get('/eleves/appel/selection/{classe}', [
                                   'as' => 'appel.selection',
                                   'uses' => 'eleves\disciplineControlleretd@selectionJour'
                               ]);*/



               Route::get('/eleves/appel/selection/{classe}', [
                   'as' => 'appel.selection',
                   'uses' => 'eleves\disciplineControlleretd@selectionJour'
               ]);

               Route::post('/eleves/appel/formulaire',
                   'eleves\disciplineControlleretd@formulaire');

               Route::post('/eleves/appel/store', 'eleves\disciplineControlleretd@storePresence');


                Route::get('/eleves/disciplineetd/', 'eleves\disciplineControlleretd@index');
                //Route::get('/eleves/disciplineetd1/', 'eleves\disciplineControlleretd@index1');
               Route::get('/eleves/disciplineetd1/', [
                   'as' => 'appel.specialitesok',
                   'uses' => 'eleves\disciplineControlleretd@index1'
               ]);
               //route pour les etats
               Route::get('/eleves/discipline_etat/', [
                   'as' => 'appel.specialites_etat',
                   'uses' => 'eleves\disciplineControlleretd@index_etat'
               ]);
               Route::post('/eleves/discipline_etat/generer', [
                   'as' => 'appel.etat.generer',
                   'uses' => 'eleves\disciplineControlleretd@genererEtat'
               ]);
               Route::get('/eleves/discipline_etat/pdf/{date_debut}/{date_fin}', [
                   'as' => 'appel.etat.pdf',
                   'uses' => 'eleves\disciplineControlleretd@exportPdfEtat'
               ]);
               Route::get('/eleves/discipline_etat/excel/{date_debut}/{date_fin}', [
                   'as' => 'appel.etat.excel',
                   'uses' => 'eleves\disciplineControlleretd@exportExcelEtat'
               ]);
               Route::get('/eleves/etat-discipline/generate', [
                   'as' => 'etat.discipline.generate',
                   'uses' => 'eleves\disciplineControlleretd@genererEtatFiltre'
               ]);
               Route::post('/eleves/discipline_etat/generer_filtre', [
                   'as' => 'appel.etat.generer.filtre',
                   'uses' => 'eleves\disciplineControlleretd@genererEtatFiltre'
               ]);

               Route::get('/eleves/discipline_etat/periode/{classe}', [
                   'as' => 'appel.etat.periode',
                   'uses' => 'eleves\disciplineControlleretd@formPeriodeEtat'
               ]);

               Route::post('/eleves/discipline_etat/generer/{classe}', [
                   'as' => 'appel.etat.generer.specialite',
                   'uses' => 'eleves\disciplineControlleretd@genererEtatSpecialite'
               ]);
               Route::get('/eleves/discipline_etat/export/{classe}/{date_debut}/{date_fin}', [
                   'as' => 'appel.etat.export.excel.specialite',
                   'uses' => 'eleves\disciplineControlleretd@exportExcelEtatSpecialite'
               ]);
               Route::get('/eleves/etat/n_niveaux/{cycle_id}', [
                   'as'   => 'appel.n_niveaux.bycycle',
                   'uses' => 'eleves\disciplineControlleretd@getNNiveauxByCycle'
               ]);
// Formulaire pÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©riode ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©tat dÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©taillÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©
               Route::get('/eleves/etat/detaille/{classe}', [
                   'as' => 'appel.etat.periode.detaille',
                   'uses' => 'eleves\disciplineControlleretd@formPeriodeEtatDetaille'
               ]);

// GÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©nÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©ration ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©tat dÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©taillÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©
               Route::post('/eleves/etat/detaille/generer/{classe}', [
                   'as' => 'appel.etat.generer.detaille',
                   'uses' => 'eleves\disciplineControlleretd@genererEtatDetaille'
               ]);
               Route::get('/eleves/etat/detaille/{classe}', [
                   'as' => 'appel.etat.periode.detaille',
                   'uses' => 'eleves\disciplineControlleretd@formPeriodeEtatDetaille'
               ]);


               Route::get('/eleves/disciplineetd/search', 'eleves\disciplineController@search');
                Route::get('/eleves/disciplineetd/show/{id}', 'eleves\disciplineController@create');
            });

    Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_DISCIPLINE_CU], function () {
                Route::post('/eleves/discipline/justifions/', 'eleves\disciplineController@justifions');
                Route::post('/eleves/discipline/justify/', 'eleves\disciplineController@justify');
                Route::post('/eleves/discipline/update/', ['as'=>'update_discipline','uses'=>'eleves\disciplineController@update']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_REPORT_NOTE], function () {
                Route::get('/eleves/bulletins/','eleves\bulletinController@index');
                Route::get('/eleves/bulletins/search/','eleves\bulletinController@index2');
                Route::get('/eleves/bulletins/find/','eleves\bulletinController@search');
                Route::post('/eleves/bulletins/print-single-seq','eleves\bulletinController@print_single_seq');

                Route::get('/eleves/releves/','eleves\relevesController@index');
                Route::get('/eleves/releves/print/','eleves\relevesController@create');
                Route::get('/eleves/releves/get/',['uses'=>'eleves\relevesController@get','as'=>'get_releves']);


                //releve soa

                Route::get('/eleves/releves_soa/','eleves\soareleveController@index');
                Route::get('/eleves/releves_soa/historique','eleves\soareleveController@history');
                Route::get('/eleves/releves_soa/historique/{id}','eleves\soareleveController@history');
                Route::get('/eleves/releves_soa/print/','eleves\soareleveController@create');
                Route::post('/eleves/releves_soa/generate/','eleves\soareleveController@generate');
                Route::get('/eleves/releves_soa/generation/{id}/print','eleves\soareleveController@printSaved');

                Route::get('/eleves/diplome_soa/','eleves\diplomeSoaController@index');
                Route::get('/diplome/soa/print/','eleves\diplomeSoaController@create');

                //ndere releve

                Route::get('/eleves/releves_ndere/','eleves\nderereleveController@index');
                Route::get('/eleves/releves_ndere/print/','eleves\nderereleveController@create');
                Route::post('/eleves/releves_ndere/generate/','eleves\nderereleveController@generate');
                Route::get('/eleves/releves_ndere/generation/{id}/print','eleves\nderereleveController@printSaved');
                Route::get('/eleves/releves_ndere/generation/{id}/destroy','eleves\nderereleveController@destroy');
                Route::get('/eleves/releves_ndere/historique','eleves\nderereleveController@history');

                // attestations de reussite ngaoundere
                Route::get('/eleves/attestations_reussite_ndere/','eleves\ndereAttestationReussiteController@index');
                Route::post('/eleves/attestations_reussite_ndere/generate/','eleves\ndereAttestationReussiteController@generate');
                Route::get('/eleves/attestations_reussite_ndere/generation/{id}/print','eleves\ndereAttestationReussiteController@printSaved');
                Route::get('/eleves/attestations_reussite_ndere/generation/{id}/destroy','eleves\ndereAttestationReussiteController@destroy');
                Route::get('/eleves/attestations_reussite_ndere/historique','eleves\ndereAttestationReussiteController@history');

                // attestations de reussite soa
                Route::get('/eleves/attestations_reussite_soa/','eleves\soaAttestationReussiteController@index');
                Route::post('/eleves/attestations_reussite_soa/generate/','eleves\soaAttestationReussiteController@generate');
                Route::get('/eleves/attestations_reussite_soa/generation/{id}/print','eleves\soaAttestationReussiteController@printSaved');
                Route::get('/eleves/attestations_reussite_soa/generation/{id}/destroy','eleves\soaAttestationReussiteController@destroy');
                Route::get('/eleves/attestations_reussite_soa/historique','eleves\soaAttestationReussiteController@history');

                // attestations de reussite ebolowa
                Route::get('/eleves/attestations_reussite_ebolowa/','eleves\ebolowaAttestationReussiteController@index');
                Route::post('/eleves/attestations_reussite_ebolowa/generate/','eleves\ebolowaAttestationReussiteController@generate');
                Route::get('/eleves/attestations_reussite_ebolowa/generation/{id}/print','eleves\ebolowaAttestationReussiteController@printSaved');
                Route::get('/eleves/attestations_reussite_ebolowa/generation/{id}/destroy','eleves\ebolowaAttestationReussiteController@destroy');
                Route::get('/eleves/attestations_reussite_ebolowa/historique','eleves\ebolowaAttestationReussiteController@history');

                //estic releve
                Route::get('/eleves/releve_estic/','eleves\esticreleveController@index');
                Route::get('/eleves/releve_estic/historique','eleves\esticreleveController@history');
                Route::get('/eleves/releve_estic/historique/{id}','eleves\esticreleveController@history');
                Route::get('/eleves/releve_estic/print/','eleves\esticreleveController@create');
                Route::post('/eleves/releve_estic/generate/','eleves\esticreleveController@generate');
                Route::get('/eleves/releve_estic/generation/{id}/print','eleves\esticreleveController@printSaved');
                Route::get('/eleves/releve_estic/generation/{id}/destroy','eleves\esticreleveController@destroy');

                //bamenda releve
                Route::get('/eleves/releves_bamenda/','eleves\bamendareleveController@index');
                Route::get('/eleves/releves_bamenda/historique','eleves\bamendareleveController@history');
                Route::get('/eleves/releves_bamenda/historique/{id}','eleves\bamendareleveController@history');
                Route::get('/eleves/releves_bamenda/print/','eleves\bamendareleveController@create');
                Route::post('/eleves/releves_bamenda/generate/','eleves\bamendareleveController@generate');
                Route::get('/eleves/releves_bamenda/generation/{id}/print','eleves\bamendareleveController@printSaved');
                Route::get('/eleves/releves_bamenda/generation/{id}/destroy','eleves\bamendareleveController@destroy');
//        Route::get('/eleves/releves/get/',['uses'=>'eleves\soareleveController@get','as'=>'get_releves']);


                Route::get('/eleves/releves_bts/','eleves\bts_releveSessionController@index');
                Route::get('/eleves/releves_bts/historique','eleves\bts_releveSessionController@history');
                Route::get('/eleves/releves_bts/historique/{id}','eleves\bts_releveSessionController@history');
                Route::get('/eleves/releves_bts/print/','eleves\bts_releveSessionController@create');
                Route::post('/eleves/releves_bts/generate/','eleves\bts_releveSessionController@generate');
                Route::get('/eleves/releves_bts/generation/{id}/print','eleves\bts_releveSessionController@printSaved');
                Route::get('/eleves/releves_bts/generation/{id}/destroy','eleves\bts_releveSessionController@destroy');
 // releve cc1

                Route::get('/eleves/releve_cc','eleves\releve_ccController@index');
                Route::get('/eleves/releves_cc/print/','eleves\releve_ccController@create');


            });





            Route::get('/eleves/', ['middleware' => 'action', 'action' => Action::ACTION_STUDENT, 'uses' => 'eleves\eleveController@index']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_NOTE], function () {
                Route::get('/eleves/notes/','eleves\noteController@index2');
                Route::get('/eleves/notes/show','eleves\noteController@show');
                Route::get('/eleves/notes/fiche','eleves\noteController@show_fiche');
                Route::get('/eleves/notes/show_deliberer','eleves\noteController@show_deliberer');
                Route::get('/eleves/notes/matieres/show/','eleves\noteController@matiere_show');
                Route::get('/eleves/notes/matieres/fiche/','eleves\noteController@matiere_fiche');
                Route::get('/eleves/notes/matieres_deliberer/show/','eleves\noteController@matiere_deliberer');
                Route::any('/eleves/notes/print_note/{id}','eleves\noteController@print_note');
                Route::get('/eleves/notes/deliberations/{id}', ['as' => 'notes.deliberations-etudiants', 'uses' => 'eleves\noteController@deliberationsEtudiants']);
                Route::post('/eleves/notes/deliberations/apply', ['as' => 'notes.deliberations-apply', 'uses' => 'eleves\noteController@applyDeliberationsEtudiants']);
                Route::get('/eleves/notes/pv/rattrapages/{id}', ['as' => 'pv.rattrapages-session-exams', 'uses' => 'eleves\noteController@listeRattrapagesSessionExams']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_NOTE_CU], function () {
                Route::get('/eleves/notes/edit','eleves\noteController@index');
                Route::get('/eleves/notes/matieres/','eleves\noteController@matiere');
                Route::get('/eleves/notes/import', ['as' => 'notes_import', 'uses' => 'eleves\noteController@importPage']);
                Route::get('/eleves/notes/import/template', ['as' => 'notes_import_template', 'uses' => 'eleves\noteController@downloadNoteImportTemplate']);
                Route::post('/eleves/notes/create/{id}','eleves\noteController@create');
                Route::post('/eleves/notes/create_deliberer/{id}','eleves\noteController@create_deliberer');
                Route::post('/eleves/notes/import', ['as' => 'notes_import_store', 'uses' => 'eleves\noteController@importNotesFromExcel']);
                Route::post('/eleves/notes/store/','eleves\noteController@store');
                Route::post('/eleves/notes/deliberer/','eleves\noteController@deliberer');



               // Route::any('/eleves/notes/print_note_cc/{id}','eleves\noteController@print_note_cc');
                Route::any('/eleves/notes/print_note_rat/{id}','eleves\noteController@print_note_rat');
                Route::any('/eleves/notes/print_note_deliberer/{id}','eleves\noteController@deliberer_note');
                Route::any('/eleves/notes/print_note_deliberer_rattrapage/{id}','eleves\noteController@deliberer_rattrapage');
                Route::any('/eleves/notes/print_note_esstic/{id}','eleves\esticnoteController@print_note');
                Route::any('/eleves/notes/print_note_soa/{id}','eleves\soanoteController@print_note');
                Route::any('/eleves/notes/print_note_ndere/{id}','eleves\nderenoteController@print_note');
                Route::post('/eleves/notes/pv/save-generation', ['as' => 'pv.save-generation', 'uses' => 'eleves\noteController@savePvGeneration']);
/*
|--------------------------------------------------------------------------
| HISTORIQUE DES PV
|--------------------------------------------------------------------------
*/

                Route::get('/eleves/notes/pv/historique/{id_classe}','eleves\noteController@historiquePv');


/*
|--------------------------------------------------------------------------
| VISUALISER UN PV DEJA ENREGISTRE
|--------------------------------------------------------------------------
*/

                 Route::get('/eleves/notes/pv/visualiser/{id}','eleves\noteController@visualiserPvGeneration');

                // fiche individualisÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©e

                Route::any('/eleves/notes/fiche_note_cc/{id}','eleves\noteController@fiche_note_cc');



                //proces verbal soa


                Route::get('/eleves/', 'eleves\eleveController@index');
                Route::get('/eleves/soa/','eleves\soanoteController@index2');

                Route::get('/eleves/notes/edit_soa','eleves\soanoteController@index');
                Route::get('/eleves/notes/show_soa','eleves\soanoteController@show');
                Route::get('/eleves/notes/matieres_soa/','eleves\soanoteController@matiere');
                Route::get('/eleves/notes/matieres/show_soa/','eleves\soanoteController@matiere_show');
                Route::post('/eleves/notes/create_soa/{id}','eleves\soanoteController@create');
                Route::post('/eleves/notes/store_soa/','eleves\soanoteController@store');

                Route::get('/eleves/bulletins/', 'eleves\bulletinController@index');
                Route::get('/eleves/bulletins/search/', 'eleves\bulletinController@index2');
                Route::get('/eleves/bulletins/find/', 'eleves\bulletinController@search');
                Route::post('/eleves/bulletins/print-single-seq', 'eleves\bulletinController@print_single_seq');

                Route::get('/eleves/bulletins/get-single-seq', 'eleves\bulletinController@get_single_seq');


                Route::any('/eleves/bulletins/print-single-trim/', 'eleves\bulletinController@print_single_trim');
                Route::any('/eleves/bulletins/print-single-year/', 'eleves\bulletinController@print_single_year');
                Route::any('/eleves/bulletins/regenerate/', 'eleves\bulletinController@regenerate');


                //proces verbal ndere

                Route::get('/eleves/', 'eleves\eleveController@index');
                Route::get('/eleves/ndere/','eleves\nderenoteController@index2');

                Route::get('/eleves/notes/edit_ndere','eleves\nderenoteController@index');
                Route::get('/eleves/notes/show_ndere','eleves\nderenoteController@show');
                Route::get('/eleves/notes/matieres_ndere/','eleves\nderenoteController@matiere');
                Route::get('/eleves/notes/matieres/show_ndere/','eleves\nderenoteController@matiere_show');
                Route::post('/eleves/notes/create_ndere/{id}','eleves\nderenoteController@create');
                Route::post('/eleves/notes/store_ndere/','eleves\nderenoteController@store');


                //proces verbal estic

                Route::get('/eleves/', 'eleves\eleveController@index');
                Route::get('/eleves/estic/','eleves\esticnoteController@index2');

                Route::get('/eleves/notes/edit','eleves\esticnoteController@index');
                Route::get('/eleves/notes/show_esstic','eleves\esticnoteController@show');
                Route::get('/eleves/notes/matieres/','eleves\esticnoteController@matiere');
                Route::get('/eleves/notes/matieres/show_esstic/','eleves\esticnoteController@matiere_show');
                Route::post('/eleves/notes/create1/{id}','eleves\esticnoteController@create');
                Route::post('/eleves/notes/store_esstic/','eleves\esticnoteController@store');

            });





            //routes pour les personnels

            Route::get('/personnel/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF, 'uses' => 'personnel\emploiTempsController@index']);
            Route::get('/ressources-humaines/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF, 'as'=>'ressources_humaines', 'uses'=>'personnel\emploiTempsController@index']);
            Route::get('/emploi-du-temps/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE, 'as'=>'emploi_temps_index', 'uses'=>'personnel\emploiTempsController@index']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER], function () {
                Route::get('/personnel/search', 'personnel\personnelController@show');
                Route::get('/personnel/list', 'personnel\personnelController@index_personnel');
                Route::get('/personnel/import/template', ['as'=>'personnel_import_template','uses'=>'personnel\personnelController@downloadImportTemplate']);
                Route::get('/personnel/edit/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_UPDATE, 'uses' => 'personnel\personnelController@edit']);
                Route::get('/personnel/fonction/pers/', 'personnel\fonction_personnelController@index2');
                Route::get('/personnel/fonction/{id}', 'personnel\fonction_personnelController@index');
                Route::get('/personnel/set-active-state/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_UPDATE, 'as'=>'changePAS','uses'=>'personnel\personnelController@changeActiveState']);
                Route::get('/personnel/activate/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_UPDATE, 'as'=>'activate_personnel','uses'=>'personnel\personnelController@activate']);
                Route::get('/personnel/deactivate/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_UPDATE, 'as'=>'deactivate_personnel','uses'=>'personnel\personnelController@deactivate']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_CU], function () {
                Route::post('/personnel/store/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_CREATE, 'uses' => 'personnel\personnelController@store']);
                Route::post('/personnel/import', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_CREATE, 'as'=>'personnel_import','uses'=>'personnel\personnelController@importFromExcel']);
                Route::post('/personnel/update/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_UPDATE, 'uses' => 'personnel\personnelController@update']);
                Route::post('/personnel/fonction/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_CREATE, 'uses' => 'personnel\fonction_personnelController@store']);
                Route::post('/personnel/fonction/update', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_UPDATE, 'uses' => 'personnel\fonction_personnelController@update']);
            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_DELETE], function () {
                Route::get('/personnel/destroy/{id}', 'personnel\personnelController@destroy');
                Route::get('/personnel/fonction/destroy/{id}', 'personnel\fonction_personnelController@destroy');
            });







    ////routes pour les enseignements
    ///

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE], function () {
                Route::get('/personnel/emploi-du-temps/', function () {
                    return redirect()->route('emploi_temps_index');
                });
                Route::get('/emploi-du-temps/plages-horaires/', ['as'=>'plages_horaires','uses'=>'personnel\emploiTempsController@plages']);
                Route::get('/emploi-du-temps/cours-enseignants/', ['as'=>'cours_enseignants','uses'=>'personnel\emploiTempsController@coursIndex']);
                Route::get('/personnel/fiches-progression', ['as'=>'fiches_progression','uses'=>'personnel\ficheProgressionController@index']);
                Route::get('/personnel/fiches-progression/enseignants', ['as'=>'fiches_progression_enseignants','uses'=>'personnel\ficheProgressionController@enseignants']);
                Route::get('/personnel/fiches-progression/enseignants/{id}/cours', ['as'=>'fiches_progression_enseignant_cours','uses'=>'personnel\ficheProgressionController@coursEnseignant']);
                Route::get('/personnel/fiches-progression/enseignants/{id}/fiches', ['as'=>'fiches_progression_enseignant_fiches','uses'=>'personnel\ficheProgressionController@fichesEnseignant']);
                Route::get('/personnel/fiches-progression/par-specialite', ['as'=>'fiches_progression_par_specialite','uses'=>'personnel\ficheProgressionController@parSpecialite']);
                Route::get('/personnel/fiches-progression/manquantes', ['as'=>'fiches_progression_manquantes','uses'=>'personnel\ficheProgressionController@manquantes']);
                Route::get('/personnel/fiches-progression/show/{id}', ['as'=>'fiches_progression_show','uses'=>'personnel\ficheProgressionController@show']);
                Route::get('/personnel/fiches-progression/download/{id}', ['as'=>'fiches_progression_download','uses'=>'personnel\ficheProgressionController@download']);
                Route::get('/emploi-du-temps/cours-enseignants/create', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'cours_enseignants_create','uses'=>'personnel\emploiTempsController@createCours']);
                Route::get('/emploi-du-temps/cours-enseignants/specialites/{id}/create', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'cours_enseignants_create_context','uses'=>'personnel\emploiTempsController@createCours']);
                Route::get('/emploi-du-temps/cours-enseignants/edit/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'cours_enseignants_edit','uses'=>'personnel\emploiTempsController@editCours']);
                Route::get('/emploi-du-temps/cours-enseignants/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'as'=>'cours_enseignants_destroy','uses'=>'personnel\emploiTempsController@destroyCours']);
                Route::get('/emploi-du-temps/emplois-specialites/', ['as'=>'emploi_temps_specialites','uses'=>'personnel\emploiTempsController@specialites']);
                Route::get('/emploi-du-temps/emplois-enseignants/', ['as'=>'emploi_temps_enseignants','uses'=>'personnel\emploiTempsController@enseignants']);
                Route::get('/emploi-du-temps/enseignants/{id}/definir/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'definir_emploi_enseignant','uses'=>'personnel\emploiTempsController@definirEnseignant']);
                Route::get('/emploi-du-temps/emplois-permanents/', ['as'=>'emplois_permanents','uses'=>'personnel\emploiTempsController@emploisPermanents']);
                Route::get('/emploi-du-temps/volumes-specialites', ['as'=>'volumes_specialites','uses'=>'personnel\emploiTempsController@volumes']);
                Route::get('/emploi-du-temps/decompte-heures/', ['as'=>'decompte_heures','uses'=>'personnel\emploiTempsController@decompte']);
                Route::get('/emploi-du-temps/decompte-heures/saisie-manuelle', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'biometrie_heures_manual_create','uses'=>'personnel\emploiTempsController@manualCreate']);
                Route::get('/emploi-du-temps/decompte-heures/export/csv', ['as'=>'biometrie_heures_csv','uses'=>'personnel\emploiTempsController@exportHeuresCsv']);
                Route::get('/emploi-du-temps/personnel-entites/', function () { return redirect()->to(url('/personnel/fonction/pers/')); });
                Route::get('/emploi-du-temps/discipline-personnels/', function () { return redirect()->to(url('/personnel/discipline/')); });
                Route::get('/emploi-du-temps/matieres/', function () { return redirect()->to(url('/admin/matieres/')); });
                Route::get('/emploi-du-temps/programmes-specialites/', function () { return redirect()->to(url('/admin/programmes/')); });
                Route::get('/emploi-du-temps/salles/', function () { return redirect()->to(url('/admin/salles/')); });
                Route::get('/emploi-du-temps/taux-horaires/', function () { return redirect()->to(url('/admin/taux/')); });
                Route::get('/emploi-du-temps/salaires-permanents/', function () { return redirect()->to(url('/admin/salaire/')); });
                Route::get('/emploi-du-temps/paie-permanents/', function () { return redirect()->to(url('/comptabilite/payement-salaire/')); });
                Route::get('/emploi-du-temps/biometrie-permanents/', function () { return redirect()->route('decompte_heures'); });
                Route::get('/personnel/find/', 'personnel\dispenseController@find');
                Route::get('/personnel/dispenses/', 'personnel\dispenseController@index1');
                Route::get('/personnel/dispense/{id}', 'personnel\dispenseController@index');
                Route::any('/personnel/heures-prevues/show/', 'personnel\presenceController@show_heure_prevue');
                Route::get('/personnel/emploi-temps/show/', 'personnel\dispenseController@show');

            });
            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CU], function () {
                Route::post('/emploi-du-temps/plages-horaires/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'plages_horaires_store','uses'=>'personnel\emploiTempsController@storePlage']);
                Route::post('/emploi-du-temps/plages-horaires/update/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'plages_horaires_update','uses'=>'personnel\emploiTempsController@updatePlage']);
                Route::post('/emploi-du-temps/cours-enseignants/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'cours_enseignants_store','uses'=>'personnel\emploiTempsController@storeCours']);
                Route::post('/emploi-du-temps/cours-enseignants/update/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'cours_enseignants_update','uses'=>'personnel\emploiTempsController@updateCours']);
                Route::post('/personnel/fiches-progression/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'fiches_progression_store','uses'=>'personnel\ficheProgressionController@store']);
                Route::post('/emploi-du-temps/emplois-permanents/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'emplois_permanents_store','uses'=>'personnel\emploiTempsController@storeEmploiPermanent']);
                Route::post('/emploi-du-temps/decompte-heures/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'decompte_heures_store','uses'=>'personnel\emploiTempsController@storeHeure']);
                Route::post('/emploi-du-temps/decompte-heures/import', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'biometrie_heures_store','uses'=>'personnel\emploiTempsController@storeBiometrie']);
                Route::post('/emploi-du-temps/decompte-heures/saisie-manuelle', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'as'=>'biometrie_heures_manual_store','uses'=>'personnel\emploiTempsController@manualStore']);
                Route::post('/emploi-du-temps/decompte-heures/{id}/consolider', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'biometrie_heures_consolider','uses'=>'personnel\emploiTempsController@consoliderBiometrie']);
                Route::post('/emploi-du-temps/decompte-heures/{id}/clear', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'biometrie_heures_clear','uses'=>'personnel\emploiTempsController@clearBiometrie']);
                Route::post('/emploi-du-temps/decompte-heures/{id}/destroy', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'as'=>'biometrie_heures_destroy','uses'=>'personnel\emploiTempsController@destroyBiometrie']);
                Route::post('/emploi-du-temps/decompte-heures/association', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'as'=>'biometrie_heures_mapping','uses'=>'personnel\emploiTempsController@storeMapping']);
                Route::get('/personnel/dispense/store', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'uses' => 'personnel\dispenseController@store']);
                Route::any('/personnel/dispense/edit', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'uses' => 'personnel\dispenseController@edit']);
                Route::post('/personnel/dispense/create', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CREATE, 'uses' => 'personnel\dispenseController@create']);
                Route::post('/personnel/dispense/update', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'uses' => 'personnel\dispenseController@update']);
                Route::any('/personnel/heures-prevues/update/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'uses' => 'personnel\presenceController@update_heure_prevue']);////
                Route::any('/personnel/emploi-temps/edit/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_UPDATE, 'uses' => 'personnel\dispenseController@edit_timetable']);
            });
            Route::get('/personnel/dispense/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'uses' => 'personnel\dispenseController@destroy']);
            Route::get('/emploi-du-temps/plages-horaires/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'as'=>'plages_horaires_destroy', 'uses'=>'personnel\emploiTempsController@destroyPlage']);
            Route::get('/emploi-du-temps/emplois-permanents/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'as'=>'emplois_permanents_destroy', 'uses'=>'personnel\emploiTempsController@destroyEmploiPermanent']);
            Route::get('/personnel/fiches-progression/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'as'=>'fiches_progression_destroy','uses'=>'personnel\ficheProgressionController@destroy']);





    ////routes pour les titulaires


            Route::get('/personnel/titulaire', 'personnel\titulaireController@index');
            Route::get('/personnel/titulaire/destroy/{id}', 'personnel\titulaireController@destroy');
            Route::post('/personnel/titulaire/store', 'personnel\titulaireController@store');
            Route::post('/personnel/titulaire/update', 'personnel\titulaireController@update');


    //pour les  abscences et retards

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_DISCIPLINE], function () {
                Route::get('/personnel/discipline/', 'personnel\disciplineController@index');
                Route::get('/personnel/discipline/show/{id}', 'personnel\disciplineController@create');
                Route::get('/personnel/discipline/show/precision/{id}', 'personnel\disciplineController@show');
                Route::any('/personnel/discipline/state/', ['as'=>'etat_disciplinaire_personnel','uses'=>'personnel\disciplineController@state']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_DISCIPLINE_CU], function () {
                Route::post('/personnel/discipline/justify/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_DISCIPLINE_UPDATE, 'uses' => 'personnel\disciplineController@justify']);
                Route::post('/personnel/discipline/update/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_DISCIPLINE_UPDATE, 'as'=>'update_discipline_personnel','uses'=>'personnel\disciplineController@update']);
            });




            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_HOUR_COUNT], function () {
                Route::get('/personnel/heures-effectuees/', 'personnel\presenceController@index');
                Route::get('/personnel/heures-effectuees/{id}', 'personnel\presenceController@create');

            });
            //pour les prÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©sences
            Route::get('/personnel/heures-effectuees/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_HOUR_COUNT_DELETE, 'uses' => 'personnel\presenceController@destroy']);
            Route::post('/personnel/heures-effectuees/update/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_HOUR_COUNT_UPDATE, 'uses' => 'personnel\presenceController@update']);




            //pour la comptabilite
            Route::get('/comptabilite/', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING, 'uses' => 'comptabilite\comptabiliteController@index']);
            // les dÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã¢â‚¬Â ÃƒÂ¢Ã¢â€šÂ¬Ã¢â€žÂ¢ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬ÃƒÂ¢Ã¢â‚¬Å¾Ã‚Â¢ÃƒÆ’Ã†â€™Ãƒâ€ Ã¢â‚¬â„¢ÃƒÆ’Ã‚Â¢ÃƒÂ¢Ã¢â‚¬Å¡Ã‚Â¬Ãƒâ€¦Ã‚Â¡ÃƒÆ’Ã†â€™ÃƒÂ¢Ã¢â€šÂ¬Ã…Â¡ÃƒÆ’Ã¢â‚¬Å¡Ãƒâ€šÃ‚Â©caissements
 //categorie budgetaire
            Route::post('/comptabilite/categorie/update', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_CATEGORY_UPDATE, 'uses' => 'comptabilite\Categorie_budgetairesController@update']);
            Route::post('/comptabilite/categorie/store', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_CATEGORY_CREATE, 'uses' => 'comptabilite\Categorie_budgetairesController@store']);
            Route::get('/comptabilite/categorie/', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_CATEGORY_READ, 'uses' => 'comptabilite\Categorie_budgetairesController@index']);
            Route::get('/comptabilite/categorie/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_CATEGORY_DELETE, 'uses' => 'comptabilite\Categorie_budgetairesController@destroy']);

            //ligne budgetaire

        Route::post('/comptabilite/ligne/update', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_UPDATE, 'uses' => 'comptabilite\LigneBudgetController@update']);
        Route::post('/comptabilite/ligne/store', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_CREATE, 'uses' => 'comptabilite\LigneBudgetController@store']);
        Route::get('/comptabilite/ligne/', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_READ, 'uses' => 'comptabilite\LigneBudgetController@index']);
        Route::get('/comptabilite/ligne/get-data/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_READ, 'uses' => 'comptabilite\LigneBudgetController@getLineData']);
        Route::get('/comptabilite/ligne/report/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_READ, 'uses' => 'comptabilite\LigneBudgetController@report']);
        Route::get('/comptabilite/print-ligne/report/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_READ, 'uses' => 'comptabilite\LigneBudgetController@print_line']);
        Route::get('/comptabilite/categorie/report/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_CATEGORY_READ, 'uses' => 'comptabilite\LigneBudgetController@categorie_report']);


        Route::get('/comptabilite/ligne/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_BUDGET_LINE_DELETE, 'uses' => 'comptabilite\LigneBudgetController@destroy']);

        Route::get('/comptabilite/donnee/ligne/{id}', 'comptabilite\LigneBudgetController@edit_data');
        Route::post('/comptabilite/donnee/ligne/{id}', 'comptabilite\LigneBudgetController@update_data');

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_WITHDRAWAL], function () {
                Route::get('/admin/retrait/','admin\decaissementController@index');
                Route::get('/search/retrait', 'searchController@retrait');
            });


            Route::post('/admin/retrait/store',['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_WITHDRAWAL_CREATE, 'uses' => 'admin\decaissementController@store']);
            Route::post('/admin/retrait/update',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE, 'uses' => 'admin\decaissementController@update']);
            Route::post('/admin/retrait/update-montant',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE, 'as' => 'update_retrait_amount', 'uses' => 'admin\decaissementController@update_amount']);
            // le payement des salaires

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_STAFF_PAYMENT], function () {
                Route::get('/comptabilite/payement-salaire/','comptabilite\payementController@index');
                Route::get('/comptabilite/payement-salaire/show/{id}','comptabilite\payementController@show');

            });

           Route::get('/comptabilite/payement-salaire/print/{id}',['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_STAFF_PAYMENT_PRINT_RECEIPT, 'uses' => 'comptabilite\payementController@print_bulletin']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_STAFF_PAYMENT_CU], function () {
                Route::get('/comptabilite/payement-salaire/create/{id}','comptabilite\payementController@create');
                Route::post('/comptabilite/payement-salaire/store/','comptabilite\payementController@store');
            });

            //Bilan
            Route::any('/comptabilite/bilan/entrees',['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_ENTRY_BALANCE, 'uses' => 'comptabilite\bilanComptaController@index']);
            Route::any('/comptabilite/bilan/sorties',['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_OUTPUT_BALANCE, 'uses' => 'comptabilite\bilanComptaController@indexSorties']);








    });
//});

Route::get('/gestion/login/','gestion\EtudiantcontrollerCon@index');
Route::post('/gestion/login/etudiant','gestion\EtudiantcontrollerCon@store');
Route::get('/gestion/login/etudiant/annees','gestion\EtudiantcontrollerCon@liste_annee');
Route::get('/gestion/login/etudiant/liste','gestion\EtudiantcontrollerCon@notesLockedOrAnnees');
Route::post('/gestion/login/etudiant/liste','gestion\EtudiantcontrollerCon@liste_inscription');
Route::any('/user-profile/edit-password1','gestion\EtudiantcontrollerCon@change_password');
Route::get('/gestion/login/etudiant/disconnect_user','gestion\EtudiantcontrollerCon@disconnect_user');


Route::group(['middleware'=>'start'],function () {

    Route::get('/start/', 'startController@index');
    Route::post('/start/store', 'startController@store');
    Route::post('/start/store/admin', 'startController@storeAdmin');

});



Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
