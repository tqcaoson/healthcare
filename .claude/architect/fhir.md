# FHIR R4 — Resource Mapping

Mọi FHIR response phải tuân theo cấu trúc chuẩn. Controller không build JSON thủ công — dùng `FhirMapper`.

## Domain Model → FHIR Resource

```php
class FhirMapper
{
    // Patient → FHIR Patient resource
    public function patientToFhir(Patient $patient): array
    {
        $demo = $patient->currentDemographics;

        return [
            'resourceType' => 'Patient',
            'id'           => $patient->uuid,
            'meta' => [
                'versionId'   => (string) $patient->getVersion(),
                'lastUpdated' => $patient->updated_at->toIso8601String(),
            ],
            'identifier' => $patient->identifiers->map(fn($i) => [
                'system' => $i->system,
                'value'  => $i->value,
            ])->toArray(),
            'active' => $patient->status === 'active',
            'name' => [[
                'use'    => 'official',
                'family' => $demo->family_name,
                'given'  => [$demo->given_name],
            ]],
            'gender'    => $demo->gender,
            'birthDate' => $demo->birth_date->toDateString(),
            'telecom'   => array_filter([
                $demo->phone ? ['system' => 'phone', 'value' => $demo->phone] : null,
                $demo->email ? ['system' => 'email', 'value' => $demo->email] : null,
            ]),
            'address' => $demo->address_line ? [[
                'line'       => [$demo->address_line],
                'city'       => $demo->city,
                'postalCode' => $demo->postal_code,
            ]] : [],
            'communication' => [[
                'language' => [
                    'coding' => [['system' => 'urn:ietf:bcp:47', 'code' => $demo->language]],
                ],
            ]],
            'deceasedDateTime' => $patient->deceased_at?->toIso8601String(),
        ];
    }

    // VitalSign → FHIR Observation (Bundle of Observations)
    public function vitalSignToFhir(VitalSign $vs): array
    {
        $components = [];

        $map = [
            'systolic_bp'      => ['8480-6', 'Systolic blood pressure',    'mm[Hg]'],
            'diastolic_bp'     => ['8462-4', 'Diastolic blood pressure',   'mm[Hg]'],
            'heart_rate'       => ['8867-4', 'Heart rate',                  '/min'],
            'respiratory_rate' => ['9279-1', 'Respiratory rate',            '/min'],
            'temperature'      => ['8310-5', 'Body temperature',            'Cel'],
            'spo2'             => ['59408-5','Oxygen saturation',           '%'],
            'weight_kg'        => ['29463-7','Body weight',                 'kg'],
            'height_cm'        => ['8302-2', 'Body height',                 'cm'],
            'bmi'              => ['39156-5','Body mass index',             'kg/m2'],
            'pain_score'       => ['72514-3','Pain severity',               '{score}'],
        ];

        foreach ($map as $field => [$loinc, $display, $unit]) {
            if ($vs->$field !== null) {
                $components[] = [
                    'code' => [
                        'coding' => [['system' => 'http://loinc.org', 'code' => $loinc, 'display' => $display]],
                    ],
                    'valueQuantity' => [
                        'value'  => (float) $vs->$field,
                        'unit'   => $unit,
                        'system' => 'http://unitsofmeasure.org',
                        'code'   => $unit,
                    ],
                ];
            }
        }

        return [
            'resourceType' => 'Observation',
            'id'           => (string) $vs->id,
            'status'       => 'final',
            'category'     => [[
                'coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                              'code'   => 'vital-signs']],
            ]],
            'code' => [
                'coding' => [['system' => 'http://loinc.org', 'code' => '85353-1', 'display' => 'Vital signs panel']],
            ],
            'subject'          => ['reference' => "Patient/{$vs->patient->uuid}"],
            'encounter'        => ['reference' => "Encounter/{$vs->encounter->uuid}"],
            'effectiveDateTime' => $vs->recorded_at->toIso8601String(),
            'performer'        => [['reference' => "Practitioner/{$vs->recordedBy->id}"]],
            'component'        => $components,
        ];
    }

    // LabResult → FHIR Observation
    public function labResultToFhir(LabResult $result): array
    {
        $interpretation = match($result->abnormal_flag) {
            'H'  => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'H']]]],
            'L'  => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'L']]]],
            'HH' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'HH']]]],
            'LL' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation', 'code' => 'LL']]]],
            default => [],
        };

        $value = [];
        if ($result->value_quantity !== null) {
            $value = ['valueQuantity' => ['value' => (float) $result->value_quantity, 'unit' => $result->value_unit, 'system' => 'http://unitsofmeasure.org']];
        } elseif ($result->value_string !== null) {
            $value = ['valueString' => $result->value_string];
        } elseif ($result->value_coded !== null) {
            $value = ['valueCodeableConcept' => ['coding' => [['system' => 'http://snomed.info/sct', 'code' => $result->value_coded, 'display' => $result->value_coded_display]]]];
        }

        return array_merge([
            'resourceType' => 'Observation',
            'id'           => $result->uuid,
            'status'       => $result->status,
            'category'     => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/observation-category', 'code' => 'laboratory']]]],
            'code'         => ['coding' => [['system' => 'http://loinc.org', 'code' => $result->loinc_code, 'display' => $result->loinc_display]]],
            'subject'           => ['reference' => "Patient/{$result->patient->uuid}"],
            'effectiveDateTime' => $result->resulted_at->toIso8601String(),
            'issued'            => $result->resulted_at->toIso8601String(),
            'interpretation'    => $interpretation,
            'referenceRange'    => $result->reference_low || $result->reference_high ? [[
                'low'  => $result->reference_low  ? ['value' => (float) $result->reference_low,  'unit' => $result->value_unit] : null,
                'high' => $result->reference_high ? ['value' => (float) $result->reference_high, 'unit' => $result->value_unit] : null,
                'text' => $result->reference_text,
            ]] : [],
        ], $value);
    }

    // MedicationOrder → FHIR MedicationRequest
    public function medicationOrderToFhir(MedicationOrder $mo): array
    {
        return [
            'resourceType'   => 'MedicationRequest',
            'id'             => $mo->order->uuid,
            'status'         => $this->mapOrderStatusToFhir($mo->order->status),
            'intent'         => 'order',
            'medicationCodeableConcept' => [
                'coding' => array_filter([
                    $mo->rxnorm_code ? ['system' => 'http://www.nlm.nih.gov/research/umls/rxnorm', 'code' => $mo->rxnorm_code, 'display' => $mo->drug_name] : null,
                ]),
                'text' => $mo->drug_name,
            ],
            'subject'        => ['reference' => "Patient/{$mo->order->patient->uuid}"],
            'encounter'      => ['reference' => "Encounter/{$mo->order->encounter->uuid}"],
            'authoredOn'     => $mo->order->ordered_at->toIso8601String(),
            'requester'      => ['reference' => "Practitioner/{$mo->order->orderedBy->id}"],
            'dosageInstruction' => [[
                'text'      => $mo->instructions,
                'route'     => ['text' => $mo->route],
                'timing'    => ['code' => ['text' => $mo->frequency]],
                'doseAndRate' => [['doseQuantity' => ['value' => (float) $mo->dose_value, 'unit' => $mo->dose_unit]]],
            ]],
            'substitution' => ['allowedBoolean' => $mo->substitution_allowed],
        ];
    }

    // Condition → FHIR Condition
    public function conditionToFhir(Condition $condition): array
    {
        return [
            'resourceType' => 'Condition',
            'id'           => $condition->uuid,
            'clinicalStatus' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical', 'code' => $condition->clinical_status]]],
            'verificationStatus' => ['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-ver-status', 'code' => $condition->verification]]],
            'category' => [['coding' => [['system' => 'http://terminology.hl7.org/CodeSystem/condition-category', 'code' => $condition->category]]]],
            'severity' => $condition->severity ? [
                'coding' => [['system' => 'http://snomed.info/sct', 'code' => match($condition->severity) {
                    'mild'     => '255604002',
                    'moderate' => '6736007',
                    'severe'   => '24484000',
                }]],
            ] : null,
            'code' => [
                'coding' => array_filter([
                    $condition->icd10_code ? ['system' => 'http://hl7.org/fhir/sid/icd-10-cm', 'code' => $condition->icd10_code, 'display' => $condition->icd10_display] : null,
                    $condition->snomed_code ? ['system' => 'http://snomed.info/sct', 'code' => $condition->snomed_code, 'display' => $condition->snomed_display] : null,
                ]),
                'text' => $condition->free_text ?? $condition->icd10_display,
            ],
            'subject'           => ['reference' => "Patient/{$condition->patient->uuid}"],
            'onsetDateTime'     => $condition->onset_date?->toDateString(),
            'abatementDateTime' => $condition->abatement_date?->toDateString(),
            'recordedDate'      => $condition->recorded_at->toIso8601String(),
        ];
    }
}
```

