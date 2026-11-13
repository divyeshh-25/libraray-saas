{{-- Search + filters for a [data-table]. $filters: [label => [options]] keyed to column index via $cols --}}
@props(['target', 'placeholder' => 'Search…', 'filters' => []])
<div class="flex flex-wrap items-center gap-2 px-4 py-3 border-b border-line">
    <div class="relative w-full sm:w-72">
        <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
        <input class="input !pl-9" placeholder="{{ $placeholder }}" data-table-search="{{ $target }}">
    </div>
    @foreach ($filters as $label => $f)
        <select class="input !w-auto select2" data-table-filter="{{ $target }}" data-col="{{ $f['col'] }}" data-placeholder="{{ $label }}">
            <option value="">{{ $label }}</option>
            @foreach ($f['options'] as $o)<option>{{ $o }}</option>@endforeach
        </select>
    @endforeach
    <span class="ml-auto text-[12.5px] text-muted" data-table-count="{{ $target }}"></span>
</div>
