@extends('layouts.app')
@section('title', 'Students')
@section('content')
@php use App\Support\Mock; @endphp
<x-page-header title="Students" subtitle="Enrolled members across all branches">
    <button class="btn" data-toast="Export started (demo)">Export</button>
    <a href="{{ route('students.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add student</a>
</x-page-header>
<div class="card">
    <x-toolbar target="students" placeholder="Search by name, code or phone" :filters="[
        'All branches' => ['col' => 2, 'options' => ['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11', 'Bopal']],
        'All shifts' => ['col' => 3, 'options' => ['Morning', 'Afternoon', 'Evening', 'Full Day']],
        'All statuses' => ['col' => 6, 'options' => ['Active', 'Due', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="students">
            <thead><tr><th>Student</th><th>Phone</th><th>Branch</th><th>Shift</th><th>Seat</th><th class="text-right">Fee / mo</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach (Mock::students() as $s)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s['name']" /><div><a href="{{ route('students.show', $s['id']) }}" class="font-medium hover:text-brand-700">{{ $s['name'] }}</a><div class="text-[12px] text-muted">{{ $s['code'] }}</div></div></div></td>
                        <td class="whitespace-nowrap">{{ $s['phone'] }}</td>
                        <td>{{ $s['branch'] }}</td>
                        <td class="whitespace-nowrap">{{ $s['shift'] }}</td>
                        <td>{{ $s['seat'] }}</td>
                        <td class="text-right">₹{{ number_format($s['fee']) }}</td>
                        <td><x-badge :status="$s['status']" /></td>
                        <td class="text-right"><x-row-actions :view="route('students.show', $s['id'])" :edit="route('students.edit', $s['id'])" :label="$s['name']" :extra="['Collect fee']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No students match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="students" :size="8" />
</div>
@endsection
