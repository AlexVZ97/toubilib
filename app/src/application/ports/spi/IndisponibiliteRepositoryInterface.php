<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Indisponibilite;

interface IndisponibiliteRepositoryInterface
{
    /** @throws \toubilib\domain\exceptions\IndisponibiliteNotFoundException */
    public function findById(string $id): Indisponibilite;

    /**
     * Indisponibilités du praticien qui CHEVAUCHENT la période [debut, fin[
     * (fin = null : sans limite)
     * @return Indisponibilite[]
     */
    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, ?\DateTimeImmutable $fin = null): array;

    public function save(Indisponibilite $indisponibilite): void;

    public function delete(string $id): void;
}
