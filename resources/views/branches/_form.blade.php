@php $b = $branch ?? null; @endphp
<form class="flex flex-col h-full" data-ajax-form method="POST" action="{{ $mode === 'edit' ? route('branches.update', $b->id) : route('branches.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        <div data-form-errors></div>

        <div>
            <div class="text-[13px] font-medium mb-3">Branch details</div>
            <div class="grid gap-4">
                <x-field label="Library" name="tenant" :options="$tenants" :value="$b?->tenant?->library_name ?? ''" required />
                <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="$b ? ucfirst($b->status) : 'Active'" required />
                <x-field label="Branch name" name="name" :value="$b->name ?? ''" required />
                <x-field label="Branch code" name="code" :value="$b->code ?? ''" hint="Short unique code used in student IDs and invoices." required />
                <x-field label="Phone" name="phone" :value="$b->phone ?? ''" />
                <x-field label="City" name="city" :value="$b->city ?? ''" />
                <x-field label="Address" name="address" type="textarea" :value="$b->address ?? ''" />
            </div>
        </div>

        <div class="pt-2 border-t border-line">
            <div class="text-[13px] font-medium mb-3 mt-4">Operating hours</div>
            <div class="grid gap-4">
                <x-field label="Opens at" name="opens" type="time" :value="$b?->opens_at ? substr($b->opens_at, 0, 5) : '06:00'" />
                <x-field label="Closes at" name="closes" type="time" :value="$b?->closes_at ? substr($b->closes_at, 0, 5) : '23:00'" />
                <x-field label="Weekly off" name="off" :options="['None', 'Sunday', 'Saturday']" :value="$b->weekly_off ?? 'None'" />
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-line shrink-0">
        <a href="{{ route('branches.index') }}" class="btn" data-offcanvas-close>Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span data-submit-label>{{ $mode === 'edit' ? 'Save changes' : 'Create branch' }}</span>
        </button>
    </div>
</form>
