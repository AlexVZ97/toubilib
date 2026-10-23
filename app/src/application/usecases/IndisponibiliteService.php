<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use Psr\Log\LoggerInterface;
use toubilib\application\dto\CreateIndisponibiliteDTO;
use toubilib\application\dto\IndisponibiliteDTO;
use toubilib\application\ports\api\IndisponibiliteServiceInterface;
use toubilib\application\ports\spi\IndisponibiliteRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\domain\entities\Indisponibilite;
use toubilib\domain\exceptions\IndisponibiliteNotFoundException;

class IndisponibiliteService implements IndisponibiliteServiceInterface
{
    public function __construct(
        private PraticienRepositoryInterface $praticienRepository,
        private IndisponibiliteRepositoryInterface $indisponibiliteRepository,
        private RendezVousRepositoryInterface $rdvRepository,
        private LoggerInterface $logger,
    ) {}

    public function listerIndisponibilites(string $praticienId): array
    {
        $this->praticienRepository->findById($praticienId);
        return array_map(
            fn(Indisponibilite $i) => IndisponibiliteDTO::fromEntity($i),
            $this->indisponibiliteRepository->findByPraticienEntre($praticienId, new \DateTimeImmutable())
        );
    }

    public function creerIndisponibilite(CreateIndisponibiliteDTO $dto): IndisponibiliteDTO
    {
        $praticien = $this->praticienRepository->findById($dto->praticienId);
        $debutJour = $dto->dateDebut->setTime(0, 0);
        $finJour = $dto->dateFin->setTime(0, 0)->modify('+1 day');
        $praticien->chargerAgenda($this->rdvRepository->findByPraticienEntre($praticien->getId(), $debutJour, $finJour));
        $praticien->chargerIndisponibilites(
            $this->indisponibiliteRepository->findByPraticienEntre($praticien->getId(), $dto->dateDebut, $dto->dateFin)
        );

        $indispo = $praticien->ajouterIndisponibilite($dto->dateDebut, $dto->dateFin, $dto->motif);

        $this->indisponibiliteRepository->save($indispo);
        $this->logger->info('Indisponibilité créée', [
            'praticien' => $praticien->getId(), 'indisponibilite' => $indispo->getId(),
            'debut' => $dto->dateDebut->format('Y-m-d H:i'), 'fin' => $dto->dateFin->format('Y-m-d H:i'),
        ]);
        return IndisponibiliteDTO::fromEntity($indispo);
    }

    public function supprimerIndisponibilite(string $praticienId, string $indisponibiliteId): void
    {
        $indispo = $this->indisponibiliteRepository->findById($indisponibiliteId);
        if ($indispo->getPraticienId() !== $praticienId) {
            throw new IndisponibiliteNotFoundException("Indisponibilité $indisponibiliteId introuvable pour ce praticien");
        }
        $this->indisponibiliteRepository->delete($indisponibiliteId);
        $this->logger->info('Indisponibilité supprimée', ['praticien' => $praticienId, 'indisponibilite' => $indisponibiliteId]);
    }
}
