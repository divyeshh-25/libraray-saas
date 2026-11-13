@extends('layouts.app')
@section('title', 'Archived staff')
@section('content')
<x-page-header title="Archived staff" subtitle="Staff members that have been archived (soft deleted)" :back="['Staff', route('staff.index')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="staff-archived">
            <thead><tr><th>Name</th><th>Designation</th><th>Branch</th><th>Status</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($staff as $s)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s->user->name" /><div><div class="font-medium">{{ $s->user->name }}</div><div class="text-[12px] text-muted">{{ $s->employee_code }} · {{ $s->user->email }}</div></div></div></td>
                        <td>{{ $s->designation }}</td>
                        <td>{{ $s->user->branch?->name }}</td>
                        <td><span class="badge badge-muted">Archived</span></td>
                        <td class="text-muted whitespace-nowrap">{{ $s->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('staff.restore', $s->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="6" message="No archived staff." />
            </tbody>
        </table>
    </div>
</div>
@endsection
