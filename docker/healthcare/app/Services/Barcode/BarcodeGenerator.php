<?php

namespace App\Services\Barcode;

class BarcodeGenerator
{
    public function patient(
        string $mrn
    ): string {

        return base64_encode($mrn);
    }

    public function specimen(
        string $accession
    ): string {

        return base64_encode($accession);
    }
}