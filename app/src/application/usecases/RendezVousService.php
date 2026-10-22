<?php
declare(strict_types=1);

namespace toubilib\application\usecases;

use toubilib\application\dto\RendezVousDTO;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\application\ports\spi\RendezVousRepositoryInterface;

class RendezVousService implements RendezVousServiceInterface
{
    // Le repository est INJECTÉ : le service ne connaît que l'interface
    public function __construct(
        private RendezVousRepositoryInterface $rdvRepository
    ) {}

    public function getRendezVous(string $id): RendezVousDTO
    {
        return RendezVousDTO::fromEntity($this->rdvRepository->findById($id));
    }

    public function annulerRendezVous(string $id): RendezVousDTO
    {
        $rdv = $this->rdvRepository->findById($id);  // RendezVousNotFoundException si absent
        $rdv->annuler();                             // AnnulationImpossibleException si interdit
        $this->rdvRepository->save($rdv);
        return RendezVousDTO::fromEntity($rdv);
    }
}
