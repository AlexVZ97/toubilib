<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\RendezVous;

/**
 * Données d'un RDV retournées par le service (vers l'extérieur du noyau).
 * Propriétés publiques en lecture seule : json_encode() les sérialise directement.
 */
final class RendezVousDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $praticien_id,
        public readonly string $patient_id,
        public readonly string $date_debut,
        public readonly string $date_fin,
        public readonly string $date_creation,
        public readonly int $duree,
        public readonly string $motif_visite,
        public readonly string $etat,
    ) {}

    public static function fromEntity(RendezVous $rdv): self
    {
        return new self(
            id: $rdv->getId(),
            praticien_id: $rdv->getPraticienId(),
            patient_id: $rdv->getPatientId(),
            date_debut: $rdv->getDateHeureDebut()->format('Y-m-d H:i'),
            date_fin: $rdv->getDateHeureFin()->format('Y-m-d H:i'),
            date_creation: $rdv->getDateCreation()->format('Y-m-d H:i'),
            duree: $rdv->getDuree(),
            motif_visite: $rdv->getMotifVisite(),
            etat: $rdv->getStatut()->name,
        );
    }
}
