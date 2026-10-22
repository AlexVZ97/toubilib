<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\domain\exceptions\PraticienNotFoundException;

/** GET /praticiens/{id}/creneaux?debut=AAAA-MM-JJ&fin=AAAA-MM-JJ  — fonctionnalité 7 */
class CreneauxPraticienAction extends AbstractAction
{
    public function __construct(private RendezVousServiceInterface $rdvService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        [$debut, $fin] = $this->periode($rq);

        try {
            $creneaux = $this->rdvService->creneauxOccupes($args['id'], $debut, $fin);
        } catch (PraticienNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        }

        $periode = ['debut' => $debut->format('Y-m-d'), 'fin' => $fin->format('Y-m-d')];
        return $this->json($rs, [
            'type'         => 'collection',
            'praticien_id' => $args['id'],
            'periode'      => $periode,
            'count'        => count($creneaux),
            'creneaux'     => $creneaux,
            'links'        => [
                'self'      => $this->lien($rq, 'praticien-creneaux', ['id' => $args['id']], $periode),
                'praticien' => $this->lien($rq, 'praticien-detail', ['id' => $args['id']]),
            ],
        ]);
    }
}
