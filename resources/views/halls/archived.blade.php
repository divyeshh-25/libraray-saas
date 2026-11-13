@extends('layouts.app')
@section('title', 'Archived halls')
@section('content')
<x-page-header title="Archived halls" subtitle="Halls that have been archived (soft deleted)" :back="['Halls', route('halls.index')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="halls-archived">
            <thead><tr><th>Hall</th><th>Branch</th><th>Library</th><th>Status</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($halls as $h)
                    <tr>
                        <td><a href="{{ route('halls.show', $h->id) }}" class="font-medium hover:text-brand-700">{{ $h->name }}</a></td>
                        <td>{{ $h->branch?->name }}</td>
                        <td>{{ $h->branch?->tenant?->library_name }}</td>
                        <td><span class="badge badge-muted">Archived</span></td>
                        <td class="text-muted whitespace-nowrap">{{ $h->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('halls.restore', $h->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="6" message="No archived halls." />
            </tbody>
        </table>
    </div>
</div>
@endsection
