<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\CreneauDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\application\ports\spi\PraticienRepositoryInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\CreateRdvValidator;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;

class RendezVousService implements RendezVousServiceInterface
{
    // Dépendances INJECTÉES : le service ne connaît que des interfaces / le validateur
    public function __construct(
        private RendezVousRepositoryInterface $rdvRepository,
        private PraticienRepositoryInterface $praticienRepository,
        private CreateRdvValidator $createRdvValidator,
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
        // 1. le praticien et le patient existent ? (ValidationException sinon)
        [$praticien, $patient] = $this->createRdvValidator->validate($dto);

        // 2. charger l'agenda du praticien pour la journée demandée (suffisant pour tester les chevauchements)
        $debutJournee = $dto->dateHeureDebut->setTime(0, 0);
        $praticien->chargerAgenda(
            $this->rdvRepository->findByPraticienEntre($praticien->getId(), $debutJournee, $debutJournee->modify('+1 day'))
        );

        // 3. le domaine valide les règles métier et crée l'entité
        $rdv = RendezVous::creer($praticien, $patient, $dto->dateHeureDebut, $dto->motifVisite);

        // 4. persistance
        $this->rdvRepository->save($rdv);

        return RendezVousDTO::fromEntity($rdv);
    }

    public function annulerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);  // RendezVousNotFoundException si absent
        $rdv->annuler();                             // AnnulationImpossibleException si interdit
        $this->rdvRepository->save($rdv);
        return RendezVousDTO::fromEntity($rdv);
    }

    public function honorerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        $rdv->honorer();                             // ChangementEtatImpossibleException si interdit
        $this->rdvRepository->save($rdv);
        return RendezVousDTO::fromEntity($rdv);
    }

    public function ignorerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        $rdv->ignorer();
        $this->rdvRepository->save($rdv);
        return RendezVousDTO::fromEntity($rdv);
    }

    /**
     * RDV du praticien entre le jour $debut (0h00) et le jour $fin (inclus).
     * @return RendezVous[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    private function rdvsDuPraticien(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array
    {
        $this->praticienRepository->findById($praticienId);     // 404 si le praticien n'existe pas
        return $this->rdvRepository->findByPraticienEntre(
            $praticienId,
            $debut->setTime(0, 0),
            $fin->setTime(0, 0)->modify('+1 day')
        );
    }
}
