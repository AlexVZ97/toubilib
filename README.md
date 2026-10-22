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
| `adapters/persistence` | Adaptateurs de sortie : repositories PDO |

Configuration du conteneur : `app/config/settings.php`, `services.php`, `api.php` ; routes : `app/config/routes.php`.

## Endpoints

| Méthode | URI | Description | Statuts |
|---|---|---|---|
| GET | `/praticiens` | Lister les praticiens | 200 |
| GET | `/praticiens/{id}` | Détail d'un praticien | 200, 404 |
| GET | `/rdvs/{id}` | Détail d'un RDV | 200, 404 |
| POST | `/rdvs` | Créer un RDV (body JSON) | 201, 400, 409, 422 |
| POST | `/rdvs/{id}/annuler` | Annuler un RDV | 200, 404, 409 |

### Exemple : créer un RDV

```bash
curl -i -X POST http://localhost:7080/rdvs \
     -H "Content-Type: application/json" \
     -d '{"praticien_id":"af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b","patient_id":"d975aca7-50c5-3d16-b211-cf7d302cba50","date_heure_debut":"2026-12-07 10:00","motif_visite":"CO"}'
```

Motifs : `CI` (consultation initiale, 30 min), `CO` (consultation, 20 min), `CS` (suivi, 15 min).
Horaires acceptés : du lundi au vendredi, de 8h00 à 19h00.

## Tableau de bord

| # | Fonctionnalité | État |
|---|---|---|
| 1 | Lister les praticiens | ✅ |
| 2 | Détail d'un praticien | ✅ |
| 3 | Détail d'un RDV | ✅ |
| 4 | Agenda d'un praticien | ⏳ |
| 5 | Créer un RDV | ✅ |
| 6 | Annuler un RDV | ✅ |
| 7 | Créneaux occupés d'un praticien | ⏳ |

### Membres du groupe et réalisations

- _Nom Prénom_ : …
