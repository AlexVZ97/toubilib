<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\PraticienServiceInterface;
use toubilib\domain\exceptions\PraticienNotFoundException;

/** GET /praticiens/{id}  — fonctionnalité 2 */
class GetPraticienAction extends AbstractAction
{
    public function __construct(private PraticienServiceInterface $praticienService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $praticien = $this->praticienService->getPraticien($args['id']);
        } catch (PraticienNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());   // 404
        }

        return $this->json($rs, ['type' => 'ressource', 'praticien' => $praticien]);
    }
}
