<?php

namespace App\Http\Controllers\Web;

use App\Domain\Encounter\Models\Encounter;
use App\Domain\Order\Models\Order;
use App\Domain\Patient\Models\Patient;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['patient.currentDemographics', 'encounter'])
            ->latest('ordered_at');

        if ($type = $request->input('order_type')) {
            $query->where('order_type', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $patients   = Patient::with('currentDemographics')->active()->get();
        $encounters = Encounter::with('patient.currentDemographics')
            ->where('status', 'in-progress')->get();

        return view('orders.place-order', compact('patients', 'encounters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'encounter_id'       => 'required|exists:encounters,id',
            'order_type'         => 'required|in:lab,radiology,medication,nursing,diet,referral,procedure',
            'priority'           => 'required|in:routine,urgent,stat,asap',
            'clinical_indication'=> 'nullable|string|max:500',
        ]);

        $encounter = Encounter::findOrFail($request->encounter_id);

        Order::create([
            'uuid'                => Str::uuid(),
            'order_number'        => 'ORD-' . strtoupper(Str::random(8)),
            'encounter_id'        => $encounter->id,
            'patient_id'          => $encounter->patient_id,
            'order_type'          => $request->order_type,
            'status'              => 'pending',
            'priority'            => $request->priority,
            'ordered_by'          => auth()->id(),
            'ordered_at'          => now(),
            'clinical_indication' => $request->clinical_indication,
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Lệnh đã được đặt thành công.');
    }

    public function show($id)
    {
        $order = Order::with(['patient.currentDemographics', 'encounter'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    public function edit($id)
    {
        $order = Order::with(['patient.currentDemographics', 'encounter'])->findOrFail($id);
        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status'   => 'required|in:draft,pending,active,on-hold,completed,cancelled,stopped,entered-in-error',
            'priority' => 'required|in:routine,urgent,stat,asap',
        ]);

        $order->update($request->only(['status', 'priority', 'clinical_indication']));

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Lệnh đã được cập nhật.');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
            'cancel_reason'=> 'Huỷ từ giao diện',
        ]);

        return redirect()->route('orders.index')
            ->with('success', 'Lệnh đã được huỷ.');
    }
}
