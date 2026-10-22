<?php

use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\ValidationException;

$valide = [
    'praticien_id'     => 'af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b',
    'patient_id'       => 'd975aca7-50c5-3d16-b211-cf7d302cba50',
    'date_heure_debut' => '2026-12-15 10:00',
    'motif_visite'     => 'co',
];

it('crée le DTO à partir de données valides', function () use ($valide) {
    $dto = CreateRdvDTO::fromArray($valide);
    expect($dto->dateHeureDebut->format('Y-m-d H:i:s'))->toBe('2026-12-15 10:00:00')
        ->and($dto->motifVisite)->toBe('CO');
});

it('signale les champs manquants', function () {
    try {
        CreateRdvDTO::fromArray([]);
        $this->fail('ValidationException attendue');
    } catch (ValidationException $e) {
        expect($e->getErrors())->toHaveKeys(['praticien_id', 'patient_id', 'date_heure_debut', 'motif_visite']);
    }
});

it('refuse un id qui n\'est pas un UUID', function () use ($valide) {
    CreateRdvDTO::fromArray([...$valide, 'praticien_id' => '123']);
})->throws(ValidationException::class);

it('refuse une date au mauvais format', function () use ($valide) {
    CreateRdvDTO::fromArray([...$valide, 'date_heure_debut' => '15/12/2026 10h']);
})->throws(ValidationException::class);

it('refuse un champ du mauvais type', function () use ($valide) {
    CreateRdvDTO::fromArray([...$valide, 'motif_visite' => ['CO']]);
})->throws(ValidationException::class);
