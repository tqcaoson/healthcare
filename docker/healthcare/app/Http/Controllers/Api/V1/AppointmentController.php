<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Scheduling\Models\Appointment;
use App\Domain\Scheduling\Services\AppointmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(protected AppointmentService $appointmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $appointments = Appointment::query()
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('provider_id'), fn ($q) => $q->where('provider_id', $request->provider_id))
            ->orderBy('start_at')
            ->paginate(20);

        return response()->json(['data' => $appointments]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'provider_id' => 'required|exists:users,id',
            'department_id' => 'required|integer',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'reason' => 'nullable|string',
            'appointment_type' => 'nullable|string',
        ]);

        $appointment = $this->appointmentService->book($validated);

        return response()->json(['data' => $appointment], 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        return response()->json(['data' => $appointment]);
    }

    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:proposed,pending,booked,arrived,fulfilled,cancelled,noshow',
            'start_at' => 'sometimes|date',
            'end_at' => 'sometimes|date',
        ]);

        $appointment->update($validated);

        return response()->json(['data' => $appointment->fresh()]);
    }

    public function destroy(Appointment $appointment): JsonResponse
    {
        $this->appointmentService->cancel($appointment, 'Cancelled via API');

        return response()->json(['message' => 'Appointment cancelled.']);
    }
}
