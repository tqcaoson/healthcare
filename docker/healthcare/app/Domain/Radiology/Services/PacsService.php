<?php

namespace App\Domain\Radiology\Services;

use App\Domain\Radiology\Models\RadiologyStudy;

class PacsService
{
    public function generateViewerUrl(
        RadiologyStudy $study
    ): string {

        return sprintf(
            '%s/studies/%s',
            config('services.pacs.viewer_url'),
            $study->dicom_study_uid
        );
    }

    public function pushStudy(
        RadiologyStudy $study
    ): bool {

        return true;
    }
}