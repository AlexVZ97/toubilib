<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

/** Un créneau occupé : seulement les horaires (pas d'info sur le patient). */
final class CreneauDTO
{
    public function __construct(
        public readonly string $date_debut,
        public readonly string $date_fin,
    ) {}

    public static function fromEntity(RendezVous $rdv): self
    {
        return new self(
            $rdv->getDateHeureDebut()->format('Y-m-d H:i'),
            $rdv->getDateHeureFin()->format('Y-m-d H:i'),
        );
    }
}
