<?php

namespace App\Http\Controllers\Web;

use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Models\PatientDemographic;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Patient::class);

        $query = Patient::with('currentDemographics')->active();

        if ($search = $request->input('search')) {
            $query->whereHas('currentDemographics', function ($q) use ($search) {
                $q->where('given_name', 'like', "%$search%")
                  ->orWhere('family_name', 'like', "%$search%");
            })->orWhere('mrn', 'like', "%$search%");
        }

        $patients = $query->latest()->paginate(20)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        $this->authorize('create', Patient::class);

        return view('patients.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);
        $request->validate([
            'given_name'  => 'required|string|max:100',
            'family_name' => 'required|string|max:100',
            'birth_date'  => 'required|date',
            'gender'      => 'required|in:male,female,other,unknown',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
        ]);

        $patient = Patient::create([
            'uuid'   => Str::uuid(),
            'mrn'    => 'MRN-' . strtoupper(Str::random(8)),
            'status' => 'active',
        ]);

        PatientDemographic::create([
            'patient_id'  => $patient->id,
            'given_name'  => $request->given_name,
            'family_name' => $request->family_name,
            'birth_date'  => $request->birth_date,
            'gender'      => $request->gender,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'is_current'  => true,
            'changed_by'  => auth()->id(),
            'changed_at'  => now(),
        ]);

        return redirect()->route('patients.show', $patient->id)
            ->with('success', 'Bệnh nhân đã được tạo thành công.');
    }

    public function show($id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('view', $patient);

        $patient->load([
            'currentDemographics',
            'allergies',
            'conditions',
            'encounters' => fn($q) => $q->latest('period_start')->limit(10),
        ]);

        return view('patients.show', compact('patient'));
    }

    public function edit($id)
    {
        $patient = Patient::with('currentDemographics')->findOrFail($id);
        $this->authorize('update', $patient);

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('update', $patient);

        $request->validate([
            'given_name'  => 'required|string|max:100',
            'family_name' => 'required|string|max:100',
            'birth_date'  => 'required|date',
            'gender'      => 'required|in:male,female,other,unknown',
            'phone'       => 'nullable|string|max:20',
            'email'       => 'nullable|email|max:255',
        ]);

        // Mark old demographics as not current
        PatientDemographic::where('patient_id', $patient->id)
            ->where('is_current', true)
            ->update(['is_current' => false]);

        PatientDemographic::create([
            'patient_id'  => $patient->id,
            'given_name'  => $request->given_name,
            'family_name' => $request->family_name,
            'birth_date'  => $request->birth_date,
            'gender'      => $request->gender,
            'phone'       => $request->phone,
            'email'       => $request->email,
            'is_current'  => true,
            'changed_by'  => auth()->id(),
            'changed_at'  => now(),
        ]);

        return redirect()->route('patients.show', $patient->id)
            ->with('success', 'Thông tin bệnh nhân đã được cập nhật.');
    }

    public function destroy($id)
    {
        $patient = Patient::findOrFail($id);
        $this->authorize('delete', $patient);
        $patient->update(['status' => 'inactive']);
        $patient->delete();

        return redirect()->route('patients.index')
            ->with('success', 'Bệnh nhân đã được vô hiệu hóa.');
    }
}
