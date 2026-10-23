<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;

class PraticienService implements PraticienServiceInterface
{
    public function __construct(
        private PraticienRepositoryInterface $praticienRepository
    ) {}

    public function listerPraticiens(): array
    {
        return $this->versDTO($this->praticienRepository->findAll());
    }

    public function rechercherPraticiens(?string $specialite, ?string $ville): array
    {
        if ($specialite === null && $ville === null) {
            return $this->listerPraticiens();
        }
        return $this->versDTO($this->praticienRepository->search($specialite, $ville));
    }

    public function getPraticien(string $id): PraticienDetailDTO
    {
        return PraticienDetailDTO::fromEntity($this->praticienRepository->findById($id));
    }

    /** entités → DTO : array_map applique fromEntity à chaque praticien */
    private function versDTO(array $praticiens): array
    {
        return array_map(fn($p) => PraticienDTO::fromEntity($p), $praticiens);
    }
}
