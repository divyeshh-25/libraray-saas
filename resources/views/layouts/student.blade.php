<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') · LMS</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
<div class="min-h-screen max-w-xl mx-auto">
    <header class="sticky top-0 z-10 bg-white border-b border-line px-4 h-14 flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <span class="w-7 h-7 rounded-md bg-gradient-to-br from-brand-500 to-brand-700 text-white grid place-items-center text-[13px] font-semibold">W</span>
            <span class="font-semibold text-[15px]">Student Portal</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('student.profile.edit') }}" class="text-muted hover:text-brand-700" title="Edit profile"><x-icon name="users" class="w-5 h-5" /></a>
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button class="text-muted hover:text-red-600" title="Sign out"><x-icon name="login" class="w-5 h-5" /></button>
            </form>
        </div>
    </header>
    <main class="p-4 space-y-4 pb-10">
        @yield('content')
    </main>
</div>
</body>
</html>
