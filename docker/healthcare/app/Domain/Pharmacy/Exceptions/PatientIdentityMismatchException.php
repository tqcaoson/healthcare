<?php

namespace App\Domain\Pharmacy\Exceptions;

use RuntimeException;

class PatientIdentityMismatchException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Patient identity mismatch — barcode không khớp');
    }
}
