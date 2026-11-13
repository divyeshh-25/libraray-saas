@extends('layouts.app')
@section('title', 'Halls')
@section('content')
@php use App\Support\Mock; @endphp
<x-page-header title="Halls" subtitle="Reading halls, cabins and discussion rooms">
    <a href="#" class="btn" data-toast="Opens the floor plan designer in a later phase">Floor plan designer</a>
    <a href="{{ route('halls.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add hall</a>
</x-page-header>
<div class="card">
    <x-toolbar target="halls" placeholder="Search halls" :filters="[
        'All branches' => ['col' => 1, 'options' => ['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11']],
        'All statuses' => ['col' => 5, 'options' => ['Active', 'Maintenance']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="halls">
            <thead><tr><th>Hall</th><th>Branch</th><th class="text-right">Floors</th><th class="text-right">Seats</th><th class="w-44">Occupancy</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach (Mock::halls() as $h)
                    @php $p = round($h['occupied'] / $h['seats'] * 100); @endphp
                    <tr>
                        <td><a href="{{ route('halls.show', $h['id']) }}" class="font-medium hover:text-brand-700">{{ $h['name'] }}</a><div class="text-[12px] text-muted">{{ $h['tenant'] }}</div></td>
                        <td>{{ $h['branch'] }}</td>
                        <td class="text-right">{{ $h['floors'] }}</td>
                        <td class="text-right">{{ $h['seats'] }}</td>
                        <td><div class="flex items-center gap-2"><div class="h-1.5 flex-1 bg-slate-100 rounded-full"><div class="h-full rounded-full {{ $p >= 90 ? 'bg-amber-500' : 'bg-brand-600' }}" style="width: {{ $p }}%"></div></div><span class="text-[12px] text-muted w-8">{{ $p }}%</span></div></td>
                        <td><x-badge :status="$h['status']" /></td>
                        <td class="text-right"><x-row-actions :view="route('halls.show', $h['id'])" :edit="route('halls.edit', $h['id'])" :label="$h['name']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No halls match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="halls" :size="8" />
</div>
@endsection
