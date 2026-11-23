@php $e = $enrollment ?? null; $lockedStudent = $presetStudent ?? $e?->student; @endphp
<form class="flex flex-col h-full" data-ajax-form method="POST" action="{{ $mode === 'edit' ? route('enrollments.update', $e->id) : route('enrollments.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
        <div data-form-errors></div>

        @if ($lockedStudent)
            <div>
                <label class="label">Student</label>
                <input class="input" value="{{ $lockedStudent->user->name }} ({{ $lockedStudent->student_code }})" disabled>
                <input type="hidden" name="student_id" value="{{ $lockedStudent->id }}">
            </div>
        @else
            <div>
                <label class="label" for="student_id">Student<span class="text-red-600"> *</span></label>
                <select id="student_id" name="student_id" class="input select2" data-placeholder="Select student" required>
                    <option value="" disabled selected>Select student</option>
                    @foreach ($students as $s)
                        <option value="{{ $s->id }}">{{ $s->user->name }} ({{ $s->student_code }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="label" for="branch_id">Branch<span class="text-red-600"> *</span></label>
            <select id="branch_id" name="branch_id" class="input select2" data-placeholder="Select branch" required>
                <option value="" disabled selected>Select branch</option>
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}" @selected($e?->branch_id == $b->id)>{{ $b->name }} ({{ $b->code }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label" for="shift_id">Shift<span class="text-red-600"> *</span></label>
            <select id="shift_id" name="shift_id" class="input select2" data-placeholder="Select shift" data-cascade-by-branch required>
                <option value="" disabled selected>Select shift</option>
                @foreach ($shifts as $sh)
                    <option value="{{ $sh->id }}" data-branch-id="{{ $sh->branch_id }}" @selected($e?->shift_id == $sh->id)>{{ $sh->name }} ({{ $sh->start_time }}–{{ $sh->end_time }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label" for="fee_structure_id">Fee plan<span class="text-red-600"> *</span></label>
            <select id="fee_structure_id" name="fee_structure_id" class="input select2" data-placeholder="Select fee plan" data-cascade-by-branch required>
                <option value="" disabled selected>Select fee plan</option>
                @foreach ($feeStructures as $f)
                    <option value="{{ $f->id }}" data-branch-id="{{ $f->branch_id }}" @selected($e?->fee_structure_id == $f->id)>{{ $f->name }} · ₹{{ number_format($f->amount) }}/{{ $f->duration_type }}</option>
                @endforeach
            </select>
        </div>

        @if ($mode === 'create')
            <div>
                <label class="label" for="seat_id">Seat (optional)</label>
                <select id="seat_id" name="seat_id" class="input select2" data-placeholder="Select seat" data-cascade-by-branch>
                    <option value="">Allocate later</option>
                    @foreach ($seats as $seat)
                        <option value="{{ $seat->id }}" data-branch-id="{{ $seat->branch_id }}" @if ($seat->activeAllocation()) data-unavailable="1" @endif @disabled($seat->activeAllocation())>{{ $seat->seat_number }} @if ($seat->activeAllocation()) (Occupied) @endif</option>
                    @endforeach
                </select>
                <p class="hint mt-1">Choose a branch first. Leave empty to allocate a seat later from the Hall floor plan.</p>
            </div>
        @endif

        <x-field label="Enrollment date" name="enrollment_date" type="date" :value="$e?->enrollment_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
        <x-field label="Start date" name="start_date" type="date" :value="$e?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
        <x-field label="End date" name="end_date" type="date" :value="$e?->end_date?->format('Y-m-d') ?? ''" hint="Leave empty for an open-ended enrollment." />

        @if ($mode === 'create')
            <div class="pt-2 border-t border-line">
                <div class="text-[13px] font-medium mb-1">Payment at enrollment</div>
                <p class="hint mb-3">An invoice for the full fee plan amount is generated automatically. Enter an amount to take a full or partial payment now; the rest stays as outstanding balance.</p>
                <div class="space-y-4">
                    <x-field label="Amount received (₹)" name="initial_payment" type="number" hint="Leave empty to collect later." />
                    <div>
                        <label class="label" for="payment_method">Payment method</label>
                        <select id="payment_method" name="payment_method" class="input">
                            @foreach (\App\Models\FeeTransaction::METHODS as $m)
                                <option value="{{ $m }}">{{ ucwords(str_replace('_', ' ', $m)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-field label="Reference no." name="payment_reference" hint="UPI / cheque / bank reference (optional)." />
                </div>
            </div>
        @endif

        @if ($mode === 'edit')
            <x-field label="Status" name="status" :options="['Active', 'Completed', 'Cancelled']" :value="$e ? ucfirst($e->status) : 'Active'" required />
        @endif
    </div>

    <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-line shrink-0">
        <a href="#" class="btn" data-offcanvas-close>Cancel</a>
        <button type="submit" class="btn btn-primary">
            <span data-submit-label>{{ $mode === 'edit' ? 'Save changes' : 'Enroll student' }}</span>
        </button>
    </div>
</form>
