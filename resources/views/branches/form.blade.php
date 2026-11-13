@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit branch' : 'Add branch')
@section('content')
@php $b = $mode === 'edit' ? \App\Support\Mock::branch((int) $id) : null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit branch' : 'Add branch'" :subtitle="$b ? $b['tenant'].' · '.$b['name'] : null" :back="['Branches', route('branches.index')]" />
<form class="space-y-4 max-w-4xl" onsubmit="return false">
    <div class="card">
        <div class="card-head"><div class="card-title">Branch details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Library" name="tenant" :options="['Gyan Deep Library', 'Saraswati Study Hub', 'Scholars Corner', 'Focus Study Zone', 'Knowledge Point']" :value="$b['tenant'] ?? ''" />
            <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="ucfirst($b['status'] ?? 'active')" />
            <x-field label="Branch name" name="name" :value="$b['name'] ?? ''" />
            <x-field label="Branch code" name="code" :value="$b['code'] ?? ''" hint="Short unique code used in student IDs and invoices." />
            <x-field label="Phone" name="phone" :value="$b['phone'] ?? ''" />
            <x-field label="City" name="city" :value="$b['city'] ?? ''" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Operating hours</div></div>
        <div class="card-body grid sm:grid-cols-3 gap-4">
            <x-field label="Opens at" name="opens" type="time" value="06:00" />
            <x-field label="Closes at" name="closes" type="time" value="23:00" />
            <x-field label="Weekly off" name="off" :options="['None', 'Sunday', 'Saturday']" value="None" />
        </div>
    </div>
    <x-form-actions :cancel="route('branches.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create branch'" />
</form>
@endsection
