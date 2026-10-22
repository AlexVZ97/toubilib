<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;

/** Port de sortie pour les praticiens. */
interface PraticienRepositoryInterface
{
    /** @return Praticien[] */
    public function findAll(): array;

    /** @throws \toubilib\domain\exceptions\PraticienNotFoundException */
    public function findById(string $id): Praticien;
}
