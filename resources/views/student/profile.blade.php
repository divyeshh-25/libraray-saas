@extends('layouts.student')
@section('title', 'Edit profile')
@section('content')
@php $u = $student->user; @endphp

<div class="flex items-center gap-2">
    <a href="{{ route('student.dashboard') }}" class="text-muted hover:text-brand-700"><x-icon name="chevron" class="w-4 h-4 rotate-180" /></a>
    <h1 class="font-semibold text-[16px]">Edit profile</h1>
</div>

@if (session('success'))
    <div class="rounded-md bg-emerald-50 text-emerald-800 px-3.5 py-3 text-[13px]">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-head"><div class="card-title">Personal information</div></div>
        <div class="card-body space-y-4">
            <x-field label="Full name" name="name" :value="$u->name" required />
            <x-field label="Email" name="email" :value="$u->email" hint="Contact your branch to change your email." />
            <x-field label="Phone" name="phone" :value="$u->phone" />
            <x-field label="Guardian name" name="guardian_name" :value="$student->guardian_name" />
            <x-field label="Guardian phone" name="guardian_phone" :value="$student->guardian_phone" />
            <x-field label="Address" name="address" type="textarea" :value="$student->address" />
        </div>
    </div>
    <button class="btn btn-primary w-full !h-11">Save changes</button>
</form>

<form method="POST" action="{{ route('student.profile.password') }}" class="space-y-4">
    @csrf
    @method('PUT')
    <div class="card">
        <div class="card-head"><div class="card-title">Change password</div></div>
        <div class="card-body space-y-4">
            <x-password label="Current password" name="current_password" />
            <x-password label="New password" name="password" />
            <x-password label="Confirm new password" name="password_confirmation" />
        </div>
    </div>
    <button class="btn w-full !h-11">Update password</button>
</form>
@endsection
