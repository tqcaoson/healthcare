<div
    x-data="pwaInstall"
    x-show="show"
    class="fixed bottom-5 right-5 bg-slate-900 text-white rounded-2xl shadow-2xl p-5 max-w-sm z-50"
>

    <div class="font-semibold text-lg">
        Install Application
    </div>

    <p class="text-sm mt-2 text-slate-300">
        Install this HIS application for offline support
    </p>

    <div class="mt-5 flex gap-3">

        <button
            class="btn-primary"
            @click="install"
        >
            Install
        </button>

        <button
            class="btn-secondary"
            @click="show = false"
        >
            Later
        </button>

    </div>
</div>