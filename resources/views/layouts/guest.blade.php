<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · LMS</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="min-h-screen grid lg:grid-cols-[minmax(0,1fr)_480px]">
    <section class="hidden lg:flex flex-col justify-between bg-slate-900 text-white p-12 relative overflow-hidden bg-[radial-gradient(ellipse_at_bottom_left,#2b44a3_0%,transparent_60%)]">
        <div class="flex items-center gap-2.5">
            <span class="w-8 h-8 rounded-md bg-brand-500 grid place-items-center font-semibold">W</span>
            <span class="font-semibold text-[16px]">LMS</span>
        </div>
        <div class="max-w-md">
            <h2 class="text-[28px] leading-tight font-semibold tracking-tight">Run every library branch from one place.</h2>
            <p class="text-slate-400 mt-3">Admissions, seat allocation, fee collection and attendance for study libraries and reading halls.</p>
            <dl class="mt-10 grid grid-cols-3 gap-6 text-[13px]">
                <div><dt class="text-slate-400">Libraries</dt><dd class="text-[22px] font-semibold mt-1">148</dd></div>
                <div><dt class="text-slate-400">Students</dt><dd class="text-[22px] font-semibold mt-1">12,480</dd></div>
                <div><dt class="text-slate-400">Branches</dt><dd class="text-[22px] font-semibold mt-1">213</dd></div>
            </dl>
        </div>
        <p class="text-[12.5px] text-slate-500">© 2026 DivyeshCodes</p>
    </section>

    <section class="flex items-center justify-center p-6 sm:p-10 bg-white">
        <div class="w-full max-w-sm" style="animation: rise .5s cubic-bezier(.22,1,.36,1) backwards">
            <div class="lg:hidden flex items-center gap-2.5 mb-8">
                <span class="w-8 h-8 rounded-md bg-brand-500 text-white grid place-items-center font-semibold">W</span>
                <span class="font-semibold text-[16px]">LMS</span>
            </div>
            @yield('content')
        </div>
    </section>
</div>
</body>
</html>
