<div
    x-show="open"
    x-transition
    x-trap.noscroll="open"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="$id('modal-title')"
    class="fixed inset-0 z-50 flex items-center justify-center"
    @keydown.escape.window="open = false"
>

    {{-- Backdrop: only close if the dialog doesn't contain unsaved form data --}}
    <div
        class="absolute inset-0 bg-black/50"
        @click="if (typeof confirmClose === 'undefined' || confirmClose()) open = false"
        aria-hidden="true"
    ></div>

    <div
        class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6"
    >

        <div class="flex justify-between items-center mb-6">

            <h2 :id="$id('modal-title')" class="text-xl font-semibold">
                <span x-text="title"></span>
            </h2>

            <button
                type="button"
                class="text-slate-500 hover:text-slate-700"
                aria-label="Đóng hộp thoại"
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