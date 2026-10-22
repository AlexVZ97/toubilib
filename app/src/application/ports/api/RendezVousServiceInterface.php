<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\RendezVousDTO;

/** Port d'entrée (API) : ce que le noyau offre au monde extérieur. */
interface RendezVousServiceInterface
{
    /**
     * Fonctionnalité 3
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     */
    public function getRendezVous(string $id): RendezVousDTO;

    /**
     * Fonctionnalité 6
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\AnnulationImpossibleException
     */
    public function annulerRendezVous(string $id): RendezVousDTO;
}
