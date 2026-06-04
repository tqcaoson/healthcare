<div
    x-data="{ sidebarOpen: false }"
    x-show="sidebarOpen"
    class="fixed inset-0 z-40 lg:hidden"
>

    <div
        class="absolute inset-0 bg-black/50"
        @click="sidebarOpen = false"
    ></div>

    <div class="relative w-72 h-full bg-slate-900 text-white">

        @include('components.sidebar')

    </div>
</div>