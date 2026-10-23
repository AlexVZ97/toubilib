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
    $pediatre = new Specialite(3, 'pédiatrie', 'Maladies des enfants');
    return new PraticienService(new InMemoryPraticienRepository(
        new Praticien('p1', 'Paul', 'Marine', 'Paris', 'marine@ex.fr', '0102030405', $generaliste),
        new Praticien('p2', 'Durand', 'Luc', 'Nancy', 'luc@ex.fr', '0607080910', $generaliste),
        new Praticien('p3', 'Martin', 'Léa', 'Nancy', 'lea@ex.fr', '0611111111', $pediatre),
    ));
}

it('liste tous les praticiens sous forme de DTO', function () {
    $liste = servicePraticiens()->listerPraticiens();
    expect($liste)->toHaveCount(3)
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

// ── recherche (fonctionnalité 8) ──
it('recherche par spécialité', function () {
    $r = servicePraticiens()->rechercherPraticiens('pédiatrie', null);
    expect($r)->toHaveCount(1)->and($r[0]->nom)->toBe('Martin');
});

it('recherche par ville sans tenir compte de la casse', function () {
    expect(servicePraticiens()->rechercherPraticiens(null, 'NANCY'))->toHaveCount(2);
});

it('combine spécialité ET ville', function () {
    $r = servicePraticiens()->rechercherPraticiens('générale', 'nancy');
    expect($r)->toHaveCount(1)->and($r[0]->id)->toBe('p2');
});

it('sans critère, retourne tous les praticiens', function () {
    expect(servicePraticiens()->rechercherPraticiens(null, null))->toHaveCount(3);
});
