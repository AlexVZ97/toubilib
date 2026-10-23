<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\IndisponibiliteRepositoryInterface;
use toubilib\domain\entities\Indisponibilite;
use toubilib\domain\exceptions\IndisponibiliteNotFoundException;

class InMemoryIndisponibiliteRepository implements IndisponibiliteRepositoryInterface
{
    /** @var array<string, Indisponibilite> */
    public array $indispos = [];

    public function __construct(Indisponibilite ...$indispos)
    {
        foreach ($indispos as $i) {
            $this->indispos[$i->getId()] = $i;
        }
    }

    public function findById(string $id): Indisponibilite
    {
        return $this->indispos[$id] ?? throw new IndisponibiliteNotFoundException("Indisponibilité $id introuvable");
    }

    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, ?\DateTimeImmutable $fin = null): array
    {
        return array_values(array_filter($this->indispos, fn(Indisponibilite $i) =>
            $i->getPraticienId() === $praticienId
            && $i->getDateFin() > $debut
            && ($fin === null || $i->getDateDebut() < $fin)
        ));
    }

    public function save(Indisponibilite $indisponibilite): void
    {
        $this->indispos[$indisponibilite->getId()] = $indisponibilite;
    }

    public function delete(string $id): void
    {
        unset($this->indispos[$id]);
    }
}
