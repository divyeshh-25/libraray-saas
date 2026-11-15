@extends('layouts.app')
@section('title', 'Branches')
@section('content')
<x-page-header title="Branches" subtitle="Physical locations run by each library">
    @canDo('branches', 'delete')
        <a href="{{ route('branches.archived') }}" class="btn" title="Archived branches"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    @endcanDo
    @canDo('branches', 'add')
        <a href="{{ route('branches.create') }}" data-offcanvas-url="{{ route('branches.create') }}" data-offcanvas-title="Add branch" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add branch</a>
    @endcanDo
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
                        <td class="text-right"><x-row-actions :view="route('branches.show', $b->id)" :edit="viewer_can('branches', 'update') ? route('branches.edit', $b->id) : null" edit-offcanvas edit-title="Edit branch" :delete="viewer_can('branches', 'delete') ? route('branches.destroy', $b->id) : null" delete-label="Archive" :label="$b->name.' branch'" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No branches match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="branches" :size="8" />
</div>

<div id="branch-offcanvas" class="fixed inset-0 z-[55] hidden" data-offcanvas data-offcanvas-table="branches">
    <a href="#" class="absolute inset-0 bg-slate-900/40 opacity-0 transition-opacity duration-200" data-offcanvas-backdrop data-offcanvas-close aria-label="Close"></a>
    <div class="absolute inset-y-0 right-0 w-full sm:max-w-lg bg-white shadow-xl flex flex-col translate-x-full transition-transform duration-200" data-offcanvas-panel>
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-line shrink-0">
            <div class="text-[15px] font-semibold" data-offcanvas-title>Add branch</div>
            <button type="button" class="btn btn-sm !px-2" data-offcanvas-close aria-label="Close"><x-icon name="x" class="w-4 h-4" /></button>
        </div>
        <div class="flex-1 flex flex-col overflow-hidden" data-offcanvas-body></div>
    </div>
</div>
@endsection
