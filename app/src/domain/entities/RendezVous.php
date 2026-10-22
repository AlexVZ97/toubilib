<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use toubilib\domain\exceptions\AnnulationImpossibleException;

class RendezVous
{
    public function __construct(
        private string $id,
        private string $praticienId,
        private string $patientId,
        private \DateTimeImmutable $dateHeureDebut,
        private int $duree,                 // en minutes
        private string $motifVisite,
        private RendezVousStatut $statut = RendezVousStatut::PREVU,
        private ?\DateTimeImmutable $dateCreation = null,
    ) {
        $this->dateCreation = $dateCreation ?? new \DateTimeImmutable();
    }

    // ── méthodes métier ─────────────────────────────────────────

    /**
     * Annule le RDV.
     * $maintenant est optionnel : il permet de fixer "l'heure actuelle" dans les tests.
     * @throws AnnulationImpossibleException
     */
    public function annuler(?\DateTimeImmutable $maintenant = null): void
    {
        $maintenant = $maintenant ?? new \DateTimeImmutable();

        if ($this->statut !== RendezVousStatut::PREVU) {
            throw new AnnulationImpossibleException(
                "Le rendez-vous {$this->id} ne peut pas être annulé : son état est {$this->statut->name}"
            );
        }
        if ($this->dateHeureDebut <= $maintenant) {
            throw new AnnulationImpossibleException(
                "Le rendez-vous {$this->id} est déjà passé, il ne peut pas être annulé"
            );
        }
        $this->statut = RendezVousStatut::ANNULE;
    }

    // ── getters ─────────────────────────────────────────────────

    public function getId(): string { return $this->id; }
    public function getPraticienId(): string { return $this->praticienId; }
    public function getPatientId(): string { return $this->patientId; }
    public function getDateHeureDebut(): \DateTimeImmutable { return $this->dateHeureDebut; }
    public function getDateHeureFin(): \DateTimeImmutable
    {
        return $this->dateHeureDebut->modify("+{$this->duree} minutes");
    }
    public function getDuree(): int { return $this->duree; }
    public function getMotifVisite(): string { return $this->motifVisite; }
    public function getStatut(): RendezVousStatut { return $this->statut; }
    public function getDateCreation(): \DateTimeImmutable { return $this->dateCreation; }
}
