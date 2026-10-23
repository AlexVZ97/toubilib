<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\PatientServiceInterface;
use toubilib\domain\exceptions\PatientNotFoundException;

/** GET /patients/{id} */
class GetPatientAction extends AbstractAction
{
    public function __construct(private PatientServiceInterface $patientService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $patient = $this->patientService->getPatient($args['id']);
        } catch (PatientNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        }

        return $this->json($rs, ['type' => 'ressource', ...$this->patientAvecLiens($rq, $patient)]);
    }
}
