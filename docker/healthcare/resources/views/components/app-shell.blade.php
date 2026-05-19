<div class="min-h-screen flex bg-slate-100">

    <aside class="sidebar w-72">

        @include('components.sidebar')

    </aside>

    <div class="flex-1 flex flex-col">

        <header class="bg-white shadow-sm px-6 py-4">

            <div class="flex justify-between items-center">

                <div class="font-bold text-xl">
                    HIS Platform
                </div>

                <div class="flex items-center gap-4">

                    @include('components.language-switcher')

                </div>
            </div>
        </header>

        <main class="flex-1 p-6">

            {{ $slot }}

        </main>
    </div>
</div>