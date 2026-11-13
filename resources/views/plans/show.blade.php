@extends('layouts.app')
@section('title', 'Plan details')
@section('content')
<x-page-header :title="$plan->name" :back="['Plans', route('plans.index')]">
    @if ($plan->trashed())
        <span class="badge badge-muted">Archived</span>
        <button type="button" class="btn btn-primary" data-restore-url="{{ route('plans.restore', $plan->id) }}" data-redirect="{{ route('plans.index') }}"><x-icon name="restore" class="w-4 h-4" /> Restore</button>
    @else
        <a href="{{ route('plans.edit', $plan->id) }}" class="btn btn-primary">Edit plan</a>
    @endif
</x-page-header>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Subscribed tenants</div></div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Library</th><th>Owner</th><th>Started</th><th class="text-right">Amount</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse ($subscribers as $s)
                            <tr>
                                <td><a class="font-medium hover:text-brand-700" href="{{ route('tenants.show', $s->tenant_id) }}">{{ $s->tenant?->library_name }}</a></td>
                                <td class="text-muted">{{ $s->tenant?->owner_name }}</td>
                                <td class="text-muted">{{ $s->starts_at->format('d M Y') }}</td>
                                <td class="text-right">₹{{ number_format($s->amount) }}</td>
                                <td><x-badge :status="$s->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-6">No tenants are subscribed to this plan yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Overview</div><x-badge :status="$plan->status" /></div>
            <div class="p-5">
                <div class="text-[26px] font-semibold tracking-tight">₹{{ number_format($plan->price) }}</div>
                <div class="text-muted">per {{ $plan->billing_cycle === 'yearly' ? 'year' : ($plan->billing_cycle === 'quarterly' ? 'quarter' : 'month') }}</div>
                <dl class="mt-4 space-y-3 text-[13px]">
                    @foreach ([['Max branches', $plan->max_branches ?? 'Unlimited'], ['Max students', $plan->max_students ? number_format($plan->max_students) : 'Unlimited'], ['Active tenants', $plan->tenants_count]] as [$k, $v])
                        <div class="flex justify-between gap-4"><dt class="text-muted">{{ $k }}</dt><dd class="text-right font-medium">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Included features</div></div>
            <ul class="p-5 space-y-1.5 text-[13px] text-slate-700">
                @forelse ($plan->features ?? [] as $f)
                    <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" />{{ $f }}</li>
                @empty
                    <li class="text-muted">No features configured.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
