<?php

use toubilib\application\dto\CreateIndisponibiliteDTO;
use toubilib\application\exceptions\ValidationException;

it('crée le DTO avec un motif facultatif', function () {
    $dto = CreateIndisponibiliteDTO::fromArray('prat-1', ['date_debut' => '2026-12-01 08:00', 'date_fin' => '2026-12-05 19:00']);
    expect($dto->motif)->toBeNull()
        ->and($dto->dateFin->format('Y-m-d H:i'))->toBe('2026-12-05 19:00');
});

it('signale les dates manquantes', function () {
    try {
        CreateIndisponibiliteDTO::fromArray('prat-1', ['motif' => 'Congés']);
        $this->fail('ValidationException attendue');
    } catch (ValidationException $e) {
        expect($e->getErrors())->toHaveKeys(['date_debut', 'date_fin']);
    }
});

it('refuse un motif contenant du HTML', function () {
    CreateIndisponibiliteDTO::fromArray('prat-1', [
        'date_debut' => '2026-12-01 08:00', 'date_fin' => '2026-12-01 12:00', 'motif' => '<b>Congés</b>',
    ]);
})->throws(ValidationException::class);
