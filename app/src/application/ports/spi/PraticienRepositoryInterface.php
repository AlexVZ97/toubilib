<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Praticien;

/** Port de sortie pour les praticiens. */
interface PraticienRepositoryInterface
{
    /** @return Praticien[] */
    public function findAll(): array;

    /**
     * Praticiens dont la spécialité et/ou la ville contiennent les textes donnés (sans tenir compte de la casse).
     * Un critère null est ignoré.
     * @return Praticien[]
     */
    public function search(?string $specialite, ?string $ville): array;

    /** @throws \toubilib\domain\exceptions\PraticienNotFoundException */
    public function findById(string $id): Praticien;
}
