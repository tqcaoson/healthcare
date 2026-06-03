<div
    x-data="sessionTimeout"
    x-cloak
    x-show="show"
    x-trap.noscroll="show"
    role="dialog"
    aria-modal="true"
    aria-labelledby="session-timeout-title"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
>

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

        <h2 id="session-timeout-title" class="text-xl font-semibold mb-4">
            Phiên sắp hết hạn
        </h2>

        <p class="text-slate-600">
            Phiên làm việc của bạn sẽ hết hạn sau
            <span class="font-bold text-red-600" x-text="countdown"></span>
            giây do không hoạt động.
        </p>

        <div class="mt-6 flex gap-3">

            <button
                class="btn-primary"
                @click="extend"
            >
                Continue Session
            </button>

            <button
                class="btn-danger"
                @click="logout"
            >
                Logout
            </button>

        </div>
    </div>
</div>