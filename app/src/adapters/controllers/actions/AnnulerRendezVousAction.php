<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\domain\exceptions\AnnulationImpossibleException;
use toubilib\domain\exceptions\RendezVousNotFoundException;

/** POST /rdvs/{id}/annuler  — fonctionnalité 6 */
class AnnulerRendezVousAction extends AbstractAction
{
    public function __construct(private RendezVousServiceInterface $rdvService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $rdv = $this->rdvService->annulerRendezVous($args['id']);
        } catch (RendezVousNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());                          // 404
        } catch (AnnulationImpossibleException $e) {
            throw (new HttpException($rq, $e->getMessage(), 409))->setTitle('409 Conflict'); // 409
        }

        return $this->json($rs, ['type' => 'ressource', 'rendez_vous' => $rdv]);
    }
}
