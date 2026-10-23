<?php

use Psr\Log\NullLogger;
use tests\pest\fakes\Fixtures;
use tests\pest\fakes\InMemoryIndisponibiliteRepository;
use tests\pest\fakes\InMemoryPraticienRepository;
use tests\pest\fakes\InMemoryRendezVousRepository;
use toubilib\application\dto\CreateIndisponibiliteDTO;
use toubilib\application\usecases\IndisponibiliteService;
use toubilib\domain\exceptions\ConflitAgendaException;
use toubilib\domain\exceptions\IndisponibiliteNotFoundException;
use toubilib\domain\exceptions\PraticienNotFoundException;

function serviceIndispo(InMemoryIndisponibiliteRepository $indispoRepo, ?InMemoryRendezVousRepository $rdvRepo = null): IndisponibiliteService
{
    return new IndisponibiliteService(
        new InMemoryPraticienRepository(Fixtures::praticien('prat-1')),
        $indispoRepo,
        $rdvRepo ?? new InMemoryRendezVousRepository(),
        new NullLogger(),
    );
}

function jourFutur(string $heure): string
{
    return (new DateTimeImmutable('+10 days'))->format('Y-m-d') . " $heure";
}

it('crée et sauvegarde une indisponibilité', function () {
    $repo = new InMemoryIndisponibiliteRepository();
    $dto = serviceIndispo($repo)->creerIndisponibilite(CreateIndisponibiliteDTO::fromArray('prat-1',
        ['date_debut' => jourFutur('08:00'), 'date_fin' => jourFutur('12:00'), 'motif' => 'Formation']));
    expect($repo->indispos)->toHaveKey($dto->id)
        ->and($dto->motif)->toBe('Formation');
});

it('refuse une indisponibilité qui recouvre un RDV prévu', function () {
    $rdv = Fixtures::rdv(jourFutur('09:00'), 20, praticienId: 'prat-1');
    serviceIndispo(new InMemoryIndisponibiliteRepository(), new InMemoryRendezVousRepository($rdv))
        ->creerIndisponibilite(CreateIndisponibiliteDTO::fromArray('prat-1',
            ['date_debut' => jourFutur('08:00'), 'date_fin' => jourFutur('12:00')]));
})->throws(ConflitAgendaException::class);

it('liste les indisponibilités à venir seulement', function () {
    $repo = new InMemoryIndisponibiliteRepository(
        Fixtures::indispo('-10 days', '-9 days', id: 'passee'),
        Fixtures::indispo('+5 days', '+6 days', id: 'future'),
    );
    expect(array_map(fn($i) => $i->id, serviceIndispo($repo)->listerIndisponibilites('prat-1')))->toBe(['future']);
});

it('refuse la liste pour un praticien inexistant', function () {
    serviceIndispo(new InMemoryIndisponibiliteRepository())->listerIndisponibilites('inconnu');
})->throws(PraticienNotFoundException::class);

it('supprime une indisponibilité', function () {
    $repo = new InMemoryIndisponibiliteRepository(Fixtures::indispo('+5 days', '+6 days'));
    serviceIndispo($repo)->supprimerIndisponibilite('prat-1', 'ind-1');
    expect($repo->indispos)->toBeEmpty();
});

it('refuse de supprimer l\'indisponibilité d\'un autre praticien', function () {
    $repo = new InMemoryIndisponibiliteRepository(Fixtures::indispo('+5 days', '+6 days', praticienId: 'autre'));
    serviceIndispo($repo)->supprimerIndisponibilite('prat-1', 'ind-1');
})->throws(IndisponibiliteNotFoundException::class);
