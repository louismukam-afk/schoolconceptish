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
            Route::get('/utilisateurs/desactiver/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTIVATE_DEACTIVATE,'uses' =>'user\userController@deactivate']);

            //routes pour les roles
            Route::get('admin/roles/{id}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION,'uses'=>'admin\RoleController@index','as'=>'role_management']);
            Route::post('admin/roles/store',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU,'uses'=>'admin\RoleController@store','as'=>'store_role']);
            Route::get('admin/roles/delete/{id}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU,'uses'=>'admin\RoleController@destroy','as'=>'delete_role']);

              Route::get('admin/actions/{id}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION,'uses'=>'admin\actionController@index','as'=>'action_management']);
            Route::post('admin/actions/update',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_USER_ACTION_CU,'uses'=>'admin\actionController@update','as'=>'update_actions']);


            Route::post('/inscriptions/payement/update', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_PAYMENT_UPDATE,'uses'=>'inscriptions\payementController@update','as'=>'update_payement']);
            Route::get('/inscriptions/enregistrement/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_DELETE,'uses'=>'inscriptions\enregistrementController@destroy','as'=>'destroy_eleve']);
            Route::get('/inscriptions/remove/{id_eleve}',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_SUBCRIPTION_DELETE, 'uses' => 'inscriptions\inscriptionController@destroy']);



        //Tableau de bord d'admin
            Route::get('/inscriptions/eleve/set-status',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'uses' => 'inscriptions\enregistrementController@set_status']);
            Route::post('/inscriptions/eleve/set-multi-status',['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STUDENT_REGISTRATION_STATUS_UPDATE, 'uses' => 'inscriptions\enregistrementController@set_multi_status']);


            Route::get('/admin/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN, 'uses' => 'admin\adminController@index']);
            //routes pour le verrou des notes
            Route::get('/admin/verrou-note/', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK, 'uses' => 'admin\verrou_noteController@index']);
            Route::get('/admin/verrou-note/activate/{sequence}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'uses' => 'admin\verrou_noteController@activate']);
            Route::get('/admin/verrou-note/deactivate/{sequence}', ['middleware' => 'action', 'action' => Action::ACTION_ADMIN_NOTE_LOCK_UPDATE, 'uses' => 'admin\verrou_noteController@deactivate']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CLASS_SETTINGS], function () {
                Route::get('/admin/cycles/', 'admin\cycleController@index');
                Route::get('/admin/nniveaux/', 'admin\nniveauController@index');
                Route::get('/admin/salles/', 'admin\salleController@index');
                Route::get('/admin/niveaux/', 'admin\niveauController@index');
                Route::get('/admin/classes/', 'admin\classeController@index');

                Route::get('/admin/matieres/', 'admin\matiereController@index');
                Route::get('/admin/programmes/', 'admin\programmeController@index');
                Route::get('/admin/programmes/selection-matieres/', 'admin\programmeController@create');
                Route::get('/admin/programmes/groupe/{id}', 'admin\groupeController@create');



            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CLASS_SETTINGS_CU], function () {
                Route::post('/admin/cycles/store', 'admin\cycleController@store');
                Route::post('/admin/cycles/update', 'admin\cycleController@update');
                Route::post('/admin/nniveaux/store', 'admin\nniveauController@store');
                Route::post('/admin/nniveaux/update', 'admin\nniveauController@update');
                Route::post('/admin/salles/store', 'admin\salleController@store');
                Route::post('/admin/salles/update', 'admin\salleController@update');
                Route::post('/admin/niveaux/store', 'admin\niveauController@store');
                Route::post('/admin/niveaux/update', 'admin\niveauController@update');
                Route::post('/admin/classes/update', 'admin\classeController@update');
                Route::post('/admin/classes/store', 'admin\classeController@store');

                Route::post('/admin/matieres/update/', 'admin\matiereController@update');
                Route::post('/admin/matieres/store', 'admin\matiereController@store');
                Route::post('/admin/programmes/store/', 'admin\programmeController@store');
                Route::post('/admin/programmes/update/', 'admin\programmeController@update');
                Route::post('/admin/programmes/groupe/store/', 'admin\groupeController@store');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_CLASS_SETTINGS_DELETE], function () {
                Route::get('/admin/cycles/destroy/{id}', 'admin\cycleController@destroy');
                Route::get('/admin/nniveaux/destroy/{id}', 'admin\nniveauController@destroy');
                Route::get('/admin/salles/destroy/{id}', 'admin\salleController@destroy');
                Route::get('/admin/niveaux/destroy/{id}', 'admin\niveauController@destroy');
                Route::get('/admin/classes/destroy/{id}', 'admin\classeController@destroy');

                Route::get('/admin/matieres/destroy/{id}', 'admin\matiereController@destroy');
                Route::get('/admin/programmes/destroy/{id}', 'admin\programmeController@destroy');

                Route::get('/admin/programmes/groupe/destroy/{id}', 'admin\groupeController@destroy');


            });


            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FEES_SETTINGS], function () {
                Route::get('/admin/frais/', 'admin\FraisController@index');
                Route::get('/admin/scolarites/', 'admin\scolariteController@index');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FEES_SETTINGS_CU], function () {
                Route::post('/admin/frais/store', 'admin\FraisController@store');
                Route::post('/admin/frais/update', 'admin\FraisController@update');
                Route::post('/admin/scolarite/update', 'admin\scolariteController@update');
                Route::post('/admin/scolarite/store', 'admin\scolariteController@store');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_FEES_SETTINGS_DELETE], function () {
                Route::get('/admin/frais/destroy/{id}', 'admin\FraisController@destroy');
                Route::get('/admin/scolarite/destroy/{id}', 'admin\scolariteController@destroy');

            });



            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_SETTINGS], function () {
                Route::get('/admin/fonctions/', 'admin\fonctionController@index');
                Route::get('/admin/horaires/', 'admin\horaireController@index');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_SETTINGS_CU], function () {
                Route::post('/admin/fonctions/store', 'admin\fonctionController@store');
                Route::post('/admin/fonctions/update', 'admin\fonctionController@update');
                Route::post('/admin/horaires/store', 'admin\horaireController@store');
                Route::post('/admin/horaires/update', 'admin\horaireController@update');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_SETTINGS_DELETE], function () {
                Route::get('/admin/fonctions/destroy/{id}', 'admin\fonctionController@destroy');
                Route::get('/admin/horaires/destroy/{id}', 'admin\horaireController@destroy');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS], function () {
                Route::get('/admin/salaire/','admin\salaireController@index');
                Route::get('/admin/taux/', 'admin\tauxController@index');
                Route::get('/admin/taux/create/{id}', 'admin\tauxController@create');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_CU], function () {
                Route::post('/admin/salaire/store', 'admin\salaireController@store');
                Route::post('/admin/salaire/update', 'admin\salaireController@update');
                Route::post('/admin/taux/store/', 'admin\tauxController@store');


            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ADMIN_STAFF_FINANCIAL_SETTINGS_DELETE], function () {
                Route::get('/admin/salaire/destroy/{id}', 'admin\salaireController@destroy');
                Route::get('/admin/taux/destroy/{id}', 'admin\tauxController@destroy');
            });




            //Inscriptions
            Route::get('/inscriptions/', ['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION, 'uses' => 'inscriptions\inscriptionController@index']);



            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION], function () {
                Route::get('/inscriptions/new', 'inscriptions\inscriptionController@search');
                Route::get('/inscriptions/eleves/search', 'inscriptions\enregistrementController@index');
                Route::get('/inscriptions/find', 'inscriptions\inscriptionController@find');
                Route::get('/inscriptions/find-inscription', 'inscriptions\inscriptionController@find_inscription');
                Route::get('/inscriptions/payement/', 'inscriptions\payementController@index');
                Route::get('/inscriptions/payement/search', 'inscriptions\payementController@search');
                Route::get('/inscriptions/enregistrement/edit/{id}', 'inscriptions\enregistrementController@edit');
                Route::get('/inscriptions/eleve/status','inscriptions\enregistrementController@status_eleve');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_STUDENT_CU], function () {

                Route::get('/inscriptions/enregistrement/create/', 'inscriptions\enregistrementController@create');
                Route::post('/inscriptions/enregistrement/store/', 'inscriptions\enregistrementController@store');
                Route::post('/inscriptions/enregistrement/update/{id}', 'inscriptions\enregistrementController@update');
            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_CREATE], function () {
                Route::get('/inscriptions/create/{id}', 'inscriptions\inscriptionController@create');
                Route::post('/inscriptions/store', 'inscriptions\inscriptionController@store');

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
                Route::get('/inscriptions/selection-listes','inscriptions\listesController@index');
                Route::get('/inscriptions/listes/show',['as'=>'show_sco_liste','uses'=>'inscriptions\listesController@show']);
                Route::get('/inscriptions/listes/frais/show', ['as'=>'show_frais_liste','uses'=>'inscriptions\listesController@showFrais']);

            });
            Route::get('/inscriptions/fiche-scolarite',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_LIST_TUITION_SHEET,'uses' => 'inscriptions\listesController@fiche_scolarite']);
            Route::get('/inscriptions/print/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT, 'uses' => 'inscriptions\inscriptionController@print_receipe']);
            Route::get('/inscriptions/payement/print/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_PAYMENT_PRINT_RECEIPT, 'uses' => 'inscriptions\payementController@print_receipe']);


            //Route::get('/inscriptions/recu','inscriptions\inscriptionController@show');
            Route::get('/inscriptions/print-certificate/{id}',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SUBSCRIPTION_CERTIFICATE, 'uses' => 'inscriptions\inscriptionController@certificate']);
            Route::any('/inscriptions/photos/',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_STUDENT_PHOTO, 'uses' => 'inscriptions\photosController@create']);
            Route::any('/inscriptions/photos/store',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_STUDENT_PHOTO_CU, 'as'=>'store_photo','uses'=>'inscriptions\photosController@store']);


            Route::get('/inscriptions/eleve/scan-carte',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCAN_CARD, 'uses' => 'inscriptions\inscriptionController@index_scan_carte']);
            Route::get('/inscriptions/eleve/process-scan',['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_SCAN_CARD, 'uses' => 'inscriptions\inscriptionController@process_scan']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_REGISTRATION_STUDENT_CARD], function () {
                Route::any('/inscriptions/cartes/',['as'=>'show_carte','uses'=>'inscriptions\photosController@show_carte']);
                Route::any('/inscriptions/cartes/eleve/',['as'=>'show_carte_eleve','uses'=>'inscriptions\photosController@search_carte_eleve']);

                
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





            //Les élèves

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_DISCIPLINE], function () {
                Route::get('/eleves/discipline/', 'eleves\disciplineController@index');
                Route::get('/eleves/discipline/search', 'eleves\disciplineController@search');
                Route::get('/eleves/discipline/show/{id}', 'eleves\disciplineController@create');
                Route::get('/eleves/discipline/show/precision/{id}', 'eleves\disciplineController@show');
                Route::any('/eleves/discipline/state/', ['as'=>'etat_disciplinaire','uses'=>'eleves\disciplineController@state']);

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_DISCIPLINE_CU], function () {
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
                Route::get('/eleves/releves_soa/print/','eleves\soareleveController@create');

                Route::get('/eleves/diplome_soa/','eleves\diplomeSoaController@index');
                Route::get('/diplome/soa/print/','eleves\diplomeSoaController@create');

                //ndere releve

                Route::get('/eleves/releves_ndere/','eleves\nderereleveController@index');
                Route::get('/eleves/releves_ndere/print/','eleves\nderereleveController@create');

                //estic releve
                Route::get('/eleves/releve_estic/','eleves\esticreleveController@index');
                Route::get('/eleves/releve_estic/print/','eleves\esticreleveController@create');
//        Route::get('/eleves/releves/get/',['uses'=>'eleves\soareleveController@get','as'=>'get_releves']);


                Route::get('/eleves/releves_bts/','eleves\bts_releveController@index');
                Route::get('/eleves/releves_bts/print/','eleves\bts_releveController@create');



            });





            Route::get('/eleves/', ['middleware' => 'action', 'action' => Action::ACTION_STUDENT, 'uses' => 'eleves\eleveController@index']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_NOTE], function () {
                Route::get('/eleves/notes/','eleves\noteController@index2');
                Route::get('/eleves/notes/show','eleves\noteController@show');
                Route::get('/eleves/notes/matieres/show/','eleves\noteController@matiere_show');
                Route::any('/eleves/notes/print_note/{id}','eleves\noteController@print_note');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STUDENT_NOTE_CU], function () {
                Route::get('/eleves/notes/edit','eleves\noteController@index');
                Route::get('/eleves/notes/matieres/','eleves\noteController@matiere');
                Route::post('/eleves/notes/create/{id}','eleves\noteController@create');
                Route::post('/eleves/notes/store/','eleves\noteController@store');
  Route::any('/eleves/notes/print_note_sn/{id}','eleves\noteController@print_note_sn');

                Route::any('/eleves/notes/print_note_cc/{id}','eleves\noteController@print_note_cc');
                Route::any('/eleves/notes/print_note_rat/{id}','eleves\noteController@print_note_rat');
                Route::any('/eleves/notes/print_note_esstic/{id}','eleves\esticnoteController@print_note');
                Route::any('/eleves/notes/print_note_soa/{id}','eleves\soanoteController@print_note');
                Route::any('/eleves/notes/print_note_ndere/{id}','eleves\nderenoteController@print_note');



                //proces verbal soa


                Route::get('/eleves/', 'eleves\eleveController@index');
                Route::get('/eleves/soa/','eleves\soanoteController@index2');

                Route::get('/eleves/notes/edit_soa','eleves\soanoteController@index');
                Route::get('/eleves/notes/show_soa','eleves\soanoteController@show');
                Route::get('/eleves/notes/matieres_soa/','eleves\soanoteController@matiere');
                Route::get('/eleves/notes/matieres/show_soa/','eleves\soanoteController@matiere_show');
                Route::post('/eleves/notes/create_soa/{id}','eleves\soanoteController@create');
                Route::post('/eleves/notes/store_soa/','eleves\soanoteController@store');

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
                Route::post('/eleves/notes/create/{id}','eleves\esticnoteController@create');
                Route::post('/eleves/notes/store_esstic/','eleves\esticnoteController@store');

            });





            //routes pour les personnels

            Route::get('/personnel/', ['middleware' => 'action', 'action' => Action::ACTION_STAFF, 'uses' => 'personnel\personnelController@index']);

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER], function () {
                Route::get('/personnel/search', 'personnel\personnelController@show');
                Route::get('/personnel/list', 'personnel\personnelController@index_personnel');
                Route::get('/personnel/edit/{id}', 'personnel\personnelController@edit');
                Route::get('/personnel/fonction/pers/', 'personnel\fonction_personnelController@index2');
                Route::get('/personnel/fonction/{id}', 'personnel\fonction_personnelController@index');

            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_CU], function () {
                Route::post('/personnel/store/', 'personnel\personnelController@store');
                Route::post('/personnel/update/{id}', 'personnel\personnelController@update');
                Route::post('/personnel/fonction/store', 'personnel\fonction_personnelController@store');
                Route::post('/personnel/fonction/update', 'personnel\fonction_personnelController@update');
            });

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_WORKER_DELETE], function () {
                Route::get('/personnel/destroy/{id}', 'personnel\personnelController@destroy');
                Route::get('/personnel/fonction/destroy/{id}', 'personnel\fonction_personnelController@destroy');
            });







    ////routes pour les enseignements
    ///

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE], function () {
                Route::get('/personnel/find/', 'personnel\dispenseController@find');
                Route::get('/personnel/dispenses/', 'personnel\dispenseController@index1');
                Route::get('/personnel/dispense/{id}', 'personnel\dispenseController@index');
                Route::any('/personnel/heures-prevues/show/', 'personnel\presenceController@show_heure_prevue');
                Route::get('/personnel/emploi-temps/show/', 'personnel\dispenseController@show');

            });
            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_CU], function () {
                Route::get('/personnel/dispense/store', 'personnel\dispenseController@store');
                Route::any('/personnel/dispense/edit', 'personnel\dispenseController@edit');
                Route::post('/personnel/dispense/create', 'personnel\dispenseController@create');
                Route::post('/personnel/dispense/update', 'personnel\dispenseController@update');
                Route::any('/personnel/heures-prevues/update/', 'personnel\presenceController@update_heure_prevue');////
                Route::any('/personnel/emploi-temps/edit/{id}', 'personnel\dispenseController@edit_timetable');
            });
            Route::get('/personnel/dispense/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_TIMETABLE_DELETE, 'uses' => 'personnel\dispenseController@destroy']);





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
                Route::post('/personnel/discipline/justify/', 'personnel\disciplineController@justify');
                Route::post('/personnel/discipline/update/', ['as'=>'update_discipline_personnel','uses'=>'personnel\disciplineController@update']);
            });




            Route::group(['middleware' => 'action', 'action' => Action::ACTION_STAFF_HOUR_COUNT], function () {
                Route::get('/personnel/heures-effectuees/', 'personnel\presenceController@index');
                Route::get('/personnel/heures-effectuees/{id}', 'personnel\presenceController@create');

            });
            //pour les présences
            Route::get('/personnel/heures-effectuees/destroy/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_HOUR_COUNT_DELETE, 'uses' => 'personnel\presenceController@destroy']);
            Route::post('/personnel/heures-effectuees/update/{id}', ['middleware' => 'action', 'action' => Action::ACTION_STAFF_HOUR_COUNT_CU, 'uses' => 'personnel\presenceController@update']);




            //pour la comptabilite
            Route::get('/comptabilite/', ['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING, 'uses' => 'comptabilite\comptabiliteController@index']);
            // les décaissements

            Route::group(['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_WITHDRAWAL], function () {
                Route::get('/admin/retrait/','admin\decaissementController@index');
                Route::get('/search/retrait', 'searchController@retrait');
            });


            Route::post('/admin/retrait/store',['middleware' => 'action', 'action' => Action::ACTION_ACCOUNTING_WITHDRAWAL_CREATE, 'uses' => 'admin\decaissementController@store']);
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
});

Route::get('/gestion/login/','gestion\EtudiantcontrollerCon@index');
Route::post('/gestion/login/etudiant','gestion\EtudiantcontrollerCon@store');
Route::get('/gestion/login/etudiant/annees','gestion\EtudiantcontrollerCon@liste_annee');
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
