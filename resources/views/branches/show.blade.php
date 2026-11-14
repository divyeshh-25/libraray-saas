@extends('layouts.app')
@section('title', 'Branch details')
@section('content')
@php
    $hours = $branch->opens_at && $branch->closes_at
        ? \Carbon\Carbon::parse($branch->opens_at)->format('g:i A').' – '.\Carbon\Carbon::parse($branch->closes_at)->format('g:i A')
        : '—';
@endphp
<x-page-header :title="$branch->name" :subtitle="$branch->tenant?->library_name.' · '.$branch->code" :back="['Branches', route('branches.index')]">
    @if ($branch->trashed())
        <span class="badge badge-muted">Archived</span>
        @canDo('branches', 'delete')
            <button type="button" class="btn btn-primary" data-restore-url="{{ route('branches.restore', $branch->id) }}" data-redirect="{{ route('branches.index') }}"><x-icon name="restore" class="w-4 h-4" /> Restore</button>
        @endcanDo
    @else
        @canDo('branches', 'update')
            <a href="{{ route('branches.edit', $branch->id) }}" class="btn btn-primary">Edit branch</a>
        @endcanDo
    @endif
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat label="Halls" :value="$halls->count()" />
    <x-stat label="Total seats" :value="$seatsCount" />
    <x-stat label="Students enrolled" :value="$branch->students_count" />
    <x-stat label="Occupancy" :value="$occupancy.'%'" />
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2">
        <div class="card-head"><div class="card-title">Halls in this branch</div><a href="{{ route('halls.create') }}" class="btn btn-sm">Add hall</a></div>
        <table class="tbl">
            <thead><tr><th>Hall</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($halls as $h)
                    <tr>
                        <td class="font-medium">{{ $h->name }}</td>
                        <td><x-badge :status="$h->status" /></td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-6">No halls yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Contact</div><x-badge :status="$branch->status" /></div>
        <dl class="p-5 space-y-3 text-[13px]">
            @foreach ([['Library', $branch->tenant?->library_name], ['City', $branch->city], ['Phone', $branch->phone], ['Hours', $hours], ['Weekly off', $branch->weekly_off ?? 'None']] as [$k, $v])
                <div class="flex justify-between gap-4"><dt class="text-muted">{{ $k }}</dt><dd class="text-right font-medium">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>
@endsection
