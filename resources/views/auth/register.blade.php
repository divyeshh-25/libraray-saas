@extends('layouts.guest')
@section('title', 'Create account')
@section('meta_description', 'Start your 14-day free trial of LibNextGen — library management software with admissions, floor planning, fee collection and attendance. No card required.')
@section('content')
<h1 class="text-[22px] font-semibold tracking-tight">Create your account</h1>
<p class="text-muted mt-1">Start a 14-day free trial. No card required.</p>

@if ($errors->any())
    <div class="mt-6 rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="mt-7 space-y-4" method="POST" action="{{ route('register') }}">
    @csrf
    <x-field label="Full name" name="name" placeholder="Anand Verma" required />
    <x-field label="Library name" name="library" placeholder="Gyan Deep Library" required />
    <div class="grid sm:grid-cols-2 gap-4">
        <x-field label="Email" name="email" type="email" required />
        <x-field label="Phone" name="phone" />
    </div>
    <x-password hint="" />
    <x-password label="Confirm password" name="password_confirmation" />
    <label class="flex items-start gap-2 cursor-pointer text-[13px]"><input type="checkbox" required class="accent-brand-600 w-4 h-4 mt-0.5"> <span>I agree to the <a href="#" class="text-brand-700 hover:underline">Terms</a> and <a href="#" class="text-brand-700 hover:underline">Privacy Policy</a>.</span></label>
    <button class="btn btn-primary w-full !h-10">Create account</button>
</form>
<p class="mt-6 text-center text-muted">Already registered? <a href="{{ route('login') }}" class="text-brand-700 hover:underline">Sign in</a></p>
@endsection
