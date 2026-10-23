<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use toubilib\domain\entities\Patient;

final class PatientDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $nom,
        public readonly string $prenom,
        public readonly ?string $date_naissance,
        public readonly ?string $email,
        public readonly string $telephone,
        public readonly ?string $adresse,
        public readonly ?string $code_postal,
        public readonly ?string $ville,
    ) {}

    public static function fromEntity(Patient $p): self
    {
        return new self(
            id: $p->getId(),
            nom: $p->getNom(),
            prenom: $p->getPrenom(),
            date_naissance: $p->getDateNaissance()?->format('Y-m-d'),
            email: $p->getEmail(),
            telephone: $p->getTelephone(),
            adresse: $p->getAdresse(),
            code_postal: $p->getCodePostal(),
            ville: $p->getVille(),
        );
    }
}
