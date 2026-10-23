<?php
declare(strict_types=1);

namespace toubilib\domain\entities;

class Patient
{
    public function __construct(
        private string $id,
        private string $nom,
        private string $prenom,
        private string $telephone,
        private ?string $email = null,
        private ?\DateTimeImmutable $dateNaissance = null,
        private ?string $adresse = null,
        private ?string $codePostal = null,
        private ?string $ville = null,
    ) {}

    public function getId(): string { return $this->id; }
    public function getNom(): string { return $this->nom; }
    public function getPrenom(): string { return $this->prenom; }
    public function getTelephone(): string { return $this->telephone; }
    public function getEmail(): ?string { return $this->email; }
    public function getDateNaissance(): ?\DateTimeImmutable { return $this->dateNaissance; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function getVille(): ?string { return $this->ville; }
}
