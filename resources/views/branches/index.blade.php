@extends('layouts.app')
@section('title', 'Branches')
@section('content')
@php use App\Support\Mock; @endphp
<x-page-header title="Branches" subtitle="Physical locations run by each library">
    <a href="{{ route('branches.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add branch</a>
</x-page-header>
<div class="card">
    <x-toolbar target="branches" placeholder="Search by branch, code or library" :filters="[
        'All libraries' => ['col' => 1, 'options' => ['Gyan Deep Library', 'Saraswati Study Hub', 'Scholars Corner', 'Focus Study Zone', 'Knowledge Point']],
        'All cities' => ['col' => 2, 'options' => ['Ahmedabad', 'Surat', 'Vadodara', 'Gandhinagar', 'Rajkot']],
        'All statuses' => ['col' => 6, 'options' => ['Active', 'Inactive']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="branches">
            <thead><tr><th>Branch</th><th>Library</th><th>City</th><th class="text-right">Halls</th><th class="text-right">Seats</th><th class="text-right">Students</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach (Mock::branches() as $b)
                    <tr>
                        <td><a href="{{ route('branches.show', $b['id']) }}" class="font-medium hover:text-brand-700">{{ $b['name'] }}</a><div class="text-[12px] text-muted">{{ $b['code'] }}</div></td>
                        <td>{{ $b['tenant'] }}</td>
                        <td>{{ $b['city'] }}</td>
                        <td class="text-right">{{ $b['halls'] }}</td>
                        <td class="text-right">{{ $b['seats'] }}</td>
                        <td class="text-right">{{ $b['students'] }}</td>
                        <td><x-badge :status="$b['status']" /></td>
                        <td class="text-right"><x-row-actions :view="route('branches.show', $b['id'])" :edit="route('branches.edit', $b['id'])" :label="$b['name'].' branch'" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="8" message="No branches match your filters." />
            </tbody>
        </table>
    </div>
    <x-pager target="branches" :size="8" />
</div>
@endsection
