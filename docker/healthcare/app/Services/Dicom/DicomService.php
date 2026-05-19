<?php

namespace App\Services\Dicom;

class DicomService
{
    public function validate(
        string $path
    ): bool {

        return str_ends_with(
            strtolower($path),
            '.dcm'
        );
    }

    public function metadata(
        string $path
    ): array {

        return [
            'study_uid' => null,
            'series_uid' => null,
            'modality' => null,
        ];
    }
}