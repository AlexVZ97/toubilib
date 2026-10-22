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
        [$praticien, $patient] = $this->createRdvValidator->validate($dto);

        $debutJournee = $dto->dateHeureDebut->setTime(0, 0);
        $praticien->chargerAgenda(
            $this->rdvRepository->findByPraticienEntre($praticien->getId(), $debutJournee, $debutJournee->modify('+1 day'))
        );

        $rdv = RendezVous::creer($praticien, $patient, $dto->dateHeureDebut, $dto->motifVisite);

        $this->rdvRepository->save($rdv);

        return RendezVousDTO::fromEntity($rdv);
    }

    public function annulerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);
        $rdv->annuler();
        $this->rdvRepository->save($rdv);
        return RendezVousDTO::fromEntity($rdv);
    }
}
