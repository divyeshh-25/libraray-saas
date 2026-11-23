@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    $planTotal = max(1, $plans->sum('tenants_count'));
    $shades = ['#c3d0f5', '#7f98e8', '#3453c4', '#243a8f', '#9aa7d4'];
@endphp

@include('dashboards._greeting', ['subtitle' => $attention->isNotEmpty() ? $attention->count().' subscription(s) expire within 14 days.' : 'All subscriptions are healthy.'])

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <x-stat label="Active tenants" value="{{ number_format($tenantsActive) }}" note="{{ $tenantTotal }} tenants in total" />
    <x-stat label="Active subscriptions" value="{{ number_format($activeSubscriptions) }}" note="{{ $plansActive }} active plans" />
    <x-stat label="Revenue this month" value="₹{{ number_format($revenueThisMonth) }}" note="₹{{ number_format($revenueTotal) }} all time" />
    <x-stat label="Registered students" value="{{ number_format($studentTotal) }}" note="across all tenants" />
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2">
        <div class="card-head">
            <div class="card-title">Subscription revenue <span class="text-muted font-normal">(₹)</span></div>
            <span class="text-[12.5px] text-muted">Last 12 months</span>
        </div>
        <div class="card-body"><x-bar-chart :data="$revenue" prefix="₹" /></div>
    </div>

    <div class="card">
        <div class="card-head"><div class="card-title">Tenants by plan</div></div>
        <div class="card-body space-y-4">
            @if ($plans->isEmpty())
                <p class="text-muted text-[13px]">No plans yet.</p>
            @else
                <div class="flex h-2.5 rounded-full overflow-hidden bg-slate-100">
                    @foreach ($plans as $plan)<div style="width: {{ $plan->tenants_count / $planTotal * 100 }}%; background: {{ $shades[$loop->index % count($shades)] }}"></div>@endforeach
                </div>
                <ul class="space-y-2.5">
                    @foreach ($plans as $plan)
                        <li class="flex items-center gap-2.5">
                            <span class="w-2.5 h-2.5 rounded-sm" style="background: {{ $shades[$loop->index % count($shades)] }}"></span>
                            <span>{{ $plan->name }}</span>
                            <span class="ml-auto text-muted">{{ $plan->tenants_count }} tenants</span>
                            <span class="w-10 text-right font-medium">{{ round($plan->tenants_count / $planTotal * 100) }}%</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            <div class="pt-3 border-t border-line text-[12.5px] text-muted">
                Tenant status:
                @forelse ($tenantsByStatus as $status => $count)<span class="text-ink font-medium">{{ $count }}</span> {{ $status }}{{ $loop->last ? '' : ' · ' }}@empty none @endforelse
            </div>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2">
        <div class="card-head">
            <div class="card-title">Subscriptions needing attention</div>
            <a href="{{ route('tenants.index') }}" class="text-[12.5px] text-brand-700 hover:underline">View all tenants</a>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Library</th><th>Plan</th><th>Status</th><th>Detail</th></tr></thead>
                <tbody>
                    @forelse ($attention as $sub)
                        @php $expired = $sub->expires_at->isPast(); @endphp
                        <tr>
                            <td><div class="font-medium">{{ $sub->tenant?->library_name }}</div><div class="text-[12px] text-muted">{{ $sub->tenant?->owner_name }}</div></td>
                            <td>{{ $sub->plan?->name }}</td>
                            <td><x-badge :status="$expired ? 'expired' : 'due'" /></td>
                            <td class="text-muted">{{ $expired ? 'Expired '.$sub->expires_at->diffForHumans() : 'Expires '.$sub->expires_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-6">Nothing needs attention.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><div class="card-title">Newest tenants</div></div>
        <ul class="divide-y divide-line">
            @forelse ($recentTenants as $t)
                <li class="flex items-center gap-3 px-5 py-3">
                    <x-initials :name="$t->library_name" />
                    <div class="min-w-0"><div class="font-medium truncate">{{ $t->library_name }}</div><div class="text-[12.5px] text-muted">{{ $t->city ?? $t->owner_name }}</div></div>
                    <span class="ml-auto"><x-badge :status="$t->status" /></span>
                </li>
            @empty
                <li class="px-5 py-6 text-center text-muted text-[13px]">No tenants yet.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
