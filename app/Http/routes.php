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


Route::group(['middleware'=>'auth'],function (){
    Route::get('/', 'WelcomeController@index');
    Route::get('home', 'WelcomeController@index');

    Route::group(['middleware'=>'lvl0'],function (){
        Route::post('/update-school-year/', 'WelcomeController@editYear');

    });

    Route::group(['middleware'=>'lvl1'],function (){




        //pour les recherches
        Route::get('/search/retrait', 'searchController@retrait');
        //pour les utilisateurs
        Route::get('/utilisateurs/', 'user\userController@index');
        Route::post('/utilisateurs/activer', 'user\userController@activate');
        Route::get('/utilisateurs/desactiver/{id}', 'user\userController@deactivate');

    });

    //Tableau de bord d'admin
    Route::group(['middleware'=>'lvl2'],function (){
        Route::get('/admin/', 'admin\adminController@index');
//routes pour les cycles
        Route::get('/admin/cycles/', 'admin\cycleController@index');
        Route::get('/admin/cycles/destroy/{id}', 'admin\cycleController@destroy');
        Route::post('/admin/cycles/store', 'admin\cycleController@store');
// Les niveaux
        Route::get('/admin/niveaux/', 'admin\niveauController@index');
        Route::get('/admin/niveaux/destroy/{id}', 'admin\niveauController@destroy');
        Route::post('/admin/niveaux/store', 'admin\niveauController@store');
//Les classes
        Route::get('/admin/classes/', 'admin\classeController@index');
        Route::get('/admin/classes/destroy/{id}', 'admin\classeController@destroy');
        Route::post('/admin/classes/update', 'admin\classeController@update');
        Route::post('/admin/classes/store', 'admin\classeController@store');
//Les scolarités
        Route::get('/admin/scolarites/', 'admin\scolariteController@index');
        Route::get('/admin/scolarite/destroy/{id}', 'admin\scolariteController@destroy');
        Route::post('/admin/scolarite/update', 'admin\scolariteController@update');
        Route::post('/admin/scolarite/store', 'admin\scolariteController@store');
//Matières
        Route::get('/admin/matieres/', 'admin\matiereController@index');
        Route::post('/admin/matieres/update/', 'admin\matiereController@update');
        Route::post('/admin/matieres/store', 'admin\matiereController@store');
//Programmes
        Route::get('/admin/programmes/', 'admin\programmeController@index');
        Route::get('/admin/programmes/selection-matieres/{id}', 'admin\programmeController@create');
        Route::post('/admin/programmes/store/', 'admin\programmeController@store');
        Route::get('/admin/programmes/destroy/{id}', 'admin\programmeController@destroy');

//routes pour les fonctions
        Route::get('/admin/fonctions/', 'admin\fonctionController@index');
        Route::get('/admin/fonctions/destroy/{id}', 'admin\fonctionController@destroy');
        Route::post('/admin/fonctions/store', 'admin\fonctionController@store');
        Route::post('/admin/fonctions/update', 'admin\fonctionController@update');


//routes pour les horaires
        Route::get('/admin/horaires/', 'admin\horaireController@index');
        Route::get('/admin/horaires/destroy/{id}', 'admin\horaireController@destroy');
        Route::post('/admin/horaires/store', 'admin\horaireController@store');
        Route::post('/admin/horaires/update', 'admin\horaireController@update');

///routes pour les salaires

        Route::get('/admin/salaire/','admin\salaireController@index');
        Route::post('/admin/salaire/store', 'admin\salaireController@store');
        Route::post('/admin/salaire/update', 'admin\salaireController@update');
        Route::get('/admin/salaire/destroy/{id}', 'admin\salaireController@destroy');

        //pour les taux
        Route::get('/admin/taux/', 'admin\tauxController@index');
        Route::get('/admin/taux/create/{id}', 'admin\tauxController@create');
        Route::post('/admin/taux/store/', 'admin\tauxController@store');


    });


    Route::group(['middleware'=>'lvl19'],function (){
        //Inscriptions
        Route::get('/inscriptions/eleves/search', 'inscriptions\enregistrementController@index');
        Route::get('/inscriptions/enregistrement/create/', 'inscriptions\enregistrementController@create');
        Route::get('/inscriptions/enregistrement/edit/{id}', 'inscriptions\enregistrementController@edit');
        Route::post('/inscriptions/enregistrement/store/', 'inscriptions\enregistrementController@store');
        Route::post('/inscriptions/enregistrement/update/{id}', 'inscriptions\enregistrementController@update');
        Route::get('/inscriptions/', 'inscriptions\inscriptionController@index');
        Route::get('/inscriptions/new', 'inscriptions\inscriptionController@search');
        Route::get('/inscriptions/find', 'inscriptions\inscriptionController@find');
        Route::get('/inscriptions/create/{id}', 'inscriptions\inscriptionController@create');
        Route::post('/inscriptions/store', 'inscriptions\inscriptionController@store');
        Route::get('/inscriptions/payement/', 'inscriptions\payementController@index');
        Route::get('/inscriptions/payement/search', 'inscriptions\payementController@search');
        Route::get('/inscriptions/payement/create/{id}', 'inscriptions\payementController@create');
        Route::post('/inscriptions/payement/store/{id}', 'inscriptions\payementController@store');
        Route::get('/inscriptions/recu','inscriptions\inscriptionController@show');
        Route::get('/inscriptions/print/{id}','inscriptions\inscriptionController@print_receipe');
        Route::get('/inscriptions/payement/print/{id}','inscriptions\payementController@print_receipe');
        Route::get('/inscriptions/payement/list/{id}','inscriptions\payementController@index_liste');
        Route::get('/inscriptions/selection-listes','inscriptions\listesController@index');
        Route::get('/inscriptions/listes/show/{id}','inscriptions\listesController@show');

    });

    Route::group(['middleware'=>'lvl18'],function (){
        //Les élèves
        Route::get('/eleves/', 'eleves\eleveController@index');
        Route::get('/eleves/discipline/', 'eleves\disciplineController@index');
        Route::get('/eleves/discipline/search', 'eleves\disciplineController@search');
        Route::get('/eleves/discipline/show/{id}', 'eleves\disciplineController@create');
        Route::post('/eleves/discipline/update/', 'eleves\disciplineController@update');

        Route::get('/eleves/notes/','eleves\noteController@index');
        Route::get('/eleves/notes/matieres/{id}','eleves\noteController@matiere');
        Route::post('/eleves/notes/create/{id}','eleves\noteController@create');
        Route::post('/eleves/notes/store/','eleves\noteController@store');

        Route::get('/eleves/bulletins/','eleves\bulletinController@index');
        Route::get('/eleves/bulletins/search/','eleves\bulletinController@index2');
        Route::get('/eleves/bulletins/find/','eleves\bulletinController@search');
        Route::post('/eleves/bulletins/print-single-seq','eleves\bulletinController@print_single_seq');

    });

    Route::group(['middleware'=>'lvl17'],function (){
        //routes pour les personnels

        Route::get('/personnel/', 'personnel\personnelController@index');
        Route::get('/personnel/search', 'personnel\personnelController@show');
        Route::get('/personnel/list', 'personnel\personnelController@index_personnel');
        Route::get('/personnel/edit/{id}', 'personnel\personnelController@edit');
        Route::post('/personnel/store/', 'personnel\personnelController@store');
        Route::post('/personnel/update/{id}', 'personnel\personnelController@update');


////routes pour les fonctions_personnel
        Route::get('/personnel/fonction/pers/', 'personnel\fonction_personnelController@index2');
        Route::get('/personnel/fonction/{id}', 'personnel\fonction_personnelController@index');
        Route::get('/personnel/fonction/destroy/{id}', 'personnel\fonction_personnelController@destroy');
        Route::post('/personnel/fonction/store', 'personnel\fonction_personnelController@store');
        Route::post('/personnel/fonction/update', 'personnel\fonction_personnelController@update');


////routes pour les enseignements

        Route::post('/personnel/dispense/create', 'personnel\dispenseController@create');
        Route::get('/personnel/find/', 'personnel\dispenseController@find');
        Route::get('/personnel/dispense/destroy/{id}', 'personnel\dispenseController@destroy');
        Route::get('/personnel/dispense/store', 'personnel\dispenseController@store');
        Route::post('/personnel/dispense/update', 'personnel\dispenseController@update');
        Route::get('/personnel/dispenses/', 'personnel\dispenseController@index1');
        Route::get('/personnel/dispense/{id}', 'personnel\dispenseController@index');


////routes pour les titulaires


        Route::get('/personnel/titulaire', 'personnel\titulaireController@index');
        Route::get('/personnel/titulaire/destroy/{id}', 'personnel\titulaireController@destroy');
        Route::post('/personnel/titulaire/store', 'personnel\titulaireController@store');
        Route::post('/personnel/titulaire/update', 'personnel\titulaireController@update');

//pour les présences , abscences et retards
        Route::get('/personnel/discipline/', 'personnel\disciplineController@index');
        Route::get('/personnel/discipline/destroy/{id_p}/{id}', 'personnel\disciplineController@destroy');
        Route::get('/personnel/discipline/{id}', 'personnel\disciplineController@show');

        Route::post('/personnel/discipline/store', 'personnel\disciplineController@store');


    });

    //pour la comptabilite
    Route::get('/comptabilite/', 'comptabilite\comptabiliteController@index');
            // les décaissements
    Route::get('/admin/retrait/','admin\decaissementController@index');
    Route::post('/admin/retrait/store','admin\decaissementController@store');
         // le payement des salaires
    Route::get('/comptabilite/payement-salaire/','comptabilite\payementController@index');
    Route::get('/comptabilite/payement-salaire/create/{id}','comptabilite\payementController@create');
    Route::post('/comptabilite/payement-salaire/store/','comptabilite\payementController@store');





    //pour les recherches

    Route::get('/search/personnel', 'searchController@personnel');
    Route::get('/search/eleve', 'searchController@eleve');









});
Route::group(['middleware'=>'start'],function () {

    Route::get('/start/', 'startController@index');
    Route::post('/start/store', 'startController@store');
    Route::post('/start/store/admin', 'startController@storeAdmin');
});



Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);
