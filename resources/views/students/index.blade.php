@extends('layouts.app')
@section('title', 'Students')
@section('content')
<x-page-header title="Students" subtitle="Enrolled members across all branches">
    @canDo('students', 'delete')
        <a href="{{ route('students.archived') }}" class="btn" title="Archived students"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    @endcanDo
    <button class="btn" data-toast="Export started (demo)">Export</button>
    @canDo('students', 'add')
        <a href="{{ route('students.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add student</a>
    @endcanDo
</x-page-header>
<div class="card">
    <x-toolbar target="students" placeholder="Search by name, code or phone" :filters="[
        'All branches' => ['col' => 2, 'options' => $branches],
        'All statuses' => ['col' => 6, 'options' => ['Active', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="students">
            <thead><tr><th>Student</th><th>Phone</th><th>Branch</th><th>Shift</th><th>Seat</th><th class="text-right">Fee / mo</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach ($students as $s)
                    @php $enrollment = $s->enrollments->first(); @endphp
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s->user->name" /><div><a href="{{ route('students.show', $s->id) }}" class="font-medium hover:text-brand-700">{{ $s->user->name }}</a><div class="text-[12px] text-muted">{{ $s->student_code }}</div></div></div></td>
                        <td class="whitespace-nowrap">{{ $s->user->phone }}</td>
                        <td>{{ $s->user->branch?->name }}</td>
                        <td class="whitespace-nowrap">{{ $enrollment?->shift?->name ?? $s->shift }}</td>
                        <td>{{ $enrollment?->seatAllocation?->seat?->seat_number ?? $s->seat_label }}</td>
                        <td class="text-right">{{ $enrollment?->feeStructure?->amount ?? $s->fee_amount ? '₹'.number_format($enrollment?->feeStructure?->amount ?? $s->fee_amount) : '—' }}</td>
                        <td><x-badge :status="$s->status" /></td>
                        <td class="text-right"><x-row-actions :view="route('students.show', $s->id)" :edit="viewer_can('students', 'update') ? route('students.edit', $s->id) : null" :delete="viewer_can('students', 'delete') ? route('students.destroy', $s->id) : null" delete-label="Archive" :label="$s->user->name" :links="array_filter([
                            viewer_can('students', 'enroll') ? ['label' => 'Enroll', 'url' => route('enrollments.create', ['student' => $s->id]), 'title' => 'Enroll '.$s->user->name] : null,
                            ['label' => 'Enrollment history', 'url' => route('students.enrollment-history', $s->id), 'title' => 'Enrollment history — '.$s->user->name],
                        ])" :collect-fee="viewer_can('students', 'update') ? [
                            'url' => route('students.collect-fee', $s->id),
                            'studentName' => $s->user->name,
                            'feeStructureId' => $enrollment?->fee_structure_id,
                            'amount' => $enrollment?->feeStructure?->amount ?? $s->fee_amount,
                        ] : null" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No students match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="students" :size="8" />
</div>

<div id="student-offcanvas" class="fixed inset-0 z-[55] hidden" data-offcanvas data-offcanvas-table="students">
    <a href="#" class="absolute inset-0 bg-slate-900/40 opacity-0 transition-opacity duration-200" data-offcanvas-backdrop data-offcanvas-close aria-label="Close"></a>
    <div class="absolute inset-y-0 right-0 w-full sm:max-w-lg bg-white shadow-xl flex flex-col translate-x-full transition-transform duration-200" data-offcanvas-panel>
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-line shrink-0">
            <div class="text-[15px] font-semibold" data-offcanvas-title></div>
            <button type="button" class="btn btn-sm !px-2" data-offcanvas-close aria-label="Close"><x-icon name="x" class="w-4 h-4" /></button>
        </div>
        <div class="flex-1 flex flex-col overflow-hidden" data-offcanvas-body></div>
    </div>
</div>

@include('students._collect_fee_modal')
@endsection
