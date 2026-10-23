<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\PatientDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\PatientServiceInterface;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;

class PatientService implements PatientServiceInterface
{
    public function __construct(
        private PatientRepositoryInterface $patientRepository,
        private RendezVousRepositoryInterface $rdvRepository,
    ) {}

    public function getPatient(string $id): PatientDTO
    {
        return PatientDTO::fromEntity($this->patientRepository->findById($id));
    }

    public function historiquePatient(string $id): array
    {
        $this->patientRepository->findById($id);          // PatientNotFoundException → 404
        return array_map(
            fn(RendezVous $rdv) => RendezVousDTO::fromEntity($rdv),
            $this->rdvRepository->findByPatient($id)
        );
    }
}
