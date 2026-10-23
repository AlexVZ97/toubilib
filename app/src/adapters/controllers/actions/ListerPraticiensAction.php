<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\ValidatorBuilder as v;
use Slim\Exception\HttpBadRequestException;
use toubilib\application\ports\api\PraticienServiceInterface;

/**
 * GET /praticiens                                  — fonctionnalité 1 (liste)
 * GET /praticiens?specialite=pédiatrie&ville=nancy — fonctionnalité 8 (recherche)
 */
class ListerPraticiensAction extends AbstractAction
{
    public function __construct(private PraticienServiceInterface $praticienService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        $q = $rq->getQueryParams();
        $criteres = [];
        foreach (['specialite', 'ville'] as $nom) {
            if (!isset($q[$nom]) || $q[$nom] === '') {
                continue;
            }
            // filtrage : texte simple de 1 à 48 caractères, sans balises HTML
            if (!v::stringType()->length(v::between(1, 48))->isValid($q[$nom]) || $q[$nom] !== strip_tags($q[$nom])) {
                throw new HttpBadRequestException($rq, "Paramètre '$nom' invalide");
            }
            $criteres[$nom] = trim($q[$nom]);
        }

        $praticiens = $this->praticienService->rechercherPraticiens(
            $criteres['specialite'] ?? null,
            $criteres['ville'] ?? null,
        );

        return $this->json($rs, [
            'type'       => 'collection',
            'criteres'   => (object) $criteres,
            'count'      => count($praticiens),
            'praticiens' => array_map(fn($p) => $this->praticienAvecLiens($rq, $p), $praticiens),
            'links'      => ['self' => $this->lien($rq, 'praticiens-liste', [], $criteres)],
        ]);
    }
}
