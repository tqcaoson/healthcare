<?php

namespace App\Domain\Documents\Services;

use App\Domain\Documents\Models\ClinicalDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentStorageService
{
    public function store(
        UploadedFile $file,
        array $meta
    ): ClinicalDocument {

        $path = Storage::disk('secure')
            ->putFile(
                'clinical-documents',
                $file
            );

        return ClinicalDocument::create([
            'uuid' => (string) str()->uuid(),
            ...$meta,
            'mime_type' => $file->getMimeType(),
            'disk' => 'secure',
            'path' => $path,
            'checksum' => hash_file(
                'sha256',
                $file->getRealPath()
            ),
            'uploaded_at' => now(),
        ]);
    }
}