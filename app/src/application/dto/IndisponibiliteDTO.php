<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Indisponibilite;

final class IndisponibiliteDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $praticien_id,
        public readonly string $date_debut,
        public readonly string $date_fin,
        public readonly ?string $motif,
    ) {}

    public static function fromEntity(Indisponibilite $i): self
    {
        return new self(
            $i->getId(),
            $i->getPraticienId(),
            $i->getDateDebut()->format('Y-m-d H:i'),
            $i->getDateFin()->format('Y-m-d H:i'),
            $i->getMotif(),
        );
    }
}
