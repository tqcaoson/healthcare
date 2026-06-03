<?php

namespace App\Domain\Clinical\Services;

use App\Domain\Clinical\Models\Allergy;
use App\Domain\Order\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CdsAlertService
{
    public function checkOrder(Order $order): Collection
    {
        $alerts = collect();

        if ($order->order_type === 'medication') {
            $alerts = $alerts->merge($this->checkDrugInteractions($order));
            $alerts = $alerts->merge($this->checkAllergyConflicts($order));
        }

        if ($alerts->isNotEmpty()) {
            Log::channel('audit')->info('cds_alerts_fired', [
                'order_id'   => $order->id,
                'patient_id' => $order->patient_id,
                'alert_count' => $alerts->count(),
                'severities'  => $alerts->pluck('severity')->unique()->values(),
            ]);
        }

        return $alerts->sortByDesc('severity');
    }

    protected function checkDrugInteractions(Order $order): Collection
    {
        $alerts = collect();

        if (! $order->rxnorm_code) {
            return $alerts;
        }

        // Load active medication orders for the same patient/encounter
        $activeDrugs = Order::query()
            ->where('patient_id', $order->patient_id)
            ->where('order_type', 'medication')
            ->whereIn('status', ['pending', 'active'])
            ->where('id', '!=', $order->id)
            ->whereNotNull('rxnorm_code')
            ->pluck('rxnorm_code')
            ->all();

        // Known high-severity interaction pairs (RxNorm codes).
        // In production this table should be populated from a drug-interaction
        // knowledge base (e.g. First Databank, Multum).
        $interactionPairs = config('health.drug_interaction_pairs', []);

        foreach ($interactionPairs as $pair) {
            if (
                in_array($order->rxnorm_code, $pair, true) &&
                count(array_intersect($activeDrugs, $pair)) > 0
            ) {
                $alerts->push([
                    'type'     => 'drug_interaction',
                    'severity' => 'high',
                    'message'  => "Potential drug interaction between {$order->rxnorm_code} and active medication.",
                    'codes'    => $pair,
                ]);
            }
        }

        return $alerts;
    }

    protected function checkAllergyConflicts(Order $order): Collection
    {
        $alerts = collect();

        if (! $order->rxnorm_code && ! $order->drug_name) {
            return $alerts;
        }

        $allergies = Allergy::query()
            ->where('patient_id', $order->patient_id)
            ->whereIn('clinical_status', ['active', 'confirmed'])
            ->get();

        foreach ($allergies as $allergy) {
            $substanceLower  = strtolower((string) $allergy->substance_display);
            $drugNameLower   = strtolower((string) $order->drug_name);
            $rxnorm          = (string) $order->rxnorm_code;

            $nameMatch   = $drugNameLower && str_contains($substanceLower, $drugNameLower);
            $rxnormMatch = $rxnorm && $allergy->rxnorm_code === $rxnorm;

            if ($nameMatch || $rxnormMatch) {
                $alerts->push([
                    'type'        => 'allergy_conflict',
                    'severity'    => $allergy->severity ?? 'high',
                    'message'     => "Patient has documented allergy to {$allergy->substance_display}.",
                    'allergy_id'  => $allergy->id,
                    'reaction'    => $allergy->manifestation_display,
                ]);
            }
        }

        return $alerts;
    }
}