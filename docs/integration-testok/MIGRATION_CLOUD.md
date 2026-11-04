# Reprise du fichier SQL Test OK sur le cloud

Cette procédure migre les données de `hintel_gestion_note.sql` fourni le 30 octobre 2026 vers la base PostgreSQL **SchoolConcept**. La reprise **réutilise les étudiants et inscriptions existants et crée uniquement ce qui manque**, puis reprend les notes avec leurs tutelles. Elle ne restaure pas le SQL MySQL directement dans PostgreSQL et ne copie pas les identifiants de la base locale dans celle du serveur.

## Livrables

| Fichier | Rôle |
| --- | --- |
| `tools/testok/package-sql.php` | Convertit le SQL en paquet JSON privé sans exécuter ses instructions. |
| `app/Services/TestokSqlDumpReader.php` | Lit les INSERT littéraux et exclut les comptes utilisateurs. |
| `database/migrations/2026_10_30_000001_*.php` à `000003_*.php` | Structure des documents, traçabilité, séquences PostgreSQL et crédits décimaux. |
| `database/migrations-testok-data/2026_10_30_000004_import_testok_hintel_sql.php` | Migration Laravel des données, lancée explicitement par le script ci-dessous. |
| `tools/testok/migrate-data.php` | Simulation puis exécution de la migration de données avec campus explicite. |
| `public/images/tutelles/testok/` | Logos des cinq tutelles, à déployer avec le code. |

La migration de données est volontairement dans un dossier distinct : un `php artisan migrate` ordinaire ne lance pas cette reprise. Une exécution réussie est enregistrée dans la table Laravel `migrations` sous `2026_10_30_000004_import_testok_hintel_sql`.

## Données du fichier fourni

Empreinte SHA-256 du SQL :

```text
864c6fa1728e60d0f0bedf33af757568264bd9aae7f6ba04c03993151c5af604
```

Le paquet contient avant contrôle : 829 étudiants, 1 142 inscriptions, 37 325 notes, 756 programmes, 1 167 sessions, 647 matières, 814 codes et les référentiels associés. Années présentes : 2023-2024, 2024-2025 et 2025-2026. Il ne contient pas d'année 2026-2027.

La table `utilisateur`, ses mots de passe et ses comptes ne sont pas exportés. Les instructions de création, suppression, modification de structure et les routines du SQL ne sont jamais exécutées.

## 1. Préparer le paquet sur le poste local

Depuis la racine de SchoolConcept, créer un dossier **neuf** :

```powershell
New-Item -ItemType Directory -Force storage/app/import-testok/cloud-20261030
php tools/testok/package-sql.php --sql=C:\Users\louis\Downloads\hintel_gestion_note.sql --output=storage/app/import-testok/cloud-20261030
```

Le paquet de ce fichier est déjà préparé localement dans `storage/app/import-testok/cloud-20261030/` :

```text
.htaccess
manifest.json
source.json
```

`manifest.json` conserve les nombres de lignes et les empreintes. `source.json` contient les données personnelles nécessaires à la reprise. Le convertisseur refuse d'écraser un paquet existant. Pour le reconstruire, choisir un autre dossier.

## 2. Déployer le code et transporter les données

Versionner sur Git les migrations, services, scripts, vues, configurations et logos de l'intégration. Ne pas ajouter `.env`, le SQL original, les correspondances locales ni `storage/app/import-testok/` : ce dernier est déjà exclu de Git. Le dépôt actuel est public ; le paquet de données se transfère séparément par SFTP/SCP ou par l'outil privé de l'hébergeur.

Sur le serveur, placer les trois fichiers du paquet dans :

```text
CHEMIN_SCHOOLCONCEPT/storage/app/import-testok/cloud-20261030/
```

Déployer le code compatible avec la version PHP/Laravel de ce projet, puis régénérer l'autoload Composer si le processus de déploiement l'exige. Les scripts se lancent à la racine du projet. Le PHP CLI doit disposer des extensions utilisées par SchoolConcept, notamment PDO PostgreSQL et mbstring ; **MySQL/mysqli n'est pas requis pour cette procédure SQL**.

La racine web du serveur doit être `public/`. Sous Apache, le paquet contient en plus un `.htaccess` interdisant son accès. Sous Nginx, le `.htaccess` n'est pas interprété : conserver impérativement `storage/` hors des chemins servis et ne pas créer de lien public vers le paquet.

## 3. Sauvegarder et installer la structure

Avant toute migration sur le cloud : sauvegarder la base PostgreSQL et les fichiers déployés, conserver la sauvegarde hors du répertoire web et vérifier qu'elle peut être restaurée. Planifier la reprise pendant une fenêtre de maintenance, sans saisie concurrente ni traitements de fond modifiant les notes ou inscriptions.

