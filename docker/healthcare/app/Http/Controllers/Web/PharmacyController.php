<?php

namespace App\Http\Controllers\Web;

use App\Domain\Encounter\Models\Encounter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function medicationScan(Request $request)
    {
        abort_unless(
            in_array(auth()->user()->role, ['nurse', 'physician', 'superadmin']),
            403,
            'Chỉ điều dưỡng và bác sĩ có thể thực hiện phát thuốc tại giường.'
        );

        $encounterId = $request->integer('encounter_id') ?: null;

        $encounter = null;
        if ($encounterId) {
            $encounter = Encounter::with('patient.currentDemographics')
                ->where('id', $encounterId)
                ->whereIn('status', ['in-progress', 'arrived'])
                ->firstOrFail();
        }

        // Active inpatient/emergency encounters for the dropdown selector
        $activeEncounters = Encounter::with('patient.currentDemographics')
            ->whereIn('status', ['in-progress', 'arrived'])
            ->whereIn('encounter_type', ['inpatient', 'emergency'])
            ->latest('period_start')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'label' => ($e->patient->currentDemographics?->family_name . ' '
                    . $e->patient->currentDemographics?->given_name)
                    . ' — Phòng ' . ($e->bed?->room_number ?? 'N/A')
                    . ' (' . $e->status . ')',
                'patient_id' => $e->patient_id,
            ]);

        return view('pharmacy.medication-scan', compact('encounter', 'activeEncounters', 'encounterId'));
    }
}
