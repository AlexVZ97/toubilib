<?php

use tests\pest\fakes\Fixtures;
use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\dto\RendezVousDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\usecases\RendezVousService;
use toubilib\application\validators\CreateRdvValidator;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\exceptions\PraticienIndisponibleException;
use toubilib\domain\exceptions\RendezVousNotFoundException;

const PRAT = 'af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b';
const PAT  = 'd975aca7-50c5-3d16-b211-cf7d302cba50';

/** Service branché sur de FAUX repositories en mémoire (aucune base de données). */
function serviceRdv(InMemoryRendezVousRepository $rdvRepo): RendezVousService
{
    $validator = new CreateRdvValidator(
        new InMemoryPraticienRepository(Fixtures::praticien(PRAT)),
        new InMemoryPatientRepository(Fixtures::patient(PAT)),
    );
    return new RendezVousService($rdvRepo, $validator);
}

function lundiProchain(string $heure): string
{
    return (new DateTimeImmutable('next monday'))->format('Y-m-d') . " $heure";
}

function demande(array $modifs = []): CreateRdvDTO
{
    return CreateRdvDTO::fromArray([
        'praticien_id' => PRAT, 'patient_id' => PAT,
        'date_heure_debut' => lundiProchain('10:00'), 'motif_visite' => 'CO',
        ...$modifs,
    ]);
}

// ── détail (fonctionnalité 3) ──
it('retourne le détail d\'un RDV', function () {
    $dto = serviceRdv(new InMemoryRendezVousRepository(Fixtures::rdv('2026-11-02 09:00', 15)))->getRendezVous('rdv-1');
    expect($dto->id)->toBe('rdv-1')
        ->and($dto->date_fin)->toBe('2026-11-02 09:15')
        ->and($dto->etat)->toBe('PREVU');
});

// ── création (fonctionnalité 5) ──
it('crée un RDV et le sauvegarde', function () {
    $repo = new InMemoryRendezVousRepository();
    $dto = serviceRdv($repo)->creerRendezVous(demande());

    expect($dto)->toBeInstanceOf(RendezVousDTO::class)
        ->and($dto->duree)->toBe(20)
        ->and($dto->etat)->toBe('PREVU')
        ->and($repo->rdvs)->toHaveKey($dto->id);
});

it('refuse un praticien ou un patient inexistant', function () {
    try {
        serviceRdv(new InMemoryRendezVousRepository())->creerRendezVous(demande([
            'praticien_id' => '00000000-0000-4000-8000-000000000000',
            'patient_id'   => '00000000-0000-4000-8000-000000000001',
        ]));
        $this->fail('ValidationException attendue');
    } catch (ValidationException $e) {
        expect($e->getErrors())->toHaveKeys(['praticien_id', 'patient_id']);
    }
});

it('refuse un créneau déjà occupé', function () {
    $occupe = Fixtures::rdv(lundiProchain('09:50'), 20, praticienId: PRAT);   // 9:50 → 10:10
    serviceRdv(new InMemoryRendezVousRepository($occupe))->creerRendezVous(demande());
})->throws(PraticienIndisponibleException::class);

// ── annulation (fonctionnalité 6) ──
it('annule un RDV et le sauvegarde', function () {
    $repo = new InMemoryRendezVousRepository(Fixtures::rdv('+7 days'));
    $dto = serviceRdv($repo)->annulerRendezVous('rdv-1');

    expect($dto->etat)->toBe('ANNULE')
        ->and($repo->rdvs['rdv-1']->getStatut())->toBe(RendezVousStatut::ANNULE);
});

it('lève une exception si le RDV n\'existe pas', function () {
    serviceRdv(new InMemoryRendezVousRepository())->annulerRendezVous('inconnu');
})->throws(RendezVousNotFoundException::class);
