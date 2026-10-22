<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\domain\exceptions\RendezVousNotFoundException;

/** GET /rdvs/{id}  — fonctionnalité 3 */
class GetRendezVousAction extends AbstractAction
{
    public function __construct(private RendezVousServiceInterface $rdvService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $rdv = $this->rdvService->getRendezVous($args['id']);
        } catch (RendezVousNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());   // 404
        }

        return $this->json($rs, ['type' => 'ressource', 'rendez_vous' => $rdv]);
    }
}
