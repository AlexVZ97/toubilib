<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

class InMemoryPatientRepository implements PatientRepositoryInterface
{
    /** @var array<string, Patient> */
    private array $patients = [];

    public function __construct(Patient ...$patients)
    {
        foreach ($patients as $p) {
            $this->patients[$p->getId()] = $p;
        }
    }

    public function findById(string $id): Patient
    {
        return $this->patients[$id] ?? throw new PatientNotFoundException("Patient $id introuvable");
    }
}
