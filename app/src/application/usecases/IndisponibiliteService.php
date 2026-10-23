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
        $this->praticienRepository->findById($praticienId);              // 404 si inexistant
        return array_map(
            fn(Indisponibilite $i) => IndisponibiliteDTO::fromEntity($i),
            $this->indisponibiliteRepository->findByPraticienEntre($praticienId, new \DateTimeImmutable())
        );
    }

    public function creerIndisponibilite(CreateIndisponibiliteDTO $dto): IndisponibiliteDTO
    {
        // 1. charger le praticien, avec son agenda et ses indisponibilités sur la période
        $praticien = $this->praticienRepository->findById($dto->praticienId);
        $debutJour = $dto->dateDebut->setTime(0, 0);
        $finJour = $dto->dateFin->setTime(0, 0)->modify('+1 day');
        $praticien->chargerAgenda($this->rdvRepository->findByPraticienEntre($praticien->getId(), $debutJour, $finJour));
        $praticien->chargerIndisponibilites(
            $this->indisponibiliteRepository->findByPraticienEntre($praticien->getId(), $dto->dateDebut, $dto->dateFin)
        );

        // 2. le domaine vérifie les règles et crée l'indisponibilité
        $indispo = $praticien->ajouterIndisponibilite($dto->dateDebut, $dto->dateFin, $dto->motif);

        // 3. persistance
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
            // elle existe, mais pas pour CE praticien : pour l'URL demandée, la ressource n'existe pas
            throw new IndisponibiliteNotFoundException("Indisponibilité $indisponibiliteId introuvable pour ce praticien");
        }
        $this->indisponibiliteRepository->delete($indisponibiliteId);
        $this->logger->info('Indisponibilité supprimée', ['praticien' => $praticienId, 'indisponibilite' => $indisponibiliteId]);
    }
}
