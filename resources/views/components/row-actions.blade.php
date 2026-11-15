@props(['view' => null, 'edit' => null, 'label' => 'record', 'extra' => [], 'links' => [], 'collectFee' => null, 'delete' => null, 'deleteLabel' => 'Delete', 'editOffcanvas' => false, 'editTitle' => 'Edit'])
<div class="relative inline-block" data-menu>
    <button type="button" class="btn btn-sm !px-1.5 !border-transparent" data-menu-toggle aria-label="Actions"><x-icon name="dots" class="w-4 h-4 text-muted" /></button>
    <div class="menu">
        @if ($view)<a href="{{ $view }}">View details</a>@endif
        @if ($edit)<a href="{{ $edit }}" @if ($editOffcanvas) data-offcanvas-url="{{ $edit }}" data-offcanvas-title="{{ $editTitle }}" @endif>Edit</a>@endif
        @foreach ($links as $l)<a href="#" data-offcanvas-url="{{ $l['url'] }}" data-offcanvas-title="{{ $l['title'] }}">{{ $l['label'] }}</a>@endforeach
        @if ($collectFee)<button type="button" data-collect-fee data-url="{{ $collectFee['url'] }}" data-student-name="{{ $collectFee['studentName'] }}" data-fee-structure-id="{{ $collectFee['feeStructureId'] }}" data-amount="{{ $collectFee['amount'] }}">Collect fee</button>@endif
        @foreach ($extra as $e)<button type="button" data-toast="{{ $e }} (demo)">{{ $e }}</button>@endforeach
        @if ($delete)<button type="button" class="!text-red-700" data-confirm="{{ $deleteLabel }} {{ $label }}?" data-delete-url="{{ $delete }}">{{ $deleteLabel }}</button>@endif
    </div>
</div>
