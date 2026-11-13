@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit plan' : 'Add plan')
@section('content')
@php
    $p = $plan ?? null;
    $featureList = ['Seat allocation', 'Fee invoices & receipts', 'Attendance tracking', 'Floor plan designer', 'Custom roles & permissions', 'SMS / WhatsApp reminders', 'Priority support'];
    $selectedFeatures = old('features', $p->features ?? []);
@endphp
<x-page-header :title="$mode === 'edit' ? 'Edit plan' : 'Add plan'" :back="['Plans', route('plans.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] mb-4">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="grid lg:grid-cols-3 gap-4 items-start" method="POST" action="{{ $mode === 'edit' ? route('plans.update', $p->id) : route('plans.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Plan details</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Plan name" name="name" :value="$p->name ?? ''" placeholder="e.g. Growth" required />
                <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="$p ? ucfirst($p->status) : ''" required />
                <x-field label="Price (₹)" name="price" type="number" :value="$p->price ?? ''" required />
                <x-field label="Billing cycle" name="cycle" :options="['Monthly', 'Quarterly', 'Yearly']" :value="$p ? ucfirst($p->billing_cycle) : ''" required />
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Limits</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Maximum branches" name="max_branches" type="number" :value="$p->max_branches ?? ''" />
                <x-field label="Maximum students" name="max_students" type="number" :value="$p->max_students ?? ''" />
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Included features</div></div>
        <div class="card-body space-y-2.5">
            @foreach ($featureList as $f)
                <label class="flex items-center gap-2.5 cursor-pointer"><input type="checkbox" name="features[]" value="{{ $f }}" class="accent-brand-600 w-4 h-4" @checked(in_array($f, $selectedFeatures))> {{ $f }}</label>
            @endforeach
        </div>
    </div>
    <div class="lg:col-span-3"><x-form-actions :cancel="route('plans.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create plan'" submit /></div>
</form>
@endsection
