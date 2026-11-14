@extends('layouts.app')
@section('title', 'Fee Structure')
@section('content')
<x-page-header title="Fee Structure" subtitle="Pricing per branch, shift and duration">
    <a href="{{ route('settings.fees.archived') }}" class="btn" title="Archived fee structures"><x-icon name="archive" class="w-4 h-4" /> Archive</a>
    <label for="fee-form-toggle" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> Add fee</label>
</x-page-header>

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] mb-4">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="checkbox" id="fee-form-toggle" class="peer hidden" @checked($errors->any())>
<form class="card mb-4 hidden peer-checked:block" method="POST" action="{{ route('settings.fees.store') }}">
    @csrf
    <div class="card-head"><div class="card-title">New fee structure</div></div>
    <div class="card-body grid sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-field label="Name" name="name" placeholder="e.g. Morning Monthly" required />
        <x-field label="Branch" name="branch" :options="$branches" required />
        <x-field label="Shift" name="shift" :options="['Morning', 'Afternoon', 'Evening', 'Full Day']" required />
        <x-field label="Duration" name="duration" :options="['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly']" required />
        <x-field label="Amount (₹)" name="amount" type="number" required />
    </div>
    <div class="px-5 pb-5 flex justify-end gap-2">
        <label for="fee-form-toggle" class="btn cursor-pointer">Cancel</label>
        <button type="submit" class="btn btn-primary">Save fee</button>
    </div>
</form>

<div class="card">
    <x-toolbar target="fees" placeholder="Search fee structures" :filters="[
        'All branches' => ['col' => 1, 'options' => $branches->all()],
        'All durations' => ['col' => 3, 'options' => ['Daily', 'Weekly', 'Monthly', 'Quarterly', 'Yearly']],
    ]" />
    <div class="overflow-x-auto">
        <table class="tbl" data-table="fees">
            <thead><tr><th>Name</th><th>Branch</th><th>Shift</th><th>Duration</th><th class="text-right">Amount</th><th>Status</th><th class="w-10"></th></tr></thead>
            <tbody>
                @foreach ($fees as $f)
                    <tr>
                        <td class="font-medium">{{ $f->name }}</td>
                        <td>{{ $f->branch?->name }}</td>
                        <td>{{ $f->shift?->name }}</td>
                        <td>{{ ucfirst($f->duration_type) }}</td>
                        <td class="text-right">₹{{ number_format($f->amount) }}</td>
                        <td><x-badge :status="$f->status" /></td>
                        <td class="text-right"><x-row-actions :edit="route('settings.fees.edit', $f->id)" :delete="route('settings.fees.destroy', $f->id)" delete-label="Archive" :label="$f->name" /></td>
                    </tr>
                @endforeach
                <x-empty-row :cols="7" message="No fee structures found." />
            </tbody>
        </table>
    </div>
    <x-pager target="fees" :size="8" />
</div>
@endsection
