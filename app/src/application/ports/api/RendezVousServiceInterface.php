<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;

interface RendezVousServiceInterface
{
    /** @throws \toubilib\domain\exceptions\RendezVousNotFoundException */
    public function getRendezVous(string $id): RendezVousDTO;

    /**
     * @throws \toubilib\application\exceptions\ValidationException        praticien ou patient inexistant
     * @throws \toubilib\domain\exceptions\MotifVisiteInvalideException     motif inconnu
     * @throws \toubilib\domain\exceptions\HoraireInvalideException         date passée, week-end, hors horaires
     * @throws \toubilib\domain\exceptions\PraticienIndisponibleException   chevauchement avec un autre RDV
     */
    public function creerRendezVous(CreateRdvDTO $dto): RendezVousDTO;

    /**
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\AnnulationImpossibleException
     */
    public function annulerRendezVous(string $id): RendezVousDTO;
}
