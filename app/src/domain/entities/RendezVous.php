<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use Ramsey\Uuid\Uuid;
use toubilib\domain\exceptions\AnnulationImpossibleException;
use toubilib\domain\exceptions\ChangementEtatImpossibleException;

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

    // ── création validée d'un RDV (TD1.3) ───────────────────────

    /**
     * Valide la demande et crée un nouveau RDV.
     * @throws \toubilib\domain\exceptions\MotifVisiteInvalideException
     * @throws \toubilib\domain\exceptions\HoraireInvalideException
     * @throws \toubilib\domain\exceptions\PraticienIndisponibleException
     */
    public static function creer(
        Praticien $praticien,
        Patient $patient,
        \DateTimeImmutable $debut,
        string $motif,
        ?\DateTimeImmutable $maintenant = null,
    ): self {
        $duree = $praticien->dureeMotif($motif);                // 1. motif correct ?
        $praticien->verifierHoraire($debut, $duree, $maintenant); // 2. date/heure acceptables ?

        $rdv = new self(
            id: Uuid::uuid4()->toString(),
            praticienId: $praticien->getId(),
            patientId: $patient->getId(),
            dateHeureDebut: $debut,
            duree: $duree,
            motifVisite: $motif,
        );

        $praticien->verifierDisponibilite($rdv);                // 3. pas de chevauchement ?
        return $rdv;
    }

    /**
     * Deux RDV se chevauchent si chacun commence avant la fin de l'autre.
     *   [début1, fin1[  et  [début2, fin2[  se chevauchent  ⇔  début1 < fin2  ET  début2 < fin1
     */
    public function chevauche(RendezVous $autre): bool
    {
        return $this->getDateHeureDebut() < $autre->getDateHeureFin()
            && $autre->getDateHeureDebut() < $this->getDateHeureFin();
    }

    // ── changements d'état ──────────────────────────────────────

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

    /**
     * Le patient s'est présenté (fonctionnalité 9).
     * @throws ChangementEtatImpossibleException
     */
    public function honorer(?\DateTimeImmutable $maintenant = null): void
    {
        $this->terminer(RendezVousStatut::HONORE, $maintenant);
    }

    /**
     * Le patient ne s'est pas présenté (fonctionnalité 9).
     * @throws ChangementEtatImpossibleException
     */
    public function ignorer(?\DateTimeImmutable $maintenant = null): void
    {
        $this->terminer(RendezVousStatut::IGNORE, $maintenant);
    }

    /** Un RDV ne peut être honoré/ignoré que s'il est PREVU et que son heure est arrivée. */
    private function terminer(RendezVousStatut $nouvelEtat, ?\DateTimeImmutable $maintenant): void
    {
        $maintenant = $maintenant ?? new \DateTimeImmutable();

        if ($this->statut !== RendezVousStatut::PREVU) {
            throw new ChangementEtatImpossibleException(
                "Le rendez-vous {$this->id} est {$this->statut->name} : il ne peut pas passer à {$nouvelEtat->name}"
            );
        }
        if ($this->dateHeureDebut > $maintenant) {
            throw new ChangementEtatImpossibleException(
                "Le rendez-vous {$this->id} n'a pas encore eu lieu : il ne peut pas passer à {$nouvelEtat->name}"
            );
        }
        $this->statut = $nouvelEtat;
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
