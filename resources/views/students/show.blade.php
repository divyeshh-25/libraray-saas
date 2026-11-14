@extends('layouts.app')
@section('title', 'Student details')
@section('content')
@php
    $s = $student;
    $present = $attendances->where('status', 'present')->count();
    $absent = $attendances->where('status', 'absent')->count();
@endphp
<x-page-header :title="$s->user->name" :subtitle="$s->student_code.' · Admitted '.($s->admission_date?->format('d M Y') ?? '—')" :back="['Students', route('students.index')]">
    @if ($s->trashed())
        <span class="badge badge-muted">Archived</span>
        @canDo('students', 'delete')
            <button type="button" class="btn btn-primary" data-restore-url="{{ route('students.restore', $s->id) }}" data-redirect="{{ route('students.index') }}"><x-icon name="restore" class="w-4 h-4" /> Restore</button>
        @endcanDo
    @else
        <button class="btn" data-toast="Receipt sent (demo)">Collect fee</button>
        @canDo('students', 'update')
            <a href="{{ route('students.edit', $s->id) }}" class="btn btn-primary">Edit student</a>
        @endcanDo
    @endif
</x-page-header>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="space-y-4">
        <div class="card">
            <div class="p-5 flex items-center gap-3 border-b border-line">
                <x-initials :name="$s->user->name" class="w-12 h-12 text-base" />
                <div><div class="font-semibold">{{ $s->user->name }}</div><x-badge :status="$s->status" /></div>
            </div>
            <dl class="p-5 space-y-3 text-[13px]">
                @foreach ([['Phone', $s->user->phone], ['Branch', $s->user->branch?->name], ['Shift', $s->shift], ['Seat', $s->seat_label], ['Guardian', $s->guardian_name], ['Guardian phone', $s->guardian_phone]] as [$k, $v])
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ $k }}</dt><dd class="text-right font-medium">{{ $v ?: '—' }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Attendance</div><span class="text-[12.5px] text-muted">{{ $present }} present · {{ $absent }} absent</span></div>
            <div class="card-body">
                @if ($attendances->isEmpty())
                    <p class="text-muted text-[13px] py-4 text-center">No attendance recorded yet.</p>
                @else
                    <div class="grid grid-cols-7 gap-1.5 max-w-md">
                        @foreach ($attendances as $a)
                            <div class="h-8 rounded grid place-items-center text-[12px] {{ $a->status === 'present' ? 'bg-emerald-50 text-emerald-700' : ($a->status === 'absent' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-400') }}" title="{{ $a->attendance_date->format('d M') }}">{{ $a->attendance_date->format('d') }}</div>
                        @endforeach
                    </div>
                    <p class="hint mt-3">Green: present · Red: absent · Gray: other</p>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Fee history</div></div>
            <table class="tbl">
                <thead><tr><th>Invoice</th><th>Date</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        <tr><td class="font-medium">{{ $inv->invoice_number }}</td><td class="text-muted">{{ $inv->invoice_date->format('d M Y') }}</td><td class="text-right">₹{{ number_format($inv->final_amount) }}</td><td><x-badge :status="$inv->status" /></td></tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-6">No fee invoices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
