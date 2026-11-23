@extends('layouts.guest')
@section('title', 'Sign in')
@section('meta_description', 'Sign in to your LibNextGen library, staff or student account to manage branches, seats, fees and attendance.')
@section('content')
<h1 class="text-[22px] font-semibold tracking-tight">Welcome back</h1>
<p class="text-muted mt-1">Sign in to your library, staff or student account.</p>

@if (session('status'))
    <div class="mt-6 rounded-md bg-emerald-50 text-emerald-800 px-3.5 py-3 text-[13px]">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mt-6 rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        {{ $errors->first() }}
    </div>
@endif

<form id="login-form" class="mt-7 space-y-4" method="POST" action="{{ route('login') }}">
    @csrf
    <x-field label="Email" name="email" type="email" placeholder="you@company.com" required />
    <x-password />
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="remember" class="accent-brand-600 w-4 h-4"> Remember me</label>
        <a href="{{ route('password.request') }}" class="text-brand-700 hover:underline">Forgot password?</a>
    </div>
    <button class="btn btn-primary w-full !h-10">Sign in</button>
</form>

@php
    $demoAccounts = [
        ['Library Admin', 'owner@demolibrary.test',],
        ['Librarian', 'main.librarian@demolibrary.test',],
        ['Student', 'main.student001@demolibrary.test',],
    ];
@endphp
<div class="mt-6 rounded-lg border border-line bg-slate-50 p-4">
    <div class="text-[12.5px] font-medium">Demo accounts <span class="text-muted font-normal">· password: <code>password</code></span></div>
    <div class="mt-3 space-y-2">
        @foreach ($demoAccounts as [$label, $email])
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0"><div class="text-[13px] font-medium">{{ $label }}</div><div class="text-[12px] text-muted truncate">{{ $email }}</div></div>
                <button type="button" class="btn btn-sm shrink-0" data-demo-login data-email="{{ $email }}" data-password="password">Copy</button>
            </div>
        @endforeach
    </div>
</div>
<script>
    document.querySelectorAll('[data-demo-login]').forEach((btn) => btn.addEventListener('click', () => {
        const form = document.getElementById('login-form');
        form.querySelector('[name=email]').value = btn.dataset.email;
        form.querySelector('[name=password]').value = btn.dataset.password;
        const orig = btn.textContent;
        btn.textContent = 'Filled ✓';
        setTimeout(() => (btn.textContent = orig), 1200);
    }));
</script>

<p class="mt-6 text-center text-muted">New to Wepro? <a href="{{ route('register') }}" class="text-brand-700 hover:underline">Create an account</a></p>
@endsection
