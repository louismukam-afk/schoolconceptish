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
    Route::group(['middleware'=>'dc'],function (){
    Route::get('/', 'WelcomeController@index');
    Route::get('home', 'WelcomeController@index');
        Route::post('/admin/scolarite/get', ['as'=>'get_scolarite','uses'=>'admin\scolariteController@getScolarite']);

        Route::get('/admin/nniveaux/get/{id}', 'admin\nniveauController@get');

    Route::group(['middleware'=>'lvl0'],function (){
        Route::get('/admin/blk/', 'WelcomeController@blk');
        Route::get('/admin/unblk/', 'WelcomeController@unblk');

    });

    Route::group(['middleware'=>'lvl1'],function (){




        //pour les recherches
        Route::get('/search/retrait', 'searchController@retrait');
        //pour les utilisateurs
        Route::get('/utilisateurs/', 'user\userController@index');
        Route::post('/utilisateurs/activer', 'user\userController@activate');
        Route::get('/utilisateurs/desactiver/{id}', 'user\userController@deactivate');
        Route::get('/eleves/notes/delete', 'user\userController@deactivate');

    });

    //Tableau de bord d'admin
    Route::group(['middleware'=>'lvl2'],function (){
        Route::get('/admin/', 'admin\adminController@index');
        //routes pour le verrou des notes
        Route::get('/admin/verrou-note/', 'admin\verrou_noteController@index');
        Route::get('/admin/verrou-note/activate/{sequence}', 'admin\verrou_noteController@activate');
        Route::get('/admin/verrou-note/deactivate/{sequence}', 'admin\verrou_noteController@deactivate');

//routes pour les cycles
        Route::get('/admin/cycles/', 'admin\cycleController@index');
        Route::get('/admin/cycles/destroy/{id}', 'admin\cycleController@destroy');
        Route::post('/admin/cycles/store', 'admin\cycleController@store');

        //Les nniveaux
        Route::get('/admin/nniveaux/', 'admin\nniveauController@index');
        Route::get('/admin/nniveaux/destroy/{id}', 'admin\nniveauController@destroy');
        Route::post('/admin/nniveaux/store', 'admin\nniveauController@store');
        Route::post('/admin/nniveaux/update', 'admin\nniveauController@update');


        //Les salles
        Route::get('/admin/salles/', 'admin\salleController@index');
        Route::get('/admin/salles/destroy/{id}', 'admin\salleController@destroy');
        Route::post('/admin/salles/store', 'admin\salleController@store');
        Route::post('/admin/salles/update', 'admin\salleController@update');

      //Les codages
        Route::get('/admin/codages/', 'admin\codagesController@index');
        Route::get('/admin/codages/destroy/{id}', 'admin\codagesController@destroy');
        Route::post('/admin/codages/store', 'admin\codagesController@store');
        Route::post('/admin/codages/update', 'admin\codagesController@update');

        // Les frais
        Route::get('/admin/frais/', 'admin\FraisController@index');
        Route::get('/admin/frais/destroy/{id}', 'admin\FraisController@destroy');
        Route::post('/admin/frais/store', 'admin\FraisController@store');
        Route::post('/admin/frais/update', 'admin\FraisController@update');

         

// Les niveaux
        Route::get('/admin/niveaux/', 'admin\niveauController@index');
        Route::get('/admin/niveaux/destroy/{id}', 'admin\niveauController@destroy');
        Route::post('/admin/niveaux/store', 'admin\niveauController@store');
        Route::post('/admin/niveaux/update', 'admin\niveauController@update');
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
        Route::post('/admin/scolarite/get', ['as'=>'get_scolarite','uses'=>'admin\scolariteController@getScolarite']);

//Matières
        Route::get('/admin/matieres/', 'admin\matiereController@index');
        Route::post('/admin/matieres/update/', 'admin\matiereController@update');
        Route::post('/admin/matieres/store', 'admin\matiereController@store');
          Route::get('/admin/matiere/destroy/{id}', 'admin\matiereController@destroy');
//Programmes
        Route::get('/admin/programmes/', 'admin\programmeController@index');
        Route::get('/admin/programmes/selection-matieres/', 'admin\programmeController@create');
        Route::any('/admin/programmes/store/', 'admin\programmeController@store');
        Route::post('/admin/programmes/update/', 'admin\programmeController@update');
        Route::get('/admin/programmes/destroy/{id}', 'admin\programmeController@destroy');

        Route::get('/admin/programmes/groupe/{id}', 'admin\groupeController@create');
        Route::get('/admin/programmes/groupe/destroy/{id}', 'admin\groupeController@destroy');
        Route::post('/admin/programmes/groupe/store/', 'admin\groupeController@store');




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
        Route::get('/admin/taux/destroy/{id}', 'admin\tauxController@destroy');

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
        Route::get('/inscriptions/edit/{id}', 'inscriptions\inscriptionController@edit');
        Route::post('/inscriptions/store', 'inscriptions\inscriptionController@store');
        Route::get('/inscriptions/payement/', 'inscriptions\payementController@index');
        Route::get('/inscriptions/payement/get/inscription', ['uses'=>'inscriptions\payementController@getLeftInscription','as'=>'get_left_inscription']);

        Route::post('/inscriptions/update', 'inscriptions\inscriptionController@update');
        Route::get('/inscriptions/payement/', 'inscriptions\payementController@index');
        Route::get('/inscriptions/payement/search', 'inscriptions\payementController@search');
        Route::get('/inscriptions/payement/create/{id}', 'inscriptions\payementController@create');
        Route::post('/inscriptions/payement/store/{id}', 'inscriptions\payementController@store');
        Route::get('/inscriptions/recu','inscriptions\inscriptionController@show');
        Route::get('/inscriptions/print/{id}','inscriptions\inscriptionController@print_receipe');
        Route::get('/inscriptions/payement/print/{id}','inscriptions\payementController@print_receipe');
        Route::get('/inscriptions/payement/list/{id}','inscriptions\payementController@index_liste');
        Route::get('/inscriptions/selection-listes','inscriptions\listesController@index');
        Route::get('/inscriptions/listes/show/','inscriptions\listesController@show');
        Route::get('/inscriptions/print-certificate/{id}','inscriptions\inscriptionController@certificate');
        Route::get('/inscriptions/remove/{id_eleve}','inscriptions\inscriptionController@destroy');
        Route::any('/inscriptions/photos/','inscriptions\photosController@create');
        Route::any('/inscriptions/photos/store',['as'=>'store_photo','uses'=>'inscriptions\photosController@store']);
        Route::any('/inscriptions/cartes/',['as'=>'show_carte','uses'=>'inscriptions\photosController@show_carte']);
        Route::any('/inscriptions/cartes/eleve/',['as'=>'show_carte_eleve','uses'=>'inscriptions\photosController@search_carte_eleve']);


 Route::get('/inscriptions/selection-liste','inscriptions\trancheController@index');
        Route::get('/inscriptions/tranche/show/','inscriptions\trancheController@show');

//reductions

        Route::get('/inscriptions/remise/formulaire/{id}','inscriptions\inscriptionController@formulaire_remise');
        Route::post('/inscriptions/remise/reduction','inscriptions\inscriptionController@reduction');
        Route::get('/inscriptions/remise/liste/','inscriptions\inscriptionController@liste_remise');



        //liste des etudiants
         Route::get('/fiche/selection-listes','fiche\listesController@index');
        Route::get('/fiche/listes/show/','fiche\listesController@show');
          Route::get('/fiche/selection-listes','fiche\listesController@index');


    });

    Route::group(['middleware'=>'lvl18'],function (){
        //Les élèves

        Route::get('/eleves/discipline/', 'eleves\disciplineController@index');
        Route::get('/eleves/discipline/search', 'eleves\disciplineController@search');
        Route::get('/eleves/discipline/show/{id}', 'eleves\disciplineController@create');
        Route::get('/eleves/discipline/show/precision/{id}', 'eleves\disciplineController@show');
        Route::post('/eleves/discipline/justify/', 'eleves\disciplineController@justify');

        Route::post('/eleves/discipline/update/', ['as'=>'update_discipline','uses'=>'eleves\disciplineController@update']);
        Route::any('/eleves/discipline/state/', ['as'=>'etat_disciplinaire','uses'=>'eleves\disciplineController@state']);



        Route::get('/eleves/bulletins/','eleves\bulletinController@index');
        Route::get('/eleves/bulletins/search/','eleves\bulletinController@index2');
        Route::get('/eleves/bulletins/find/','eleves\bulletinController@search');
        Route::post('/eleves/bulletins/print-single-seq','eleves\bulletinController@print_single_seq');

          Route::get('/eleves/releves/','eleves\relevesController@index');
        Route::get('/eleves/releves/print/','eleves\relevesController@create');

         Route::get('/eleves/releve/','eleves\releve1Controller@index');
        Route::get('/eleves/releve/print/','eleves\releve1Controller@create');

        //releve soa

        Route::get('/eleves/releves_soa/','eleves\soareleveController@index');
        Route::get('/eleves/releves_soa/historique','eleves\soareleveController@history');
        Route::get('/eleves/releves_soa/historique/{id}','eleves\soareleveController@history');
        Route::get('/eleves/releves_soa/print/','eleves\soareleveController@create');
        Route::post('/eleves/releves_soa/generate/','eleves\soareleveController@generate');
        Route::get('/eleves/releves_soa/generation/{id}/print','eleves\soareleveController@printSaved');

        //ndere releve

        Route::get('/eleves/releves_ndere/','eleves\nderereleveController@index');
        Route::get('/eleves/releves_ndere/print/','eleves\nderereleveController@create');
        Route::post('/eleves/releves_ndere/generate/','eleves\nderereleveController@generate');
        Route::get('/eleves/releves_ndere/generation/{id}/print','eleves\nderereleveController@printSaved');
        Route::get('/eleves/releves_ndere/generation/{id}/destroy','eleves\nderereleveController@destroy');
        Route::get('/eleves/releves_ndere/historique','eleves\nderereleveController@history');

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

        //bts releve
        Route::get('/eleves/releves_bts/','eleves\bts_releveSessionController@index');
        Route::get('/eleves/releves_bts/historique','eleves\bts_releveSessionController@history');
        Route::get('/eleves/releves_bts/historique/{id}','eleves\bts_releveSessionController@history');
        Route::get('/eleves/releves_bts/print/','eleves\bts_releveSessionController@create');
        Route::post('/eleves/releves_bts/generate/','eleves\bts_releveSessionController@generate');
        Route::get('/eleves/releves_bts/generation/{id}/print','eleves\bts_releveSessionController@printSaved');
        Route::get('/eleves/releves_bts/generation/{id}/destroy','eleves\bts_releveSessionController@destroy');






    });
    Route::group(['middleware'=>'lvl18.5'],function (){
        Route::get('/eleves/', 'eleves\eleveController@index');
        Route::get('/eleves/notes/','eleves\noteController@index2');

        Route::get('/eleves/notes/edit','eleves\noteController@index');
        Route::get('/eleves/notes/show','eleves\noteController@show');
        Route::any('/eleves/notes/print_note/{id}','eleves\noteController@print_note');
        Route::get('/eleves/notes/matieres/','eleves\noteController@matiere');
        Route::get('/eleves/notes/matieres/show/','eleves\noteController@matiere_show');
        Route::post('/eleves/notes/create/{id}','eleves\noteController@create');
        Route::post('/eleves/notes/store/','eleves\noteController@store');
        Route::any('/eleves/notes/print_note_cc/{id}','eleves\noteController@print_note_cc');



        //proces verbal soa


        Route::get('/eleves/', 'eleves\eleveController@index');
        Route::get('/eleves/soa/','eleves\soanoteController@index2');

        Route::get('/eleves/notes/edit_soa','eleves\soanoteController@index');
        Route::get('/eleves/notes/show_soa','eleves\soanoteController@show');
        Route::any('/eleves/notes/print_note_soa/{id}','eleves\soanoteController@print_note');
        Route::get('/eleves/notes/matieres_soa/','eleves\soanoteController@matiere');
        Route::get('/eleves/notes/matieres/show_soa/','eleves\soanoteController@matiere_show');
        Route::post('/eleves/notes/create_soa/{id}','eleves\soanoteController@create');
        Route::post('/eleves/notes/store_soa/','eleves\soanoteController@store');

        //proces verbal ndere

        Route::get('/eleves/', 'eleves\eleveController@index');
        Route::get('/eleves/ndere/','eleves\nderenoteController@index2');

        Route::get('/eleves/notes/edit_ndere','eleves\nderenoteController@index');
        Route::get('/eleves/notes/show_ndere','eleves\nderenoteController@show');
        Route::any('/eleves/notes/print_note_ndere/{id}','eleves\nderenoteController@print_note');
        Route::get('/eleves/notes/matieres_ndere/','eleves\nderenoteController@matiere');
        Route::get('/eleves/notes/matieres/show_ndere/','eleves\nderenoteController@matiere_show');
        Route::post('/eleves/notes/create_ndere/{id}','eleves\nderenoteController@create');
        Route::post('/eleves/notes/store_ndere/','eleves\nderenoteController@store');


        //proces verbal estic

        Route::get('/eleves/', 'eleves\eleveController@index');
        Route::get('/eleves/estic/','eleves\esticnoteController@index2');

        Route::get('/eleves/notes/edit','eleves\esticnoteController@index');
        Route::get('/eleves/notes/show','eleves\esticnoteController@show');
        Route::any('/eleves/notes/print_note_estic/{id}','eleves\esticnoteController@print_note');
        Route::get('/eleves/notes/matieres/','eleves\esticnoteController@matiere');
        Route::get('/eleves/notes/matieres/show/','eleves\esticnoteController@matiere_show');
        Route::post('/eleves/notes/create/{id}','eleves\esticnoteController@create');
        Route::post('/eleves/notes/store/','eleves\esticnoteController@store');



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
        Route::any('/personnel/dispense/edit', 'personnel\dispenseController@edit');

        Route::post('/personnel/dispense/update', 'personnel\dispenseController@update');
        Route::get('/personnel/dispenses/', 'personnel\dispenseController@index1');
        Route::get('/personnel/dispense/{id}', 'personnel\dispenseController@index');
        ////
        /// routes pour les emplois du temps
        Route::get('/personnel/emploi-temps/show/', 'personnel\dispenseController@show');
        Route::any('/personnel/emploi-temps/edit/{id}', 'personnel\dispenseController@edit_timetable');


////routes pour les titulaires


        Route::get('/personnel/titulaire', 'personnel\titulaireController@index');
        Route::get('/personnel/titulaire/destroy/{id}', 'personnel\titulaireController@destroy');
        Route::post('/personnel/titulaire/store', 'personnel\titulaireController@store');
        Route::post('/personnel/titulaire/update', 'personnel\titulaireController@update');

//pour les  abscences et retards
        Route::get('/personnel/discipline/', 'personnel\disciplineController@index');

        Route::get('/personnel/discipline/show/{id}', 'personnel\disciplineController@create');
        Route::get('/personnel/discipline/show/precision/{id}', 'personnel\disciplineController@show');
        Route::post('/personnel/discipline/justify/', 'personnel\disciplineController@justify');

        Route::post('/personnel/discipline/update/', ['as'=>'update_discipline_personnel','uses'=>'personnel\disciplineController@update']);
        Route::any('/personnel/discipline/state/', ['as'=>'etat_disciplinaire_personnel','uses'=>'personnel\disciplineController@state']);


        //pour les présences
        Route::get('/personnel/heures-effectuees/', 'personnel\presenceController@index');
        Route::get('/personnel/heures-effectuees/{id}', 'personnel\presenceController@create');
        Route::get('/personnel/heures-effectuees/destroy/{id}', 'personnel\presenceController@destroy');
        Route::post('/personnel/heures-effectuees/update/{id}', 'personnel\presenceController@update');

        Route::any('/personnel/heures-prevues/show/', 'personnel\presenceController@show_heure_prevue');

        Route::any('/personnel/heures-prevues/update/', 'personnel\presenceController@update_heure_prevue');

    });

    Route::group(['middleware'=>'lvl16'],function (){
        //pour la comptabilite
        Route::get('/comptabilite/', 'comptabilite\comptabiliteController@index');
        // les décaissements
        Route::get('/admin/retrait/','admin\decaissementController@index');
        Route::post('/admin/retrait/store','admin\decaissementController@store');
        // le payement des salaires
        Route::get('/comptabilite/payement-salaire/','comptabilite\payementController@index');
        Route::get('/comptabilite/payement-salaire/show/{id}','comptabilite\payementController@show');
        Route::get('/comptabilite/payement-salaire/print/{id}','comptabilite\payementController@print_bulletin');
        Route::get('/comptabilite/payement-salaire/create/{id}','comptabilite\payementController@create');
        Route::post('/comptabilite/payement-salaire/store/','comptabilite\payementController@store');
        //Bilan
        Route::any('/comptabilite/bilan/entrees','comptabilite\bilanComptaController@index');
        Route::any('/comptabilite/bilan/sorties','comptabilite\bilanComptaController@indexSorties');
         Route::any('/comptabilite/bilan/sortie','comptabilite\bilanComptaController@indexSorties_ordre');


    });






    //pour les recherches

    Route::get('/search/personnel', 'searchController@personnel');
    Route::get('/search/eleve', 'searchController@eleve');
    Route::any('/select/classe', 'searchController@select_classe');
    Route::post('/update-school-year/', 'WelcomeController@editYear');

    //Pour la langue
    Route::any('/language/french', 'WelcomeController@fr_locale');
    Route::any('/language/english','WelcomeController@en_locale');

    //modifier le mot de passe utilisateur
    Route::any('/user-profile/edit-password','WelcomeController@change_password');
});
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
