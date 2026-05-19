<div class="bg-white rounded-2xl shadow-sm p-6">

    <div class="flex justify-between items-center mb-5">

        <h2 class="font-semibold text-lg">
            {{ $title }}
        </h2>

        <div class="text-sm text-slate-500">
            {{ $range }}
        </div>

    </div>

    <div
        data-react-component="{{ $component }}"
        data-props='@json($props)'
        class="h-[350px]"
    ></div>
</div>