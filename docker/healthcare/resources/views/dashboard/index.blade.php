@extends('layouts.app')

@section('page-title', 'Patient Card')
@section('main-class', 'bg-slate-100')

@section('content')
@php
    use Carbon\Carbon;

    $demo       = $demo ?? null;
    $vitals     = $vitals ?? null;
    $conditions = $conditions ?? collect();
    $calendar   = $calendar ?? ['month'=>now()->format('F Y'),'today'=>now()->day,'first_dow'=>now()->startOfMonth()->dayOfWeek,'days_in'=>now()->daysInMonth,'busy_days'=>[]];
    $doctors    = $doctors ?? [];
    $appointments = $appointments ?? collect();

    // Week days for schedule header
    $weekStart = Carbon::now()->startOfWeek(); // Mon
    $weekDays  = [];
    for ($i = 0; $i < 7; $i++) {
        $weekDays[] = $weekStart->copy()->addDays($i);
    }

    // Group appointments by day-of-week index (0=Mon)
    $apptByDay = [];
    foreach ($appointments as $appt) {
        $dow = Carbon::parse($appt->start_at)->dayOfWeek; // 0=Sun
        // Convert to Mon-based index
        $idx = ($dow === 0) ? 6 : $dow - 1;
        $apptByDay[$idx][] = $appt;
    }

    // Patient vitals fallback
    $height    = $vitals?->height_cm ?? '—';
    $weight    = $vitals?->weight_kg ?? '—';
    $bloodType = $demo?->blood_type  ?? 'AB+';

    $patientName = $demo ? trim(($demo->given_name ?? '') . ' ' . ($demo->family_name ?? '')) : 'No Active Patient';
    $age = $demo?->birth_date ? Carbon::parse($demo->birth_date)->age . ' years old' : '—';

    $conditionList = $conditions->pluck('code_display')->filter()->implode(', ') ?: '—';
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-100 to-blue-50 p-6 flex flex-col gap-5">

    {{-- ── Top header bar ────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('patients.index') }}"
               class="w-8 h-8 flex items-center justify-center rounded-full bg-white shadow text-slate-500 hover:text-slate-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-xl font-bold text-slate-800">Patient card</h1>
        </div>

        <div class="flex items-center gap-3">
            {{-- Appointments history dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-2 text-blue-600 font-medium text-sm hover:text-blue-700 bg-white rounded-xl px-4 py-2 shadow-sm border border-slate-200">
                    Appointments history
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak
                     class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 z-20 py-1">
                    <a href="{{ route('appointments.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">All appointments</a>
                    @if($patient)
                    <a href="{{ route('patients.show', $patient->id) }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Patient history</a>
                    @endif
                </div>
            </div>

            {{-- New Patient --}}
            <a href="{{ route('patients.create') }}"
               class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-xl shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Patient
            </a>

            {{-- Notification bell --}}
            <button class="relative w-9 h-9 flex items-center justify-center bg-white rounded-full shadow text-slate-500 hover:text-blue-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
            </button>

            {{-- Avatar --}}
            <div class="w-9 h-9 rounded-full bg-slate-300 overflow-hidden flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 12c2.7 0 4-1.79 4-4s-1.3-4-4-4-4 1.79-4 4 1.3 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Main 2-column layout ───────────────────────────────────── --}}
    <div class="flex gap-5 flex-1">

        {{-- ── LEFT: Patient info + Schedule ─────────────────────── --}}
        <div class="flex-1 flex flex-col gap-5 min-w-0">

            {{-- Patient Banner --}}
            <div class="bg-white rounded-2xl shadow-sm p-5 flex gap-6 items-start">

                {{-- Blue patient card --}}
                <div class="bg-blue-600 rounded-2xl p-5 flex flex-col items-center gap-3 min-w-[170px] text-white">
                    <div class="w-14 h-14 rounded-full bg-blue-400 flex items-center justify-center overflow-hidden border-2 border-white">
                        @if($patient)
                            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.7 0 4-1.79 4-4s-1.3-4-4-4-4 1.79-4 4 1.3 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                            </svg>
                        @else
                            <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        @endif
                    </div>
                    <div class="text-center">
                        <p class="font-bold text-base leading-tight">{{ $patientName }}</p>
                        <p class="text-blue-200 text-xs mt-0.5">{{ $age }}</p>
                    </div>
                    <div class="flex gap-3 text-center">
                        <div>
                            <p class="text-blue-200 text-[10px]">Height</p>
                            <p class="font-bold text-sm">{{ $height }}</p>
                        </div>
                        <div class="w-px bg-blue-500"></div>
                        <div>
                            <p class="text-blue-200 text-[10px]">Weight</p>
                            <p class="font-bold text-sm">{{ $weight }}</p>
                        </div>
                        <div class="w-px bg-blue-500"></div>
                        <div>
                            <p class="text-blue-200 text-[10px]">Blood</p>
                            <p class="font-bold text-sm">{{ $bloodType }}</p>
                        </div>
                    </div>
                </div>

                {{-- Patient details --}}
                <div class="flex-1 grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs font-medium mb-1">Phone Number</p>
                        <p class="text-slate-800 font-medium">{{ $demo?->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium mb-1">Email</p>
                        <p class="text-slate-800 font-medium break-all">{{ $demo?->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium mb-1">MRN</p>
                        <p class="text-slate-800 font-medium">{{ $patient?->mrn ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs font-medium mb-1">Card Number</p>
                        <p class="text-slate-800 font-medium">{{ $patient?->mrn ?? '—' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-slate-400 text-xs font-medium mb-1">Medical Conditions</p>
                        <p class="text-slate-800 font-medium">{{ $conditionList }}</p>
                    </div>
                    @if($patient)
                    <div class="col-span-3 flex gap-2 pt-1">
                        <a href="{{ route('patients.show', $patient->id) }}"
                           class="text-xs text-blue-600 hover:underline font-medium">View full record →</a>
                        <span class="text-slate-200">|</span>
                        <a href="{{ route('encounters.create') }}?patient_id={{ $patient->id }}"
                           class="text-xs text-blue-600 hover:underline font-medium">New encounter</a>
                        <span class="text-slate-200">|</span>
                        <a href="{{ route('orders.create') }}?patient_id={{ $patient->id }}"
                           class="text-xs text-blue-600 hover:underline font-medium">Place order</a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Week Schedule --}}
            <div class="bg-white rounded-2xl shadow-sm flex-1 overflow-hidden">
                {{-- Day headers --}}
                <div class="grid border-b border-slate-100" style="grid-template-columns: 52px repeat(7,1fr);">
                    <div class="p-3 flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    @foreach($weekDays as $idx => $day)
                    @php $isToday = $day->isToday(); @endphp
                    <div class="py-3 text-center border-l border-slate-100 {{ $isToday ? 'text-blue-600' : 'text-slate-500' }}">
                        <p class="text-base font-bold {{ $isToday ? 'text-blue-600' : 'text-slate-800' }}">{{ $day->format('d') }}</p>
                        <p class="text-[11px] {{ $isToday ? 'text-blue-500 font-semibold' : 'text-slate-400' }}">{{ $day->format('l') }}</p>
                        @if($isToday)
                        <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mx-auto mt-1"></div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Scrollable time grid --}}
                <div class="overflow-y-auto" style="max-height: 340px;">
                    @for($hour = 8; $hour <= 17; $hour++)
                    <div class="grid border-b border-slate-50 hover:bg-slate-50/50 transition"
                         style="grid-template-columns: 52px repeat(7,1fr); min-height: 56px;">
                        <div class="p-2 text-right text-xs text-slate-400 pt-2 pr-3 shrink-0">
                            {{ sprintf('%d:00', $hour) }}
                        </div>
                        @foreach($weekDays as $idx => $day)
                        @php
                            $dayAppts = $apptByDay[$idx] ?? [];
                            $hourAppts = array_filter($dayAppts, fn($a) =>
                                Carbon::parse($a->start_at)->hour === $hour
                            );
                        @endphp
                        <div class="border-l border-slate-100 p-1 relative">
                            @foreach($hourAppts as $appt)
                            @php
                                $durationH = max(1, Carbon::parse($appt->start_at)->diffInHours(Carbon::parse($appt->end_at)));
                                $typeColors = [
                                    'Consultation' => 'bg-blue-100 border-blue-400 text-blue-800',
                                    'Operation'    => 'bg-yellow-100 border-yellow-400 text-yellow-800',
                                    'Analysis'     => 'bg-teal-100 border-teal-400 text-teal-800',
                                    'Rehabilitation' => 'bg-cyan-100 border-cyan-400 text-cyan-800',
                                ];
                                $type  = $appt->appointment_type ?? 'Consultation';
                                $color = $typeColors[$type] ?? 'bg-slate-100 border-slate-400 text-slate-700';
                                $timeStr = Carbon::parse($appt->start_at)->format('H:i') . '-' . Carbon::parse($appt->end_at)->format('H:i');
                            @endphp
                            <div class="rounded-lg border-l-4 px-2 py-1 text-xs {{ $color }} mb-1"
                                 style="min-height: {{ $durationH * 48 }}px;">
                                <p class="font-semibold leading-tight">{{ $type }}</p>
                                <p class="text-[10px] opacity-75">{{ $timeStr }}</p>
                            </div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endfor
                </div>
            </div>

            {{-- Metrics row --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Total Patients</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $metrics['patients_total'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Active Encounters</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $metrics['active_encounters'] }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Admissions Today</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $metrics['admissions_today'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── RIGHT panel ────────────────────────────────────────── --}}
        <div class="w-64 flex flex-col gap-4 shrink-0">

            {{-- Mini Calendar --}}
            <div class="bg-white rounded-2xl shadow-sm p-4"
                 x-data="{
                     year:  {{ now()->year }},
                     month: {{ now()->month - 1 }},
                     today: { y: {{ now()->year }}, m: {{ now()->month - 1 }}, d: {{ now()->day }} },
                     busyDays: @json($calendar['busy_days']),
                     monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
                     get label() { return this.monthNames[this.month] + ' ' + this.year; },
                     get firstDow() {
                         return new Date(this.year, this.month, 1).getDay();
                     },
                     get daysInMonth() {
                         return new Date(this.year, this.month + 1, 0).getDate();
                     },
                     get cells() {
                         let out = [];
                         let start = 1 - this.firstDow;
                         let rows = Math.ceil((this.firstDow + this.daysInMonth) / 7);
                         for (let r = 0; r < rows; r++) {
                             for (let c = 0; c < 7; c++) {
                                 let d = start + r * 7 + c;
                                 out.push(d >= 1 && d <= this.daysInMonth ? d : null);
                             }
                         }
                         return out;
                     },
                     isToday(d) {
                         return d !== null && d === this.today.d && this.month === this.today.m && this.year === this.today.y;
                     },
                     isBusy(d) {
                         return d !== null && this.month === this.today.m && this.year === this.today.y && this.busyDays.includes(d);
                     },
                     prev() { if (this.month === 0) { this.month = 11; this.year--; } else { this.month--; } },
                     next() { if (this.month === 11) { this.month = 0; this.year++; } else { this.month++; } },
                 }">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-slate-800 text-sm" x-text="label"></span>
                    <div class="flex gap-1">
                        <button @click="prev()" class="w-6 h-6 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <button @click="next()" class="w-6 h-6 rounded-full hover:bg-slate-100 flex items-center justify-center text-slate-400">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Day-of-week headers --}}
                <div class="grid grid-cols-7 text-center mb-1">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d)
                    <div class="text-[10px] text-slate-400 font-medium py-1">{{ $d }}</div>
                    @endforeach
                </div>

                {{-- Calendar grid --}}
                <div class="grid grid-cols-7 gap-y-1 text-center">
                    <template x-for="(d, i) in cells" :key="i">
                        <div class="relative flex items-center justify-center">
                            <template x-if="d !== null">
                                <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs cursor-default"
                                      :class="isToday(d) ? 'bg-blue-600 text-white font-bold' : (isBusy(d) ? 'text-blue-600 font-semibold' : 'text-slate-700 hover:bg-slate-100')"
                                      x-text="d"></span>
                            </template>
                            <template x-if="isBusy(d) && !isToday(d)">
                                <span class="absolute bottom-0 left-1/2 -translate-x-1/2 w-1 h-1 bg-blue-400 rounded-full"></span>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Doctors --}}
            <div class="bg-white rounded-2xl shadow-sm p-4 flex-1">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-slate-800 text-sm">Doctors</span>
                    <button class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                </div>

                @if(count($doctors) > 0)
                <div class="space-y-3">
                    @foreach($doctors as $doc)
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($doc['name'], 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $doc['name'] }}</p>
                            <p class="text-xs text-slate-400">{{ $doc['specialty'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="space-y-3">
                    @foreach([['Dr. Clarence Hamilton','Therapist'],['Dr. Brett Hoffman','Surgeon'],['Dr. Miguel Leonard','Hematologist'],['Dr. Mamie Holloway','Rehabilitologist']] as [$name,$spec])
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($name, 3, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $name }}</p>
                            <p class="text-xs text-slate-400">{{ $spec }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Write Prescription card --}}
            <div class="bg-white rounded-2xl shadow-sm p-4 relative overflow-hidden">
                <div class="relative z-10">
                    <p class="font-semibold text-slate-800 text-sm leading-snug mb-3">Write Prescription<br>to patient</p>
                    @if($patient)
                    <a href="{{ route('medications.create') }}?patient_id={{ $patient->id }}"
                       class="w-9 h-9 bg-blue-600 hover:bg-blue-700 transition rounded-xl flex items-center justify-center shadow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>
                    @else
                    <a href="{{ route('patients.index') }}"
                       class="w-9 h-9 bg-blue-600 hover:bg-blue-700 transition rounded-xl flex items-center justify-center shadow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </a>
                    @endif
                </div>
                {{-- Decorative pills illustration --}}
                <div class="absolute right-0 bottom-0 w-24 h-20 opacity-10">
                    <svg viewBox="0 0 100 80" fill="none" class="w-full h-full">
                        <ellipse cx="60" cy="40" rx="30" ry="12" transform="rotate(45 60 40)" fill="#3b82f6"/>
                        <ellipse cx="40" cy="55" rx="20" ry="8" transform="rotate(30 40 55)" fill="#60a5fa"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
