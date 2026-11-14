@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
@php
    use App\Support\Mock;
    $revenue = array_combine(Mock::months(), [412, 436, 451, 449, 478, 502, 515, 540, 561, 588, 612, 648]);
    $growth = array_combine(Mock::months(), [7420, 7890, 8310, 8620, 9040, 9480, 9910, 10420, 10980, 11540, 12010, 12480]);
    $plans = [['Starter', 38, '#c3d0f5'], ['Growth', 31, '#7f98e8'], ['Pro', 11, '#3453c4']];
    $planTotal = array_sum(array_column($plans, 1));
    $renewals = [
        ['Rina Kapadia', 'Lakshya Reading Hall', 'Starter', 'Expired 2 days ago', 'expired'],
        ['Kavita Joshi', 'Pustak Reading Lounge', 'Growth', 'Trial ends in 3 days', 'trial'],
        ['Pooja Rana', 'Aspire Study Center', 'Starter', 'Trial ends in 9 days', 'trial'],
        ['Harsh Mehta', 'Knowledge Point', 'Growth', 'Renews in 12 days', 'active'],
    ];
    $dot = ['ok' => 'bg-emerald-500', 'warn' => 'bg-amber-500', 'bad' => 'bg-red-500', 'info' => 'bg-brand-500'];
@endphp

<div class="relative overflow-hidden rounded-xl border border-line bg-white px-6 py-5 flex flex-wrap items-center justify-between gap-4 shadow-sm">
    <div class="absolute inset-y-0 right-0 w-1/2 pointer-events-none bg-[radial-gradient(ellipse_at_right,var(--color-brand-100),transparent_70%)] opacity-70"></div>
    <div class="relative">
        <div class="flex items-center gap-2 text-[12.5px] text-muted"><span class="live-dot"></span> Live overview · November 2026</div>
        <h1 class="text-[22px] font-semibold tracking-tight mt-1">Good morning, Divyesh 👋</h1>
        <p class="text-muted mt-0.5">3 subscriptions need attention today. Everything else looks healthy.</p>
    </div>
    @php $principal = auth()->guard('admin')->user() ?? auth()->guard('web')->user(); @endphp
    <div class="relative flex flex-wrap gap-2">
        @if ($principal?->canAccessModule('students'))
            <a href="{{ route('students.create') }}" class="btn">New admission</a>
        @endif
        @if ($principal?->canAccessModule('branches'))
            <a href="{{ route('branches.create') }}" class="btn">Add branch</a>
        @endif
        @if ($principal?->canAccessModule('tenants'))
            <a href="{{ route('tenants.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add tenant</a>
        @endif
    </div>
</div>

<div class="mt-4"></div>

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    <x-stat label="Active tenants" value="148" trend="+6" note="12 on trial" />
    <x-stat label="Registered students" value="12,480" trend="+4.1%" note="across 213 branches" />
    <x-stat label="Monthly revenue" value="₹6.48 L" trend="+5.9%" note="vs ₹6.12 L in October" />
    <x-stat label="Seat occupancy" value="71%" trend="-1.2%" note="21,340 of 30,060 seats" />
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2">
        <div class="card-head">
            <div class="card-title">Subscription revenue <span class="text-muted font-normal">(₹ thousands)</span></div>
            <span class="text-[12.5px] text-muted">Last 12 months</span>
        </div>
        <div class="card-body"><x-bar-chart :data="$revenue" /></div>
    </div>

    <div class="card">
        <div class="card-head"><div class="card-title">Tenants by plan</div></div>
        <div class="card-body space-y-4">
            <div class="flex h-2.5 rounded-full overflow-hidden">
                @foreach ($plans as [$n, $c, $col])<div style="width: {{ $c / $planTotal * 100 }}%; background: {{ $col }}"></div>@endforeach
            </div>
            <ul class="space-y-2.5">
                @foreach ($plans as [$n, $c, $col])
                    <li class="flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-sm" style="background: {{ $col }}"></span>
                        <span>{{ $n }}</span>
                        <span class="ml-auto text-muted">{{ $c }} tenants</span>
                        <span class="w-10 text-right font-medium">{{ round($c / $planTotal * 100) }}%</span>
                    </li>
                @endforeach
            </ul>
            <div class="pt-3 border-t border-line text-[12.5px] text-muted">Average revenue per tenant: <span class="text-ink font-medium">₹4,380</span></div>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card">
        <div class="card-head"><div class="card-title">Student growth</div><span class="text-[12.5px] text-muted">12,480 total</span></div>
        <div class="card-body"><x-line-chart :data="$growth" /></div>
    </div>

    <div class="card lg:col-span-2">
        <div class="card-head">
            <div class="card-title">Subscriptions needing attention</div>
            <a href="{{ route('tenants.index') }}" class="text-[12.5px] text-brand-700 hover:underline">View all tenants</a>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead><tr><th>Library</th><th>Plan</th><th>Status</th><th>Detail</th></tr></thead>
                <tbody>
                    @foreach ($renewals as [$owner, $lib, $plan, $detail, $st])
                        <tr>
                            <td><div class="font-medium">{{ $lib }}</div><div class="text-[12px] text-muted">{{ $owner }}</div></td>
                            <td>{{ $plan }}</td>
                            <td><x-badge :status="$st" /></td>
                            <td class="text-muted">{{ $detail }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-head"><div class="card-title">Recent activity</div></div>
    <ul class="divide-y divide-line">
        @foreach (Mock::activities() as [$title, $meta, $when, $tone])
            <li class="flex items-start gap-3 px-5 py-3">
                <span class="w-2 h-2 rounded-full mt-1.5 {{ $dot[$tone] }}"></span>
                <div class="min-w-0">
                    <div class="font-medium">{{ $title }}</div>
                    <div class="text-[12.5px] text-muted">{{ $meta }}</div>
                </div>
                <span class="ml-auto text-[12.5px] text-muted whitespace-nowrap">{{ $when }}</span>
            </li>
        @endforeach
    </ul>
</div>
@endsection
