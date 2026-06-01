<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Order\Models\Order;
use App\Domain\Order\Services\CpoeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected CpoeService $cpoeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->when($request->filled('encounter_id'), fn ($q) => $q->where('encounter_id', $request->encounter_id))
            ->when($request->filled('patient_id'), fn ($q) => $q->where('patient_id', $request->patient_id))
            ->paginate(20);

        return response()->json(['data' => $orders]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'encounter_id' => 'required|exists:encounters,id',
            'patient_id' => 'required|exists:patients,id',
            'order_type' => 'required|in:lab,radiology,medication,nursing,diet,referral,procedure',
            'priority' => 'sometimes|in:routine,urgent,stat,asap',
            'clinical_indication' => 'nullable|string',
        ]);

        $order = Order::create([
            'uuid' => (string) str()->uuid(),
            'order_number' => 'ORD-' . strtoupper(str()->random(8)),
            'ordered_by' => $request->user()->id,
            'ordered_at' => now(),
            'status' => 'pending',
            ...$validated,
        ]);

        return response()->json(['data' => $order], 201);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json(['data' => $order]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'priority' => 'sometimes|in:routine,urgent,stat,asap',
        ]);

        $order->update($validated);

        return response()->json(['data' => $order->fresh()]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => request()->user()->id,
            'cancel_reason' => 'Cancelled via API',
        ]);

        return response()->json(['message' => 'Order cancelled.']);
    }
}
