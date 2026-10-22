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
| POST | `/rdvs/{id}/annuler` | Annuler un RDV | 200, 404, 409 |

## Tableau de bord

| # | Fonctionnalité | État |
|---|---|---|
| 1 | Lister les praticiens | ⏳ |
| 2 | Détail d'un praticien | ⏳ |
| 3 | Détail d'un RDV | ⏳ |
| 4 | Agenda d'un praticien | ⏳ |
| 5 | Créer un RDV | ⏳ |
| 6 | Annuler un RDV | ✅ |
| 7 | Créneaux occupés d'un praticien | ⏳ |

### Membres du groupe et réalisations

- _Nom Prénom_ : …
