@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit hall' : 'Add hall')
@section('content')
@php $h = $hall ?? null; @endphp
<x-page-header :title="$mode === 'edit' ? 'Edit hall' : 'Add hall'" :back="['Halls', route('halls.index')]" />

@if ($errors->any())
    <div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px] max-w-4xl">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form class="space-y-4 max-w-4xl" method="POST" action="{{ $mode === 'edit' ? route('halls.update', $h->id) : route('halls.store') }}">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif
    <div class="card">
        <div class="card-head"><div class="card-title">Hall details</div></div>
        <div class="card-body grid sm:grid-cols-2 gap-4">
            <x-field label="Branch" name="branch" :options="$branches" :value="$h ? $h->branch->name.' ('.$h->branch->code.')' : ''" required />
            <x-field label="Status" name="status" :options="['Active', 'Maintenance', 'Inactive']" :value="$h ? ucfirst($h->status) : ''" required />
            <x-field label="Hall name" name="name" :value="$h->name ?? ''" placeholder="e.g. Silent Hall A" span="sm:col-span-2" required />
            <x-field label="Description" name="description" type="textarea" span="sm:col-span-2" placeholder="Short note shown to staff, e.g. AC, no phone calls" :value="$h->description ?? ''" />
        </div>
    </div>
    <div class="card">
        <div class="card-head"><div class="card-title">Layout</div></div>
        <div class="card-body grid sm:grid-cols-3 gap-4">
            <x-field label="Floors" name="floors" type="number" :value="$h->floors ?? 1" />
            <x-field label="Rows" name="rows" type="number" :value="$h->rows ?? 6" />
            <x-field label="Seats per row" name="cols" type="number" :value="$h->cols ?? 10" hint="Fine-tune seats later in the floor plan designer." />
        </div>
    </div>
    <x-form-actions :cancel="route('halls.index')" :save="$mode === 'edit' ? 'Save changes' : 'Create hall'" submit />
</form>
@endsection
