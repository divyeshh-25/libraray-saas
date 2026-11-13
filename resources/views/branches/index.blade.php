@extends('layouts.app')
@section('title', 'Branches')
@section('content')
<x-page-header title="Branches" subtitle="Physical locations run by each library">
    <a href="{{ route('branches.archived') }}" class="btn" title="Archived branches"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    <a href="{{ route('branches.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add branch</a>
</x-page-header>
<div class="card">
    <x-toolbar target="branches" placeholder="Search by branch, code or library" :filters="[
        'All libraries' => ['col' => 1, 'options' => $tenants->all()],
        'All cities' => ['col' => 2, 'options' => $cities->all()],
        'All statuses' => ['col' => 6, 'options' => ['Active', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="branches">
            <thead><tr><th>Branch</th><th>Library</th><th>City</th><th class="text-right">Halls</th><th class="text-right">Seats</th><th class="text-right">Students</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach ($branches as $b)
                    <tr>
                        <td><a href="{{ route('branches.show', $b->id) }}" class="font-medium hover:text-brand-700">{{ $b->name }}</a><div class="text-[12px] text-muted">{{ $b->code }}</div></td>
                        <td>{{ $b->tenant?->library_name }}</td>
                        <td>{{ $b->city }}</td>
                        <td class="text-right">{{ $b->halls_count }}</td>
                        <td class="text-right">{{ $b->seats_count }}</td>
                        <td class="text-right">{{ $b->students_count }}</td>
                        <td><x-badge :status="$b->status" /></td>
                        <td class="text-right"><x-row-actions :view="route('branches.show', $b->id)" :edit="route('branches.edit', $b->id)" :delete="route('branches.destroy', $b->id)" delete-label="Archive" :label="$b->name.' branch'" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No branches match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="branches" :size="8" />
</div>
@endsection
