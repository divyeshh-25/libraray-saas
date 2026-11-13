@extends('layouts.guest')
@section('title', 'Sign in')
@section('content')
<h1 class="text-[22px] font-semibold tracking-tight">Welcome back</h1>
<p class="text-muted mt-1">Sign in to your admin account.</p>

@if (session('status'))
    <div class="mt-6 rounded-md bg-emerald-50 text-emerald-800 px-3.5 py-3 text-[13px]">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mt-6 rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        {{ $errors->first() }}
    </div>
@endif

<form class="mt-7 space-y-4" method="POST" action="{{ route('login') }}">
    @csrf
    <x-field label="Email" name="email" type="email" placeholder="you@company.com" required />
    <x-password />
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="remember" class="accent-brand-600 w-4 h-4"> Remember me</label>
        <a href="{{ route('password.request') }}" class="text-brand-700 hover:underline">Forgot password?</a>
    </div>
    <button class="btn btn-primary w-full !h-10">Sign in</button>
</form>
<p class="mt-6 text-center text-muted">New to Wepro? <a href="{{ route('register') }}" class="text-brand-700 hover:underline">Create an account</a></p>
@endsection
