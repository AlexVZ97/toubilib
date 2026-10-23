# Toubilib – API de gestion de rendez-vous médicaux

Projet du module **Développement Web Serveur avancé** (BUT Informatique S5 – IUT Nancy-Charlemagne).
Backend PHP / Slim 4 exposant une API RESTful, organisé selon l'**architecture hexagonale**
avec **inversion de dépendances** et conteneur d'injection de dépendances **PHP-DI** (sans autowiring).

## Lancement

```bash
# 1. fichiers de configuration (non versionnés)
cp toubilib.env.dist toubilib.env            # puis remplir POSTGRES_USER / POSTGRES_PASSWORD
cp app/config/.env.dist app/config/.env      # mêmes identifiants

# 2. démarrer les services (api, base PostgreSQL, adminer)
docker compose up -d --build

# 3. créer la base et charger les données
docker compose exec toubilib.db psql -U <user> -d toubilib -f /var/sql/toubilib.schema.sql
docker compose exec toubilib.db psql -U <user> -d toubilib -f /var/sql/toubilib.data.sql
docker compose exec toubilib.db psql -U <user> -d toubilib -f /var/sql/toubilib.indisponibilites.sql

# 4. lancer les tests
docker compose exec api.toubilib vendor/bin/pest
```

| Service | URL |
|---|---|
| API | http://localhost:7080 |
| Adminer | http://localhost:8880 (serveur : `toubilib.db`) |

## Architecture (`app/src`)

| Dossier | Rôle |
|---|---|
| `domain/entities`, `domain/exceptions` | Entités métier et règles de gestion |
| `application/ports/api` | Ports d'entrée (interfaces des services) |
| `application/ports/spi` | Ports de sortie (interfaces des repositories) |
| `application/usecases` | Services métier (cas d'utilisation) |
| `application/dto` | DTO échangés avec le noyau |
| `adapters/controllers/actions` | Adaptateurs d'entrée : actions Slim |
| `adapters/controllers/middlewares` | Middlewares Slim (CORS) |
| `adapters/controllers/handlers` | Gestionnaire d'erreurs (avec logs) |
| `adapters/persistence` | Adaptateurs de sortie : repositories PDO |

Configuration du conteneur : `app/config/settings.php`, `services.php`, `api.php` ; routes : `app/config/routes.php`.

## Endpoints

Toutes les réponses sont en JSON et contiennent des liens **HATEOAS** (`links`).
Les erreurs sont aussi renvoyées en JSON, avec le statut HTTP adéquat.

| Méthode | URI | Description | Statuts |
|---|---|---|---|
| GET | `/praticiens` | Lister les praticiens | 200 |
| GET | `/praticiens?specialite=…&ville=…` | Rechercher des praticiens (texte partiel, sans casse) | 200, 400 |
| GET | `/praticiens/{id}` | Détail d'un praticien | 200, 404 |
| GET | `/praticiens/{id}/rdvs?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ` | Agenda d'un praticien sur une période | 200, 400, 404 |
| GET | `/praticiens/{id}/creneaux?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ` | Créneaux occupés d'un praticien | 200, 400, 404 |
| GET | `/praticiens/{id}/indisponibilites` | Indisponibilités en cours / à venir | 200, 404 |
| POST | `/praticiens/{id}/indisponibilites` | Créer une indisponibilité (body JSON) | 201, 400, 404, 409, 422 |
| DELETE | `/praticiens/{id}/indisponibilites/{indispoId}` | Supprimer une indisponibilité | 204, 404 |
| GET | `/patients/{id}` | Détail d'un patient | 200, 404 |
| GET | `/patients/{id}/rdvs` | Historique des RDV d'un patient | 200, 404 |
| POST | `/rdvs` | Créer un RDV (body JSON) | 201, 400, 409, 422 |
| GET | `/rdvs/{id}` | Détail d'un RDV | 200, 404 |
| POST | `/rdvs/{id}/annuler` | Annuler un RDV | 200, 404, 409 |
| POST | `/rdvs/{id}/honorer` | Marquer un RDV comme honoré | 200, 404, 409 |
| POST | `/rdvs/{id}/ignorer` | Marquer un RDV comme ignoré | 200, 404, 409 |

Sans `debut`/`fin`, la période est la journée du jour. `fin` est incluse.
Les en-têtes **CORS** sont ajoutés à toutes les réponses (middleware `Cors`), et les requêtes `OPTIONS` (preflight) reçoivent `204`.

### Exemple : créer un RDV

```bash
curl -i -X POST http://localhost:7080/rdvs \
     -H "Content-Type: application/json" \
     -d '{"praticien_id":"af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b","patient_id":"d975aca7-50c5-3d16-b211-cf7d302cba50","date_heure_debut":"2026-12-07 10:00","motif_visite":"CO"}'
```

### Exemple : déclarer une indisponibilité

```bash
curl -i -X POST http://localhost:7080/praticiens/af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b/indisponibilites \
     -H "Content-Type: application/json" \
     -d '{"date_debut":"2026-12-21 08:00","date_fin":"2026-12-24 19:00","motif":"Congés"}'
```

Une indisponibilité ne peut pas recouvrir des RDV prévus (409) ; aucun RDV ne peut être pris pendant une indisponibilité (409).

Motifs : `CI` (consultation initiale, 30 min), `CO` (consultation, 20 min), `CS` (suivi, 15 min).
Horaires acceptés : du lundi au vendredi, de 8h00 à 19h00.

### Logs

L'application utilise **Monolog** (via l'interface PSR-3 `LoggerInterface`, injectée par le conteneur).
Les logs sont écrits dans `app/logs/toubilib.log` :

