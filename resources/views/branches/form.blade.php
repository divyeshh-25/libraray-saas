@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit branch' : 'Add branch')
@section('content')
@php $b = $branch ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit branch' : 'Add branch'" :subtitle="$b ? $b->tenant?->library_name.' · '.$b->name : null" :back="['Branches', route('branches.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] max-w-4xl">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="space-y-4 max-w-4xl" method="POST" action="{{ $mode === 'edit' ? route('branches.update', $b->id) : route('branches.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Branch details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Library" name="tenant" :options="$tenants" :value="$b?->tenant?->library_name ?? ''" required />
            <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="$b ? ucfirst($b->status) : ''" required />
            <x-field label="Branch name" name="name" :value="$b->name ?? ''" required />
            <x-field label="Branch code" name="code" :value="$b->code ?? ''" hint="Short unique code used in student IDs and invoices." required />
            <x-field label="Phone" name="phone" :value="$b->phone ?? ''" />
            <x-field label="City" name="city" :value="$b->city ?? ''" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" :value="$b->address ?? ''" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Operating hours</div></div>
        <div class="card-body grid sm:grid-cols-3 gap-4">
            <x-field label="Opens at" name="opens" type="time" :value="$b?->opens_at ? substr($b->opens_at, 0, 5) : '06:00'" />
            <x-field label="Closes at" name="closes" type="time" :value="$b?->closes_at ? substr($b->closes_at, 0, 5) : '23:00'" />
            <x-field label="Weekly off" name="off" :options="['None', 'Sunday', 'Saturday']" :value="$b->weekly_off ?? 'None'" />
        </div>
    </div>
    <x-form-actions :cancel="route('branches.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create branch'" submit />
</form>
@endsection
