<?php

namespace App\Domain\Pharmacy\Exceptions;

use RuntimeException;

class MedicationBarcodeMismatchException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Medication barcode không khớp với order — kiểm tra lại thuốc');
    }
}
