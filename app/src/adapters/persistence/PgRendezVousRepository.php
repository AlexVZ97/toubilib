<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\RendezVousNotFoundException;

/** Adaptateur de sortie : implante le port SPI avec PDO / PostgreSQL. */
class PgRendezVousRepository implements RendezVousRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $id): RendezVous
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rdv WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new RendezVousNotFoundException("Rendez-vous $id introuvable");
        }
        return $this->toEntity($row);
    }

    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM rdv
             WHERE praticien_id = :praticien_id
               AND date_heure_debut >= :debut AND date_heure_debut < :fin
             ORDER BY date_heure_debut'
        );
        $stmt->execute([
            'praticien_id' => $praticienId,
            'debut'        => $debut->format('Y-m-d H:i:s'),
            'fin'          => $fin->format('Y-m-d H:i:s'),
        ]);
        return array_map(fn(array $row) => $this->toEntity($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function findByPatient(string $patientId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM rdv WHERE patient_id = :patient_id ORDER BY date_heure_debut DESC');
        $stmt->execute(['patient_id' => $patientId]);
        return array_map(fn(array $row) => $this->toEntity($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function save(RendezVous $rdv): void
    {
        $data = [
            'id'            => $rdv->getId(),
            'praticien_id'  => $rdv->getPraticienId(),
            'patient_id'    => $rdv->getPatientId(),
            'debut'         => $rdv->getDateHeureDebut()->format('Y-m-d H:i:s'),
            'fin'           => $rdv->getDateHeureFin()->format('Y-m-d H:i:s'),
            'status'        => $rdv->getStatut()->value,
            'duree'         => $rdv->getDuree(),
            'creation'      => $rdv->getDateCreation()->format('Y-m-d H:i:s'),
            'motif'         => $rdv->getMotifVisite(),
        ];

        // La table rdv n'a pas de clé primaire : on tente un UPDATE, sinon INSERT
        $update = $this->pdo->prepare(
            'UPDATE rdv SET praticien_id = :praticien_id, patient_id = :patient_id,
                    date_heure_debut = :debut, date_heure_fin = :fin, status = :status,
                    duree = :duree, date_creation = :creation, motif_visite = :motif
             WHERE id = :id'
        );
        $update->execute($data);

        if ($update->rowCount() === 0) {
            $insert = $this->pdo->prepare(
                'INSERT INTO rdv (id, praticien_id, patient_id, date_heure_debut, date_heure_fin,
                                  status, duree, date_creation, motif_visite)
                 VALUES (:id, :praticien_id, :patient_id, :debut, :fin, :status, :duree, :creation, :motif)'
            );
            $insert->execute($data);
        }
    }

    /** Transforme une ligne SQL en entité du domaine */
    private function toEntity(array $row): RendezVous
    {
        return new RendezVous(
            id: $row['id'],
            praticienId: $row['praticien_id'],
            patientId: $row['patient_id'],
            dateHeureDebut: new \DateTimeImmutable($row['date_heure_debut']),
            duree: (int) $row['duree'],
            motifVisite: (string) $row['motif_visite'],
            statut: RendezVousStatut::from((int) $row['status']),
            dateCreation: $row['date_creation'] ? new \DateTimeImmutable($row['date_creation']) : null,
        );
    }
}
