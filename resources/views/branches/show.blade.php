@extends('layouts.app')
@section('title', 'Branch details')
@section('content')
@php
    use App\Support\Mock;
    $b = Mock::branch((int) $id);
    $halls = collect(Mock::halls())->take(4);
    $occ = round($b['students'] / $b['seats'] * 100);
@endphp
<x-page-header :title="$b['name']" :subtitle="$b['tenant'].' · '.$b['code']" :back="['Branches', route('branches.index')]">
    <a href="{{ route('branches.edit', $b['id']) }}" class="btn btn-primary">Edit branch</a>
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat label="Halls" :value="$b['halls']" />
    <x-stat label="Total seats" :value="$b['seats']" />
    <x-stat label="Students enrolled" :value="$b['students']" />
    <x-stat label="Occupancy" :value="$occ.'%'" />
</div>

<div class="grid lg:grid-cols-3 gap-4 mt-4">
    <div class="card lg:col-span-2">
        <div class="card-head"><div class="card-title">Halls in this branch</div><a href="{{ route('halls.create') }}" class="btn btn-sm">Add hall</a></div>
        <table class="tbl">
            <thead><tr><th>Hall</th><th class="text-right">Seats</th><th class="w-48">Occupancy</th><th>Status</th></tr></thead>
            <tbody>
                @foreach ($halls as $h)
                    @php $p = round($h['occupied'] / $h['seats'] * 100); @endphp
                    <tr>
                        <td><a href="{{ route('halls.show', $h['id']) }}" class="font-medium hover:text-brand-700">{{ $h['name'] }}</a></td>
                        <td class="text-right">{{ $h['seats'] }}</td>
                        <td><div class="flex items-center gap-2"><div class="h-1.5 flex-1 bg-slate-100 rounded-full"><div class="h-full bg-brand-600 rounded-full" style="width: {{ $p }}%"></div></div><span class="text-[12px] text-muted w-8">{{ $p }}%</span></div></td>
                        <td><x-badge :status="$h['status']" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Contact</div><x-badge :status="$b['status']" /></div>
        <dl class="p-5 space-y-3 text-[13px]">
            @foreach ([['Library', $b['tenant']], ['City', $b['city']], ['Phone', $b['phone']], ['Hours', '6:00 AM – 11:00 PM'], ['Manager', 'Ritesh Parmar']] as [$k, $v])
                <div class="flex justify-between gap-4"><dt class="text-muted">{{ $k }}</dt><dd class="text-right font-medium">{{ $v }}</dd></div>
            @endforeach
        </dl>
    </div>
</div>
@endsection
