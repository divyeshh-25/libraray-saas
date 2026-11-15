<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · LMS</title>
    @fonts
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.14.5/sweetalert2.all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $currentPrincipal = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
    $can = fn (string $module) => $currentPrincipal?->canAccessModule($module) ?? false;

    $nav = collect([
        ['Dashboard', 'dashboard', 'dashboard', 'dashboard', null],
        ['Tenants', 'tenants.index', 'tenants*', 'building', 'tenants'],
        ['Plans', 'plans.index', 'plans*', 'tag', 'plans'],
        ['Branches', 'branches.index', 'branches*', 'pin', 'branches'],
        ['Halls', 'halls.index', 'halls*', 'grid', 'halls'],
        ['Staff', 'staff.index', 'staff*', 'users', 'staff'],
        ['Students', 'students.index', 'students*', 'cap', 'students'],
        ['Attendance', 'attendance.index', 'attendance*', 'clock', 'attendance'],
    ])->filter(fn ($item) => $item[4] === null || $can($item[4]));

    $settings = collect([
        ['Fee Structure', 'settings.fees', 'fees'],
        ['General Settings', 'settings.general', 'general'],
        ['Roles & Permissions', 'settings.roles', 'roles'],
    ])->filter(fn ($item) => $can($item[2]));
    $inSettings = request()->routeIs('settings.*');
    $showSettings = $settings->isNotEmpty();
