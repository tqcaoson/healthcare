<header class="bg-white border-b border-slate-200 h-16">
    <div class="h-full px-6 flex items-center justify-between">

        <div>
            <h2 class="text-lg font-semibold text-slate-800">
                @yield('page-title', 'Dashboard')
            </h2>
        </div>

        <div class="flex items-center gap-4">

            {{-- Notification --}}
            <button class="relative">
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full px-1">
                    3
                </span>

                🔔
            </button>

            {{-- User --}}
            <div class="flex items-center gap-3">
                <div>
                    <div class="font-medium">
                        {{ auth()->user()->username }}
                    </div>

                    <div class="text-xs text-slate-500">
                        {{ auth()->user()->role }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>