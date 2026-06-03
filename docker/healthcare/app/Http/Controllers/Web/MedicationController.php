<?php

namespace App\Http\Controllers\Web;

use App\Domain\Medication\Models\MedicationAdministration;
use App\Domain\Patient\Models\Patient;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MedicationController extends Controller
{
    public function index(Request $request)
    {
        $query = MedicationAdministration::with('patient.currentDemographics')
            ->latest('administered_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($days = $request->input('days', 7)) {
            $query->where('administered_at', '>=', now()->subDays($days));
        }

        $administrations = $query->paginate(20)->withQueryString();

        return view('medications.index', compact('administrations'));
    }

    public function create()
    {
        $patients = Patient::with('currentDemographics')->active()->get();
        return view('medications.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'      => 'required|exists:patients,id',
            'drug_name'       => 'required|string|max:255',
            'dose_value'      => 'required|numeric',
            'dose_unit'       => 'required|string|max:20',
            'route'           => 'required|string|max:50',
            'administered_at' => 'required|date',
        ]);

        MedicationAdministration::create([
            'uuid'            => Str::uuid(),
            'order_id'        => null,
            'patient_id'      => $request->patient_id,
            'encounter_id'    => null,
            'status'          => 'completed',
            'drug_name'       => $request->drug_name,
            'rxnorm_code'     => $request->rxnorm_code,
            'dose_value'      => $request->dose_value,
            'dose_unit'       => $request->dose_unit,
            'route'           => $request->route,
            'administered_at' => $request->administered_at,
            'administered_by' => auth()->id(),
            'is_prn'          => $request->boolean('is_prn'),
            'prn_indication'  => $request->prn_indication,
            'notes'           => $request->notes,
        ]);

        return redirect()->route('medications.index')
            ->with('success', 'Đã ghi nhận thuốc đã dùng.');
    }

    public function show($id)
    {
        $administration = MedicationAdministration::with('patient.currentDemographics')->findOrFail($id);
        return view('medications.show', compact('administration'));
    }
}