- `INFO` : événements métier (RDV créé, annulé, honoré, ignoré) ;
- `NOTICE` : erreurs du client (4xx) ;
- `ERROR` : erreurs du serveur (5xx), avec l'exception et sa localisation.

Le niveau minimal est réglable dans `app/config/settings.php` (`logs.level`).

```bash
tail -f app/logs/toubilib.log
```

### Tests automatiques de l'API

Le script `scripts/test-api.sh` envoie une série de requêtes avec `curl` et vérifie les statuts HTTP :

```bash
./scripts/test-api.sh                 # API sur http://localhost:7080
```

## Tableau de bord

| # | Fonctionnalité | État |
|---|---|---|
| 1 | Lister les praticiens | ✅ |
| 2 | Détail d'un praticien | ✅ |
| 3 | Détail d'un RDV | ✅ |
| 4 | Agenda d'un praticien | ✅ |
| 5 | Créer un RDV | ✅ |
| 6 | Annuler un RDV | ✅ |
| 7 | Créneaux occupés d'un praticien | ✅ |
| 8 | Rechercher un praticien (spécialité, ville) | ✅ |
| 9 | Cycle de vie d'un RDV (honoré, ignoré) | ✅ |
| 10 | Historique des consultations d'un patient | ✅ |
| 11 | Indisponibilités temporaires d'un praticien | ✅ |

### Membre du groupe et réalisations

Projet réalisé individuellement par **Alejandro Villalobos Zaragoza**.

| Étape | Réalisations |
|---|---|
| TD1.1 | Architecture hexagonale, entité `RendezVous`, fonctionnalité 6 (annuler un RDV), tests Pest |
| TD1.2 | Conteneur PHP-DI sans autowiring (`settings`, `services`, `api`), fonctionnalités 1, 2, 3 |
| TD1.3 | Création d'un RDV (fonctionnalité 5) : DTO validé avec Respect\\Validation, validateur, règles métier (motif, horaires, chevauchements) |
| TD1.4 | API RESTful : HATEOAS, fonctionnalités 4 et 7, middleware CORS, script de tests `scripts/test-api.sh` |
| Fonctionnalités additionnelles | 8 (recherche), 9 (honorer / ignorer), 10 (historique patient), 11 (indisponibilités) |
| Transverse | Logs Monolog (PSR-3), gestion des erreurs, Docker, README |
