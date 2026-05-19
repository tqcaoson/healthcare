<div
    x-data="errorBoundary"
    x-show="hasError"
    class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center"
>

    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-lg">

        <div class="text-6xl mb-5">
            ⚠️
        </div>

        <h2 class="text-2xl font-bold">
            Frontend Failure
        </h2>

        <p class="text-slate-500 mt-3">
            Unexpected UI runtime error occurred
        </p>

        <button
            class="btn-primary mt-6"
            @click="reload"
        >
            Reload Application
        </button>

    </div>
</div>