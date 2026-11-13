@extends('layouts.app')
@section('title', 'Edit profile')
@section('content')
<x-page-header title="Edit profile" subtitle="Your personal details and sign-in settings" :back="['Dashboard', route('dashboard')]" />

<form class="space-y-4 max-w-3xl" onsubmit="return false">
    <div class="card">
        <div class="card-head"><div class="card-title">Personal information</div></div>
        <div class="card-body space-y-4">
            <div class="flex items-center gap-4">
                <span class="w-14 h-14 rounded-full bg-slate-800 text-white grid place-items-center text-[16px] font-medium">DP</span>
                <button type="button" class="btn btn-sm" data-toast="Upload is disabled in this preview">Change photo</button>
                <span class="hint !mt-0">JPG or PNG, up to 2 MB.</span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <x-field label="Full name" name="name" value="Divyesh Panchal" />
                <x-field label="Role" name="role" value="Super Admin" hint="Assigned by an administrator." />
                <x-field label="Email" name="email" type="email" value="divyesh@DivyeshCodes.test" />
                <x-field label="Phone" name="phone" value="98250 11234" />
                <x-field label="Language" name="lang" :options="['English', 'ગુજરાતી', 'हिन्दी']" />
                <x-field label="Timezone" name="tz" :options="['Asia/Kolkata (UTC+05:30)', 'UTC']" />
            </div>
        </div>
    </div>

    <div class="card" id="password">
        <div class="card-head"><div class="card-title">Change password</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <div class="sm:col-span-2"><x-password label="Current password" name="current_password" /></div>
            <x-password label="New password" name="password" />
            <x-password label="Confirm new password" name="password_confirmation" />
        </div>
    </div>

    <x-form-actions :cancel="route('dashboard')" />
</form>
@endsection
