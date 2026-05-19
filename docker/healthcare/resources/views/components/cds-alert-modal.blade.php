<div
    x-data="cdsAlert"
    x-show="open"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
>

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6">

        <div class="flex items-start gap-4">

            <div class="text-5xl">
                ⚠️
            </div>

            <div class="flex-1">

                <h2 class="text-xl font-bold text-red-700">
                    Clinical Decision Support Alert
                </h2>

                <p
                    class="mt-3 text-slate-700"
                    x-text="message"
                ></p>

                <div class="mt-6 flex gap-3">

                    <button
                        class="btn-danger"
                        @click="override"
                    >
                        Override
                    </button>

                    <button
                        class="btn-secondary"
                        @click="cancel"
                    >
                        Cancel
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>