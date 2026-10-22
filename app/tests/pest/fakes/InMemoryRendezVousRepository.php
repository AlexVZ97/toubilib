<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\exceptions\RendezVousNotFoundException;

/** Faux repository en mémoire : permet de tester le service SANS base de données. */
class InMemoryRendezVousRepository implements RendezVousRepositoryInterface
{
    /** @var array<string, RendezVous> */
    public array $rdvs = [];

    public function __construct(RendezVous ...$rdvs)
    {
        foreach ($rdvs as $rdv) {
            $this->rdvs[$rdv->getId()] = $rdv;
        }
    }

    public function findById(string $id): RendezVous
    {
        return $this->rdvs[$id] ?? throw new RendezVousNotFoundException("Rendez-vous $id introuvable");
    }

    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        return array_values(array_filter($this->rdvs, fn(RendezVous $r) =>
            $r->getPraticienId() === $praticienId
            && $r->getDateHeureDebut() >= $debut && $r->getDateHeureDebut() < $fin
        ));
    }

    public function save(RendezVous $rdv): void
    {
        $this->rdvs[$rdv->getId()] = $rdv;
    }
}
