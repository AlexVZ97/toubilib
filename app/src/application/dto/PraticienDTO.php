<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Praticien;

final class PraticienDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $titre,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly string $ville,
        public readonly string $specialite,
    ) {}

    public static function fromEntity(Praticien $p): self
    {
        return new self(
            id: $p->getId(),
            titre: $p->getTitre(),
            nom: $p->getNom(),
            prenom: $p->getPrenom(),
            ville: $p->getVille(),
            specialite: $p->getSpecialite()->getLibelle(),
        );
    }
}
