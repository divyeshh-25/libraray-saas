@extends('layouts.app')
@section('title', 'Student details')
@section('content')
@php
    use App\Support\Mock;
    $s = Mock::student((int) $id);
    $invoices = [['Nov 2026', 'INV-2611-0412', $s['fee'], $s['status'] === 'due' ? 'due' : 'paid'], ['Oct 2026', 'INV-2610-0388', $s['fee'], 'paid'], ['Sep 2026', 'INV-2609-0351', $s['fee'], 'paid']];
    $days = ['P', 'P', 'P', 'A', 'P', 'P', '–', 'P', 'P', 'P', 'P', 'A', 'P', '–', 'P', 'P', 'P', 'P', 'P', 'A', 'P', '–', 'P', 'P', 'P', 'P', 'P', 'P'];
@endphp
<x-page-header :title="$s['name']" :subtitle="$s['code'].' · Admitted '.$s['admitted']" :back="['Students', route('students.index')]">
    <button class="btn" data-toast="Receipt sent (demo)">Collect fee</button>
    <a href="{{ route('students.edit', $s['id']) }}" class="btn btn-primary">Edit student</a>
</x-page-header>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="space-y-4">
        <div class="card">
            <div class="p-5 flex items-center gap-3 border-b border-line">
                <span class="w-12 h-12 rounded-full bg-brand-50 text-brand-700 grid place-items-center font-semibold">{{ collect(explode(' ', $s['name']))->map(fn ($p) => $p[0])->implode('') }}</span>
                <div><div class="font-semibold">{{ $s['name'] }}</div><x-badge :status="$s['status']" /></div>
            </div>
            <dl class="p-5 space-y-3 text-[13px]">
                @foreach ([['Phone', $s['phone']], ['Branch', $s['branch']], ['Shift', $s['shift']], ['Seat', $s['seat']], ['Guardian', 'Mahesh '.explode(' ', $s['name'])[1]], ['Guardian phone', '98250 40117']] as [$k, $v])
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ $k }}</dt><dd class="text-right font-medium">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Attendance · November</div><span class="text-[12.5px] text-muted">22 present · 3 absent</span></div>
            <div class="card-body">
                <div class="grid grid-cols-7 gap-1.5 max-w-md">
                    @foreach ($days as $i => $d)
                        <div class="h-8 rounded grid place-items-center text-[12px] {{ $d === 'P' ? 'bg-emerald-50 text-emerald-700' : ($d === 'A' ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-400') }}" title="Nov {{ $i + 1 }}">{{ $i + 1 }}</div>
                    @endforeach
                </div>
                <p class="hint mt-3">Green: present · Red: absent · Gray: weekly off</p>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Fee history</div></div>
            <table class="tbl">
                <thead><tr><th>Period</th><th>Invoice</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($invoices as [$per, $no, $amt, $st])
                        <tr><td>{{ $per }}</td><td class="text-muted">{{ $no }}</td><td class="text-right">₹{{ number_format($amt) }}</td><td><x-badge :status="$st" /></td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
