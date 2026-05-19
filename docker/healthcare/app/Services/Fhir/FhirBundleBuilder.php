<?php

namespace App\Services\Fhir;

class FhirBundleBuilder
{
    public function collection(
        array $resources
    ): array {

        return [
            'resourceType' => 'Bundle',
            'type' => 'collection',
            'total' => count($resources),
            'entry' => collect($resources)
                ->map(fn ($resource) => [
                    'resource' => $resource,
                ])
                ->values()
                ->all(),
        ];
    }
}