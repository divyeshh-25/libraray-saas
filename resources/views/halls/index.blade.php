@extends('layouts.app')
@section('title', 'Halls')
@section('content')
<x-page-header title="Halls" subtitle="Reading halls, cabins and discussion rooms">
    <a href="{{ route('halls.archived') }}" class="btn" title="Archived halls"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    <a href="#" class="btn" data-toast="Opens the floor plan designer in a later phase">Floor plan designer</a>
    <a href="{{ route('halls.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add hall</a>
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
                    @php $seats = ($h->rows ?? 0) * ($h->cols ?? 0); @endphp
                    <tr>
                        <td><a href="{{ route('halls.show', $h->id) }}" class="font-medium hover:text-brand-700">{{ $h->name }}</a><div class="text-[12px] text-muted">{{ $h->branch?->tenant?->library_name }}</div></td>
                        <td>{{ $h->branch?->name }}</td>
                        <td class="text-right">{{ $h->floors }}</td>
                        <td class="text-right">{{ $seats }}</td>
                        <td><div class="flex items-center gap-2"><div class="h-1.5 flex-1 bg-slate-100 rounded-full"><div class="h-full rounded-full bg-brand-600" style="width: 0%"></div></div><span class="text-[12px] text-muted w-8">0%</span></div></td>
                        <td><x-badge :status="$h->status" /></td>
                        <td class="text-right"><x-row-actions :view="route('halls.show', $h->id)" :edit="route('halls.edit', $h->id)" :delete="route('halls.destroy', $h->id)" delete-label="Archive" :label="$h->name" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No halls match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="halls" :size="8" />
</div>
@endsection
