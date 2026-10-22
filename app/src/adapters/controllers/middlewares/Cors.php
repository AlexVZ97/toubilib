<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\middlewares;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

/**
 * Middleware CORS : autorise un front (autre origine, ex. http://localhost:5173)
 * à appeler l'API depuis le navigateur.
 * - requête "preflight" (OPTIONS) : on répond tout de suite 204 avec les en-têtes
 * - autres requêtes : on laisse passer, puis on ajoute les en-têtes à la réponse
 */
class Cors
{
    public function __construct(private ResponseFactoryInterface $responseFactory) {}

    public function __invoke(Request $rq, Handler $next): Response
    {
        $rs = $rq->getMethod() === 'OPTIONS'
            ? $this->responseFactory->createResponse(204)
            : $next->handle($rq);

        return $rs
            ->withHeader('Access-Control-Allow-Origin', $rq->getHeaderLine('Origin') ?: '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Origin')
            ->withHeader('Access-Control-Expose-Headers', 'Location')
            ->withHeader('Access-Control-Max-Age', '3600');
    }
}
