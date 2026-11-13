@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit staff' : 'Add staff')
@section('content')
@php use App\Support\Mock; $s = $mode === 'edit' ? Mock::staffMember((int) $id) : null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit staff member' : 'Add staff member'" :subtitle="$s['name'] ?? null" :back="['Staff', route('staff.index')]" />
<form class="space-y-4 max-w-4xl" onsubmit="return false">
    <div class="card">
        <div class="card-head"><div class="card-title">Personal information</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Full name" name="name" :value="$s['name'] ?? ''" />
            <x-field label="Employee code" name="code" :value="$s['code'] ?? 'EMP-0111'" />
            <x-field label="Email" name="email" type="email" :value="$s['email'] ?? ''" />
            <x-field label="Phone" name="phone" :value="$s['phone'] ?? ''" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Employment</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Branch" name="branch" :options="['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11', 'Bopal']" :value="$s['branch'] ?? ''" />
            <x-field label="Designation" name="designation" :value="$s['designation'] ?? ''" />
            <x-field label="Role" name="role" :options="array_column(Mock::roles(), 'name')" :value="$s['role'] ?? 'Receptionist'" hint="Controls what this person can see and change." />
            <x-field label="Joining date" name="joined" type="date" value="2026-11-13" />
            <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="ucfirst($s['status'] ?? 'active')" />
        </div>
    </div>
    <x-form-actions :cancel="route('staff.index')" :save="$mode === 'edit' ? 'Save changes' : 'Add staff'" />
</form>
@endsection
