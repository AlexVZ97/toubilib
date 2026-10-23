<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\dto\CreateIndisponibiliteDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\IndisponibiliteServiceInterface;
use toubilib\domain\exceptions\ConflitAgendaException;
use toubilib\domain\exceptions\IndisponibiliteInvalideException;
use toubilib\domain\exceptions\PraticienNotFoundException;

/** POST /praticiens/{id}/indisponibilites  — fonctionnalité 11 */
class CreerIndisponibiliteAction extends AbstractAction
{
    public function __construct(private IndisponibiliteServiceInterface $indispoService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        $body = $rq->getParsedBody();
        try {
            $dto = CreateIndisponibiliteDTO::fromArray($args['id'], is_array($body) ? $body : []);
        } catch (ValidationException $e) {
            return $this->json($rs, ['message' => 'Données invalides', 'errors' => $e->getErrors()], 400);
        }

        try {
            $indispo = $this->indispoService->creerIndisponibilite($dto);
        } catch (PraticienNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());                 // 404
        } catch (IndisponibiliteInvalideException $e) {
            return $this->json($rs, ['message' => $e->getMessage()], 422);         // dates incohérentes
        } catch (ConflitAgendaException $e) {
            return $this->json($rs, ['message' => $e->getMessage()], 409);         // RDV prévus pendant la période
        }

        return $this->json($rs, ['type' => 'ressource', ...$this->indisponibiliteAvecLiens($rq, $indispo)], 201);
    }
}
