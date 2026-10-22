<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

enum RendezVousStatut: int
{
    case PREVU  = 0;
    case ANNULE = -1;
    case HONORE = 1;
    case IGNORE = 2;
}
