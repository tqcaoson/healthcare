<?php

namespace App\Http\Controllers\Web;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Medication\Models\MedicationAdministration;
use App\Domain\Order\Models\Order;
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
        $patients   = Patient::with('currentDemographics')->active()->get();
        $encounters = Encounter::with('patient.currentDemographics')
            ->whereIn('status', ['in-progress', 'arrived'])
            ->get();
        $orders = Order::with('patient.currentDemographics')
            ->where('order_type', 'medication')
            ->whereIn('status', ['pending', 'active'])
            ->get();

        $encountersJson = $encounters->map(fn($e) => [
            'id'         => $e->id,
            'patient_id' => (string) $e->patient_id,
            'label'      => '#'.$e->id.' — '.($e->patient->currentDemographics?->family_name).' '.($e->patient->currentDemographics?->given_name).' ('.$e->status.')',
        ]);

        $ordersJson = $orders->map(fn($o) => [
            'id'         => $o->id,
            'patient_id' => (string) $o->patient_id,
            'label'      => $o->order_number.' — '.($o->clinical_indication ?? 'Thuốc').' ('.$o->status.')',
        ]);

        return view('medications.create', compact('patients', 'encountersJson', 'ordersJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'      => 'required|exists:patients,id',
            'order_id'        => 'required|exists:orders,id',
            'encounter_id'    => 'required|exists:encounters,id',
            'drug_name'       => 'required|string|max:255',
            'dose_value'      => 'required|numeric|min:0.001',
            'dose_unit'       => 'required|string|max:20',
            'route'           => 'required|string|max:50',
            'administered_at' => 'required|date|before_or_equal:now',
        ]);

        // Verify the order belongs to the stated patient to prevent cross-patient linking
        \App\Domain\Order\Models\Order::where('id', $request->order_id)
            ->where('patient_id', $request->patient_id)
            ->where('order_type', 'medication')
            ->whereIn('status', ['pending', 'active'])
            ->firstOrFail();

        MedicationAdministration::create([
            'uuid'            => Str::uuid(),
            'order_id'        => $request->order_id,
            'patient_id'      => $request->patient_id,
            'encounter_id'    => $request->encounter_id,
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
