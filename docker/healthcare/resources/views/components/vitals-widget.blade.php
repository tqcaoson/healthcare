<div class="bg-white rounded-2xl shadow-sm p-5">

    <h2 class="text-lg font-semibold mb-4">
        Latest Vitals
    </h2>

    <div class="space-y-3">

        <div class="vital-row">
            <span>Blood Pressure</span>

            <strong>
                {{ $vitals->systolic_bp }}/{{ $vitals->diastolic_bp }}
            </strong>
        </div>

        <div class="vital-row">
            <span>Heart Rate</span>

            <strong>
                {{ $vitals->heart_rate }} bpm
            </strong>
        </div>

        <div class="vital-row">
            <span>Temperature</span>

            <strong>
                {{ $vitals->temperature }} °C
            </strong>
        </div>

        <div class="vital-row">
            <span>SpO2</span>

            <strong>
                {{ $vitals->spo2 }} %
            </strong>
        </div>
    </div>
</div>