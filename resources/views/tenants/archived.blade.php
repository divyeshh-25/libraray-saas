@extends('layouts.app')
@section('title', 'Archived tenants')
@section('content')
<x-page-header title="Archived tenants" subtitle="Libraries that have been archived (soft deleted)" :back="['Tenants', route('tenants.index')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="tenants-archived">
            <thead><tr><th>Library</th><th>City</th><th>Plan</th><th>Status</th><th class="text-right">Branches</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($tenants as $t)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-initials :name="$t->library_name" />
                                <div><a href="{{ route('tenants.show', $t->id) }}" class="font-medium hover:text-brand-700">{{ $t->library_name }}</a>
                                <div class="text-[12px] text-muted">{{ $t->owner_name }} · {{ $t->email }}</div></div>
                            </div>
                        </td>
                        <td>{{ $t->city }}</td>
                        <td>{{ $t->activePlan?->plan?->name ?? '—' }}</td>
                        <td><span class="badge badge-muted">Archived</span></td>
                        <td class="text-right">{{ $t->branches_count }}</td>
                        <td class="text-muted whitespace-nowrap">{{ $t->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('tenants.restore', $t->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No archived tenants." />
            </tbody>
        </table>
    </div>
</div>
@endsection
