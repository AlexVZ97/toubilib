<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\RendezVous;

interface RendezVousRepositoryInterface
{
    /** @throws \toubilib\domain\exceptions\RendezVousNotFoundException */
    public function findById(string $id): RendezVous;

    /** @return RendezVous[] */
    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array;

    public function save(RendezVous $rdv): void;
}
