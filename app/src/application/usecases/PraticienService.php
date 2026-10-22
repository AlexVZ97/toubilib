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
        // entités → DTO : array_map applique fromEntity à chaque praticien
        return array_map(
            fn($praticien) => PraticienDTO::fromEntity($praticien),
            $this->praticienRepository->findAll()
        );
    }

    public function getPraticien(string $id): PraticienDetailDTO
    {
        return PraticienDetailDTO::fromEntity($this->praticienRepository->findById($id));
    }
}
