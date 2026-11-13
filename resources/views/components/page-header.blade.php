@props(['title', 'subtitle' => null, 'back' => null])
<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
    <div>
        @if ($back)
            <a href="{{ $back[1] }}" class="text-[12.5px] text-muted hover:text-ink">← {{ $back[0] }}</a>
        @endif
        <h1 class="text-[20px] font-semibold tracking-tight {{ $back ? 'mt-1' : '' }}">{{ $title }}</h1>
        @if ($subtitle)<p class="text-muted mt-0.5">{{ $subtitle }}</p>@endif
    </div>
    @if (! $slot->isEmpty())
        <div class="flex items-center gap-2">{{ $slot }}</div>
    @endif
</div>
