@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php $statusLabel = fn ($id) => $todayStatus[$id] ?? null; @endphp

@include('dashboards._greeting', ['subtitle' => $presentToday.' of '.$assignedCount.' students are in today · '.$seatAvailable.' seats available.'])

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <x-stat label="Assigned students" value="{{ number_format($assignedCount) }}" note="active in your branch" />
    <x-stat label="Present today" value="{{ $attendanceToday['present'] ?? 0 }}" note="{{ $attendanceToday['late'] ?? 0 }} late · {{ $attendanceToday['absent'] ?? 0 }} absent" />
    <x-stat label="Seats available" value="{{ number_format($seatAvailable) }}" note="{{ $seatOccupied }} of {{ $seatTotal }} occupied" />
    @if ($showFees)
        <x-stat label="Outstanding fees" value="₹{{ number_format($outstanding) }}" note="unpaid and partial invoices" />
    @else
        <x-stat label="Seat occupancy" value="{{ $seatTotal > 0 ? round($seatOccupied / $seatTotal * 100) : 0 }}%" note="across your halls" />
    @endif
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2">
        <div class="card-head"><div class="card-title">Assigned students</div>
            @if (auth()->user()->canAccessModule('students'))<a href="{{ route('students.index') }}" class="text-[12.5px] text-brand-700 hover:underline">View all</a>@endif
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Student</th><th>Shift</th><th>Seat</th><th>Today</th></tr></thead>
                <tbody>
                    @forelse ($students as $s)
                        @php $e = $s->enrollments->first(); $st = $statusLabel($s->id); @endphp
                        <tr>
                            <td><div class="font-medium">{{ $s->user->name }}</div><div class="text-[12px] text-muted">{{ $s->student_code }}</div></td>
                            <td>{{ $e?->shift?->name ?? '—' }}</td>
                            <td>{{ $e?->seatAllocation?->seat?->seat_number ?? '—' }}</td>
                            <td>@if ($st)<span class="badge {{ $st === 'present' ? 'badge-ok' : ($st === 'absent' ? 'badge-bad' : 'badge-warn') }}">{{ ucfirst(str_replace('_', ' ', $st)) }}</span>@else<span class="text-muted">Not marked</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-6">No students assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">What you can do</div></div>
            <div class="card-body flex flex-wrap gap-2">
                @forelse ($activities as [$label, $route])
                    <a href="{{ route($route) }}" class="btn">{{ $label }}</a>
                @empty
                    <p class="text-muted text-[13px]">No activities have been granted to your role yet.</p>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Seat availability</div></div>
            <ul class="divide-y divide-line">
                @forelse ($halls as $h)
                    <li class="px-5 py-3">
                        <div class="flex items-center justify-between"><span class="font-medium">{{ $h['hall']->name }}</span><span class="text-[12.5px] text-muted">{{ $h['available'] }} free of {{ $h['total'] }}</span></div>
                        <div class="h-1.5 rounded-full bg-slate-100 mt-2 overflow-hidden"><div class="h-full bg-brand-500" style="width: {{ $h['total'] > 0 ? round($h['occupied'] / $h['total'] * 100) : 0 }}%"></div></div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-muted text-[13px]">No halls yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
