<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use toubilib\application\dto\CreateRdvDTO;
use toubilib\application\exceptions\ValidationException;
use toubilib\application\ports\api\RendezVousServiceInterface;
use toubilib\domain\exceptions\HoraireInvalideException;
use toubilib\domain\exceptions\MotifVisiteInvalideException;
use toubilib\domain\exceptions\PraticienIndisponibleException;

class CreerRendezVousAction extends AbstractAction
{
    public function __construct(private RendezVousServiceInterface $rdvService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        $body = $rq->getParsedBody();
        try {
            $dto = CreateRdvDTO::fromArray(is_array($body) ? $body : []);
        } catch (ValidationException $e) {
            return $this->json($rs, ['message' => 'Données invalides', 'errors' => $e->getErrors()], 400);
        }

        try {
            $rdv = $this->rdvService->creerRendezVous($dto);
        } catch (ValidationException $e) {
            return $this->json($rs, ['message' => 'Données invalides', 'errors' => $e->getErrors()], 422);
        } catch (MotifVisiteInvalideException | HoraireInvalideException $e) {
            return $this->json($rs, ['message' => $e->getMessage()], 422);
        } catch (PraticienIndisponibleException $e) {
            return $this->json($rs, ['message' => $e->getMessage()], 409);
        }

        $ressource = $this->rdvAvecLiens($rq, $rdv);
        return $this->json($rs, ['type' => 'ressource', ...$ressource], 201)
            ->withHeader('Location', $ressource['links']['self']['href']);
    }
}
