<?php
declare(strict_types=1);

namespace toubilib\application\ports\api;

use toubilib\application\dto\CreateIndisponibiliteDTO;
use toubilib\application\dto\IndisponibiliteDTO;

/** Port d'entrée : gestion des indisponibilités d'un praticien (fonctionnalité 11). */
interface IndisponibiliteServiceInterface
{
    /**
     * Indisponibilités en cours ou à venir
     * @return IndisponibiliteDTO[]
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     */
    public function listerIndisponibilites(string $praticienId): array;

    /**
     * @throws \toubilib\domain\exceptions\PraticienNotFoundException
     * @throws \toubilib\domain\exceptions\IndisponibiliteInvalideException   dates incohérentes ou passées
     * @throws \toubilib\domain\exceptions\ConflitAgendaException             RDV prévus / autre indisponibilité
     */
    public function creerIndisponibilite(CreateIndisponibiliteDTO $dto): IndisponibiliteDTO;

    /** @throws \toubilib\domain\exceptions\IndisponibiliteNotFoundException */
    public function supprimerIndisponibilite(string $praticienId, string $indisponibiliteId): void;
}
