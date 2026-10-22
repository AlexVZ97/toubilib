<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\Patient;

interface PatientRepositoryInterface
{
    /** @throws \toubilib\domain\exceptions\PatientNotFoundException */
    public function findById(string $id): Patient;
}
