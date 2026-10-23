<?php

use tests\pest\fakes\Fixtures;
use tests\pest\fakes\InMemoryPatientRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\usecases\PatientService;
use toubilib\domain\exceptions\PatientNotFoundException;

function servicePatients(): PatientService
{
    return new PatientService(
        new InMemoryPatientRepository(Fixtures::patient('pat-1'), Fixtures::patient('pat-2')),
        new InMemoryRendezVousRepository(
            Fixtures::rdv('2026-01-10 09:00', id: 'ancien'),
            Fixtures::rdv('2026-06-15 14:00', id: 'recent'),
            Fixtures::rdv('2026-03-01 10:00', id: 'autre', patientId: 'pat-2'),
        ),
    );
}

it('retourne le détail d\'un patient', function () {
    expect(servicePatients()->getPatient('pat-1')->nom)->toBe('Teixeira');
});

it('retourne l\'historique d\'un patient, du plus récent au plus ancien', function () {
    $h = servicePatients()->historiquePatient('pat-1');
    expect(array_map(fn($r) => $r->id, $h))->toBe(['recent', 'ancien']);
});

it('retourne un historique vide pour un patient sans RDV', function () {
    $s = new PatientService(new InMemoryPatientRepository(Fixtures::patient('seul')), new InMemoryRendezVousRepository());
    expect($s->historiquePatient('seul'))->toBe([]);
});

it('refuse l\'historique d\'un patient inexistant', function () {
    servicePatients()->historiquePatient('inconnu');
})->throws(PatientNotFoundException::class);
