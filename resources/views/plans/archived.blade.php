@extends('layouts.app')
@section('title', 'Archived plans')
@section('content')
<x-page-header title="Archived plans" subtitle="Plans that have been archived (soft deleted)" :back="['Plans', route('plans.index')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="plans-archived">
            <thead><tr><th>Plan</th><th class="text-right">Price</th><th>Billing</th><th class="text-right">Tenants</th><th>Status</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($plans as $p)
                    <tr>
                        <td class="font-medium">{{ $p->name }}</td>
                        <td class="text-right">₹{{ number_format($p->price) }}</td>
                        <td>{{ ucfirst($p->billing_cycle) }}</td>
                        <td class="text-right">{{ $p->tenants_count }}</td>
                        <td><span class="badge badge-muted">Archived</span></td>
                        <td class="text-muted whitespace-nowrap">{{ $p->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('plans.restore', $p->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No archived plans." />
            </tbody>
        </table>
    </div>
</div>
@endsection
