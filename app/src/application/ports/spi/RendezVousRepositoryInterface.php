<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\RendezVous;

/** Port de sortie (SPI) : ce dont le noyau a besoin de la persistance. */
interface RendezVousRepositoryInterface
{
    /** @throws \toubilib\domain\exceptions\RendezVousNotFoundException */
    public function findById(string $id): RendezVous;

    public function save(RendezVous $rdv): void;
}
