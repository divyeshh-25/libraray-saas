@php $s = $student; @endphp
<div class="flex flex-col h-full">
    <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3">
        <div class="flex items-center justify-between">
            <div class="text-[13px] text-muted">{{ $s->student_code }}</div>
            @canDo('students', 'enroll')
                <a href="#" class="btn btn-sm btn-primary" data-offcanvas-url="{{ route('enrollments.create', ['student' => $s->id]) }}" data-offcanvas-title="Enroll {{ $s->user->name }}"><x-icon name="plus" class="w-4 h-4" /> New enrollment</a>
            @endcanDo
        </div>

        @forelse ($enrollments as $e)
            <div class="card">
                <div class="card-body text-[13px] space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="font-medium">{{ $e->branch?->name }}</div>
                        <x-badge :status="$e->trashed() ? 'archived' : $e->status" />
                    </div>
                    <div class="flex justify-between gap-4"><span class="text-muted">Shift</span><span>{{ $e->shift?->name }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-muted">Fee plan</span><span>{{ $e->feeStructure?->name }} @if($e->feeStructure)· ₹{{ number_format($e->feeStructure->amount) }}@endif</span></div>
                    <div class="flex justify-between gap-4"><span class="text-muted">Seat</span><span>{{ $e->seatAllocation?->seat?->seat_number ?? '—' }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-muted">Enrolled</span><span>{{ $e->enrollment_date->format('d M Y') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-muted">Valid</span><span>{{ $e->start_date->format('d M Y') }}@if($e->end_date) &ndash; {{ $e->end_date->format('d M Y') }}@endif</span></div>
                </div>
            </div>
        @empty
            <p class="text-muted text-[13px] text-center py-6">No enrollments yet.</p>
        @endforelse
    </div>
    <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-line shrink-0">
        <a href="#" class="btn" data-offcanvas-close>Close</a>
    </div>
</div>
