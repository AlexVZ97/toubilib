<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;

/** Port d'entrée pour les praticiens. */
interface PraticienServiceInterface
{
    /** Fonctionnalité 1 — @return PraticienDTO[] */
    public function listerPraticiens(): array;

    /**
     * Fonctionnalité 2
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    public function getPraticien(string $id): PraticienDetailDTO;
}
