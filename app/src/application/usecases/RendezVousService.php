<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;
use toubilib\application\validators\CreateRdvValidator;
use toubilib\domain\entities\RendezVous;

class RendezVousService implements RendezVousServiceInterface
{
    // Dépendances INJECTÉES : le service ne connaît que des interfaces / le validateur
    public function __construct(
        private RendezVousRepositoryInterface $rdvRepository,
        private CreateRdvValidator $createRdvValidator,
    ) {}

    public function getRendezVous(string $id): RendezVousDTO
    {
        return RendezVousDTO::fromEntity($this->rdvRepository->findById($id));
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
}
