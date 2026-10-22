<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;

interface PraticienServiceInterface
{
    /** @return PraticienDTO[] */
    public function listerPraticiens(): array;

    /** @throws \toubilib\domain\exceptions\PraticienNotFoundException */
    public function getPraticien(string $id): PraticienDetailDTO;
}
