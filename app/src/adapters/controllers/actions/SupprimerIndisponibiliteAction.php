<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\IndisponibiliteServiceInterface;
use toubilib\domain\exceptions\IndisponibiliteNotFoundException;

/** DELETE /praticiens/{id}/indisponibilites/{indispoId}  — fonctionnalité 11 */
class SupprimerIndisponibiliteAction extends AbstractAction
{
    public function __construct(private IndisponibiliteServiceInterface $indispoService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $this->indispoService->supprimerIndisponibilite($args['id'], $args['indispoId']);
        } catch (IndisponibiliteNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        }
        return $rs->withStatus(204);    // 204 No Content : supprimé, rien à renvoyer
    }
}
