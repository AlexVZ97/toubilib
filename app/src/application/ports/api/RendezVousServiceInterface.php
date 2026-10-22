<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\CreneauDTO;
use toubilib\application\dto\RendezVousDTO;

interface RendezVousServiceInterface
{
    /** @throws \toubilib\domain\exceptions\RendezVousNotFoundException */
    public function getRendezVous(string $id): RendezVousDTO;

    /**
     * @return RendezVousDTO[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    public function agendaPraticien(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array;

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

    /**
     * @return CreneauDTO[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    public function creneauxOccupes(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array;

    /**
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\ChangementEtatImpossibleException
     */
    public function honorerRendezVous(string $id): RendezVousDTO;

    /**
     * @throws \toubilib\domain\exceptions\RendezVousNotFoundException
     * @throws \toubilib\domain\exceptions\ChangementEtatImpossibleException
     */
    public function ignorerRendezVous(string $id): RendezVousDTO;
}
