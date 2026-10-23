<?php
declare(strict_types=1);

namespace toubilib\application\ports\spi;

use toubilib\domain\entities\RendezVous;

/** Port de sortie (SPI) : ce dont le noyau a besoin de la persistance. */
interface RendezVousRepositoryInterface
{
    /** @throws \toubilib\domain\exceptions\RendezVousNotFoundException */
    public function findById(string $id): RendezVous;

    /**
     * RDV d'un praticien commençant dans la période [debut, fin[
     * @return RendezVous[]
     */
    public function findByPraticienEntre(string $praticienId, \DateTimeImmutable $debut, \DateTimeImmutable $fin): array;

    /**
     * Tous les RDV d'un patient, du plus récent au plus ancien
     * @return RendezVous[]
     */
    public function findByPatient(string $patientId): array;

    /** Crée le RDV s'il n'existe pas, le met à jour sinon. */
    public function save(RendezVous $rdv): void;
}
