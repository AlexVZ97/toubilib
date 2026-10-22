<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\PatientRepositoryInterface;
use toubilib\domain\entities\Patient;
use toubilib\domain\exceptions\PatientNotFoundException;

class PgPatientRepository implements PatientRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $id): Patient
    {
        $stmt = $this->pdo->prepare('SELECT * FROM patient WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new PatientNotFoundException("Patient $id introuvable");
        }
        return new Patient(
            id: $row['id'],
            nom: $row['nom'],
            prenom: $row['prenom'],
            telephone: $row['telephone'],
            email: $row['email'],
            dateNaissance: $row['date_naissance'] ? new \DateTimeImmutable($row['date_naissance']) : null,
        );
    }
}
