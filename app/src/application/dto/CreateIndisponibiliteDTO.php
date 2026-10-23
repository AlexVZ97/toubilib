<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\ValidatorBuilder as v;
use toubilib\application\exceptions\ValidationException;

/** Demande de création d'une indisponibilité : n'existe que si les données sont bien formées. */
final class CreateIndisponibiliteDTO
{
    public const FORMAT_DATE = 'Y-m-d H:i';

    private function __construct(
        public readonly string $praticienId,
        public readonly \DateTimeImmutable $dateDebut,
        public readonly \DateTimeImmutable $dateFin,
        public readonly ?string $motif,
    ) {}

    /** @throws ValidationException */
    public static function fromArray(string $praticienId, array $data): self
    {
        try {
            v::key('date_debut', v::stringType()->dateTime(self::FORMAT_DATE))
                ->key('date_fin', v::stringType()->dateTime(self::FORMAT_DATE))
                ->assert($data);
        } catch (RespectValidationException $e) {
            $errors = $e->getMessages();
            unset($errors['__root__']);
            throw new ValidationException($errors ?: ['body' => $e->getMessage()]);
        }

        // motif : facultatif, texte de 128 caractères max, sans balises HTML
        $motif = $data['motif'] ?? null;
        if ($motif !== null && (!is_string($motif) || mb_strlen($motif) > 128 || $motif !== strip_tags($motif))) {
            throw new ValidationException(['motif' => 'Le motif doit être un texte de 128 caractères maximum, sans HTML']);
        }

        return new self(
            praticienId: $praticienId,
            dateDebut: \DateTimeImmutable::createFromFormat('!' . self::FORMAT_DATE, $data['date_debut']),
            dateFin: \DateTimeImmutable::createFromFormat('!' . self::FORMAT_DATE, $data['date_fin']),
            motif: $motif === null || trim($motif) === '' ? null : trim($motif),
        );
    }
}
