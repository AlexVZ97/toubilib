<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\ports\api\PraticienServiceInterface;

/** GET /praticiens  — fonctionnalité 1 */
class ListerPraticiensAction extends AbstractAction
{
    public function __construct(private PraticienServiceInterface $praticienService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        $praticiens = $this->praticienService->listerPraticiens();

        return $this->json($rs, [
            'type'       => 'collection',
            'count'      => count($praticiens),
            'praticiens' => array_map(fn($p) => $this->praticienAvecLiens($rq, $p), $praticiens),
            'links'      => ['self' => $this->lien($rq, 'praticiens-liste')],
        ]);
    }
}
