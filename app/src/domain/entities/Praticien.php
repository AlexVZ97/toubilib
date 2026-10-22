<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

use toubilib\domain\exceptions\HoraireInvalideException;
use toubilib\domain\exceptions\MotifVisiteInvalideException;
use toubilib\domain\exceptions\PraticienIndisponibleException;

class Praticien
{
    public const MOTIFS = ['CI' => 30, 'CO' => 20, 'CS' => 15];

    public const HEURE_OUVERTURE = 8;
    public const HEURE_FERMETURE = 19;

    /** @var RendezVous[] */
    private array $agenda = [];

    public function __construct(
        private string $id,
        private string $nom,
        private string $prenom,
        private string $ville,
        private string $email,
        private string $telephone,
        private Specialite $specialite,
        private string $titre = 'Dr.',
        private ?string $rppsId = null,
        private bool $accepteNouveauPatient = true,
    ) {}

    /** @throws MotifVisiteInvalideException */
    public function verifierMotif(string $motif): void
    {
        if (!array_key_exists($motif, self::MOTIFS)) {
            throw new MotifVisiteInvalideException(
                "Motif '$motif' invalide. Motifs possibles : " . implode(', ', array_keys(self::MOTIFS))
            );
        }
    }

    /** @throws MotifVisiteInvalideException */
    public function dureeMotif(string $motif): int
    {
        $this->verifierMotif($motif);
        return self::MOTIFS[$motif];
    }

    /** @throws HoraireInvalideException */
    public function verifierHoraire(\DateTimeImmutable $debut, int $duree, ?\DateTimeImmutable $maintenant = null): void
    {
        $maintenant = $maintenant ?? new \DateTimeImmutable();
        $fin = $debut->modify("+$duree minutes");
        $ouverture = $debut->setTime(self::HEURE_OUVERTURE, 0);
        $fermeture = $debut->setTime(self::HEURE_FERMETURE, 0);

        if ($debut <= $maintenant) {
            throw new HoraireInvalideException("La date du RDV doit être dans le futur");
        }
        if ((int) $debut->format('N') > 5) {
            throw new HoraireInvalideException("Le praticien ne consulte pas le week-end");
        }
        if ($debut < $ouverture || $fin > $fermeture) {
            throw new HoraireInvalideException(sprintf(
                "Le RDV doit avoir lieu entre %dh00 et %dh00", self::HEURE_OUVERTURE, self::HEURE_FERMETURE
            ));
        }
    }

    public function chargerAgenda(array $rdvs): void
    {
        $this->agenda = $rdvs;
    }

    /** @throws PraticienIndisponibleException */
    public function verifierDisponibilite(RendezVous $demande): void
    {
        foreach ($this->agenda as $rdv) {
            if ($rdv->getStatut() !== RendezVousStatut::ANNULE && $demande->chevauche($rdv)) {
                throw new PraticienIndisponibleException(sprintf(
                    "Le praticien n'est pas disponible : un RDV est déjà prévu de %s à %s",
                    $rdv->getDateHeureDebut()->format('H:i'),
                    $rdv->getDateHeureFin()->format('H:i')
                ));
            }
        }
    }

    public function getId(): string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getVille(): string { return $this->ville; }
    public function getEmail(): string { return $this->email; }
    public function getTelephone(): string { return $this->telephone; }
    public function getSpecialite(): Specialite { return $this->specialite; }
    public function getTitre(): string { return $this->titre; }
    public function getRppsId(): ?string { return $this->rppsId; }
    public function accepteNouveauPatient(): bool { return $this->accepteNouveauPatient; }
    /** @return RendezVous[] */
    public function getAgenda(): array { return $this->agenda; }
}
