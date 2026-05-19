<div
    x-data="toastStore"
    class="fixed top-5 right-5 z-[999] space-y-3"
>

    <template x-for="toast in toasts">

        <div
            class="rounded-2xl px-5 py-4 shadow-xl text-white min-w-[320px]"
            :class="{
                'bg-green-600': toast.type === 'success',
                'bg-red-600': toast.type === 'error',
                'bg-blue-600': toast.type === 'info',
                'bg-amber-500': toast.type === 'warning',
            }"
        >

            <div class="font-semibold">
                <span x-text="toast.title"></span>
            </div>

            <div class="text-sm mt-1 opacity-90">
                <span x-text="toast.message"></span>
            </div>

        </div>

    </template>
</div>