<aside class="w-72 bg-slate-900 text-white flex flex-col">
    <div class="h-16 flex items-center px-6 border-b border-slate-800">
        <h1 class="text-xl font-bold">
            Healthcare HIS
        </h1>
    </div>

    <nav class="flex-1 overflow-y-auto p-4 space-y-1">

        <a href="/dashboard"
           class="sidebar-link {{ request()->is('dashboard*') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="/patients"
           class="sidebar-link {{ request()->is('patients*') ? 'active' : '' }}">
            Patients
        </a>

        <a href="/encounters"
           class="sidebar-link {{ request()->is('encounters*') ? 'active' : '' }}">
            Encounters
        </a>

        <a href="/orders"
           class="sidebar-link {{ request()->is('orders*') ? 'active' : '' }}">
            Orders
        </a>

        <a href="/results"
           class="sidebar-link {{ request()->is('results*') ? 'active' : '' }}">
            Results
        </a>

        <a href="{{ route('medications.index') }}"
           class="sidebar-link {{ request()->is('medications*') ? 'active' : '' }}">
            Medications
        </a>

        <a href="{{ route('appointments.index') }}"
           class="sidebar-link {{ request()->is('appointments*') ? 'active' : '' }}">
            Scheduling
        </a>

        <a href="{{ route('reports.index') }}"
           class="sidebar-link {{ request()->is('reports*') ? 'active' : '' }}">
            Reports
        </a>

    </nav>
</aside>