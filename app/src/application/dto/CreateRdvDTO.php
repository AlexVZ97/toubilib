<?php
declare(strict_types=1);

namespace toubilib\application\dto;

use Respect\Validation\Exceptions\ValidationException as RespectValidationException;
use Respect\Validation\ValidatorBuilder as v;
use toubilib\application\exceptions\ValidationException;

final class CreateRdvDTO
{
    public const FORMAT_DATE = 'Y-m-d H:i';

    private function __construct(
        public readonly string $praticienId,
        public readonly string $patientId,
        public readonly \DateTimeImmutable $dateHeureDebut,
        public readonly string $motifVisite,
    ) {}

    /** @throws ValidationException */
    public static function fromArray(array $data): self
    {
        try {
            v::key('praticien_id', v::stringType()->uuid())
                ->key('patient_id', v::stringType()->uuid())
                ->key('date_heure_debut', v::stringType()->dateTime(self::FORMAT_DATE))
                ->key('motif_visite', v::stringType()->alnum()->length(v::equals(2)))
                ->assert($data);
        } catch (RespectValidationException $e) {
            $errors = $e->getMessages();
            unset($errors['__root__']);
            throw new ValidationException($errors ?: ['body' => $e->getMessage()]);
        }

        return new self(
            praticienId: $data['praticien_id'],
            patientId: $data['patient_id'],
            dateHeureDebut: \DateTimeImmutable::createFromFormat('!' . self::FORMAT_DATE, $data['date_heure_debut']),
            motifVisite: strtoupper($data['motif_visite']),
        );
    }
}
