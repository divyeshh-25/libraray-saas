@extends('layouts.app')
@section('title', 'Staff')
@section('content')
@php use App\Support\Mock; @endphp
<x-page-header title="Staff" subtitle="People who run day-to-day operations">
    <a href="{{ route('staff.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add staff</a>
</x-page-header>
<div class="card">
    <x-toolbar target="staff" placeholder="Search by name, email or code" :filters="[
        'All branches' => ['col' => 2, 'options' => ['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11', 'Bopal']],
        'All roles' => ['col' => 3, 'options' => ['Manager', 'Accountant', 'Receptionist', 'Librarian', 'Support']],
        'All statuses' => ['col' => 5, 'options' => ['Active', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="staff">
            <thead><tr><th>Name</th><th>Designation</th><th>Branch</th><th>Role</th><th>Joined</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach (Mock::staff() as $s)
                    <tr>
                        <td><div class="flex items-center gap-3"><x-initials :name="$s['name']" /><div><div class="font-medium">{{ $s['name'] }}</div><div class="text-[12px] text-muted">{{ $s['code'] }} · {{ $s['email'] }}</div></div></div></td>
                        <td>{{ $s['designation'] }}</td>
                        <td>{{ $s['branch'] }}</td>
                        <td>{{ $s['role'] }}</td>
                        <td class="text-muted whitespace-nowrap">{{ $s['joined'] }}</td>
                        <td><x-badge :status="$s['status']" /></td>
                        <td class="text-right"><x-row-actions :edit="route('staff.edit', $s['id'])" :label="$s['name']" :extra="['Reset password']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No staff match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="staff" :size="8" />
</div>
@endsection
