<?php

namespace App\Http\Controllers\Web;

use App\Domain\Patient\Models\Patient;
use App\Domain\Scheduling\Models\Appointment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('patient.currentDemographics')
            ->orderBy('start_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($date = $request->input('date')) {
            $query->whereDate('start_at', $date);
        }

        $appointments = $query->paginate(20)->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $patients = Patient::with('currentDemographics')->active()->get();
        return view('appointments.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'appointment_type' => 'nullable|string|max:100',
            'reason'           => 'nullable|string|max:500',
            'start_at'         => 'required|date',
            'end_at'           => 'required|date|after:start_at',
        ]);

        Appointment::create([
            'uuid'             => Str::uuid(),
            'patient_id'       => $request->patient_id,
            'provider_id'      => auth()->id(),
            'department_id'    => 1,
            'status'           => 'booked',
            'appointment_type' => $request->appointment_type ?? 'routine',
            'reason'           => $request->reason,
            'start_at'         => $request->start_at,
            'end_at'           => $request->end_at,
            'duration_minutes' => (int) round(
                (strtotime($request->end_at) - strtotime($request->start_at)) / 60
            ),
        ]);

        return redirect()->route('appointments.index')
            ->with('success', 'Lịch hẹn đã được đặt.');
    }

    public function show($id)
    {
        $appointment = Appointment::with('patient.currentDemographics')->findOrFail($id);
        return view('appointments.show', compact('appointment'));
    }

    public function edit($id)
    {
        $appointment = Appointment::with('patient.currentDemographics')->findOrFail($id);
        $patients    = Patient::with('currentDemographics')->active()->get();
        return view('appointments.edit', compact('appointment', 'patients'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $request->validate([
            'status'   => 'required|in:proposed,pending,booked,arrived,fulfilled,cancelled,noshow,entered-in-error,checked-in,waitlist',
            'start_at' => 'required|date',
            'end_at'   => 'required|date|after:start_at',
        ]);

        $data = $request->only(['status', 'start_at', 'end_at', 'reason', 'appointment_type']);
        if ($request->status === 'cancelled') {
            $data['cancelled_at']  = now();
            $data['cancel_reason'] = $request->cancel_reason;
        }

        $appointment->update($data);

        return redirect()->route('appointments.show', $appointment->id)
            ->with('success', 'Lịch hẹn đã được cập nhật.');
    }

    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'cancelled', 'cancelled_at' => now()]);

        return redirect()->route('appointments.index')
            ->with('success', 'Lịch hẹn đã được huỷ.');
    }
}
