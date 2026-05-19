<div
    x-data="notificationInbox"
    class="bg-white rounded-2xl shadow-sm p-5"
>

    <div class="flex justify-between items-center mb-4">

        <h2 class="text-lg font-semibold">
            Notifications
        </h2>

        <span class="text-sm text-slate-500">
            <span x-text="notifications.length"></span>
        </span>
    </div>

    <div class="space-y-3">

        <template x-for="notification in notifications">

            <div
                class="border rounded-xl p-3"
            >

                <div class="font-semibold">
                    <span x-text="notification.title"></span>
                </div>

                <div class="text-sm text-slate-500">
                    <span x-text="notification.message"></span>
                </div>
            </div>

        </template>

    </div>
</div>