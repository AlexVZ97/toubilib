<?php

use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\usecases\RendezVousService;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\RendezVousNotFoundException;

it('annule un RDV et le sauvegarde', function () {
    $rdv = new RendezVous('rdv-1', 'prat-1', 'pat-1', new DateTimeImmutable('+7 days'), 30, 'CI');
    $repo = new InMemoryRendezVousRepository($rdv);
    $service = new RendezVousService($repo);         // on injecte le FAUX repository

    $dto = $service->annulerRendezVous('rdv-1');

    expect($dto)->toBeInstanceOf(RendezVousDTO::class)
        ->and($dto->etat)->toBe('ANNULE')
        ->and($repo->rdvs['rdv-1']->getStatut())->toBe(RendezVousStatut::ANNULE);
});

it('lève une exception si le RDV n\'existe pas', function () {
    (new RendezVousService(new InMemoryRendezVousRepository()))->annulerRendezVous('inconnu');
})->throws(RendezVousNotFoundException::class);

it('retourne le détail d\'un RDV', function () {
    $rdv = new RendezVous('rdv-1', 'prat-1', 'pat-1', new DateTimeImmutable('2026-11-01 09:00'), 15, 'CS');
    $dto = (new RendezVousService(new InMemoryRendezVousRepository($rdv)))->getRendezVous('rdv-1');
    expect($dto->id)->toBe('rdv-1')
        ->and($dto->date_fin)->toBe('2026-11-01 09:15')
        ->and($dto->etat)->toBe('PREVU');
});
