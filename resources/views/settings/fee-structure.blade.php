@extends('layouts.app')
@section('title', 'Fee Structure')
@section('content')
@php use App\Support\Mock; @endphp
<x-page-header title="Fee Structure" subtitle="Pricing per branch, shift and duration">
    <label for="fee-form-toggle" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add fee</label>
</x-page-header>

<input type="checkbox" id="fee-form-toggle" class="peer hidden">
<form class="card mb-4 hidden peer-checked:block" onsubmit="return false">
    <div class="card-head"><div class="card-title">New fee structure</div></div>
    <div class="card-body grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-field label="Name" name="name" placeholder="e.g. Morning Monthly" />
        <x-field label="Branch" name="branch" :options="['Navrangpura', 'Satellite', 'Adajan']" />
        <x-field label="Shift" name="shift" :options="['Morning (6–12)', 'Afternoon (12–6)', 'Evening (6–11)', 'Full Day']" />
        <x-field label="Duration" name="duration" :options="['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly']" value="Monthly" />
        <x-field label="Amount (₹)" name="amount" type="number" />
    </div>
    <div class="px-5 pb-5 flex justify-end gap-2">
        <label for="fee-form-toggle" class="btn cursor-pointer">Cancel</label>
        <button type="button" class="btn btn-primary" data-toast="Fee structure added (demo)">Save fee</button>
    </div>
</form>

<div class="card">
    <x-toolbar target="fees" placeholder="Search fee structures" :filters="[
        'All branches' => ['col' => 1, 'options' => ['Navrangpura', 'Satellite', 'Adajan']],
        'All durations' => ['col' => 3, 'options' => ['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="fees">
            <thead><tr><th>Name</th><th>Branch</th><th>Shift</th><th>Duration</th><th class="text-right">Amount</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach (Mock::feeStructures() as $f)
                    <tr>
                        <td class="font-medium">{{ $f['name'] }}</td>
                        <td>{{ $f['branch'] }}</td>
                        <td>{{ $f['shift'] }}</td>
                        <td>{{ $f['duration'] }}</td>
                        <td class="text-right">₹{{ number_format($f['amount']) }}</td>
                        <td><x-badge :status="$f['status']" /></td>
                        <td class="text-right"><x-row-actions :label="$f['name']" :extra="['Edit', 'Duplicate']" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No fee structures found." />
            </tbody>
        </table>
    </div>
    <x-pager target="fees" :size="8" />
</div>
@endsection
