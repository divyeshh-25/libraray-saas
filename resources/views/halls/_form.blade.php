@php $h = $hall ?? null; @endphp
<form class="flex flex-col h-full" data-ajax-form method="POST" action="{{ $mode === 'edit' ? route('halls.update', $h->id) : route('halls.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        <div data-form-errors></div>

        <div>
            <div class="text-[13px] font-medium mb-3">Hall details</div>
            <div class="grid gap-4">
                <x-field label="Branch" name="branch" :options="$branches" :value="$h ? $h->branch->name.' ('.$h->branch->code.')' : ''" required />
                <x-field label="Status" name="status" :options="['Active', 'Maintenance', 'Inactive']" :value="$h ? ucfirst($h->status) : 'Active'" required />
                <x-field label="Hall name" name="name" :value="$h->name ?? ''" placeholder="e.g. Silent Hall A" required />
                <x-field label="Description" name="description" type="textarea" placeholder="Short note shown to staff, e.g. AC, no phone calls" :value="$h->description ?? ''" />
            </div>
        </div>

        <div class="pt-2 border-t border-line">
            <div class="text-[13px] font-medium mb-3 mt-4">Layout</div>
            <div class="grid gap-4">
                <x-field label="Floors" name="floors" type="number" :value="$h->floors ?? 1" />
                <x-field label="Total seats" name="total_seats" type="number" :value="$h->total_seats ?? ''" hint="Target seat count. Lay the actual seats out in the floor plan designer; once designed, that count takes over here." />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-line shrink-0">
        <a href="{{ route('halls.index') }}" class="btn" data-offcanvas-close>Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span data-submit-label>{{ $mode === 'edit' ? 'Save changes' : 'Create hall' }}</span>
        </button>
    </div>
</form>
