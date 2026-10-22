<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;

/** Version COMPLÈTE d'un praticien : utilisée pour le détail (fonctionnalité 2). */
final class PraticienDetailDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $titre,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $ville,
        public readonly string $email,
        public readonly string $telephone,
        public readonly ?string $rpps_id,
        public readonly bool $accepte_nouveau_patient,
        public readonly array $specialite,
    ) {}

    public static function fromEntity(Praticien $p): self
    {
        $s = $p->getSpecialite();
        return new self(
            id: $p->getId(),
            titre: $p->getTitre(),
            nom: $p->getNom(),
            prenom: $p->getPrenom(),
            ville: $p->getVille(),
            email: $p->getEmail(),
            telephone: $p->getTelephone(),
            rpps_id: $p->getRppsId(),
            accepte_nouveau_patient: $p->accepteNouveauPatient(),
            specialite: [
                'id'          => $s->getId(),
                'libelle'     => $s->getLibelle(),
                'description' => $s->getDescription(),
            ],
        );
    }
}
