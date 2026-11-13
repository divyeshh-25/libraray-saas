@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit student' : 'Add student')
@section('content')
@php use App\Support\Mock; $s = $mode === 'edit' ? Mock::student((int) $id) : null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit student' : 'New admission'" :subtitle="$s['code'] ?? null" :back="['Students', route('students.index')]" />
<form class="space-y-4 max-w-4xl" onsubmit="return false">
    <div class="card">
        <div class="card-head"><div class="card-title">Student details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Full name" name="name" :value="$s['name'] ?? ''" />
            <x-field label="Date of birth" name="dob" type="date" />
            <x-field label="Phone" name="phone" :value="$s['phone'] ?? ''" />
            <x-field label="Email" name="email" type="email" />
            <x-field label="Address" name="address" type="textarea" span="sm:col-span-2" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Guardian</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Guardian name" name="guardian_name" />
            <x-field label="Guardian phone" name="guardian_phone" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Enrollment</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Branch" name="branch" :options="['Navrangpura', 'Satellite', 'Adajan', 'Alkapuri', 'Sector 11', 'Bopal']" :value="$s['branch'] ?? ''" />
            <x-field label="Shift" name="shift" :options="['Morning (6–12)', 'Afternoon (12–6)', 'Evening (6–11)', 'Full Day']" :value="$s['shift'] ?? ''" />
            <x-field label="Fee plan" name="fee" :options="['Monthly · ₹1,200', 'Monthly · ₹1,500', 'Monthly · ₹2,500', 'Quarterly · ₹6,900']" />
            <x-field label="Seat" name="seat" :value="$s['seat'] ?? ''" placeholder="Pick from the hall seat map" hint="Leave empty to allocate later." />
            <x-field label="Admission date" name="admitted" type="date" value="2026-11-13" />
            <x-field label="Status" name="status" :options="['Active', 'Inactive']" value="Active" />
        </div>
    </div>
    <x-form-actions :cancel="route('students.index')" :save="$mode === 'edit' ? 'Save changes' : 'Admit student'" />
</form>
@endsection
