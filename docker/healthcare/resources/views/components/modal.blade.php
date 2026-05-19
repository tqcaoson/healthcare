<div
    x-show="open"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center"
>

    <div
        class="absolute inset-0 bg-black/50"
        @click="open = false"
    ></div>

    <div
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6"
    >

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-xl font-semibold">
                <span x-text="title"></span>
            </h2>

            <button
                class="text-slate-500"
                @click="open = false"
            >
                ✕
            </button>
        </div>

        <div>
            {{ $slot }}
        </div>
    </div>
</div>