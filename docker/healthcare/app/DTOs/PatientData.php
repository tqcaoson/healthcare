<?php

namespace App\DTOs;

class PatientData
{
    public function __construct(
        public readonly string $mrn,
        public readonly string $givenName,
        public readonly string $familyName,
        public readonly string $gender,
        public readonly string $birthDate,
    ) {
    }

    public static function fromArray(
        array $data
    ): self {

        return new self(
            mrn: $data['mrn'],
            givenName: $data['given_name'],
            familyName: $data['family_name'],
            gender: $data['gender'],
            birthDate: $data['birth_date'],
        );
    }
}