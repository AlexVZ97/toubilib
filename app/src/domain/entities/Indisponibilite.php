<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use toubilib\domain\exceptions\IndisponibiliteInvalideException;

/** Période ponctuelle pendant laquelle un praticien n'accepte pas de RDV (fonctionnalité 11). */
class Indisponibilite
{
    /** @throws IndisponibiliteInvalideException si la fin n'est pas après le début */
    public function __construct(
        private string $id,
        private string $praticienId,
        private \DateTimeImmutable $dateDebut,
        private \DateTimeImmutable $dateFin,
        private ?string $motif = null,
    ) {
        if ($dateFin <= $dateDebut) {
            throw new IndisponibiliteInvalideException("La fin de l'indisponibilité doit être après son début");
        }
    }

    /** Même condition de chevauchement que pour deux RDV : début1 < fin2 ET début2 < fin1 */
    public function chevauche(\DateTimeImmutable $debut, \DateTimeImmutable $fin): bool
    {
        return $this->dateDebut < $fin && $debut < $this->dateFin;
    }

    public function getId(): string { return $this->id; }
    public function getPraticienId(): string { return $this->praticienId; }
    public function getDateDebut(): \DateTimeImmutable { return $this->dateDebut; }
    public function getDateFin(): \DateTimeImmutable { return $this->dateFin; }
    public function getMotif(): ?string { return $this->motif; }
}
