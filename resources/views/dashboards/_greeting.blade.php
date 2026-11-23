@php
    $principal = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="relative overflow-hidden rounded-xl border border-line bg-white px-6 py-5 flex flex-wrap items-center justify-between gap-4 shadow-sm">
    <div class="absolute inset-y-0 right-0 w-1/2 pointer-events-none bg-[radial-gradient(ellipse_at_right,var(--color-brand-100),transparent_70%)] opacity-70"></div>
    <div class="relative">
        <div class="flex items-center gap-2 text-[12.5px] text-muted"><span class="live-dot"></span> Live overview · {{ now()->format('F Y') }}</div>
        <h1 class="text-[22px] font-semibold tracking-tight mt-1">{{ $greeting }}, {{ $principal?->name }} 👋</h1>
        <p class="text-muted mt-0.5">{{ $subtitle }}</p>
    </div>
    <div class="relative flex flex-wrap gap-2">
        @if ($principal?->canDo('students', 'add'))
            <a href="{{ route('students.create') }}" class="btn">New admission</a>
        @endif
        @if ($principal?->canDo('branches', 'add'))
            <a href="{{ route('branches.create') }}" class="btn">Add branch</a>
        @endif
        @if ($principal?->canDo('tenants', 'add'))
            <a href="{{ route('tenants.create') }}" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add tenant</a>
        @endif
    </div>
</div>
<div class="mt-4"></div>
