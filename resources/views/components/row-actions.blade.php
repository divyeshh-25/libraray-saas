@props(['view' => null, 'edit' => null, 'label' => 'record', 'extra' => [], 'delete' => null, 'deleteLabel' => 'Delete'])
<div class="relative inline-block" data-menu>
    <button type="button" class="btn btn-sm !px-1.5 !border-transparent" data-menu-toggle aria-label="Actions"><x-icon name="dots" class="w-4 h-4 text-muted" /></button>
    <div class="menu">
        @if ($view)<a href="{{ $view }}">View details</a>@endif
        @if ($edit)<a href="{{ $edit }}">Edit</a>@endif
        @foreach ($extra as $e)<button type="button" data-toast="{{ $e }} (demo)">{{ $e }}</button>@endforeach
        <button type="button" class="!text-red-700" data-confirm="{{ $deleteLabel }} {{ $label }}?" @if ($delete) data-delete-url="{{ $delete }}" @endif>{{ $deleteLabel }}</button>
    </div>
</div>
