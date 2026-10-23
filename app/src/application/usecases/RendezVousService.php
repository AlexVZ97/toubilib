<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use Psr\Log\LoggerInterface;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\CreneauDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\application\ports\spi\IndisponibiliteRepositoryInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\CreateRdvValidator;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;

class RendezVousService implements RendezVousServiceInterface
{
    public function __construct(
        private RendezVousRepositoryInterface $rdvRepository,
        private PraticienRepositoryInterface $praticienRepository,
        private IndisponibiliteRepositoryInterface $indisponibiliteRepository,
        private CreateRdvValidator $createRdvValidator,
        private LoggerInterface $logger,
    ) {}

    public function getRendezVous(string $id): RendezVousDTO
    {
        return RendezVousDTO::fromEntity($this->rdvRepository->findById($id));
    }

    public function agendaPraticien(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        return array_map(
            fn(RendezVous $rdv) => RendezVousDTO::fromEntity($rdv),
            $this->rdvsDuPraticien($praticienId, $debut, $fin)
        );
    }

    public function creneauxOccupes(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $occupes = array_filter(
            $this->rdvsDuPraticien($praticienId, $debut, $fin),
            fn(RendezVous $rdv) => $rdv->getStatut() !== RendezVousStatut::ANNULE
        );
        return array_values(array_map(fn(RendezVous $rdv) => CreneauDTO::fromEntity($rdv), $occupes));
    }

    public function creerRendezVous(CreateRdvDTO $dto): RendezVousDTO
    {
        [$praticien, $patient] = $this->createRdvValidator->validate($dto);

        $debutJournee = $dto->dateHeureDebut->setTime(0, 0);
        $finJournee = $debutJournee->modify('+1 day');
        $praticien->chargerAgenda(
            $this->rdvRepository->findByPraticienEntre($praticien->getId(), $debutJournee, $finJournee)
        );
        $praticien->chargerIndisponibilites(
            $this->indisponibiliteRepository->findByPraticienEntre($praticien->getId(), $debutJournee, $finJournee)
        );

        $rdv = RendezVous::creer($praticien, $patient, $dto->dateHeureDebut, $dto->motifVisite);

        $this->rdvRepository->save($rdv);
        $this->logger->info('RDV créé', [
            'rdv' => $rdv->getId(), 'praticien' => $praticien->getId(), 'patient' => $patient->getId(),
            'debut' => $rdv->getDateHeureDebut()->format('Y-m-d H:i'),
        ]);

        return RendezVousDTO::fromEntity($rdv);
    }

    public function annulerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        $rdv->annuler();
        $this->rdvRepository->save($rdv);
        $this->logger->info('RDV annulé', ['rdv' => $id]);
        return RendezVousDTO::fromEntity($rdv);
    }

    public function honorerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        $rdv->honorer();
        $this->rdvRepository->save($rdv);
        $this->logger->info('RDV honoré', ['rdv' => $id]);
        return RendezVousDTO::fromEntity($rdv);
    }

    public function ignorerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        $rdv->ignorer();
        $this->rdvRepository->save($rdv);
        $this->logger->info('RDV ignoré', ['rdv' => $id]);
        return RendezVousDTO::fromEntity($rdv);
    }

    /**
     * @return RendezVous[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    private function rdvsDuPraticien(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $this->praticienRepository->findById($praticienId);
        return $this->rdvRepository->findByPraticienEntre(
            $praticienId,
            $debut->setTime(0, 0),
            $fin->setTime(0, 0)->modify('+1 day')
        );
    }
}
