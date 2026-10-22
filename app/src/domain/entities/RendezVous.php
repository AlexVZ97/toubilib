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
        private int $duree,
        private string $motifVisite,
        private RendezVousStatut $statut = RendezVousStatut::PREVU,
        private ?\DateTimeImmutable $dateCreation = null,
    ) {
        $this->dateCreation = $dateCreation ?? new \DateTimeImmutable();
    }

    /**
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
        $duree = $praticien->dureeMotif($motif);
        $praticien->verifierHoraire($debut, $duree, $maintenant);

        $rdv = new self(
            id: Uuid::uuid4()->toString(),
            praticienId: $praticien->getId(),
            patientId: $patient->getId(),
            dateHeureDebut: $debut,
            duree: $duree,
            motifVisite: $motif,
        );

        $praticien->verifierDisponibilite($rdv);
        return $rdv;
    }

    public function chevauche(RendezVous $autre): bool
    {
        return $this->getDateHeureDebut() < $autre->getDateHeureFin()
            && $autre->getDateHeureDebut() < $this->getDateHeureFin();
    }

    /** @throws AnnulationImpossibleException */
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

    /** @throws ChangementEtatImpossibleException */
    public function honorer(?\DateTimeImmutable $maintenant = null): void
    {
        $this->terminer(RendezVousStatut::HONORE, $maintenant);
    }

    /** @throws ChangementEtatImpossibleException */
    public function ignorer(?\DateTimeImmutable $maintenant = null): void
    {
        $this->terminer(RendezVousStatut::IGNORE, $maintenant);
    }

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
