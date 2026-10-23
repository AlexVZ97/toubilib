<?php
declare(strict_types=1);

namespace toubilib\adapters\controllers\handlers;

use Slim\Handlers\ErrorHandler;

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
