<?php

namespace App\Http\Controllers\Web;

use App\Domain\Patient\Models\Patient;
use App\Domain\Result\Models\LabResult;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = LabResult::with('patient.currentDemographics')
            ->latest('resulted_at');

        if ($flag = $request->input('flag')) {
            if ($flag === 'critical') {
                $query->where('is_critical', true);
            } elseif ($flag === 'abnormal') {
                $query->whereNotIn('abnormal_flag', ['N', 'null']);
            }
        }
        if ($days = $request->input('days', 7)) {
            $query->where('resulted_at', '>=', now()->subDays($days));
        }

        $results = $query->paginate(20)->withQueryString();

        return view('results.index', compact('results'));
    }

    public function create()
    {
        $patients = Patient::with('currentDemographics')->active()->get();
        return view('results.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'    => 'required|exists:patients,id',
            'loinc_code'    => 'required|string|max:20',
            'loinc_display' => 'required|string|max:500',
            'value_quantity'=> 'nullable|numeric',
            'value_unit'    => 'nullable|string|max:50',
            'abnormal_flag' => 'required|in:N,L,H,LL,HH,A,AA,null',
            'resulted_at'   => 'required|date',
        ]);

        LabResult::create([
            'uuid'           => Str::uuid(),
            'patient_id'     => $request->patient_id,
            'loinc_code'     => $request->loinc_code,
            'loinc_display'  => $request->loinc_display,
            'value_quantity' => $request->value_quantity,
            'value_unit'     => $request->value_unit,
            'abnormal_flag'  => $request->abnormal_flag,
            'status'         => 'final',
            'resulted_at'    => $request->resulted_at,
            'is_critical'    => in_array($request->abnormal_flag, ['HH', 'LL', 'AA']),
        ]);

        return redirect()->route('results.index')
            ->with('success', 'Kết quả xét nghiệm đã được nhập.');
    }

    public function show($id)
    {
        $result = LabResult::with('patient.currentDemographics')->findOrFail($id);
        return view('results.show', compact('result'));
    }

    public function edit($id)
    {
        $result = LabResult::with('patient.currentDemographics')->findOrFail($id);
        return view('results.edit', compact('result'));
    }

    public function update(Request $request, $id)
    {
        $result = LabResult::findOrFail($id);

        $request->validate([
            'value_quantity' => 'nullable|numeric',
            'value_unit'     => 'nullable|string|max:50',
            'abnormal_flag'  => 'required|in:N,L,H,LL,HH,A,AA,null',
            'status'         => 'required|in:registered,preliminary,final,corrected,amended,cancelled',
        ]);

        $result->update($request->only(['value_quantity', 'value_unit', 'abnormal_flag', 'status']));

        return redirect()->route('results.show', $result->id)
            ->with('success', 'Kết quả đã được cập nhật.');
    }

    public function destroy($id)
    {
        $result = LabResult::findOrFail($id);
        $result->update(['status' => 'cancelled']);

        return redirect()->route('results.index')
            ->with('success', 'Kết quả đã được huỷ.');
    }
}
