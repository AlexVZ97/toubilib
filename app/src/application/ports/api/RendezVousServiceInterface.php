<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\CreneauDTO;
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
     * Fonctionnalité 4 : agenda d'un praticien, jours $debut à $fin inclus
     * @return RendezVousDTO[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    public function agendaPraticien(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array;

    /**
     * Fonctionnalité 5
     * @throws \toubilib\application\exceptions\ValidationException        praticien ou patient inexistant
     * @throws \toubilib\domain\exceptions\MotifVisiteInvalideException     motif inconnu
     * @throws \toubilib\domain\exceptions\HoraireInvalideException         date passée, week-end, hors horaires
     * @throws \toubilib\domain\exceptions\PraticienIndisponibleException   chevauchement avec un RDV ou une indisponibilité
     */
    public function creerRendezVous(CreateRdvDTO $dto): RendezVousDTO;

    /**
     * Fonctionnalité 6
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\AnnulationImpossibleException
     */
    public function annulerRendezVous(string $id): RendezVousDTO;

    /**
     * Fonctionnalité 7 : créneaux occupés (RDV non annulés), jours $debut à $fin inclus
     * @return CreneauDTO[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    public function creneauxOccupes(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array;

    /**
     * Fonctionnalité 9
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\ChangementEtatImpossibleException
     */
    public function honorerRendezVous(string $id): RendezVousDTO;

    /**
     * Fonctionnalité 9
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\ChangementEtatImpossibleException
     */
    public function ignorerRendezVous(string $id): RendezVousDTO;
}
