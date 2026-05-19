<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">

    <div class="flex items-start justify-between">

        <div class="flex gap-5">

            <div
                class="w-20 h-20 rounded-2xl bg-slate-200 flex items-center justify-center text-2xl font-bold"
            >
                {{ substr($patient->currentDemographics->family_name, 0, 1) }}
            </div>

            <div>

                <h1 class="text-2xl font-bold">
                    {{ $patient->currentDemographics->family_name }}
                    {{ $patient->currentDemographics->given_name }}
                </h1>

                <div class="mt-2 flex flex-wrap gap-4 text-sm text-slate-600">

                    <div>
                        MRN:
                        <span class="font-semibold">
                            {{ $patient->mrn }}
                        </span>
                    </div>

                    <div>
                        DOB:
                        {{ $patient->currentDemographics->birth_date }}
                    </div>

                    <div>
                        Gender:
                        {{ $patient->currentDemographics->gender }}
                    </div>

                    <div>
                        Blood:
                        O+
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">

                    @foreach($patient->conditions as $condition)

                        <span
                            class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs"
                        >
                            {{ $condition->free_text }}
                        </span>

                    @endforeach

                </div>
            </div>
        </div>

        <div class="space-y-2 text-right">

            @foreach($patient->allergies as $allergy)

                <div class="bg-red-100 text-red-700 px-3 py-2 rounded-xl text-sm">
                    ⚠ {{ $allergy->substance_name }}
                </div>

            @endforeach

        </div>
    </div>
</div>