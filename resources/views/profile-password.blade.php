@extends('layouts.app')
@section('title', 'Change password')
@section('content')
<x-page-header title="Change password" subtitle="Update the password used to sign in to your account" :back="['Edit profile', route('profile')]" />

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

<form method="POST" action="{{ route('profile.password') }}" class="space-y-4 max-w-3xl">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-head"><div class="card-title">Change password</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><x-password label="Current password" name="current_password" /></div>
            <x-password label="New password" name="password" />
            <x-password label="Confirm new password" name="password_confirmation" />
        </div>
    </div>

    <x-form-actions :cancel="route('profile')" save="Update password" submit />
</form>
@endsection
