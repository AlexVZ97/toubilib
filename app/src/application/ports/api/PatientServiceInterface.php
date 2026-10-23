<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PatientDTO;
use toubilib\application\dto\RendezVousDTO;

interface PatientServiceInterface
{
    /** @throws \toubilib\domain\exceptions\PatientNotFoundException */
    public function getPatient(string $id): PatientDTO;

    /**
     * @return RendezVousDTO[]
     * @throws \toubilib\domain\exceptions\PatientNotFoundException
     */
    public function historiquePatient(string $id): array;
}
