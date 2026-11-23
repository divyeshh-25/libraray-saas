@extends('layouts.guest')
@section('title', 'Reset password')
@section('robots', 'noindex, nofollow')
@section('content')
<h1 class="text-[22px] font-semibold tracking-tight">Set a new password</h1>
<p class="text-muted mt-1">Choose a strong password you haven’t used before.</p>

@error('email')
    <div class="mt-6 rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">{{ $message }}</div>
@enderror

<form class="mt-7 space-y-4" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <x-field label="Email" name="email" type="email" value="{{ $email }}" required />
    <x-password label="New password" />
    <x-password label="Confirm new password" name="password_confirmation" />
    <ul class="text-[12.5px] text-muted space-y-1 list-disc pl-4">
        <li>At least 8 characters</li>
    </ul>
    <button class="btn btn-primary w-full !h-10">Reset password</button>
</form>
<p class="mt-6 text-center"><a href="{{ route('login') }}" class="text-brand-700 hover:underline">← Back to sign in</a></p>
@endsection
