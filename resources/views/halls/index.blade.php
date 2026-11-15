@extends('layouts.app')
@section('title', 'Halls')
@section('content')
<x-page-header title="Halls" subtitle="Reading halls, cabins and discussion rooms">
    @canDo('halls', 'delete')
        <a href="{{ route('halls.archived') }}" class="btn" title="Archived halls"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    @endcanDo
    @canDo('halls', 'add')
        <a href="{{ route('halls.create') }}" data-offcanvas-url="{{ route('halls.create') }}" data-offcanvas-title="Add hall" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add hall</a>
    @endcanDo
</x-page-header>
<div class="card">
    <x-toolbar target="halls" placeholder="Search halls" :filters="[
        'All branches' => ['col' => 1, 'options' => $branches->all()],
        'All statuses' => ['col' => 5, 'options' => ['Active', 'Maintenance', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="halls">
            <thead><tr><th>Hall</th><th>Branch</th><th class="text-right">Floors</th><th class="text-right">Seats</th><th class="w-44">Occupancy</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach ($halls as $h)
                    @php $seats = $h->seatsCount(); @endphp
                    <tr>
                        <td><a href="{{ route('halls.show', $h->id) }}" class="font-medium hover:text-brand-700">{{ $h->name }}</a><div class="text-[12px] text-muted">{{ $h->branch?->tenant?->library_name }}</div></td>
                        <td>{{ $h->branch?->name }}</td>
                        <td class="text-right">{{ $h->floors }}</td>
                        <td class="text-right">{{ $seats }}</td>
                        <td><div class="flex items-center gap-2"><div class="h-1.5 flex-1 bg-slate-100 rounded-full"><div class="h-full rounded-full bg-brand-600" style="width: 0%"></div></div><span class="text-[12px] text-muted w-8">0%</span></div></td>
                        <td><x-badge :status="$h->status" /></td>
                        <td class="text-right"><x-row-actions :view="route('halls.show', $h->id)" :edit="viewer_can('halls', 'update') ? route('halls.edit', $h->id) : null" edit-offcanvas edit-title="Edit hall" :delete="viewer_can('halls', 'delete') ? route('halls.destroy', $h->id) : null" delete-label="Archive" :label="$h->name" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No halls match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="halls" :size="8" />
</div>

<div id="hall-offcanvas" class="fixed inset-0 z-[55] hidden" data-offcanvas data-offcanvas-table="halls">
    <a href="#" class="absolute inset-0 bg-slate-900/40 opacity-0 transition-opacity duration-200" data-offcanvas-backdrop data-offcanvas-close aria-label="Close"></a>
    <div class="absolute inset-y-0 right-0 w-full sm:max-w-lg bg-white shadow-xl flex flex-col translate-x-full transition-transform duration-200" data-offcanvas-panel>
        <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-line shrink-0">
            <div class="text-[15px] font-semibold" data-offcanvas-title>Add hall</div>
            <button type="button" class="btn btn-sm !px-2" data-offcanvas-close aria-label="Close"><x-icon name="x" class="w-4 h-4" /></button>
        </div>
        <div class="flex-1 flex flex-col overflow-hidden" data-offcanvas-body></div>
    </div>
</div>
@endsection
