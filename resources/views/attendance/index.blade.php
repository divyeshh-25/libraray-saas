@extends('layouts.app')
@section('title', 'Attendance')
@section('content')
<x-page-header title="Attendance" subtitle="Mark and review daily student attendance" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('attendance.index') }}" class="grid sm:grid-cols-4 gap-4">
            <div>
                <label class="label" for="branch_id">Branch</label>
                <select id="branch_id" name="branch_id" class="input select2" data-placeholder="Select branch" data-autosubmit>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected($selectedBranch?->id === $b->id)>{{ $b->name }} ({{ $b->code }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="shift_id">Shift</label>
                <select id="shift_id" name="shift_id" class="input select2" data-placeholder="All shifts" data-autosubmit>
                    <option value="">All shifts</option>
                    @foreach ($shifts as $sh)
                        <option value="{{ $sh->id }}" @selected($selectedShiftId === $sh->id)>{{ $sh->name }} ({{ $sh->start_time }}&ndash;{{ $sh->end_time }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="date">Date</label>
                <input id="date" name="date" type="date" class="input" value="{{ $selectedDate->format('Y-m-d') }}" onchange="this.form.submit()">
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div class="card-title">Roster &middot; {{ $selectedDate->format('d M Y') }}</div>
        @canDo('attendance', 'add')
            @if ($enrollments->isNotEmpty())
                <span class="text-[12.5px] text-muted">{{ $enrollments->count() }} enrolled student(s)</span>
            @endif
        @endcanDo
    </div>
    <div class="card-body">
        @if ($enrollments->isEmpty())
            <p class="text-muted text-center py-6 text-[13px]">
                @if (!$selectedBranch)
                    No branches available.
                @else
                    No active enrollments for this branch{{ $selectedShiftId ? ' and shift' : '' }}.
                @endif
            </p>
        @else
            <form method="POST" action="{{ route('attendance.store') }}" id="attendance-form">
                @csrf
                <input type="hidden" name="attendance_date" value="{{ $selectedDate->format('Y-m-d') }}">
                <input type="hidden" name="branch_id" value="{{ $selectedBranch->id }}">
                @if ($selectedShiftId)
                    <input type="hidden" name="shift_id" value="{{ $selectedShiftId }}">
                @endif
                <div class="overflow-x-auto">
                    <table class="tbl">
                        <thead>
                            <tr><th>Student</th><th>Shift</th><th>Seat</th><th class="w-72">Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($enrollments as $e)
                                @php
                                    $key = "{$e->student_id}:{$e->shift_id}";
                                    $current = $marked[$key]->status ?? null;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <x-initials :name="$e->student->user->name" />
                                            <div>
                                                <div class="font-medium">{{ $e->student->user->name }}</div>
                                                <div class="text-[12px] text-muted">{{ $e->student->student_code }}</div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="records[{{ $loop->index }}][student_id]" value="{{ $e->student_id }}">
                                        <input type="hidden" name="records[{{ $loop->index }}][shift_id]" value="{{ $e->shift_id }}">
                                    </td>
                                    <td class="whitespace-nowrap">{{ $e->shift->name }}</td>
                                    <td>{{ $e->seatAllocation?->seat?->seat_number ?? '—' }}</td>
                                    <td>
                                        <div class="flex gap-1.5" role="group" aria-label="Attendance status for {{ $e->student->user->name }}">
                                            @foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late'] as $value => $label)
                                                <label class="flex-1">
                                                    <input type="radio" name="records[{{ $loop->parent->index }}][status]" value="{{ $value }}" class="sr-only peer" @checked($current === $value) @disabled(!viewer_can('attendance', 'add')) required>
                                                    <span class="block text-center text-[12.5px] px-2 py-1.5 rounded border border-line cursor-pointer peer-checked:text-white
                                                        @if ($value === 'present') peer-checked:bg-emerald-600 peer-checked:border-emerald-600
                                                        @elseif ($value === 'absent') peer-checked:bg-red-600 peer-checked:border-red-600
                                                        @else peer-checked:bg-amber-500 peer-checked:border-amber-500
                                                        @endif
                                                    ">{{ $label }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @canDo('attendance', 'add')
                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit" class="btn btn-primary">Save attendance</button>
                        <button type="button" class="btn" onclick="document.querySelectorAll('#attendance-form input[value=present]').forEach(i => i.checked = true)">Mark all present</button>
                    </div>
                @endcanDo
            </form>
        @endif
    </div>
</div>
@endsection
