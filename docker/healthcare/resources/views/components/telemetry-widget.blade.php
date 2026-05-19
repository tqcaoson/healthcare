<div
    x-data="telemetryWidget"
    class="bg-white rounded-2xl shadow-sm p-5"
>

    <div class="flex justify-between items-center mb-5">

        <h2 class="font-semibold text-lg">
            System Telemetry
        </h2>

        <div class="text-sm text-green-600">
            LIVE
        </div>
    </div>

    <div class="space-y-4">

        <div class="flex justify-between">

            <span>CPU</span>

            <span x-text="cpu + '%'"></span>

        </div>

        <div class="flex justify-between">

            <span>Memory</span>

            <span x-text="memory + '%'"></span>

        </div>

        <div class="flex justify-between">

            <span>WebSocket</span>

            <span x-text="socket"></span>

        </div>

        <div class="flex justify-between">

            <span>Queue Workers</span>

            <span x-text="workers"></span>

        </div>

    </div>
</div>