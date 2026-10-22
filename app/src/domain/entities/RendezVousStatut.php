<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

/**
 * États possibles d'un rendez-vous.
 * Les valeurs correspondent à la colonne "status" de la table rdv
 * (dans les données fournies : 0 = prévu, -1 = annulé).
 */
enum RendezVousStatut: int
{
    case PREVU  = 0;
    case ANNULE = -1;
    case HONORE = 1;
    case IGNORE = 2;
}
