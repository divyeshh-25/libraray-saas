@extends('layouts.app')
@section('title', 'Hall details')
@section('content')
@php
    $cols = $hall->cols ?? 10;
@endphp
<x-page-header :title="$hall->name" :subtitle="$hall->branch?->tenant?->library_name.' · '.$hall->branch?->name" :back="['Halls', route('halls.index')]">
    @if ($hall->trashed())
        <span class="badge badge-muted">Archived</span>
        @canDo('halls', 'delete')
            <button type="button" class="btn btn-primary" data-restore-url="{{ route('halls.restore', $hall->id) }}" data-redirect="{{ route('halls.index') }}"><x-icon name="restore" class="w-4 h-4" /> Restore</button>
        @endcanDo
    @else
        @canDo('halls', 'update')
            <a href="{{ route('halls.edit', $hall->id) }}" class="btn btn-primary">Edit hall</a>
        @endcanDo
    @endif
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat label="Total seats" :value="$seats" />
    <x-stat label="Occupied" :value="0" />
    <x-stat label="Available" :value="$seats" />
    <x-stat label="Floors" :value="$hall->floors" />
</div>

<div class="card mt-4">
    <div class="card-head">
        <div class="card-title">Seat map</div>
        <div class="flex items-center gap-4 text-[12.5px] text-muted">
            <span class="flex items-center gap-1.5"><i class="w-3 h-3 rounded-sm bg-slate-200 inline-block"></i> Occupied</span>
            <span class="flex items-center gap-1.5"><i class="w-3 h-3 rounded-sm border border-slate-300 bg-white inline-block"></i> Available</span>
            <span class="flex items-center gap-1.5"><i class="w-3 h-3 rounded-sm bg-brand-600 inline-block"></i> Selected</span>
        </div>
    </div>
    <div class="card-body overflow-x-auto">
        @if ($seats > 0)
            <div class="inline-grid gap-1.5" style="grid-template-columns: repeat({{ $cols }}, 2.25rem)">
                @for ($i = 1; $i <= $seats; $i++)
                    <button type="button" data-seat="free"
                        class="h-9 rounded text-[11px] bg-white border border-slate-300 hover:border-brand-500 cursor-pointer">{{ $i }}</button>
                @endfor
            </div>
        @else
            <p class="text-muted text-[13px] py-6 text-center">No seat layout configured yet. Set rows and columns on the hall's Layout to generate the seat map, or use the floor plan designer in a later phase.</p>
        @endif
    </div>
    <div class="px-5 py-3 border-t border-line flex items-center justify-between">
        <span class="text-[12.5px] text-muted" data-seat-summary>Click an available seat to select it</span>
        <button class="btn btn-sm btn-primary" data-toast="Seat allocation opens in the student flow (demo)">Allocate to student</button>
    </div>
</div>
@endsection
