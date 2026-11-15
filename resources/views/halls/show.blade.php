@extends('layouts.app')
@section('title', 'Hall details')
@section('content')
@php
    $available = $seats - $occupied;
    $currentPrincipal = auth()->guard('admin')->user() ?? auth()->guard('web')->user();
    $canAllocate = $currentPrincipal?->canDo('halls', 'update') ?? false;
@endphp
<x-page-header :title="$hall->name" :subtitle="$hall->branch?->tenant?->library_name.' · '.$hall->branch?->name" :back="['Halls', route('halls.index')]">
    @if ($hall->trashed())
        <span class="badge badge-muted">Archived</span>
        @canDo('halls', 'delete')
            <button type="button" class="btn btn-primary" data-restore-url="{{ route('halls.restore', $hall->id) }}" data-redirect="{{ route('halls.index') }}"><x-icon name="restore" class="w-4 h-4" /> Restore</button>
        @endcanDo
    @else
        @canDo('halls', 'update')
            <a href="{{ route('halls.floor-plan', $hall->id) }}" class="btn">Floor plan designer</a>
            <a href="{{ route('halls.edit', $hall->id) }}" class="btn btn-primary">Edit hall</a>
        @endcanDo
    @endif
</x-page-header>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <x-stat label="Total seats" :value="$seats" />
    <x-stat label="Occupied" :value="$occupied" />
    <x-stat label="Available" :value="$available" />
    <x-stat label="Floors" :value="$hall->floors" />
</div>

<div class="card mt-4">
    <div class="card-head">
        <div class="card-title">Seat map</div>
        <div class="flex items-center gap-4 text-[12.5px] text-muted">
            <span class="flex items-center gap-1.5"><i class="w-3 h-3 rounded-sm bg-slate-200 inline-block"></i> Occupied</span>
            <span class="flex items-center gap-1.5"><i class="w-3 h-3 rounded-sm border border-slate-300 bg-white inline-block"></i> Available</span>
            <span class="flex items-center gap-1.5"><i class="w-3 h-3 rounded-sm bg-brand-600 inline-block"></i> Selected</span>
        </div>
    </div>
    <div class="card-body overflow-x-auto">
        @if ($seatRows->isNotEmpty())
            <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, 2.25rem); max-width: 100%" id="seat-grid">
                @foreach ($seatRows as $seat)
                    @php $allocation = $seat->activeAllocation(); @endphp
                    <button type="button"
                        data-seat-id="{{ $seat->id }}"
                        data-seat-number="{{ $seat->seat_number }}"
                        @if ($allocation)
                            data-seat-status="occupied"
                            data-allocation-id="{{ $allocation->id }}"
                            data-student-name="{{ $allocation->student->user->name }}"
                            data-shift-name="{{ $allocation->shift->name }}"
                        @else
                            data-seat-status="free"
                        @endif
                        class="seat-btn h-9 rounded text-[11px] border cursor-pointer {{ $allocation ? 'bg-slate-200 border-slate-300' : 'bg-white border-slate-300 hover:border-brand-500' }}"
                        title="{{ $allocation ? $allocation->student->user->name.' · '.$allocation->shift->name : 'Available' }}">{{ $seat->seat_number }}</button>
                @endforeach
            </div>
        @else
            <p class="text-muted text-[13px] py-6 text-center">
                No seat layout designed yet.
                @if ($hall->total_seats)
                    Target: {{ $hall->total_seats }} seats.
                @endif
                Use the floor plan designer to add seats.
            </p>
        @endif
    </div>
    <div class="px-5 py-3 border-t border-line flex items-center justify-between">
        <span class="text-[12.5px] text-muted" id="seat-summary">Click an available seat to select it</span>
        @if ($canAllocate)
            <button class="btn btn-sm btn-primary" id="seat-action-btn" disabled>Allocate to student</button>
        @endif
    </div>
</div>

@if ($canAllocate)
<div id="seat-allocate-modal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/40">
    <div class="card w-full max-w-sm p-5 shadow-xl">
        <h3 class="font-semibold text-[15px]">Allocate seat <span id="seat-allocate-number"></span></h3>
        <div class="mt-4 space-y-3">
            <label class="block">
                <span class="text-[12.5px] text-muted">Student</span>
                <select id="seat-allocate-student" class="input w-full mt-1" data-placeholder="Select student">
                    <option></option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->user->name }} ({{ $student->student_code }})</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-[12.5px] text-muted">Shift</span>
                <select id="seat-allocate-shift" class="input w-full mt-1" data-placeholder="Select shift">
                    <option></option>
                    @foreach ($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }}–{{ $shift->end_time }})</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="text-[12.5px] text-muted">Start date</span>
                <input type="date" id="seat-allocate-start" class="input w-full mt-1" value="{{ now()->toDateString() }}">
            </label>
        </div>
        <div class="flex justify-end gap-2 mt-5">
            <button type="button" class="btn" id="seat-allocate-cancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="seat-allocate-confirm">Allocate</button>
        </div>
    </div>
