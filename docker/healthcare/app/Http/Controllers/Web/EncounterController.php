<?php

namespace App\Http\Controllers\Web;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Patient\Models\Patient;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EncounterController extends Controller
{
    public function index(Request $request)
    {
        $query = Encounter::with(['patient.currentDemographics'])
            ->latest('period_start');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($type = $request->input('encounter_type')) {
            $query->where('encounter_type', $type);
        }

        $encounters = $query->paginate(20)->withQueryString();

        return view('encounters.index', compact('encounters'));
    }

    public function create()
    {
        $patients = Patient::with('currentDemographics')->active()->get();
        return view('encounters.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'     => 'required|exists:patients,id',
            'encounter_type' => 'required|in:inpatient,outpatient,emergency,telehealth',
            'class'          => 'required|in:IMP,AMB,EMER,HH',
            'reason_text'    => 'nullable|string|max:1000',
            'period_start'   => 'required|date',
        ]);

        $encounter = Encounter::create([
            'uuid'           => Str::uuid(),
            'patient_id'     => $request->patient_id,
            'encounter_type' => $request->encounter_type,
            'status'         => 'in-progress',
            'class'          => $request->class,
            'reason_text'    => $request->reason_text,
            'period_start'   => $request->period_start,
            'attending_provider_id' => auth()->id(),
            'department_id'  => 1,
        ]);

        return redirect()->route('encounters.show', $encounter->id)
            ->with('success', 'Lần khám đã được tạo.');
    }

    public function show($id)
    {
        $encounter = Encounter::with([
            'patient.currentDemographics',
            'diagnoses',
        ])->findOrFail($id);

        return view('encounters.show', compact('encounter'));
    }

    public function edit($id)
    {
        $encounter = Encounter::with('patient.currentDemographics')->findOrFail($id);
        return view('encounters.edit', compact('encounter'));
    }

    public function update(Request $request, $id)
    {
        $encounter = Encounter::findOrFail($id);

        $request->validate([
            'status'      => 'required|in:planned,arrived,triaged,in-progress,onleave,finished,cancelled',
            'reason_text' => 'nullable|string|max:1000',
            'period_end'  => 'nullable|date',
        ]);

        $encounter->update($request->only(['status', 'reason_text', 'period_end']));

        return redirect()->route('encounters.show', $encounter->id)
            ->with('success', 'Lần khám đã được cập nhật.');
    }

    public function destroy($id)
    {
        $encounter = Encounter::findOrFail($id);
        $encounter->update(['status' => 'cancelled']);

        return redirect()->route('encounters.index')
            ->with('success', 'Lần khám đã được hủy.');
    }
}
