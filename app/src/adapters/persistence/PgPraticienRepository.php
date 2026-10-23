<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\PraticienNotFoundException;

class PgPraticienRepository implements PraticienRepositoryInterface
{
    // praticien + sa spécialité en une seule requête (jointure)
    private const SELECT = 'SELECT p.*, s.libelle AS specialite_libelle, s.description AS specialite_description
                            FROM praticien p
                            JOIN specialite s ON s.id = p.specialite_id';

    public function __construct(private \PDO $pdo) {}

    public function findAll(): array
    {
        $stmt = $this->pdo->query(self::SELECT . ' ORDER BY p.nom, p.prenom');
        return array_map(fn(array $row) => $this->toEntity($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function search(?string $specialite, ?string $ville): array
    {
        // on construit le WHERE selon les critères fournis ; les valeurs passent TOUJOURS
        // par des paramètres (:specialite, :ville) → pas d'injection SQL possible
        $conditions = [];
        $params = [];
        if ($specialite !== null) {
            $conditions[] = 's.libelle ILIKE :specialite';
            $params['specialite'] = '%' . $specialite . '%';
        }
        if ($ville !== null) {
            $conditions[] = 'p.ville ILIKE :ville';
            $params['ville'] = '%' . $ville . '%';
        }
        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->pdo->prepare(self::SELECT . $where . ' ORDER BY p.nom, p.prenom');
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->toEntity($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function findById(string $id): Praticien
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE p.id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            throw new PraticienNotFoundException("Praticien $id introuvable");
        }
        return $this->toEntity($row);
    }

    private function toEntity(array $row): Praticien
    {
        return new Praticien(
            id: $row['id'],
            nom: $row['nom'],
            prenom: $row['prenom'],
            ville: $row['ville'],
            email: $row['email'],
            telephone: $row['telephone'],
            specialite: new Specialite(
                (int) $row['specialite_id'],
                $row['specialite_libelle'],
                $row['specialite_description'],
            ),
            titre: $row['titre'],
            rppsId: $row['rpps_id'],
            accepteNouveauPatient: $row['nouveau_patient'] === '1',   // colonne de type bit(1)
        );
    }
}
