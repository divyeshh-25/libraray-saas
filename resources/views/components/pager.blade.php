@props(['target', 'size' => 8])
<div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-t border-line text-[12.5px] text-muted" data-pager="{{ $target }}" data-size="{{ $size }}">
    <span data-pager-info></span>
    <div class="flex items-center gap-1" data-pager-buttons></div>
</div>
