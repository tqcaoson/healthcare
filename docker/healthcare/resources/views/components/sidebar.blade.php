<aside class="w-72 bg-slate-900 text-white flex flex-col">
    <div class="h-16 flex items-center px-6 border-b border-slate-800">
        <h1 class="text-xl font-bold">
            Healthcare HIS
        </h1>
    </div>

    <nav class="flex-1 overflow-y-auto p-4 space-y-1">

        <a href="/dashboard"
           class="sidebar-link">
            Dashboard
        </a>

        <a href="/patients"
           class="sidebar-link">
            Patients
        </a>

        <a href="/encounters"
           class="sidebar-link">
            Encounters
        </a>

        <a href="/orders"
           class="sidebar-link">
            Orders
        </a>

        <a href="/results"
           class="sidebar-link">
            Results
        </a>

        <a href="{{ route('medications.index') }}"
           class="sidebar-link">
            Medications
        </a>

        <a href="{{ route('appointments.index') }}"
           class="sidebar-link">
            Scheduling
        </a>

        <a href="{{ route('reports.index') }}"
           class="sidebar-link">
            Reports
        </a>

        @if(auth()->user()?->role === 'admin')
            <a href="/admin"
               class="sidebar-link">
                Administration
            </a>
        @endif
    </nav>
</aside>