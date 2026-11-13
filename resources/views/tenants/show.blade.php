@extends('layouts.app')
@section('title', 'Tenant details')
@section('content')
@php
    $t = $tenant;
    $plan = $t->activePlan?->plan;
    $maxStudents = $plan?->max_students ?? 0;
    $maxBranches = $plan?->max_branches ?? 0;
@endphp
<x-page-header :title="$t->library_name" :back="['Tenants', route('tenants.index')]">
    @if ($t->trashed())
        <span class="badge badge-muted">Archived</span>
        <button type="button" class="btn btn-primary" data-restore-url="{{ route('tenants.restore', $t->id) }}" data-redirect="{{ route('tenants.index') }}"><x-icon name="restore" class="w-4 h-4" /> Restore</button>
    @else
        <button class="btn" data-toast="Invitation resent (demo)">Resend invite</button>
        <a href="{{ route('tenants.edit', $t->id) }}" class="btn btn-primary">Edit tenant</a>
    @endif
</x-page-header>

<div class="grid lg:grid-cols-3 gap-4" data-tabs-root>
    <div class="lg:col-span-2 space-y-4">
        <div class="card" data-tabs-root>
            <div class="px-5 pt-3 border-b border-line flex gap-6" data-tabs>
                <button class="tab active" data-tab="branches">Branches</button>
                <button class="tab" data-tab="billing">Billing history</button>
                <button class="tab" data-tab="activity">Activity</button>
            </div>
            <div data-panel="branches" class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Branch</th><th>Code</th><th class="text-right">Halls</th><th class="text-right">Seats</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($branches as $b)
                            <tr><td><a class="font-medium hover:text-brand-700" href="{{ route('branches.show', $b->id) }}">{{ $b->name }}</a></td><td class="text-muted">{{ $b->code }}</td><td class="text-right">{{ $b->halls_count }}</td><td class="text-right">{{ $b->seats_count }}</td><td><x-badge :status="$b->status" /></td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-6">No branches yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div data-panel="billing" class="hidden overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Plan</th><th>Started</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($billing as $b)
                            <tr><td class="font-medium">{{ $b->plan?->name }}</td><td class="text-muted">{{ $b->starts_at->format('d M Y') }}</td><td class="text-right">₹{{ number_format($b->amount) }}</td><td><x-badge :status="$b->status" /></td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-6">No billing history yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <ul data-panel="activity" class="hidden divide-y divide-line">
                @forelse ($activities as $a)
                    <li class="px-5 py-3 flex justify-between gap-3"><div><div class="font-medium">{{ ucfirst($a->action) }}</div><div class="text-[12.5px] text-muted">{{ $a->description }}</div></div><span class="text-[12.5px] text-muted whitespace-nowrap">{{ $a->created_at->diffForHumans() }}</span></li>
                @empty
                    <li class="px-5 py-6 text-center text-muted">No activity recorded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Overview</div><x-badge :status="$t->status" /></div>
            <dl class="p-5 space-y-3 text-[13px]">
                @foreach ([['Owner', $t->owner_name], ['Email', $t->email], ['Phone', $t->phone], ['City', $t->city], ['Customer since', $t->created_at->format('d M Y')]] as [$k, $v])
                    <div class="flex justify-between gap-4"><dt class="text-muted">{{ $k }}</dt><dd class="text-right font-medium">{{ $v }}</dd></div>
                @endforeach
            </dl>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Current plan</div><a href="{{ route('tenants.edit', $t->id) }}" class="text-[12.5px] text-brand-700 hover:underline">Change</a></div>
            <div class="p-5">
                <div class="text-[18px] font-semibold">{{ $plan?->name ?? '—' }}</div>
                <div class="text-muted">{{ $t->activePlan?->expires_at ? 'Renews on '.$t->activePlan->expires_at->format('d M Y') : 'No active subscription' }}</div>
                @php $pct = $maxStudents ? min(100, round($t->students_count / $maxStudents * 100)) : 0; @endphp
                <div class="mt-4 text-[12.5px] flex justify-between"><span class="text-muted">Student usage</span><span>{{ $t->students_count }} / {{ $maxStudents ?: '—' }}</span></div>
                <div class="h-1.5 bg-slate-100 rounded-full mt-1.5"><div class="h-full bg-brand-600 rounded-full" style="width: {{ $pct }}%"></div></div>
                <div class="mt-3 text-[12.5px] flex justify-between"><span class="text-muted">Branches</span><span>{{ $t->branches_count }} / {{ $maxBranches ?: '—' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
