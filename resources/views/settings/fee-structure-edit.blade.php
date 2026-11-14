@extends('layouts.app')
@section('title', 'Edit fee structure')
@section('content')
<x-page-header title="Edit fee structure" :subtitle="$fee->name" :back="['Fee Structure', route('settings.fees')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] mb-4">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="card max-w-4xl" method="POST" action="{{ route('settings.fees.update', $fee->id) }}">
    @csrf
    @method('PUT')
    <div class="card-head"><div class="card-title">Fee structure details</div></div>
    <div class="card-body grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-field label="Name" name="name" :value="$fee->name" placeholder="e.g. Morning Monthly" required />
        <x-field label="Branch" name="branch" :options="$branches" :value="$fee->branch?->name ?? ''" required />
        <x-field label="Shift" name="shift" :options="['Morning', 'Afternoon', 'Evening', 'Full Day']" :value="$fee->shift?->name ?? ''" required />
        <x-field label="Duration" name="duration" :options="['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly']" :value="ucfirst($fee->duration_type)" required />
        <x-field label="Amount (₹)" name="amount" type="number" :value="$fee->amount" required />
    </div>
    <div class="px-5 pb-5 flex justify-end gap-2">
        <a href="{{ route('settings.fees') }}" class="btn">Cancel</a>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
</form>
@endsection
