@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit plan' : 'Add plan')
@section('content')
@php
    $p = $mode === 'edit' ? \App\Support\Mock::plan((int) $id) : null;
    $featureList = ['Seat allocation', 'Fee invoices & receipts', 'Attendance tracking', 'Floor plan designer', 'Custom roles & permissions', 'SMS / WhatsApp reminders', 'Priority support'];
@endphp
<x-page-header :title="$mode === 'edit' ? 'Edit plan' : 'Add plan'" :back="['Plans', route('plans.index')]" />

<form class="grid lg:grid-cols-3 gap-4 items-start" onsubmit="return false">
    <div class="lg:col-span-2 space-y-4">
        <div class="card">
            <div class="card-head"><div class="card-title">Plan details</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Plan name" name="name" :value="$p['name'] ?? ''" placeholder="e.g. Growth" />
                <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="ucfirst($p['status'] ?? 'active')" />
                <x-field label="Price (₹)" name="price" type="number" :value="$p['price'] ?? ''" />
                <x-field label="Billing cycle" name="cycle" :options="['Monthly', 'Quarterly', 'Yearly']" :value="$p['cycle'] ?? 'Monthly'" />
            </div>
        </div>
        <div class="card">
            <div class="card-head"><div class="card-title">Limits</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Maximum branches" name="max_branches" type="number" :value="$p['branches'] ?? ''" />
                <x-field label="Maximum students" name="max_students" type="number" :value="$p['students'] ?? ''" />
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Included features</div></div>
        <div class="card-body space-y-2.5">
            @foreach ($featureList as $i => $f)
                <label class="flex items-center gap-2.5 cursor-pointer"><input type="checkbox" class="accent-brand-600 w-4 h-4" @checked($i < 3 || ($p && $p['id'] > 1 && $i < 5))> {{ $f }}</label>
            @endforeach
        </div>
    </div>
    <div class="lg:col-span-3"><x-form-actions :cancel="route('plans.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create plan'" /></div>
</form>
@endsection
