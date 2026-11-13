@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit hall' : 'Add hall')
@section('content')
@php $h = $mode === 'edit' ? \App\Support\Mock::hall((int) $id) : null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit hall' : 'Add hall'" :back="['Halls', route('halls.index')]" />
<form class="space-y-4 max-w-4xl" onsubmit="return false">
    <div class="card">
        <div class="card-head"><div class="card-title">Hall details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Branch" name="branch" :options="['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11', 'Bopal']" :value="$h['branch'] ?? ''" />
            <x-field label="Status" name="status" :options="['Active', 'Maintenance', 'Inactive']" :value="ucfirst($h['status'] ?? 'active')" />
            <x-field label="Hall name" name="name" :value="$h['name'] ?? ''" placeholder="e.g. Silent Hall A" span="sm:col-span-2" />
            <x-field label="Description" name="description" type="textarea" span="sm:col-span-2" placeholder="Short note shown to staff, e.g. AC, no phone calls" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Layout</div></div>
        <div class="card-body grid sm:grid-cols-3 gap-4">
            <x-field label="Floors" name="floors" type="number" :value="$h['floors'] ?? 1" />
            <x-field label="Rows" name="rows" type="number" value="6" />
            <x-field label="Seats per row" name="cols" type="number" value="10" hint="Fine-tune seats later in the floor plan designer." />
        </div>
    </div>
    <x-form-actions :cancel="route('halls.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create hall'" />
</form>
@endsection
