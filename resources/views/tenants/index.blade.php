@extends('layouts.app')
@section('title', 'Tenants')
@section('content')
<x-page-header title="Tenants" subtitle="Libraries that subscribe to the platform">
    @canDo('tenants', 'delete')
        <a href="{{ route('tenants.archived') }}" class="btn" title="Archived libraries"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    @endcanDo
    <button class="btn" data-toast="Export started (demo)">Export</button>
    @canDo('tenants', 'add')
        <a href="{{ route('tenants.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add tenant</a>
    @endcanDo
</x-page-header>

<div class="card">
    <x-toolbar target="tenants" placeholder="Search by library, owner or email" :filters="[
        'All plans' => ['col' => 2, 'options' => ['Starter', 'Growth', 'Pro']],
        'All statuses' => ['col' => 3, 'options' => ['Active', 'Trial', 'Suspended', 'Expired']],
        'All cities' => ['col' => 1, 'options' => ['Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Gandhinagar']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="tenants">
            <thead><tr><th>Library</th><th>City</th><th>Plan</th><th>Status</th><th class="text-right">Branches</th><th class="text-right">Students</th><th>Joined</th><th class="w-10"></th></tr></thead>
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
                        <td><x-badge :status="$t->status" /></td>
                        <td class="text-right">{{ $t->branches_count }}</td>
                        <td class="text-right">{{ number_format($t->students_count) }}</td>
                        <td class="text-muted whitespace-nowrap">{{ $t->created_at->format('d M Y') }}</td>
                        <td class="text-right"><x-row-actions :view="route('tenants.show', $t->id)" :edit="viewer_can('tenants', 'update') ? route('tenants.edit', $t->id) : null" :delete="viewer_can('tenants', 'delete') ? route('tenants.destroy', $t->id) : null" delete-label="Archive" :label="$t->library_name" :extra="['Suspend']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No tenants match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="tenants" :size="8" />
</div>
@endsection
