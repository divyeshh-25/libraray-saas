@extends('layouts.app')
@section('title', 'Plans')
@section('content')
@php use App\Support\Mock; @endphp
<x-page-header title="Plans" subtitle="Subscription tiers offered to libraries">
    <a href="{{ route('plans.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add plan</a>
</x-page-header>

<div class="grid md:grid-cols-3 gap-4 mb-4">
    @foreach (array_slice(Mock::plans(), 0, 3) as $p)
        <div class="card p-5 {{ $p['name'] === 'Growth' ? 'border-brand-500' : '' }}">
            <div class="flex items-center justify-between">
                <div class="font-semibold text-[15px]">{{ $p['name'] }}</div>
                @if ($p['name'] === 'Growth')<span class="badge badge-info">Most popular</span>@endif
            </div>
            <div class="mt-2"><span class="text-[26px] font-semibold tracking-tight">₹{{ number_format($p['price']) }}</span> <span class="text-muted">/ month</span></div>
            <ul class="mt-4 space-y-1.5 text-[13px] text-slate-700">
                @foreach ($p['features'] as $f)
                    <li class="flex gap-2"><x-icon name="check" class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" />{{ $f }}</li>
                @endforeach
            </ul>
            <div class="mt-4 pt-3 border-t border-line text-[12.5px] text-muted">{{ $p['tenants'] }} tenants subscribed</div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-head"><div class="card-title">All plans</div></div>
    <x-toolbar target="plans" placeholder="Search plans" :filters="['All cycles' => ['col' => 2, 'options' => ['Monthly', 'Yearly']], 'All statuses' => ['col' => 6, 'options' => ['Active', 'Inactive']]]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="plans">
            <thead><tr><th>Plan</th><th class="text-right">Price</th><th>Billing</th><th class="text-right">Branches</th><th class="text-right">Students</th><th class="text-right">Tenants</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach (Mock::plans() as $p)
                    <tr>
                        <td class="font-medium">{{ $p['name'] }}</td>
                        <td class="text-right">₹{{ number_format($p['price']) }}</td>
                        <td>{{ $p['cycle'] }}</td>
                        <td class="text-right">{{ $p['branches'] }}</td>
                        <td class="text-right">{{ number_format($p['students']) }}</td>
                        <td class="text-right">{{ $p['tenants'] }}</td>
                        <td><x-badge :status="$p['status']" /></td>
                        <td class="text-right"><x-row-actions :edit="route('plans.edit', $p['id'])" :label="$p['name'].' plan'" :extra="['Deactivate']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No plans found." />
            </tbody>
        </table>
    </div>
    <x-pager target="plans" :size="8" />
</div>
@endsection
