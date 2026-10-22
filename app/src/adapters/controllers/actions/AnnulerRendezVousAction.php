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

/** Adaptateur d'entrée : traduit HTTP  <->  appel au port API du noyau. */
class AnnulerRendezVousAction
{
    public function __construct(
        private RendezVousServiceInterface $rdvService
    ) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        $id = $args['id'];

        try {
            $rdv = $this->rdvService->annulerRendezVous($id);
        } catch (RendezVousNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());        // 404
        } catch (AnnulationImpossibleException $e) {
            throw (new HttpException($rq, $e->getMessage(), 409))->setTitle('409 Conflict');
        }

        $rs->getBody()->write(json_encode([
            'type'        => 'ressource',
            'rendez_vous' => $rdv,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $rs->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
