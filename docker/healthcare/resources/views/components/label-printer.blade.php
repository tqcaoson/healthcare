<div
    x-data="labelPrinter"
    class="bg-white rounded-2xl shadow-sm p-6"
>

    <h2 class="text-lg font-semibold mb-5">
        Specimen Labels
    </h2>

    <div class="space-y-4">

        <template x-for="label in labels">

            <div class="border rounded-xl p-4">

                <div class="font-bold text-xl">
                    <span x-text="label.patient"></span>
                </div>

                <div class="text-sm mt-2">
                    MRN:
                    <span x-text="label.mrn"></span>
                </div>

                <div class="mt-4 font-mono text-lg">
                    ||||||||||||||||||||||||
                </div>

            </div>

        </template>

    </div>

    <button
        class="btn-primary mt-5"
        @click="print"
    >
        Print Labels
    </button>
</div>