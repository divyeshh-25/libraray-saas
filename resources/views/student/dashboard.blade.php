@extends('layouts.student')
@section('title', 'Dashboard')
@section('content')
@php
    $u = $student->user;
    $seat = $enrollment?->seatAllocation?->seat;
    $checkedIn = $todayAttendance && $todayAttendance->check_in;
    $checkedOut = $todayAttendance && $todayAttendance->check_out;
@endphp

@if (session('success'))
    <div class="rounded-md bg-emerald-50 text-emerald-800 px-3.5 py-3 text-[13px]">{{ session('success') }}</div>
@endif

<div class="card px-5 py-4">
    <div class="flex items-center gap-3">
        <x-initials :name="$u->name" class="w-12 h-12 text-base" />
        <div>
            <div class="font-semibold">{{ $u->name }}</div>
            <div class="text-[12.5px] text-muted">{{ $student->student_code }}@if ($u->branch) · {{ $u->branch->name }}@endif</div>
            <div class="text-[12.5px] text-muted">{{ collect([$u->phone, $u->email])->filter()->implode(' · ') }}</div>
        </div>
    </div>
</div>

{{-- Check-in / check-out --}}
<div class="card px-5 py-6 text-center">
    @if (!$enrollment)
        <p class="text-muted text-[13px]">You don't have an active enrollment. Contact your branch to get enrolled before you can mark attendance.</p>
    @else
        <div class="text-[12.5px] text-muted mb-3">{{ $enrollment->shift->name }} shift &middot; {{ $enrollment->shift->start_time }}&ndash;{{ $enrollment->shift->end_time }}</div>

        @if (!$checkedIn)
            <form method="POST" action="{{ route('student.check-in') }}">
                @csrf
                <button class="btn btn-primary w-full !h-16 text-[17px]"><x-icon name="check" class="w-6 h-6" /> Check In</button>
            </form>
        @elseif (!$checkedOut)
            <div class="mb-3"><x-badge :status="$todayAttendance->status" /> <span class="text-[12.5px] text-muted">checked in at {{ \Illuminate\Support\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</span></div>
            <form method="POST" action="{{ route('student.check-out') }}">
                @csrf
                <button class="btn w-full !h-16 text-[17px]"><x-icon name="login" class="w-6 h-6" /> Check Out</button>
            </form>
        @else
            <div><x-badge :status="$todayAttendance->status" /></div>
            <p class="text-[12.5px] text-muted mt-2">
                In {{ \Illuminate\Support\Carbon::parse($todayAttendance->check_in)->format('h:i A') }} &middot;
                Out {{ \Illuminate\Support\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}
            </p>
        @endif
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 gap-3">
    <x-stat label="Days this month" value="{{ $daysThisMonth }}" />
    <x-stat label="Present days" value="{{ $presentDaysThisMonth }}" />
    <x-stat label="Consistency" value="{{ $consistency }}%" note="This month" />
    <x-stat label="Overall avg" value="{{ $avg }}%" note="Since enrollment" />
</div>

{{-- Enrollment / branch / shift / seat --}}
<div class="card">
    <div class="card-head"><div class="card-title">Enrollment</div></div>
    <div class="card-body text-[13px]">
        @if ($enrollment)
            <dl class="space-y-3">
                <div class="flex justify-between gap-4"><dt class="text-muted">Branch</dt><dd class="text-right font-medium">{{ $enrollment->branch->name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-muted">Shift</dt><dd class="text-right font-medium">{{ $enrollment->shift->name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-muted">Seat</dt><dd class="text-right font-medium">{{ $seat?->seat_number ?? '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-muted">Fee plan</dt><dd class="text-right font-medium">{{ $enrollment->feeStructure?->name }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-muted">Enrolled</dt><dd class="text-right font-medium">{{ $enrollment->enrollment_date->format('d M Y') }}</dd></div>
            </dl>
        @else
            <p class="text-muted text-center py-2">No active enrollment.</p>
        @endif
    </div>
</div>

{{-- Attendance history --}}
<div class="card">
    <div class="card-head"><div class="card-title">Attendance history</div></div>
    <div class="card-body">
        @if ($history->isEmpty())
            <p class="text-muted text-[13px] py-4 text-center">No attendance recorded yet.</p>
        @else
            <div class="grid grid-cols-7 gap-1.5">
                @foreach ($history as $a)
                    <div class="h-8 rounded grid place-items-center text-[12px] {{ $a->status === 'present' ? 'bg-emerald-50 text-emerald-700' : ($a->status === 'absent' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}" title="{{ $a->attendance_date->format('d M') }} &middot; {{ ucfirst($a->status) }}">{{ $a->attendance_date->format('d') }}</div>
                @endforeach
            </div>
            <p class="hint mt-3">Green: present &middot; Amber: late &middot; Red: absent</p>
        @endif
    </div>
</div>

{{-- Fees --}}
<div class="grid grid-cols-3 gap-3">
    <x-stat label="Billed" value="₹{{ number_format($feeSummary['billed']) }}" />
    <x-stat label="Paid" value="₹{{ number_format($feeSummary['paid']) }}" />
    <x-stat label="Outstanding" value="₹{{ number_format($feeSummary['outstanding']) }}" />
</div>

<div class="card">
    <div class="card-head"><div class="card-title">Invoices</div><a href="{{ route('student.profile.edit') }}" class="text-[12.5px] text-brand-700 hover:underline">Edit profile</a></div>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Invoice</th><th>Date</th><th class="text-right">Payable</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($invoices as $inv)
                    <tr>
                        <td class="font-medium">{{ $inv->invoice_number }}</td>
                        <td class="text-muted whitespace-nowrap">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td class="text-right">₹{{ number_format($inv->final_amount, 2) }}</td>
                        <td class="text-right">₹{{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="text-right">₹{{ number_format($inv->balance_amount, 2) }}</td>
                        <td><x-badge :status="$inv->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-6">No fee invoices yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-head"><div class="card-title">Payment history</div></div>
    <div class="overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Receipt</th><th>Date</th><th>Method</th><th class="text-right">Amount</th><th></th></tr></thead>
            <tbody>
                @forelse ($payments as $p)
                    <tr>
                        <td class="font-medium">{{ $p->receipt_number }}<div class="text-[12px] text-muted font-normal">{{ $p->invoice->invoice_number }}</div></td>
                        <td class="text-muted whitespace-nowrap">{{ $p->transaction_date->format('d M Y') }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $p->payment_method)) }}</td>
                        <td class="text-right">₹{{ number_format($p->amount, 2) }}</td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('student.receipts.show', $p->id) }}" target="_blank" class="text-brand-700 hover:underline text-[12.5px]">View</a>
                            · <a href="{{ route('student.receipts.pdf', $p->id) }}" class="text-brand-700 hover:underline text-[12.5px]">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-6">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
