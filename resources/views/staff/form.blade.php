@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit staff' : 'Add staff')
@section('content')
@php $s = $staff ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit staff member' : 'Add staff member'" :subtitle="$s->user->name ?? null" :back="['Staff', route('staff.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] max-w-4xl">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="space-y-4 max-w-4xl" method="POST" action="{{ $mode === 'edit' ? route('staff.update', $s->id) : route('staff.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Personal information</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Full name" name="name" :value="$s->user->name ?? ''" required />
            <x-field label="Employee code" name="code" :value="$s->employee_code ?? ''" />
            <x-field label="Email" name="email" type="email" :value="$s->user->email ?? ''" required />
            <x-field label="Phone" name="phone" :value="$s->user->phone ?? ''" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" :value="$s->address ?? ''" />
        </div>
    </div>
    @if ($mode === 'create')
    <div class="card">
        <div class="card-head"><div class="card-title">Login</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Password" name="password" type="password" required />
            <x-field label="Confirm password" name="password_confirmation" type="password" required />
        </div>
    </div>
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Employment</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Branch" name="branch" :options="$branches" :value="$s?->user->branch ? $s->user->branch->name.' ('.$s->user->branch->code.')' : ''" required />
            <x-field label="Designation" name="designation" :value="$s->designation ?? ''" />
            <x-field label="Role" name="role" :options="$roles" :value="$s?->roles->first()?->name ?? ''" hint="Controls what this person can see and change." required />
            <x-field label="Joining date" name="joined" type="date" :value="$s?->joining_date?->format('Y-m-d') ?? now()->format('Y-m-d')" />
            <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="$s ? ucfirst($s->status) : 'Active'" required />
        </div>
    </div>
    <x-form-actions :cancel="route('staff.index')" :save="$mode === 'edit' ? 'Save changes' : 'Add staff'" submit />
</form>
@endsection
