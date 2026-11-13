@extends('layouts.app')
@section('title', 'Hall details')
@section('content')
@php
    use App\Support\Mock;
    $h = Mock::hall((int) $id);
    $cols = 10; $rows = (int) ceil($h['seats'] / $cols);
    $occupied = $h['occupied'];
    mt_srand($h['id']); $order = range(1, $h['seats']); shuffle($order); $takenSeats = array_flip(array_slice($order, 0, $occupied));
@endphp
<x-page-header :title="$h['name']" :subtitle="$h['tenant'].' · '.$h['branch']" :back="['Halls', route('halls.index')]">
    <a href="{{ route('halls.edit', $h['id']) }}" class="btn btn-primary">Edit hall</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat label="Total seats" :value="$h['seats']" />
    <x-stat label="Occupied" :value="$occupied" />
    <x-stat label="Available" :value="$h['seats'] - $occupied" />
    <x-stat label="Floors" :value="$h['floors']" />
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
        <div class="inline-grid gap-1.5" style="grid-template-columns: repeat({{ $cols }}, 2.25rem)">
            @for ($i = 1; $i <= $h['seats']; $i++)
                @php $taken = isset($takenSeats[$i]); @endphp
                <button type="button" data-seat="{{ $taken ? 'taken' : 'free' }}"
                    class="h-9 rounded text-[11px] {{ $taken ? 'bg-slate-200 text-slate-500 cursor-not-allowed' : 'bg-white border border-slate-300 hover:border-brand-500 cursor-pointer' }}">{{ $i }}</button>
            @endfor
        </div>
    </div>
    <div class="px-5 py-3 border-t border-line flex items-center justify-between">
        <span class="text-[12.5px] text-muted" data-seat-summary>Click an available seat to select it</span>
        <button class="btn btn-sm btn-primary" data-toast="Seat allocation opens in the student flow (demo)">Allocate to student</button>
    </div>
</div>
@endsection
