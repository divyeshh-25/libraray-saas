@props(['label', 'value', 'note' => null, 'trend' => null])
@php
    $l = strtolower($label);
    [$icon, $tone] = match (true) {
        str_contains($l, 'tenant') => ['building', 'blue'],
        str_contains($l, 'student') => ['cap', 'violet'],
        str_contains($l, 'revenue') => ['tag', 'teal'],
        str_contains($l, 'occup') || str_contains($l, 'seat') || str_contains($l, 'available') => ['grid', 'amber'],
        str_contains($l, 'hall') || str_contains($l, 'floor') => ['grid', 'blue'],
        default => ['dashboard', 'blue'],
    };
@endphp
<div class="card card-hover px-5 py-4">
    <div class="flex items-start justify-between">
        <div class="text-[12.5px] text-muted">{{ $label }}</div>
        <span class="chip chip-{{ $tone }}"><x-icon :name="$icon" class="w-[17px] h-[17px]" /></span>
    </div>
    <div class="mt-1 flex items-baseline gap-2">
        <span class="text-[26px] font-semibold tracking-tight tabular-nums" data-count>{{ $value }}</span>
        @if ($trend)
            <span class="text-[12px] font-medium inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded {{ str_starts_with($trend, '-') ? 'text-red-700 bg-red-50' : 'text-emerald-700 bg-emerald-50' }}">
                <x-icon name="up" class="w-3 h-3 {{ str_starts_with($trend, '-') ? 'rotate-90' : '' }}" />{{ ltrim($trend, '+-') }}
            </span>
        @endif
    </div>
    @if ($note)<div class="text-[12px] text-muted mt-0.5">{{ $note }}</div>@endif
</div>