</div>
@endif

<script>
(() => {
    const canAllocate = @json($canAllocate);
    if (!canAllocate) return;

    const $ = (s, r = document) => r.querySelector(s);
    const grid = $('#seat-grid');
    const summary = $('#seat-summary');
    const actionBtn = $('#seat-action-btn');
    const modal = $('#seat-allocate-modal');
    const csrf = $('meta[name="csrf-token"]').content;
    const hallId = {{ $hall->id }};
    let selected = null;

    function toast(msg) {
        const t = $('#toast');
        $('[data-toast-text]', t).textContent = msg;
        t.classList.remove('hidden');
        t.classList.add('flex', 'show');
        setTimeout(() => { t.classList.add('hidden'); t.classList.remove('flex', 'show'); }, 2600);
    }

    grid?.addEventListener('click', (e) => {
        const btn = e.target.closest('.seat-btn');
        if (!btn) return;
        grid.querySelectorAll('.seat-btn').forEach((b) => b.classList.remove('!bg-brand-600', '!border-brand-600', '!text-white'));
        btn.classList.add('!bg-brand-600', '!border-brand-600', '!text-white');
        selected = btn;
        actionBtn.disabled = false;
        if (btn.dataset.seatStatus === 'occupied') {
            summary.textContent = `Seat ${btn.dataset.seatNumber} · ${btn.dataset.studentName} (${btn.dataset.shiftName})`;
            actionBtn.textContent = 'Release seat';
        } else {
            summary.textContent = `Seat ${btn.dataset.seatNumber} selected`;
            actionBtn.textContent = 'Allocate to student';
        }
    });

    actionBtn?.addEventListener('click', () => {
        if (!selected) return;
        if (selected.dataset.seatStatus === 'occupied') {
            releaseSeat(selected);
        } else {
            $('#seat-allocate-number').textContent = selected.dataset.seatNumber;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    });

    $('#seat-allocate-cancel')?.addEventListener('click', () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    });

    $('#seat-allocate-confirm')?.addEventListener('click', async () => {
        const studentId = $('#seat-allocate-student').value;
        const shiftId = $('#seat-allocate-shift').value;
        const startDate = $('#seat-allocate-start').value;
        if (!studentId || !shiftId || !startDate) { toast('Please fill in all fields.'); return; }

        try {
            const res = await fetch(`/halls/${hallId}/seats/${selected.dataset.seatId}/allocate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                body: JSON.stringify({ student_id: studentId, shift_id: shiftId, start_date: startDate }),
            });
            const body = await res.json().catch(() => ({}));
            if (!res.ok) { toast(body.message || 'Could not allocate seat.'); return; }

            selected.dataset.seatStatus = 'occupied';
            selected.dataset.allocationId = body.allocation_id;
            selected.dataset.studentName = body.student_name;
            selected.dataset.shiftName = body.shift_name;
            selected.title = `${body.student_name} · ${body.shift_name}`;
            selected.classList.remove('bg-white', 'hover:border-brand-500');
            selected.classList.add('bg-slate-200');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            toast('Seat allocated.');
            actionBtn.textContent = 'Release seat';
            summary.textContent = `Seat ${selected.dataset.seatNumber} · ${body.student_name} (${body.shift_name})`;
        } catch {
            toast('Could not allocate seat.');
        }
    });

    async function releaseSeat(btn) {
        const result = await Swal.fire({
            title: 'Release this seat?',
            text: `Seat ${btn.dataset.seatNumber} will be released from ${btn.dataset.studentName}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Release',
            confirmButtonColor: '#dc2626',
        });
        if (!result.isConfirmed) return;
        try {
            const res = await fetch(`/seat-allocations/${btn.dataset.allocationId}/release`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            });
            const body = await res.json().catch(() => ({}));
            if (!res.ok) { toast(body.message || 'Could not release seat.'); return; }

            btn.dataset.seatStatus = 'free';
            delete btn.dataset.allocationId;
            delete btn.dataset.studentName;
            delete btn.dataset.shiftName;
            btn.title = 'Available';
            btn.classList.remove('bg-slate-200');
            btn.classList.add('bg-white', 'hover:border-brand-500');
            toast('Seat released.');
            actionBtn.textContent = 'Allocate to student';
            summary.textContent = `Seat ${btn.dataset.seatNumber} selected`;
        } catch {
            toast('Could not release seat.');
        }
    }
})();
</script>
@endsection
