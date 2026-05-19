<div
    x-data="wcagPanel"
    class="fixed bottom-5 left-5 z-50"
>

    <button
        class="bg-slate-900 text-white rounded-full w-14 h-14 shadow-2xl"
        @click="open = !open"
    >
        ♿
    </button>

    <div
        x-show="open"
        class="absolute bottom-20 left-0 bg-white rounded-2xl shadow-2xl p-5 w-80"
    >

        <h2 class="font-semibold text-lg mb-4">
            Accessibility
        </h2>

        <div class="space-y-4">

            <button
                class="btn-secondary w-full"
                @click="toggleContrast"
            >
                High Contrast
            </button>

            <button
                class="btn-secondary w-full"
                @click="increaseFont"
            >
                Increase Font Size
            </button>

            <button
                class="btn-secondary w-full"
                @click="reduceMotion"
            >
                Reduce Motion
            </button>

        </div>
    </div>
</div>