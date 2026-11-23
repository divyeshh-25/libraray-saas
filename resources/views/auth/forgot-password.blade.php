@extends('layouts.guest')
@section('title', 'Forgot password')
@section('meta_description', 'Reset the password for your LibNextGen account.')
@section('robots', 'noindex, nofollow')
@section('content')
<h1 class="text-[22px] font-semibold tracking-tight">Forgot your password?</h1>
<p class="text-muted mt-1">Enter your email and we’ll send you a reset link.</p>

@if (session('status'))
    <div class="mt-6 rounded-md bg-emerald-50 text-emerald-800 px-3.5 py-3 text-[13px]">{{ session('status') }}</div>
@endif

<form class="mt-7 space-y-4" method="POST" action="{{ route('password.email') }}">
    @csrf
    <x-field label="Email" name="email" type="email" placeholder="you@company.com" required />
    <button class="btn btn-primary w-full !h-10">Send reset link</button>
</form>
<p class="mt-6 text-center"><a href="{{ route('login') }}" class="text-brand-700 hover:underline">← Back to sign in</a></p>
@endsection
