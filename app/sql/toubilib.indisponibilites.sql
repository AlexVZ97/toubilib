-- Fonctionnalité 11 : indisponibilités temporaires des praticiens
-- À charger APRÈS toubilib.schema.sql et toubilib.data.sql :
--   docker compose exec toubilib.db psql -U <user> -d toubilib -f /var/sql/toubilib.indisponibilites.sql

CREATE TABLE IF NOT EXISTS "public"."indisponibilite" (
    "id"           character varying(64)  NOT NULL,
    "praticien_id" character varying(64)  NOT NULL,
    "date_debut"   timestamp              NOT NULL,
    "date_fin"     timestamp              NOT NULL,
    "motif"        character varying(128),
    CONSTRAINT "indisponibilite_pkey" PRIMARY KEY ("id"),
    CONSTRAINT "indisponibilite_dates" CHECK ("date_fin" > "date_debut")
);

CREATE INDEX IF NOT EXISTS "indisponibilite_praticien_idx" ON "public"."indisponibilite" ("praticien_id", "date_debut");
