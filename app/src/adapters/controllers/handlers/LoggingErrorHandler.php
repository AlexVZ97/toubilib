<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\handlers;

use Slim\Handlers\ErrorHandler;

/**
 * Gestionnaire d'erreurs de Slim, modifié pour écrire des logs utiles :
 *  - erreur du client (4xx : 404, 409…) → NOTICE  (normal, pas grave)
 *  - erreur du serveur (5xx : base inaccessible, bug…) → ERROR (à corriger !)
 * Chaque ligne contient la requête (méthode + chemin) et le message de l'exception.
 */
class LoggingErrorHandler extends ErrorHandler
{
    protected function writeToErrorLog(): void
    {
        $contexte = [
            'requete' => $this->request->getMethod() . ' ' . $this->request->getUri()->getPath(),
            'message' => $this->exception->getMessage(),
        ];

        if ($this->statusCode >= 500) {
            $contexte['exception'] = get_class($this->exception);
            $contexte['fichier'] = $this->exception->getFile() . ':' . $this->exception->getLine();
            $this->logger->error("Erreur serveur {$this->statusCode}", $contexte);
        } else {
            $this->logger->notice("Erreur client {$this->statusCode}", $contexte);
        }
    }
}
