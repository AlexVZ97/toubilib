<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/** Classe mère des actions : regroupe le code commun (écriture d'une réponse JSON). */
abstract class AbstractAction
{
    abstract public function __invoke(Request $rq, Response $rs, array $args): Response;

    protected function json(Response $rs, mixed $data, int $status = 200): Response
    {
        $rs->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        return $rs->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}
