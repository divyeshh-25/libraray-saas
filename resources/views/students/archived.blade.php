@extends('layouts.app')
@section('title', 'Archived students')
@section('content')
<x-page-header title="Archived students" subtitle="Students that have been archived (soft deleted)" :back="['Students', route('students.index')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="students-archived">
            <thead><tr><th>Student</th><th>Phone</th><th>Branch</th><th>Status</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($students as $s)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s->user->name" /><div><a href="{{ route('students.show', $s->id) }}" class="font-medium hover:text-brand-700">{{ $s->user->name }}</a><div class="text-[12px] text-muted">{{ $s->student_code }}</div></div></div></td>
                        <td>{{ $s->user->phone }}</td>
                        <td>{{ $s->user->branch?->name }}</td>
                        <td><span class="badge badge-muted">Archived</span></td>
                        <td class="text-muted whitespace-nowrap">{{ $s->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('students.restore', $s->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="6" message="No archived students." />
            </tbody>
        </table>
    </div>
</div>
@endsection
