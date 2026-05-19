<div
    x-data="sessionTimeout"
    x-show="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
>

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">

        <h2 class="text-xl font-semibold mb-4">
            Session Expiring
        </h2>

        <p class="text-slate-600">
            Your session will expire soon due to inactivity.
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