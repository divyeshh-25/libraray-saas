@extends('layouts.app')
@section('title', 'Archived branches')
@section('content')
<x-page-header title="Archived branches" subtitle="Branches that have been archived (soft deleted)" :back="['Branches', route('branches.index')]" />

<div class="card">
    <div class="overflow-x-auto">
        <table class="tbl" data-table="branches-archived">
            <thead><tr><th>Branch</th><th>Library</th><th>City</th><th>Status</th><th>Archived on</th><th class="w-32"></th></tr></thead>
            <tbody>
                @foreach ($branches as $b)
                    <tr>
                        <td><a href="{{ route('branches.show', $b->id) }}" class="font-medium hover:text-brand-700">{{ $b->name }}</a><div class="text-[12px] text-muted">{{ $b->code }}</div></td>
                        <td>{{ $b->tenant?->library_name }}</td>
                        <td>{{ $b->city }}</td>
                        <td><span class="badge badge-muted">Archived</span></td>
                        <td class="text-muted whitespace-nowrap">{{ $b->deleted_at->format('d M Y') }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm" data-restore-url="{{ route('branches.restore', $b->id) }}"><x-icon name="restore" class="w-3.5 h-3.5" /> Restore</button>
                        </td>
                    </tr>
                @endforeach
                <x-empty-row :cols="6" message="No archived branches." />
            </tbody>
        </table>
    </div>
</div>
@endsection
