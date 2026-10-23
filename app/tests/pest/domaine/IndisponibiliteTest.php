<?php

use tests\pest\fakes\Fixtures;
use toubilib\domain\entities\Indisponibilite;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\ConflitAgendaException;
use toubilib\domain\exceptions\IndisponibiliteInvalideException;
use toubilib\domain\exceptions\PraticienIndisponibleException;

$maintenant = new DateTimeImmutable('2026-10-20 10:00');

it('refuse une indisponibilité dont la fin précède le début', function () {
    new Indisponibilite('i', 'prat-1', new DateTimeImmutable('2026-11-02 12:00'), new DateTimeImmutable('2026-11-02 08:00'));
})->throws(IndisponibiliteInvalideException::class);

it('détecte le chevauchement avec une période', function () {
    $i = Fixtures::indispo('2026-11-02 08:00', '2026-11-02 12:00');
    expect($i->chevauche(new DateTimeImmutable('2026-11-02 11:30'), new DateTimeImmutable('2026-11-02 11:50')))->toBeTrue()
        ->and($i->chevauche(new DateTimeImmutable('2026-11-02 12:00'), new DateTimeImmutable('2026-11-02 12:20')))->toBeFalse();
});

it('le praticien crée une indisponibilité valide', function () use ($maintenant) {
    $p = Fixtures::praticien();
    $i = $p->ajouterIndisponibilite(new DateTimeImmutable('2026-11-02 08:00'), new DateTimeImmutable('2026-11-06 19:00'), 'Congés', $maintenant);
    expect($i->getPraticienId())->toBe('prat-1')
        ->and($i->getMotif())->toBe('Congés')
        ->and($p->getIndisponibilites())->toHaveCount(1);
});

it('refuse une indisponibilité dans le passé', function () use ($maintenant) {
    Fixtures::praticien()->ajouterIndisponibilite(new DateTimeImmutable('2026-10-19 08:00'), new DateTimeImmutable('2026-10-19 12:00'), null, $maintenant);
})->throws(IndisponibiliteInvalideException::class);

it('refuse une indisponibilité sur des RDV déjà prévus', function () use ($maintenant) {
    $p = Fixtures::praticien();
    $p->chargerAgenda([Fixtures::rdv('2026-11-02 10:00', 20)]);
    $p->ajouterIndisponibilite(new DateTimeImmutable('2026-11-02 08:00'), new DateTimeImmutable('2026-11-02 12:00'), null, $maintenant);
})->throws(ConflitAgendaException::class);

it('accepte une indisponibilité sur un RDV annulé', function () use ($maintenant) {
    $p = Fixtures::praticien();
    $p->chargerAgenda([Fixtures::rdv('2026-11-02 10:00', 20, RendezVousStatut::ANNULE)]);
    $p->ajouterIndisponibilite(new DateTimeImmutable('2026-11-02 08:00'), new DateTimeImmutable('2026-11-02 12:00'), null, $maintenant);
    expect($p->getIndisponibilites())->toHaveCount(1);
});

it('refuse deux indisponibilités qui se chevauchent', function () use ($maintenant) {
    $p = Fixtures::praticien();
    $p->chargerIndisponibilites([Fixtures::indispo('2026-11-02 08:00', '2026-11-02 12:00')]);
    $p->ajouterIndisponibilite(new DateTimeImmutable('2026-11-02 11:00'), new DateTimeImmutable('2026-11-02 14:00'), null, $maintenant);
})->throws(ConflitAgendaException::class);

it('refuse la création d\'un RDV pendant une indisponibilité', function () use ($maintenant) {
    $p = Fixtures::praticien();
    $p->chargerIndisponibilites([Fixtures::indispo('2026-11-02 08:00', '2026-11-02 12:00')]);
    RendezVous::creer($p, Fixtures::patient(), new DateTimeImmutable('2026-11-02 11:50'), 'CO', $maintenant);
})->throws(PraticienIndisponibleException::class);
