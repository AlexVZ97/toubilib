<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\domain\entities\Praticien;
use toubilib\domain\exceptions\PraticienNotFoundException;

class InMemoryPraticienRepository implements PraticienRepositoryInterface
{
    /** @var array<string, Praticien> */
    private array $praticiens = [];

    public function __construct(Praticien ...$praticiens)
    {
        foreach ($praticiens as $p) {
            $this->praticiens[$p->getId()] = $p;
        }
    }

    public function findAll(): array
    {
        return array_values($this->praticiens);
    }

    public function search(?string $specialite, ?string $ville): array
    {
        return array_values(array_filter($this->praticiens, fn(Praticien $p) =>
            ($specialite === null || mb_stripos($p->getSpecialite()->getLibelle(), $specialite) !== false)
            && ($ville === null || mb_stripos($p->getVille(), $ville) !== false)
        ));
    }

    public function findById(string $id): Praticien
    {
        return $this->praticiens[$id] ?? throw new PraticienNotFoundException("Praticien $id introuvable");
    }
}
