@extends('layouts.app')
@section('title', 'Archived fee structures')
@section('content')
<x-page-header title="Archived fee structures" subtitle="Fee structures that have been archived (soft deleted)" :back="['Fee Structure', route('settings.fees')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="fees-archived">
            <thead><tr><th>Name</th><th>Branch</th><th>Shift</th><th class="text-right">Amount</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($fees as $f)
                    <tr>
                        <td class="font-medium">{{ $f->name }}</td>
                        <td>{{ $f->branch?->name }}</td>
                        <td>{{ $f->shift?->name }}</td>
                        <td class="text-right">₹{{ number_format($f->amount) }}</td>
                        <td class="text-muted whitespace-nowrap">{{ $f->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('settings.fees.restore', $f->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="6" message="No archived fee structures." />
            </tbody>
        </table>
    </div>
</div>
@endsection