@endphp
<body>
<div class="min-h-screen lg:pl-60">
    {{-- Sidebar --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-slate-900/40 hidden lg:hidden" data-sidebar-close></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-60 bg-slate-900 flex flex-col -translate-x-full lg:translate-x-0 transition-transform">
        <div class="h-14 px-5 flex items-center gap-2.5 border-b border-white/10 shrink-0">
            <span class="w-7 h-7 rounded-md bg-gradient-to-br from-brand-500 to-brand-700 text-white grid place-items-center text-[13px] font-semibold shadow-md shadow-brand-700/40">W</span>
            <span class="text-white font-semibold text-[15px]">LMS</span>
        </div>
        <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
            @foreach ($nav as [$label, $route, $pattern, $icon])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($pattern) ? 'active' : '' }}">
                    <x-icon :name="$icon" class="w-4 h-4 opacity-80" /> {{ $label }}
                </a>
            @endforeach

            @if ($showSettings)
            <div data-collapse>
                <button type="button" data-collapse-toggle class="nav-link w-full cursor-pointer {{ $inSettings ? 'active' : '' }}">
                    <x-icon name="cog" class="w-4 h-4 opacity-80" /> Settings
                    <x-icon name="chevron" class="w-3.5 h-3.5 ml-auto opacity-60 transition-transform {{ $inSettings ? 'rotate-90' : '' }}" data-chevron />
                </button>
                <div data-collapse-body class="accordion {{ $inSettings ? 'open' : '' }}">
                    <div class="mt-0.5">
                        @foreach ($settings as [$label, $route])
                            <a href="{{ route($route) }}" class="nav-sub {{ request()->routeIs($route) ? 'active' : '' }}">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </nav>
        @php($currentUser = auth()->guard('admin')->user() ?? auth()->guard('web')->user())
        <div class="p-3 border-t border-white/10 relative" data-menu>
            <button type="button" data-menu-toggle class="w-full flex items-center gap-2.5 p-1.5 rounded-md hover:bg-white/5 cursor-pointer text-left">
                <span class="w-8 h-8 rounded-full bg-slate-700 text-white grid place-items-center text-[12px] font-medium">{{ Str::of($currentUser->name ?? '?')->substr(0, 2)->upper() }}</span>
                <div class="leading-tight min-w-0 flex-1">
                    <div class="text-[13px] text-white truncate">{{ $currentUser->name ?? 'Guest' }}</div>
                    <div class="text-[12px] text-slate-400">{{ auth()->guard('admin')->check() ? 'Super Admin' : 'Owner' }}</div>
                </div>
                <x-icon name="chevron" class="w-3.5 h-3.5 text-slate-400 -rotate-90" />
            </button>
            <div class="menu !top-auto bottom-full !right-3 left-3 !w-auto mb-1">
                <div class="px-3 py-2 border-b border-line mb-1">
                    <div class="text-[13px] font-medium">{{ $currentUser->name ?? 'Guest' }}</div>
                    <div class="text-[12px] text-muted truncate">{{ $currentUser->email ?? '' }}</div>
                </div>
                <a href="{{ route('profile') }}">Edit profile</a>
                <a href="{{ route('profile') }}#password">Change password</a>
                <form method="POST" action="{{ auth()->guard('admin')->check() ? route('admin.logout') : route('logout') }}" class="border-t border-line mt-1 pt-2">
                    @csrf
                    <button type="submit" class="!text-red-700 w-full text-left">Sign out</button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex flex-col min-h-screen">
        <header class="h-14 bg-white border-b border-line px-4 sm:px-6 flex items-center gap-3 sticky top-0 z-20">
            <button type="button" class="lg:hidden btn btn-sm !px-2" data-sidebar-open aria-label="Open menu"><x-icon name="menu" class="w-4 h-4" /></button>
            <div class="relative flex-1 max-w-md hidden sm:block">
                <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input class="input !pl-9 !bg-slate-50" placeholder="Search tenants, students, branches…">
            </div>
            <div class="ml-auto flex items-center gap-3 text-[13px] text-muted">
                <span class="hidden md:inline">{{ now()->format('D, d M Y') }}</span>
                <div class="relative" data-menu>
                    <button type="button" data-menu-toggle class="btn btn-sm !px-2 relative" aria-label="Notifications">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg>
                        <span class="live-dot absolute -top-0.5 -right-0.5 ring-2 ring-white" data-bell-dot></span>
                    </button>
                    <div class="menu !w-80 !p-0 overflow-hidden">
                        <div class="flex items-center justify-between px-4 py-2.5 border-b border-line">
                            <span class="font-semibold text-[13px] text-ink">Notifications</span>
                            <button type="button" class="!w-auto !p-0 text-[12px] !text-brand-700 hover:underline" data-mark-read>Mark all read</button>
                        </div>
                        @foreach ([['Rina Kapadia’s plan expired', '12 min ago', 'bg-red-500'], ['Aspire Study Center started a trial', '1 hr ago', 'bg-brand-500'], ['Payment received · ₹8,999', '2 days ago', 'bg-emerald-500']] as [$t, $w, $c])
                            <div class="flex gap-3 px-4 py-3 border-b border-line last:border-0 hover:bg-slate-50 text-ink">
                                <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $c }}" data-unread></span>
                                <div><div class="text-[13px]">{{ $t }}</div><div class="text-[12px] text-muted">{{ $w }}</div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6 lg:p-8 w-full">
            @yield('content')
        </main>
    </div>
</div>

{{-- Confirm dialog (frontend only) --}}
<div id="confirm-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/40">
    <div class="card w-full max-w-sm p-5 shadow-xl">
        <h3 class="font-semibold text-[15px]" data-confirm-title>Delete this record?</h3>
        <p class="text-muted mt-1.5" data-confirm-text>This action cannot be undone.</p>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn" data-confirm-cancel>Cancel</button>
            <button type="button" class="btn btn-primary !bg-red-600 !border-red-600" data-confirm-ok>Delete</button>
        </div>
    </div>
</div>
<div id="toast" class="fixed bottom-5 right-5 z-[70] bg-slate-900 text-white text-[13px] pl-3 pr-4 py-2.5 rounded-lg shadow-xl hidden items-center gap-2.5">
    <span class="w-5 h-5 rounded-full bg-emerald-500 grid place-items-center shrink-0"><x-icon name="check" class="w-3 h-3" stroke-width="3" /></span>
    <span data-toast-text></span>
</div>
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const t = document.getElementById('toast');
            t.querySelector('[data-toast-text]').textContent = @json(session('success'));
            t.classList.remove('hidden');
            t.classList.add('flex', 'show');
            setTimeout(() => { t.classList.add('hidden'); t.classList.remove('flex', 'show'); }, 2600);
        });
    </script>
@endif
</body>
</html>
