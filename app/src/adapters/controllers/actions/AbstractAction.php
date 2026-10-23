<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\ValidatorBuilder as v;
use Slim\Exception\HttpBadRequestException;
use Slim\Routing\RouteContext;
use toubilib\application\dto\PatientDTO;
use toubilib\application\dto\PraticienDTO;
use toubilib\application\dto\PraticienDetailDTO;
use toubilib\application\dto\RendezVousDTO;

/**
 * Classe mère des actions : code commun
 *  - écriture d'une réponse JSON
 *  - construction des liens HATEOAS (à partir des NOMS de routes, cf. routes.php)
 *  - lecture d'une période ?debut=...&fin=... dans l'URL
 */
abstract class AbstractAction
{
    abstract public function __invoke(Request $rq, Response $rs, array $args): Response;

    protected function json(Response $rs, mixed $data, int $status = 200): Response
    {
        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $rs->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    // ── HATEOAS ─────────────────────────────────────────────────

    /** URL d'une route à partir de son nom : lien('rdv-detail', ['id' => 'x']) → "/rdvs/x" */
    protected function lien(Request $rq, string $route, array $params = [], array $query = []): array
    {
        return ['href' => RouteContext::fromRequest($rq)->getRouteParser()->urlFor($route, $params, $query)];
    }

    /** Un RDV + ses liens ; les actions possibles dépendent de son état. */
    protected function rdvAvecLiens(Request $rq, RendezVousDTO $rdv): array
    {
        $links = [
            'self'      => $this->lien($rq, 'rdv-detail', ['id' => $rdv->id]),
            'praticien' => $this->lien($rq, 'praticien-detail', ['id' => $rdv->praticien_id]),
            'patient'   => $this->lien($rq, 'patient-detail', ['id' => $rdv->patient_id]),
        ];
        if ($rdv->etat === 'PREVU') {
            $links['annuler'] = $this->lien($rq, 'rdv-annuler', ['id' => $rdv->id]);
            $links['honorer'] = $this->lien($rq, 'rdv-honorer', ['id' => $rdv->id]);
            $links['ignorer'] = $this->lien($rq, 'rdv-ignorer', ['id' => $rdv->id]);
        }
        return ['rendez_vous' => $rdv, 'links' => $links];
    }

    protected function praticienAvecLiens(Request $rq, PraticienDTO|PraticienDetailDTO $p): array
    {
        return [
            'praticien' => $p,
            'links' => [
                'self'     => $this->lien($rq, 'praticien-detail', ['id' => $p->id]),
                'agenda'   => $this->lien($rq, 'praticien-agenda', ['id' => $p->id]),
                'creneaux' => $this->lien($rq, 'praticien-creneaux', ['id' => $p->id]),
            ],
        ];
    }

    protected function patientAvecLiens(Request $rq, PatientDTO $p): array
    {
        return [
            'patient' => $p,
            'links' => [
                'self'       => $this->lien($rq, 'patient-detail', ['id' => $p->id]),
                'historique' => $this->lien($rq, 'patient-historique', ['id' => $p->id]),
            ],
        ];
    }

    // ── paramètres de période ───────────────────────────────────

    /**
     * Lit ?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ (par défaut : aujourd'hui).
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable}
     * @throws HttpBadRequestException  (400) si le format est faux ou si debut > fin
     */
    protected function periode(Request $rq): array
    {
        $q = $rq->getQueryParams();
        $aujourdhui = (new \DateTimeImmutable())->format('Y-m-d');
        $debut = $q['debut'] ?? $aujourdhui;
        $fin   = $q['fin'] ?? $debut;

        foreach (['debut' => $debut, 'fin' => $fin] as $nom => $valeur) {
            if (!v::stringType()->dateTime('Y-m-d')->isValid($valeur)) {
                throw new HttpBadRequestException($rq, "Paramètre '$nom' invalide : format attendu AAAA-MM-JJ");
            }
        }
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $debut);
        $f = \DateTimeImmutable::createFromFormat('!Y-m-d', $fin);
        if ($d > $f) {
            throw new HttpBadRequestException($rq, "La date de début doit précéder la date de fin");
        }
        return [$d, $f];
    }
}
