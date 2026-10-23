<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\IndisponibiliteServiceInterface;
use toubilib\domain\exceptions\PraticienNotFoundException;

/** GET /praticiens/{id}/indisponibilites  — fonctionnalité 11 */
class ListerIndisponibilitesAction extends AbstractAction
{
    public function __construct(private IndisponibiliteServiceInterface $indispoService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $indispos = $this->indispoService->listerIndisponibilites($args['id']);
        } catch (PraticienNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        }

        return $this->json($rs, [
            'type'             => 'collection',
            'praticien_id'     => $args['id'],
            'count'            => count($indispos),
            'indisponibilites' => array_map(fn($i) => $this->indisponibiliteAvecLiens($rq, $i), $indispos),
            'links'            => [
                'self'      => $this->lien($rq, 'praticien-indisponibilites', ['id' => $args['id']]),
                'praticien' => $this->lien($rq, 'praticien-detail', ['id' => $args['id']]),
            ],
        ]);
    }
}
