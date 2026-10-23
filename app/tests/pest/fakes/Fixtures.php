<?php
declare(strict_types=1);

namespace tests\pest\fakes;

use toubilib\domain\entities\Patient;
use toubilib\domain\entities\Praticien;
use toubilib\domain\entities\RendezVous;
use toubilib\domain\entities\RendezVousStatut;
use toubilib\domain\entities\Specialite;

/** Petites "usines" d'objets pour les tests. */
final class Fixtures
{
    public static function praticien(string $id = 'prat-1'): Praticien
    {
        return new Praticien($id, 'Paul', 'Marine', 'Paris', 'marine@ex.fr', '0102030405',
            new Specialite(1, 'médecine générale'));
    }

    public static function patient(string $id = 'pat-1'): Patient
    {
        return new Patient($id, 'Teixeira', 'Denis', '0605040302');
    }

    public static function rdv(string $debut, int $duree = 20, RendezVousStatut $statut = RendezVousStatut::PREVU,
                               string $id = 'rdv-1', string $praticienId = 'prat-1', string $patientId = 'pat-1'): RendezVous
    {
        return new RendezVous($id, $praticienId, $patientId, new \DateTimeImmutable($debut), $duree, 'CO', $statut);
    }
}
