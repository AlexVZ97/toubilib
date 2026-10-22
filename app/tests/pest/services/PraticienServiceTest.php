<?php

use tests\pest\fakes\InMemoryPraticienRepository;
use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\usecases\PraticienService;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\Specialite;
use toubilib\domain\exceptions\PraticienNotFoundException;

function servicePraticiens(): PraticienService
{
    $generaliste = new Specialite(1, 'médecine générale', 'Médecine Générale');
    return new PraticienService(new InMemoryPraticienRepository(
        new Praticien('p1', 'Paul', 'Marine', 'Paris', 'marine@ex.fr', '0102030405', $generaliste),
        new Praticien('p2', 'Durand', 'Luc', 'Nancy', 'luc@ex.fr', '0607080910', $generaliste),
    ));
}

it('liste tous les praticiens sous forme de DTO', function () {
    $liste = servicePraticiens()->listerPraticiens();
    expect($liste)->toHaveCount(2)
        ->and($liste[0])->toBeInstanceOf(PraticienDTO::class)
        ->and($liste[0]->specialite)->toBe('médecine générale');
});

it('retourne le détail d\'un praticien', function () {
    $p = servicePraticiens()->getPraticien('p2');
    expect($p)->toBeInstanceOf(PraticienDetailDTO::class)
        ->and($p->nom)->toBe('Durand')
        ->and($p->specialite['libelle'])->toBe('médecine générale');
});

it('lève une exception si le praticien n\'existe pas', function () {
    servicePraticiens()->getPraticien('inconnu');
})->throws(PraticienNotFoundException::class);
