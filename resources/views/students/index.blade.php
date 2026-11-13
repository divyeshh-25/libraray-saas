@extends('layouts.app')
@section('title', 'Students')
@section('content')
<x-page-header title="Students" subtitle="Enrolled members across all branches">
    <a href="{{ route('students.archived') }}" class="btn" title="Archived students"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    <button class="btn" data-toast="Export started (demo)">Export</button>
    <a href="{{ route('students.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add student</a>
</x-page-header>
<div class="card">
    <x-toolbar target="students" placeholder="Search by name, code or phone" :filters="[
        'All statuses' => ['col' => 6, 'options' => ['Active', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="students">
            <thead><tr><th>Student</th><th>Phone</th><th>Branch</th><th>Shift</th><th>Seat</th><th class="text-right">Fee / mo</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach ($students as $s)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s->user->name" /><div><a href="{{ route('students.show', $s->id) }}" class="font-medium hover:text-brand-700">{{ $s->user->name }}</a><div class="text-[12px] text-muted">{{ $s->student_code }}</div></div></div></td>
                        <td class="whitespace-nowrap">{{ $s->user->phone }}</td>
                        <td>{{ $s->user->branch?->name }}</td>
                        <td class="whitespace-nowrap">{{ $s->shift }}</td>
                        <td>{{ $s->seat_label }}</td>
                        <td class="text-right">{{ $s->fee_amount ? '₹'.number_format($s->fee_amount) : '—' }}</td>
                        <td><x-badge :status="$s->status" /></td>
                        <td class="text-right"><x-row-actions :view="route('students.show', $s->id)" :edit="route('students.edit', $s->id)" :delete="route('students.destroy', $s->id)" delete-label="Archive" :label="$s->user->name" :extra="['Collect fee']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No students match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="students" :size="8" />
</div>
@endsection
