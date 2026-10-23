<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use toubilib\application\ports\api\PatientServiceInterface;
use toubilib\domain\exceptions\PatientNotFoundException;

/** GET /patients/{id}/rdvs  — fonctionnalité 10 */
class HistoriquePatientAction extends AbstractAction
{
    public function __construct(private PatientServiceInterface $patientService) {}

    public function __invoke(Request $rq, Response $rs, array $args): Response
    {
        try {
            $rdvs = $this->patientService->historiquePatient($args['id']);
        } catch (PatientNotFoundException $e) {
            throw new HttpNotFoundException($rq, $e->getMessage());
        }

        return $this->json($rs, [
            'type'        => 'collection',
            'patient_id'  => $args['id'],
            'count'       => count($rdvs),
            'rendez_vous' => array_map(fn($rdv) => $this->rdvAvecLiens($rq, $rdv), $rdvs),
            'links'       => [
                'self'    => $this->lien($rq, 'patient-historique', ['id' => $args['id']]),
                'patient' => $this->lien($rq, 'patient-detail', ['id' => $args['id']]),
            ],
        ]);
    }
}
