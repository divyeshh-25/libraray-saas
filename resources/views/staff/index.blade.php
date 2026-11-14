@extends('layouts.app')
@section('title', 'Staff')
@section('content')
<x-page-header title="Staff" subtitle="People who run day-to-day operations">
    @canDo('staff', 'delete')
        <a href="{{ route('staff.archived') }}" class="btn" title="Archived staff"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    @endcanDo
    @canDo('staff', 'add')
        <a href="{{ route('staff.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add staff</a>
    @endcanDo
</x-page-header>
<div class="card">
    <x-toolbar target="staff" placeholder="Search by name, email or code" :filters="[
        'All statuses' => ['col' => 5, 'options' => ['Active', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="staff">
            <thead><tr><th>Name</th><th>Designation</th><th>Branch</th><th>Role</th><th>Joined</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach ($staff as $s)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s->user->name" /><div><div class="font-medium">{{ $s->user->name }}</div><div class="text-[12px] text-muted">{{ $s->employee_code }} · {{ $s->user->email }}</div></div></div></td>
                        <td>{{ $s->designation }}</td>
                        <td>{{ $s->user->branch?->name }}</td>
                        <td>{{ $s->roles->pluck('name')->join(', ') }}</td>
                        <td class="text-muted whitespace-nowrap">{{ $s->joining_date?->format('d M Y') }}</td>
                        <td><x-badge :status="$s->status" /></td>
                        <td class="text-right"><x-row-actions :edit="viewer_can('staff', 'update') ? route('staff.edit', $s->id) : null" :delete="viewer_can('staff', 'delete') ? route('staff.destroy', $s->id) : null" delete-label="Archive" :label="$s->user->name" :extra="['Reset password']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No staff match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="staff" :size="8" />
</div>
@endsection
