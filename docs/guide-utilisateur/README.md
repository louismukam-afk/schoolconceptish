# Guide d’utilisation de SchoolConcept — IS-HINTEL

Édition 2.0 du 21 octobre 2026, destinée aux utilisateurs et aux formateurs.

Adresse d’accès à l’application hébergée dans le cloud : **https://ish.informatique-ht.cm**. Une connexion Internet et un navigateur suffisent pour accéder à cette adresse.

- [Guide PDF](GUIDE_UTILISATEUR.pdf) : document autonome à lire, imprimer ou transmettre.
- [Guide HTML](GUIDE_UTILISATEUR.html) : sommaire, recherche dans les fiches et captures ouvrables en grand. Ouvrir le fichier dans Chrome ou Edge. Conserver le dossier `captures` à côté du fichier HTML.
- [Guide Markdown](GUIDE_UTILISATEUR.md) : version texte modifiable, avec liens vers les images.

Le guide contient les procédures de prise en main, de scolarité, d’encaissement, de pédagogie, de ressources humaines, de comptabilité et d’administration. Il comprend aussi les champs des formulaires, des difficultés courantes et un programme de formation avec exercices et critères de réussite.

## Périmètre et vérification

La connexion et les captures ont été réalisées sur l’installation locale fournie. Cette édition ajoute 97 procédures, 104 fiches d’interfaces et 175 figures, dont les modèles de PV pour les six formats et les historiques propres aux relevés et attestations. Plusieurs adresses aboutissant à une même interface ont été regroupées. Les formulaires n’ont pas été soumis pour créer des inscriptions, paiements, imports ou générations de résultats pendant la rédaction. Les ouvertures de pages peuvent toutefois produire des sessions, journaux ou initialisations automatiques de l’application.

Les modèles de PV, de relevés et de planning ont été rendus localement depuis les vues du projet avec une transaction de base en lecture seule. Les initialisations ou sauvegardes automatiques ont été neutralisées uniquement en mémoire pour ces aperçus ; les contrôleurs de l’application n’ont pas été modifiés. Les attestations pédagogiques montrent une identité et des résultats fictifs. Les historiques sont des captures de la session réelle ; aucune ligne de démonstration n’y a été ajoutée.

Les erreurs des pages d’horaires, de rôles, de journal et d’une variante du lien de paie sont expliquées dans les fiches et l’annexe. L’affichage final du relevé Ngaoundéré est également bloqué par la vue manquante `perso.header_releve_tutelle`. Les fonctions de paie annoncées par certains liens n’ont pas été obtenues comme écrans spécialisés. La sélection des relevés Soa a bloqué le navigateur ; son historique a pu être consulté et son modèle rendu localement. Aucune session du portail étudiant n’a été ouverte avec un compte étudiant distinct.

Les étapes métier décrites ont été rapprochées des vues et du code. Les validations finales et les parcours indisponibles restent à vérifier sur une base de formation. Les captures présentent des données de l’installation : réserver la diffusion aux personnes concernées.

## Mise à jour

Les procédures détaillées sont conservées dans `outils/procedures-detaillees.cjs`, les textes généraux dans `contenu.json` et les fiches d’interfaces dans `outils/fiches.cjs`. L’édition précédente est conservée dans `contenu-1.1.json`. L’inventaire des écrans et champs se trouve dans `inventaire.json`. Les captures sont dans `captures/` et les aperçus locaux dans `apercus/`.

Depuis la racine du projet, reconstruire les versions HTML et Markdown avec :

```powershell
node docs/guide-utilisateur/outils/enrichir-guide.cjs
node docs/guide-utilisateur/outils/generer-guide.cjs
```

Pour exporter à nouveau le PDF, avec Chrome installé :

```powershell
node docs/guide-utilisateur/outils/exporter-pdf.cjs
```

Les outils de capture utilisent une session Chrome distincte sans interface visible. Ils n’incluent aucun identifiant ou mot de passe. La capture de nouvelles pages doit être précédée d’une vérification des contrôleurs : certaines adresses de consultation exécutent des initialisations en base. Ne pas relancer une collecte globale sur une base de travail sans ce contrôle.

Le fichier `verification.json` donne les compteurs et adresses en erreur au moment de la génération. `CONTROLE_DOCUMENT.json` contient le résultat du contrôle des ressources du guide.
