<?php
declare(strict_types=1);

namespace toubilib\application\validators;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\PatientNotFoundException;
use toubilib\domain\exceptions\PraticienNotFoundException;

/**
 * Validation "données ↔ base" : le praticien et le patient indiqués existent-ils ?
 * Retourne les entités chargées, pour que le service n'ait pas à les relire.
 */
class CreateRdvValidator
{
    public function __construct(
        private PraticienRepositoryInterface $praticienRepository,
        private PatientRepositoryInterface $patientRepository,
    ) {}

    /**
     * @return array{0: Praticien, 1: Patient}
     * @throws ValidationException
     */
    public function validate(CreateRdvDTO $dto): array
    {
        $errors = [];
        $praticien = $patient = null;

        try {
            $praticien = $this->praticienRepository->findById($dto->praticienId);
        } catch (PraticienNotFoundException $e) {
            $errors['praticien_id'] = "Aucun praticien ne correspond à l'id {$dto->praticienId}";
        }
        try {
            $patient = $this->patientRepository->findById($dto->patientId);
        } catch (PatientNotFoundException $e) {
            $errors['patient_id'] = "Aucun patient ne correspond à l'id {$dto->patientId}";
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
        return [$praticien, $patient];
    }
}
