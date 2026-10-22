<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;

/** Port d'entrée (API) : ce que le noyau offre au monde extérieur. */
interface RendezVousServiceInterface
{
    /**
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\AnnulationImpossibleException
     */
    public function annulerRendezVous(string $id): RendezVousDTO;
}
