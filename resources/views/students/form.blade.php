@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit student' : 'Add student')
@section('content')
@php $s = $student ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit student' : 'New admission'" :subtitle="$s->student_code ?? null" :back="['Students', route('students.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] max-w-4xl">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="space-y-4 max-w-4xl" method="POST" action="{{ $mode === 'edit' ? route('students.update', $s->id) : route('students.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Student details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Full name" name="name" :value="$s?->user?->name ?? ''" required />
            <x-field label="Date of birth" name="dob" type="date" :value="$s?->date_of_birth?->format('Y-m-d') ?? ''" />
            <x-field label="Phone" name="phone" :value="$s?->user?->phone ?? ''" />
            <x-field label="Email" name="email" type="email" :value="$s?->user?->email ?? ''" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" :value="$s->address ?? ''" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Guardian</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Guardian name" name="guardian_name" :value="$s->guardian_name ?? ''" />
            <x-field label="Guardian phone" name="guardian_phone" :value="$s->guardian_phone ?? ''" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Enrollment</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Branch" name="branch" :options="$branches" :value="$s?->user->branch ? $s->user->branch->name.' ('.$s->user->branch->code.')' : ''" required />
            <x-field label="Shift" name="shift" :options="['Morning (6–12)', 'Afternoon (12–6)', 'Evening (6–11)', 'Full Day']" :value="$s->shift ?? ''" />
            <x-field label="Fee plan" name="fee" :options="['Monthly · ₹1,200', 'Monthly · ₹1,500', 'Monthly · ₹2,500', 'Quarterly · ₹6,900']" :value="$s?->fee_amount ? 'Monthly · ₹'.number_format($s->fee_amount) : ''" />
            <x-field label="Seat" name="seat" :value="$s->seat_label ?? ''" placeholder="Pick from the hall seat map" hint="Leave empty to allocate later." />
            <x-field label="Admission date" name="admitted" type="date" :value="$s?->admission_date?->format('Y-m-d') ?? now()->format('Y-m-d')" />
            <x-field label="Status" name="status" :options="['Active', 'Inactive']" :value="$s ? ucfirst($s->status) : ''" required />
        </div>
    </div>
    <x-form-actions :cancel="route('students.index')" :save="$mode === 'edit' ? 'Save changes' : 'Admit student'" submit />
</form>
@endsection
