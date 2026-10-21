# Paiements 2026-2027 et consultation des archives

La migration rattache uniquement les paiements sans campus de 2026-2027.
Elle utilise l'inscription directement liee au paiement, ou, en l'absence
de lien, le campus unique des inscriptions de l'etudiant pour cette annee.
Les rattachements ambigus restent inchanges. Les mouvements financiers deja
lies a ces paiements recuperent aussi leur campus manquant.
Les montants, numeros de recus et paiements des autres annees restent inchanges.

## Fichiers a transferer

Conserver ces chemins relatifs sur le serveur :

```text
config/campus.php
app/Http/Controllers/Controller.php
app/Http/Controllers/searchController.php
app/Http/Controllers/inscriptions/inscriptionController.php
app/Http/Controllers/inscriptions/payementController.php
app/Http/Controllers/inscriptions/listesController.php
app/Http/Controllers/inscriptions/enregistrementController.php
app/Http/Controllers/inscriptions/photosController.php
app/Http/Controllers/inscriptions/bourseController.php
app/Http/Controllers/comptabilite/comptabiliteController.php
app/Http/Controllers/comptabilite/bilanComptaController.php
app/Http/Controllers/comptabilite/payementController.php
app/Http/Controllers/comptabilite/LigneBudgetController.php
app/Http/Controllers/personnel/personnelController.php
app/Http/Controllers/personnel/ficheProgressionController.php
app/Http/Controllers/admin/decaissementController.php
app/Http/Controllers/admin/financeController.php
database/migrations/campus_2026_2027/2026_10_16_000003_attach_2026_2027_payments_to_inscription_campus.php
```

## Execution

Depuis le repertoire de l'application, avec sa configuration habituelle :

```sh
php artisan migrate --path=database/migrations/campus_2026_2027 --force
php artisan config:clear
```

Le sous-dossier permet d'executer uniquement cette migration, sans lancer les
autres migrations en attente. Elle est compatible avec PostgreSQL 9.5 et
reexecutable sans modifier les rattachements deja renseignes.
Ne pas deployer l'ancienne migration generale
`2026_10_16_000002_restore_legacy_payment_campuses.php` pour cette operation :
elle n'est pas limitee a 2026-2027.

## Consultation des annees passees

`config/campus.php` definit 2026 comme premiere annee de debut du fonctionnement
par campus. Pour une annee selectionnee anterieure a 2026-2027, les consultations
incluent le campus actif et les lignes sans campus. Les autres campus restent
exclus. A partir de 2026-2027, le filtre sur le campus actif reste strict.
Le module discipline conservait deja ses consultations par annee sans filtre
excluant ces archives.

## Tests

Les tests utilisent des tables temporaires et annulent leurs transactions :

```sh
php vendor/phpunit/phpunit/phpunit tests/HistoricalCampusVisibilityTest.php
```
