@extends('layouts.guest')
@section('title', 'Admin Sign in')
@section('robots', 'noindex, nofollow')
@section('content')
<h1 class="text-[22px] font-semibold tracking-tight">SaaS Admin</h1>
<p class="text-muted mt-1">Sign in to the platform admin panel.</p>

@if ($errors->any())
    <div class="mt-6 rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        {{ $errors->first() }}
    </div>
@endif

<form class="mt-7 space-y-4" method="POST" action="{{ route('admin.login') }}">
    @csrf
    <x-field label="Email" name="email" type="email" placeholder="admin@company.com" required />
    <x-password />
    <button class="btn btn-primary w-full !h-10">Sign in</button>
</form>
@endsection
