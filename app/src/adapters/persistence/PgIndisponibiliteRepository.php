<?php
declare(strict_types=1);

namespace toubilib\adapters\persistence;

use toubilib\application\ports\spi\IndisponibiliteRepositoryInterface;
use toubilib\domain\entities\Indisponibilite;
use toubilib\domain\exceptions\IndisponibiliteNotFoundException;

class PgIndisponibiliteRepository implements IndisponibiliteRepositoryInterface
{
    public function __construct(private \PDO $pdo) {}

    public function findById(string $id): Indisponibilite
    {
        $stmt = $this->pdo->prepare('SELECT * FROM indisponibilite WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            throw new IndisponibiliteNotFoundException("Indisponibilité $id introuvable");
        }
        return $this->toEntity($row);
    }

    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, ?\DateTimeImmutable $fin = null): array
    {
        // chevauchement avec la période : debut_indispo < fin  ET  fin_indispo > debut
        $sql = 'SELECT * FROM indisponibilite WHERE praticien_id = :praticien_id AND date_fin > :debut';
        $params = ['praticien_id' => $praticienId, 'debut' => $debut->format('Y-m-d H:i:s')];
        if ($fin !== null) {
            $sql .= ' AND date_debut < :fin';
            $params['fin'] = $fin->format('Y-m-d H:i:s');
        }
        $stmt = $this->pdo->prepare($sql . ' ORDER BY date_debut');
        $stmt->execute($params);
        return array_map(fn(array $row) => $this->toEntity($row), $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function save(Indisponibilite $i): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO indisponibilite (id, praticien_id, date_debut, date_fin, motif)
             VALUES (:id, :praticien_id, :debut, :fin, :motif)
             ON CONFLICT (id) DO UPDATE SET date_debut = EXCLUDED.date_debut,
                                            date_fin = EXCLUDED.date_fin, motif = EXCLUDED.motif'
        );
        $stmt->execute([
            'id'           => $i->getId(),
            'praticien_id' => $i->getPraticienId(),
            'debut'        => $i->getDateDebut()->format('Y-m-d H:i:s'),
            'fin'          => $i->getDateFin()->format('Y-m-d H:i:s'),
            'motif'        => $i->getMotif(),
        ]);
    }

    public function delete(string $id): void
    {
        $this->pdo->prepare('DELETE FROM indisponibilite WHERE id = :id')->execute(['id' => $id]);
    }

    private function toEntity(array $row): Indisponibilite
    {
        return new Indisponibilite(
            $row['id'],
            $row['praticien_id'],
            new \DateTimeImmutable($row['date_debut']),
            new \DateTimeImmutable($row['date_fin']),
            $row['motif'],
        );
    }
}
