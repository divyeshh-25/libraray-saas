@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
@php use App\Support\Mock; $actions = Mock::permissionActions(); @endphp
<x-page-header title="Roles & Permissions" subtitle="Control what each role can see and do">
    <button class="btn btn-primary" data-toast="Role created (demo)"><x-icon name="plus" class="w-4 h-4" /> New role</button>
</x-page-header>

<div class="grid lg:grid-cols-[280px_1fr] gap-4 items-start">
    <div class="card overflow-hidden">
        <div class="card-head"><div class="card-title">Roles</div></div>
        <ul class="divide-y divide-line">
            @foreach (Mock::roles() as $r)
                <li>
                    <button type="button" data-role-pick="{{ $r['id'] }}" class="w-full text-left px-5 py-3 hover:bg-slate-50 cursor-pointer {{ $loop->first ? '!bg-brand-50' : '' }}">
                        <div class="flex items-center justify-between"><span class="font-medium">{{ $r['name'] }}</span><span class="text-[12px] text-muted">{{ $r['users'] }} {{ Str::plural('user', $r['users']) }}</span></div>
                        <div class="text-[12.5px] text-muted mt-0.5">{{ $r['desc'] }}</div>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    @foreach (Mock::roles() as $r)
        @php $granted = Mock::rolePermissions($r['id']); @endphp
        <div class="card {{ $loop->first ? '' : 'hidden' }}" data-role-matrix="{{ $r['id'] }}">
            <div class="card-head">
                <div><div class="card-title">{{ $r['name'] }} permissions</div>@if ($r['system'])<div class="text-[12.5px] text-muted">System role · cannot be edited</div>@endif</div>
                <button class="btn btn-primary btn-sm {{ $r['system'] ? 'opacity-50 pointer-events-none' : '' }}" data-toast="Permissions saved (demo)">Save</button>
            </div>
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Module</th>@foreach ($actions as $a)<th class="text-center capitalize">{{ $a }}</th>@endforeach<th class="text-center">All</th></tr></thead>
                    <tbody>
                        @foreach (Mock::permissionModules() as $m)
                            <tr>
                                <td class="font-medium">{{ $m }}</td>
                                @foreach ($actions as $a)
                                    <td class="text-center"><input type="checkbox" class="accent-brand-600 w-4 h-4" @checked(in_array($a, $granted[$m])) @disabled($r['system'])></td>
                                @endforeach
                                <td class="text-center"><input type="checkbox" data-check-all class="accent-brand-600 w-4 h-4" @checked(count($granted[$m]) === count($actions)) @disabled($r['system'])></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