Vérifier que le `.env` **du serveur** désigne la bonne base SchoolConcept. Ne pas remplacer ce fichier par celui du poste local.

```bash
php artisan down
php tools/testok/install.php
```

L'installateur applique uniquement les trois migrations de structure de cette intégration et crée les cinq tutelles si elles manquent. Il conserve les tutelles existantes et leurs personnalisations. Il évite de lancer les anciennes migrations historiques encore en attente dans ce dépôt.

## 4. Choisir le campus du serveur

Dans la base cible, consulter :

```sql
SELECT id, nom FROM campus ORDER BY id;
```

Choisir l'identifiant du campus **Institut supérieur Hintel**. L'identifiant local est `1`, mais il peut être différent sur le cloud. Les commandes suivantes montrent `--campus=1` : remplacer cette valeur si nécessaire. Un campus absent provoque un arrêt.

Sur un serveur n'ayant jamais reçu Test OK, ne pas transmettre `correspondances.json` du poste local. Les étudiants sont retrouvés parmi les fiches existantes, par matricule unique ou nom complet unique. Si aucune fiche ne correspond, l'étudiant est créé avec ses données source valides ; un rapprochement ambigu ou une date de naissance invalide pour une nouvelle fiche reste dans le rapport. Les inscriptions sont retrouvées par étudiant cible, année, campus, spécialité, niveau et cycle. Une inscription manquante est créée uniquement si aucune inscription n'existe déjà pour cet étudiant et cette année. Une inscription dans un autre contexte ou plusieurs résultats sont isolés pour éviter une duplication sur une mauvaise correspondance. Les identifiants Test OK ne sont jamais utilisés directement comme identifiants SchoolConcept. Les catalogues pédagogiques et sessions nécessaires peuvent être créés dans la base cible.

## 5. Simuler la reprise

```bash
php tools/testok/migrate-data.php --package=storage/app/import-testok/cloud-20261030 --campus=1
```

La simulation prépare les catalogues, valide les relations et tente les écritures dans une transaction entièrement annulée. Elle vérifie aussi les conflits avec les notes existantes. Les séquences PostgreSQL peuvent avancer : des trous d'identifiants après simulation sont normaux.

Consulter dans le dossier privé du paquet :

- `rapport-migration.json` : campus, créations proposées, avertissements, effectifs valides et erreurs ;
- `anomalies-migration.json` : lignes écartées et motifs, avec leurs données d'origine.

Les lignes valides sont conservées et les anomalies isolées, conformément au choix de reprise. Corriger toute erreur bloquante avant l'étape suivante. Les nombres retenus dépendent aussi des correspondances et des données déjà présentes sur le serveur.

**Cas d'une base qui a déjà reçu Test OK par l'ancienne commande :** fournir le fichier de correspondances issu de **cette même base**, pour éviter de refaire des rapprochements ambigus :

```bash
php tools/testok/migrate-data.php --package=storage/app/import-testok/cloud-20261030 --campus=1 --mapping=storage/app/import-testok/correspondances.json
```

Le script refuse une reprise existante sans correspondances. Ne jamais contourner ce contrôle en supprimant les liens de traçabilité.

## 6. Appliquer la migration

Après simulation réussie et contrôle du rapport :

```bash
php tools/testok/migrate-data.php --package=storage/app/import-testok/cloud-20261030 --campus=1 --commit
```

Ajouter la même option `--mapping=...` si elle était nécessaire en simulation. Les catalogues et données valides sont importés dans une transaction. Une erreur SQL, un conflit ou une erreur du contrôle final des notes annule cette transaction. Une note existante différente ne sera jamais écrasée.

Le contrôle final vérifie **toutes les notes source retenues, pour chaque tutelle attendue**, et pas seulement un échantillon : étudiant, campus, année, matière, spécialité, programme, session source correspondante, type d'évaluation, contribution pondérée, anonymat, mois et tutelle. Le nombre cible peut dépasser le nombre source parce qu'une note commune est reprise séparément pour Ngaoundéré et Polytechnique. Les erreurs de rattachement ou les notes cibles manquantes bloquent la validation.

La politique commune de préparation et de contrôle est dans `app/Services/TestokTutellePolicy.php` :

| Filière source | Tutelle(s) |
| --- | --- |
| Agriculture | ISABEE |
| Gestion ; hôtellerie | SOA |
| Information et communication | ESSTIC |
| Génie civil, électrique, informatique ; télécom ; réseaux/cybersécurité ; BTP/cadastre | Ngaoundéré et Polytechnique |

