@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $principal = auth()->guard('web')->user();
    $occupancy = $seatTotal > 0 ? round($seatOccupied / $seatTotal * 100) : 0;
@endphp

@include('dashboards._greeting', ['subtitle' => $presentToday.' of '.$studentCount.' active students are in today.'.(($showFees ?? false) && $outstanding > 0 ? ' ₹'.number_format($outstanding).' in fees is outstanding.' : '')])

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <x-stat label="Branches" value="{{ $branches->count() }}" note="{{ $hallCount }} halls" />
    <x-stat label="Seats" value="{{ number_format($seatTotal) }}" note="{{ $seatOccupied }} occupied · {{ $seatAvailable }} available" />
    <x-stat label="Active students" value="{{ number_format($studentCount) }}" note="{{ $presentToday }} present today" />
    <x-stat label="Seat occupancy" value="{{ $occupancy }}%" note="{{ $seatOccupied }} of {{ $seatTotal }} seats" />
</div>

@if ($showFees)
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mt-4">
        <x-stat label="Fees collected this month" value="₹{{ number_format($collectedThisMonth) }}" note="Revenue ₹{{ number_format($collectedTotal) }} all time" />
        <x-stat label="Outstanding fees" value="₹{{ number_format($outstanding) }}" note="unpaid and partial invoices" />
        <x-stat label="Present today" value="{{ $attendanceToday['present'] ?? 0 }}" note="{{ $attendanceToday['late'] ?? 0 }} late" />
        <x-stat label="Absent today" value="{{ $attendanceToday['absent'] ?? 0 }}" note="marked absent" />
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mt-4">
        <div class="card lg:col-span-2">
            <div class="card-head"><div class="card-title">Fee collection <span class="text-muted font-normal">(₹)</span></div><span class="text-[12.5px] text-muted">Last 6 months</span></div>
            <div class="card-body"><x-bar-chart :data="$collectionTrend" prefix="₹" /></div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Recent payments</div></div>
            <ul class="divide-y divide-line">
                @forelse ($recentPayments as $p)
                    <li class="flex items-center gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <div class="font-medium truncate">{{ $p->student->user->name }}</div>
                            <a href="{{ route('receipts.show', $p->id) }}" target="_blank" class="text-[12.5px] text-brand-700 hover:underline">{{ $p->receipt_number }}</a>
                        </div>
                        <div class="ml-auto text-right"><div class="font-medium">₹{{ number_format($p->amount) }}</div><div class="text-[12px] text-muted">{{ $p->transaction_date->format('d M') }}</div></div>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-muted text-[13px]">No payments yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
@endif

<div class="card mt-4">
    <div class="card-head">
        <div class="card-title">Branches</div>
        @if ($principal->canAccessModule('branches'))<a href="{{ route('branches.index') }}" class="text-[12.5px] text-brand-700 hover:underline">Manage branches</a>@endif
    </div>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Branch</th><th>Code</th><th>Halls</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($branches as $b)
                    <tr><td class="font-medium">{{ $b->name }}</td><td class="text-muted">{{ $b->code }}</td><td>{{ $b->halls_count }}</td><td><x-badge :status="$b->status" /></td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-6">No branches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