## FHIR Bundle — $everything operation

```php
class PatientFhirController extends Controller
{
    public function everything(string $id): JsonResponse
    {
        $patient = Patient::where('uuid', $id)->firstOrFail();
        $this->authorize('view', $patient);

        $mapper  = app(FhirMapper::class);
        $entries = [];

        $entries[] = ['resource' => $mapper->patientToFhir($patient)];

        // Encounters (last 12 months)
        $patient->encounters()
            ->where('period_start', '>=', now()->subYear())
            ->with(['diagnoses', 'provider'])
            ->get()
            ->each(fn($e) => $entries[] = ['resource' => $mapper->encounterToFhir($e)]);

        // Conditions (active)
        $patient->conditions()->where('clinical_status', 'active')->get()
            ->each(fn($c) => $entries[] = ['resource' => $mapper->conditionToFhir($c)]);

        // Allergies
        $patient->allergies()->get()
            ->each(fn($a) => $entries[] = ['resource' => $mapper->allergyToFhir($a)]);

        // Vital signs (last 30 days)
        VitalSign::where('patient_id', $patient->id)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderByDesc('recorded_at')->get()
            ->each(fn($v) => $entries[] = ['resource' => $mapper->vitalSignToFhir($v)]);

        // Lab results (last 90 days)
        LabResult::where('patient_id', $patient->id)
            ->where('resulted_at', '>=', now()->subDays(90))
            ->orderByDesc('resulted_at')->get()
            ->each(fn($r) => $entries[] = ['resource' => $mapper->labResultToFhir($r)]);

        // Medications (active)
        MedicationOrder::whereHas('order', fn($q) => $q->where('patient_id', $patient->id)->where('status', 'active'))
            ->with('order')->get()
            ->each(fn($m) => $entries[] = ['resource' => $mapper->medicationOrderToFhir($m)]);

        return response()->json([
            'resourceType' => 'Bundle',
            'type'         => 'searchset',
            'total'        => count($entries),
            'timestamp'    => now()->toIso8601String(),
            'entry'        => $entries,
        ]);
    }
}
```

