<?php
declare(strict_types=1);

namespace toubilib\application\exceptions;

class ValidationException extends \Exception
{
    /** @param array<string,string> $errors */
    public function __construct(private array $errors, string $message = 'Données invalides')
    {
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
