<div
    x-data="notificationCenter"
    class="bg-white rounded-2xl shadow-sm p-5"
>

    <div class="flex justify-between items-center mb-5">

        <h2 class="text-lg font-semibold">
            Notification Center
        </h2>

        <span class="text-sm text-slate-500">
            <span x-text="notifications.length"></span>
        </span>
    </div>

    <div class="space-y-4">

        <template x-for="notification in notifications">

            <div class="border rounded-xl p-4">

                <div class="font-semibold">
                    <span x-text="notification.title"></span>
                </div>

                <div class="text-sm text-slate-500 mt-1">
                    <span x-text="notification.message"></span>
                </div>

            </div>

        </template>

    </div>
</div>