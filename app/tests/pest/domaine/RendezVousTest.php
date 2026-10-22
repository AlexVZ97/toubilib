<?php

use Ramsey\Uuid\Uuid;
use tests\pest\fakes\Fixtures;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\AnnulationImpossibleException;
use toubilib\domain\exceptions\HoraireInvalideException;
use toubilib\domain\exceptions\MotifVisiteInvalideException;
use toubilib\domain\exceptions\PraticienIndisponibleException;

$maintenant = new DateTimeImmutable('2026-10-20 10:00');

// ── état et dates ──
it('crée un RDV à l\'état PREVU par défaut', function () {
    expect(Fixtures::rdv('2026-11-02 09:00')->getStatut())->toBe(RendezVousStatut::PREVU);
});

it('calcule la date de fin à partir de la durée', function () {
    expect(Fixtures::rdv('2026-11-02 09:00', 20)->getDateHeureFin()->format('H:i'))->toBe('09:20');
});

// ── chevauchement ──
it('détecte deux RDV qui se chevauchent', function () {
    $a = Fixtures::rdv('2026-11-02 10:00', 30);
    expect($a->chevauche(Fixtures::rdv('2026-11-02 10:20', 20)))->toBeTrue()
        ->and($a->chevauche(Fixtures::rdv('2026-11-02 09:45', 20)))->toBeTrue()
        ->and($a->chevauche(Fixtures::rdv('2026-11-02 10:05', 10)))->toBeTrue();   // inclus dedans
});

it('ne voit pas de chevauchement entre RDV consécutifs ou éloignés', function () {
    $a = Fixtures::rdv('2026-11-02 10:00', 30);
    expect($a->chevauche(Fixtures::rdv('2026-11-02 10:30', 20)))->toBeFalse()
        ->and($a->chevauche(Fixtures::rdv('2026-11-02 09:40', 20)))->toBeFalse()
        ->and($a->chevauche(Fixtures::rdv('2026-11-02 15:00', 20)))->toBeFalse();
});

// ── création validée ──
it('crée un RDV valide avec la durée du motif', function () use ($maintenant) {
    $rdv = RendezVous::creer(Fixtures::praticien(), Fixtures::patient(),
        new DateTimeImmutable('2026-11-02 10:00'), 'CI', $maintenant);

    expect(Uuid::isValid($rdv->getId()))->toBeTrue()
        ->and($rdv->getDuree())->toBe(30)
        ->and($rdv->getPraticienId())->toBe('prat-1')
        ->and($rdv->getPatientId())->toBe('pat-1')
        ->and($rdv->getStatut())->toBe(RendezVousStatut::PREVU);
});

it('refuse la création avec un motif invalide', function () use ($maintenant) {
    RendezVous::creer(Fixtures::praticien(), Fixtures::patient(), new DateTimeImmutable('2026-11-02 10:00'), 'ZZ', $maintenant);
})->throws(MotifVisiteInvalideException::class);

it('refuse la création un dimanche', function () use ($maintenant) {
    RendezVous::creer(Fixtures::praticien(), Fixtures::patient(), new DateTimeImmutable('2026-11-08 10:00'), 'CO', $maintenant);
})->throws(HoraireInvalideException::class);

it('refuse la création si le praticien est occupé', function () use ($maintenant) {
    $praticien = Fixtures::praticien();
    $praticien->chargerAgenda([Fixtures::rdv('2026-11-02 10:00', 30)]);
    RendezVous::creer($praticien, Fixtures::patient(), new DateTimeImmutable('2026-11-02 10:10'), 'CS', $maintenant);
})->throws(PraticienIndisponibleException::class);

// ── annulation ──
it('annule un RDV prévu dans le futur', function () use ($maintenant) {
    $rdv = Fixtures::rdv('2026-11-02 09:00');
    $rdv->annuler($maintenant);
    expect($rdv->getStatut())->toBe(RendezVousStatut::ANNULE);
});

it('refuse d\'annuler un RDV passé', function () use ($maintenant) {
    Fixtures::rdv('2026-10-19 09:00')->annuler($maintenant);
})->throws(AnnulationImpossibleException::class);

it('refuse d\'annuler un RDV déjà annulé', function () use ($maintenant) {
    Fixtures::rdv('2026-11-02 09:00', statut: RendezVousStatut::ANNULE)->annuler($maintenant);
})->throws(AnnulationImpossibleException::class);

it('refuse d\'annuler un RDV honoré', function () use ($maintenant) {
    Fixtures::rdv('2026-11-02 09:00', statut: RendezVousStatut::HONORE)->annuler($maintenant);
})->throws(AnnulationImpossibleException::class);