Les identifiants de tutelles sont ceux de la base cible et sont retrouvés par leur modèle, jamais copiés depuis la base locale. Une correspondance manuelle qui enlève une tutelle commune ou en ajoute une incorrecte est refusée.

Le succès crée `correspondances-cible.json` dans le paquet, avec les identifiants propres au serveur. Conserver ce fichier avec les rapports et la sauvegarde. Si une interruption survient après validation de la transaction et avant renommage, conserver le fichier `.pending` et suivre le message du script avant toute relance.

Une relance de la commande avec `--commit` après succès indique que la migration a déjà été appliquée. Elle n'importe pas une deuxième fois les données. Le paquet est lié au SQL ci-dessus ; pour une autre version du SQL, préparer une nouvelle migration de reprise plutôt que modifier celle déjà appliquée.

## 7. Vérifier et rouvrir l'application

Vérifier l'enregistrement :

```sql
SELECT migration, batch FROM migrations
WHERE migration = '2026_10_30_000004_import_testok_hintel_sql';

SELECT source, entite, COUNT(*) AS lignes
FROM testok_import_links
WHERE source = 'testok-hintel'
GROUP BY source, entite
ORDER BY entite;
```

Dans SchoolConcept, sélectionner le campus et une année historique, puis vérifier quelques étudiants, inscriptions, notes, PV et relevés dans **Documents par tutelle**. Contrôler les cinq tutelles et les logos. Les notes des filières communes sont déclinées dans les sessions de Ngaoundéré et Polytechnique ; le nombre de notes cibles peut donc dépasser celui des lignes source valides.

Les inscriptions existantes restent intégralement conservées : identifiants, dates, montants, campus et autres champs. Les nouvelles inscriptions sont des reprises académiques à montant zéro : le SQL ne fournit pas de date d'inscription, donc la date de conversion du paquet sert par défaut de date de reprise. Une date explicite peut être renseignée dans `inscription_date_by_year` du fichier de correspondances. Aucun versement ni paiement n'est créé. Le rapport `traitement` distingue les étudiants, inscriptions, sessions et notes créés ou réutilisés. Le rapport `verification` donne les effectifs de notes attendus et vérifiés par tutelle, les notes manquantes et les erreurs. Les enseignants absents du fichier source doivent être affectés dans SchoolConcept.

```bash
php artisan up
```

Réactiver les traitements de fond arrêtés pour l'opération. Conserver les anomalies pour correction ultérieure. Aucun `migrate:rollback` destructif n'est fourni pour les données : en cas d'annulation, restaurer la sauvegarde préalable pendant la maintenance. Ce choix évite de supprimer des notes ou inscriptions modifiées après la reprise.

## Contrôles réalisés localement

La dernière simulation complète selon la politique « réutiliser ou créer si absent » a vérifié **40 844 notes cibles attendues et présentes, sans erreur de rattachement et sans note manquante**. Les écritures ont toutes été annulées après contrôle. La filière de chacun des 756 programmes a également été comparée à celle de sa spécialité source : aucune divergence dans le SQL fourni.

| Tutelle | Notes attendues | Notes vérifiées |
| --- | ---: | ---: |
| ISABEE | 15 760 | 15 760 |
| ESSTIC | 920 | 920 |
| SOA | 15 258 | 15 258 |
| Ngaoundéré | 4 453 | 4 453 |
| Polytechnique | 4 453 | 4 453 |

La politique actuelle est la réutilisation ou création en cas d'absence réelle. Les tests couvrent la conservation intégrale des inscriptions existantes, la création des fiches manquantes avec leurs notes, la relance sans doublon, les identités ambiguës, les dates invalides, les conflits de campus et la détection des mauvaises affectations. Les dix filières du SQL sont testées avec leurs tutelles, y compris toutes les filières communes. La simulation sur la base cloud reste nécessaire avant application.

La conversion du SQL fourni et la simulation transactionnelle sur la base locale déjà reprise ont réussi avec ses correspondances locales : **814 étudiants, 944 inscriptions et 36 391 notes source valides**. Le rapport isole 15 étudiants, 33 sessions, 198 inscriptions et 934 notes. La simulation a annulé ses écritures ; elle n'a pas appliqué la nouvelle migration de données.

Les tests ciblés de reprise passent pour la politique actuelle : **28 tests, 130 assertions**. Ils couvrent la lecture du SQL, le contrôle d'empreinte, le campus obligatoire, les correspondances existantes, la création des données absentes, la conservation des inscriptions existantes, l'import idempotent, les conflits de notes, les dix correspondances filière/tutelles et la détection des mauvaises affectations. Les compteurs de création et de réutilisation du rapport sont aussi testés. La syntaxe PHP des services a été vérifiée. Ces contrôles locaux ne remplacent pas la simulation sur la base cloud.
