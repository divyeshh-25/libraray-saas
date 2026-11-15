@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit enrollment' : 'New enrollment')
@section('content')
@php $e = $enrollment ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit enrollment' : 'New enrollment'" :subtitle="$e?->student?->student_code" :back="['Students', route('students.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] max-w-4xl">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="space-y-4 max-w-4xl" method="POST" action="{{ $mode === 'edit' ? route('enrollments.update', $e->id) : route('enrollments.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Enrollment details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            @if ($mode === 'edit' || $presetStudent ?? null)
                @php $lockedStudent = $presetStudent ?? $e->student; @endphp
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
                            <option value="{{ $s->id }}" @selected(old('student_id') == $s->id)>{{ $s->user->name }} ({{ $s->student_code }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="label" for="branch_id">Branch<span class="text-red-600"> *</span></label>
                <select id="branch_id" name="branch_id" class="input select2" data-placeholder="Select branch" required>
                    <option value="" disabled selected>Select branch</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('branch_id', $e?->branch_id) == $b->id)>{{ $b->name }} ({{ $b->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="shift_id">Shift<span class="text-red-600"> *</span></label>
                <select id="shift_id" name="shift_id" class="input select2" data-placeholder="Select shift" data-cascade-by-branch required>
                    <option value="" disabled selected>Select shift</option>
                    @foreach ($shifts as $sh)
                        <option value="{{ $sh->id }}" data-branch-id="{{ $sh->branch_id }}" @selected(old('shift_id', $e?->shift_id) == $sh->id)>{{ $sh->name }} ({{ $sh->start_time }}–{{ $sh->end_time }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label" for="fee_structure_id">Fee plan<span class="text-red-600"> *</span></label>
                <select id="fee_structure_id" name="fee_structure_id" class="input select2" data-placeholder="Select fee plan" data-cascade-by-branch required>
                    <option value="" disabled selected>Select fee plan</option>
                    @foreach ($feeStructures as $f)
                        <option value="{{ $f->id }}" data-branch-id="{{ $f->branch_id }}" @selected(old('fee_structure_id', $e?->fee_structure_id) == $f->id)>{{ $f->name }} · ₹{{ number_format($f->amount) }}/{{ $f->duration_type }}</option>
                    @endforeach
                </select>
            </div>

            @if ($mode === 'create')
                <div>
                    <label class="label" for="seat_id">Seat (optional)</label>
                    <select id="seat_id" name="seat_id" class="input select2" data-placeholder="Select seat" data-cascade-by-branch>
                        <option value="">Allocate later</option>
                        @foreach ($seats as $seat)
                            <option value="{{ $seat->id }}" data-branch-id="{{ $seat->branch_id }}" @if ($seat->activeAllocation()) data-unavailable="1" @endif @selected(old('seat_id') == $seat->id) @disabled($seat->activeAllocation())>{{ $seat->seat_number }} @if ($seat->activeAllocation()) (Occupied) @endif</option>
                        @endforeach
                    </select>
                    <p class="hint mt-1">Choose a branch first. Leave empty to allocate a seat later from the Hall floor plan.</p>
                </div>
            @endif

            <x-field label="Enrollment date" name="enrollment_date" type="date" :value="$e?->enrollment_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
            <x-field label="Start date" name="start_date" type="date" :value="$e?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d')" required />
            <x-field label="End date" name="end_date" type="date" :value="$e?->end_date?->format('Y-m-d') ?? ''" hint="Leave empty for an open-ended enrollment." />

            @if ($mode === 'edit')
                <x-field label="Status" name="status" :options="['Active', 'Completed', 'Cancelled']" :value="$e ? ucfirst($e->status) : 'Active'" required />
            @endif
        </div>
    </div>
    <x-form-actions :cancel="route('students.index')" :save="$mode === 'edit' ? 'Save changes' : 'Enroll student'" submit />
</form>
@endsection
