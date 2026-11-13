@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit tenant' : 'Add tenant')
@section('content')
@php
    $t = $tenant ?? null;
    $activePlan = $t?->activePlan;
@endphp
<x-page-header :title="$mode === 'edit' ? 'Edit tenant' : 'Add tenant'" :subtitle="$mode === 'edit' ? $t->library_name : 'Register a new library on the platform'" :back="['Tenants', route('tenants.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="space-y-4" method="POST" action="{{ $mode === 'edit' ? route('tenants.update', $t->id) : route('tenants.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Library details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Library name" name="library_name" :value="$t->library_name ?? ''" placeholder="e.g. Gyan Deep Library" span="sm:col-span-2" required />
            <x-field label="Owner name" name="owner_name" :value="$t->owner_name ?? ''" required />
            <x-field label="Status" name="status" :options="['Active', 'Trial', 'Suspended']" :value="$t ? ucfirst($t->status) : ''" required />
            <x-field label="Email" name="email" type="email" :value="$t->email ?? ''" required />
            <x-field label="Phone" name="phone" :value="$t->phone ?? ''" />
            <x-field label="City" name="city" :value="$t->city ?? ''" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" :value="$t->address ?? ''" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Subscription</div></div>
        <div class="card-body grid sm:grid-cols-3 gap-4">
            <x-field label="Plan" name="plan" :options="$plans" :value="$activePlan?->plan?->name ?? ''" required />
            <x-field label="Starts on" name="starts_at" type="date" :value="$activePlan?->starts_at?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
            <x-field label="Expires on" name="expires_at" type="date" :value="$activePlan?->expires_at?->format('Y-m-d') ?? now()->addDays(14)->format('Y-m-d')" hint="Trials default to 14 days." />
        </div>
    </div>
    @if ($mode === 'create')
    <div class="card">
        <div class="card-head"><div class="card-title">Owner login</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Temporary password" name="password" type="password" hint="The owner will be asked to change it on first sign-in." required />
            <x-field label="Confirm password" name="password_confirmation" type="password" required />
        </div>
    </div>
    @endif
    <x-form-actions :cancel="route('tenants.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create tenant'" submit />
</form>
@endsection
