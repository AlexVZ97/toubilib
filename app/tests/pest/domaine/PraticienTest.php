<?php

use tests\pest\fakes\Fixtures;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\HoraireInvalideException;
use toubilib\domain\exceptions\MotifVisiteInvalideException;
use toubilib\domain\exceptions\PraticienIndisponibleException;

$maintenant = new DateTimeImmutable('2026-10-20 10:00');   // un mardi
// 2026-11-02 = lundi, 2026-11-07 = samedi

// ── motif et durée ──
it('calcule la durée selon le motif', function () {
    $p = Fixtures::praticien();
    expect($p->dureeMotif('CI'))->toBe(30)
        ->and($p->dureeMotif('CO'))->toBe(20)
        ->and($p->dureeMotif('CS'))->toBe(15);
});

it('refuse un motif inconnu', function () {
    Fixtures::praticien()->verifierMotif('XX');
})->throws(MotifVisiteInvalideException::class);

// ── horaires ──
it('accepte un créneau valide (lundi 10h, 30 min)', function () use ($maintenant) {
    Fixtures::praticien()->verifierHoraire(new DateTimeImmutable('2026-11-02 10:00'), 30, $maintenant);
    expect(true)->toBeTrue();   // aucune exception
});

it('refuse une date passée', function () use ($maintenant) {
    Fixtures::praticien()->verifierHoraire(new DateTimeImmutable('2026-10-19 10:00'), 20, $maintenant);
})->throws(HoraireInvalideException::class);

it('refuse un samedi', function () use ($maintenant) {
    Fixtures::praticien()->verifierHoraire(new DateTimeImmutable('2026-11-07 10:00'), 20, $maintenant);
})->throws(HoraireInvalideException::class);

it('refuse avant l\'ouverture', function () use ($maintenant) {
    Fixtures::praticien()->verifierHoraire(new DateTimeImmutable('2026-11-02 07:30'), 20, $maintenant);
})->throws(HoraireInvalideException::class);

it('refuse un RDV qui finit après la fermeture', function () use ($maintenant) {
    Fixtures::praticien()->verifierHoraire(new DateTimeImmutable('2026-11-02 18:45'), 30, $maintenant);
})->throws(HoraireInvalideException::class);

// ── disponibilité ──
it('détecte un chevauchement avec l\'agenda', function () {
    $p = Fixtures::praticien();
    $p->chargerAgenda([Fixtures::rdv('2026-11-02 10:00', 30)]);             // 10:00 → 10:30
    $p->verifierDisponibilite(Fixtures::rdv('2026-11-02 10:15', 20, id: 'new'));
})->throws(PraticienIndisponibleException::class);

it('accepte un RDV qui commence pile à la fin du précédent', function () {
    $p = Fixtures::praticien();
    $p->chargerAgenda([Fixtures::rdv('2026-11-02 10:00', 30)]);
    $p->verifierDisponibilite(Fixtures::rdv('2026-11-02 10:30', 20, id: 'new'));
    expect(true)->toBeTrue();
});

it('ignore les RDV annulés de l\'agenda', function () {
    $p = Fixtures::praticien();
    $p->chargerAgenda([Fixtures::rdv('2026-11-02 10:00', 30, RendezVousStatut::ANNULE)]);
    $p->verifierDisponibilite(Fixtures::rdv('2026-11-02 10:00', 20, id: 'new'));
    expect(true)->toBeTrue();
});