## Routes FHIR R4

```php
// routes/fhir.php — /fhir/R4/*
Route::middleware(['auth:sanctum', 'audit.phi'])
    ->prefix('fhir/R4')
    ->group(function () {
        Route::get('metadata', [CapabilityController::class, 'show']);

        Route::get('Patient', [PatientFhirController::class, 'search']);
        Route::get('Patient/{id}', [PatientFhirController::class, 'read']);
        Route::post('Patient', [PatientFhirController::class, 'create']);
        Route::put('Patient/{id}', [PatientFhirController::class, 'update']);

        Route::get('Observation', [ObservationFhirController::class, 'search']);
        Route::get('Observation/{id}', [ObservationFhirController::class, 'read']);
        Route::get('MedicationRequest', [MedicationRequestFhirController::class, 'search']);
        Route::get('Condition', [ConditionFhirController::class, 'search']);
        Route::get('Encounter', [EncounterFhirController::class, 'search']);
        Route::get('AllergyIntolerance', [AllergyFhirController::class, 'search']);
        Route::get('DiagnosticReport', [DiagnosticReportFhirController::class, 'search']);

        Route::post('Patient/{id}/\$everything', [PatientFhirController::class, 'everything']);
        Route::post('/', [BundleFhirController::class, 'process']);
    });
```
