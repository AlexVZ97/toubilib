<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;

interface PraticienRepositoryInterface
{
    /** @return Praticien[] */
    public function findAll(): array;

    /** @return Praticien[] */
    public function search(?string $specialite, ?string $ville): array;

    /** @throws \toubilib\domain\exceptions\PraticienNotFoundException */
    public function findById(string $id): Praticien;
}
