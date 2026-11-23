@extends('layouts.app')
@section('title', 'Edit profile')
@section('content')
@php
    $initials = collect(explode(' ', trim($principal->name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<x-page-header title="Edit profile" subtitle="Your personal details and sign-in settings" :back="['Dashboard', route('dashboard')]" />

@if (session('success'))
    <div class="rounded-md bg-emerald-50 text-emerald-800 px-3.5 py-3 text-[13px] max-w-3xl">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] max-w-3xl">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('profile.update') }}" class="space-y-4 max-w-3xl">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-head"><div class="card-title">Personal information</div></div>
        <div class="card-body space-y-4">
            <div class="flex items-center gap-4">
                <span class="w-14 h-14 rounded-full bg-slate-800 text-white grid place-items-center text-[16px] font-medium">{{ $initials ?: '—' }}</span>
                <button type="button" class="btn btn-sm" disabled title="Photo upload isn't available yet">Change photo</button>
                <span class="hint !mt-0">JPG or PNG, up to 2 MB.</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-field label="Full name" name="name" :value="$principal->name" required />
                <div>
                    <label class="label">Role</label>
                    <p class="input !bg-slate-50 !cursor-default flex items-center">{{ $roleLabel }}</p>
                    <p class="hint">Assigned by an administrator.</p>
                </div>
                <x-field label="Email" name="email_display" :value="$principal->email" hint="Contact an administrator to change your email." />
                @if (! $isAdmin)
                    <x-field label="Phone" name="phone" :value="$principal->phone" />
                @endif
            </div>
        </div>
    </div>

    <x-form-actions :cancel="route('dashboard')" submit />
</form>

<div class="card max-w-3xl mt-4">
    <div class="card-head"><div class="card-title">Security</div></div>
    <div class="card-body flex items-center justify-between gap-4">
        <div>
            <p class="font-medium text-[13.5px]">Password</p>
            <p class="hint !mt-0.5">Change the password used to sign in to your account.</p>
        </div>
        <a href="{{ route('profile.password.edit') }}" class="btn btn-sm">Change password</a>
    </div>
</div>
@endsection
