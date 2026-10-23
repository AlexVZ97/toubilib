<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\PatientDTO;
use toubilib\application\dto\RendezVousDTO;

/** Port d'entrée pour les patients. */
interface PatientServiceInterface
{
    /** @throws \toubilib\domain\exceptions\PatientNotFoundException */
    public function getPatient(string $id): PatientDTO;

    /**
     * Fonctionnalité 10 : historique des RDV d'un patient (du plus récent au plus ancien)
     * @return RendezVousDTO[]
     * @throws \toubilib\domain\exceptions\PatientNotFoundException
     */
    public function historiquePatient(string $id): array;
}
