<?php

use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\AnnulationImpossibleException;

function unRdv(string $debut, RendezVousStatut $statut = RendezVousStatut::PREVU): RendezVous
{
    return new RendezVous('rdv-1', 'prat-1', 'pat-1', new DateTimeImmutable($debut), 20, 'CO', $statut);
}

$maintenant = new DateTimeImmutable('2026-10-20 10:00');

it('crée un RDV à l\'état PREVU par défaut', function () {
    expect(unRdv('2026-11-01 09:00')->getStatut())->toBe(RendezVousStatut::PREVU);
});

it('calcule la date de fin à partir de la durée', function () {
    expect(unRdv('2026-11-01 09:00')->getDateHeureFin()->format('H:i'))->toBe('09:20');
});

it('annule un RDV prévu dans le futur', function () use ($maintenant) {
    $rdv = unRdv('2026-11-01 09:00');
    $rdv->annuler($maintenant);
    expect($rdv->getStatut())->toBe(RendezVousStatut::ANNULE);
});

it('refuse d\'annuler un RDV passé', function () use ($maintenant) {
    unRdv('2026-10-19 09:00')->annuler($maintenant);
})->throws(AnnulationImpossibleException::class);

it('refuse d\'annuler un RDV déjà annulé', function () use ($maintenant) {
    unRdv('2026-11-01 09:00', RendezVousStatut::ANNULE)->annuler($maintenant);
})->throws(AnnulationImpossibleException::class);

it('refuse d\'annuler un RDV honoré', function () use ($maintenant) {
    unRdv('2026-11-01 09:00', RendezVousStatut::HONORE)->annuler($maintenant);
})->throws(AnnulationImpossibleException::class);
