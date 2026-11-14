@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
<x-page-header title="Roles & Permissions" subtitle="Control what each role can see and do">
    @canDo('roles', 'add')
        <label for="role-form-toggle" class="btn btn-primary"><x-icon name="plus" class="w-4 h-4" /> New role</label>
    @endcanDo
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

@canDo('roles', 'add')
<input type="checkbox" id="role-form-toggle" class="peer hidden">
<form class="card mb-4 hidden peer-checked:block" method="POST" action="{{ route('settings.roles.store') }}">
    @csrf
    <div class="card-head"><div class="card-title">New role</div></div>
    <div class="card-body grid sm:grid-cols-2 gap-4">
        <x-field label="Role name" name="name" placeholder="e.g. Front Desk" required />
        <x-field label="Description" name="description" placeholder="Short note about this role" />
    </div>
    <div class="px-5 pb-5 flex justify-end gap-2">
        <label for="role-form-toggle" class="btn cursor-pointer">Cancel</label>
        <button type="submit" class="btn btn-primary">Create role</button>
    </div>
</form>
@endcanDo

<div class="flex flex-wrap gap-4 mb-4">
    @foreach ($roles as $r)
        <div class="card p-4 w-64 border-l-4 {{ $selectedRole?->id === $r->id ? 'border-brand-500' : 'border-line' }}">
            <div class="flex items-center justify-between">
                <span class="font-semibold">{{ $r->name }}</span>
                @if (! $r->is_system && (viewer_can('roles', 'update') || viewer_can('roles', 'delete')))
                <div class="relative inline-block" data-menu>
                    <button type="button" class="btn btn-sm !px-1.5 !border-transparent" data-menu-toggle aria-label="Actions"><x-icon name="dots" class="w-4 h-4 text-muted" /></button>
                    <div class="menu">
                        @canDo('roles', 'update')
                            <button type="button" data-edit-pick="{{ $r->id }}">Edit</button>
                        @endcanDo
                        @canDo('roles', 'delete')
                        <form method="POST" action="{{ route('settings.roles.destroy', $r->id) }}" onsubmit="return confirm('Delete {{ $r->name }}? Staff assigned to this role will lose it.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="!text-red-700">Delete</button>
                        </form>
                        @endcanDo
                    </div>
                </div>
                @endif
            </div>
            <div class="mt-3 text-[13px] flex items-center justify-between">
                <span class="text-muted">Permission</span>
                <a href="{{ route('settings.roles', ['role' => $r->id]) }}" class="text-brand-700 hover:underline">View</a>
            </div>
            <div class="mt-1.5 text-[13px] flex items-center justify-between">
                <span class="text-muted">Member:</span>
                <span class="font-medium">{{ $r->staff_count }}</span>
            </div>
        </div>
    @endforeach
</div>

@canDo('roles', 'update')
@foreach ($roles as $r)
    @unless ($r->is_system)
        <form class="card mb-4 hidden" data-edit-panel="{{ $r->id }}" method="POST" action="{{ route('settings.roles.update', $r->id) }}">
            @csrf
            @method('PUT')
            <div class="card-head"><div class="card-title">Edit {{ $r->name }}</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Role name" name="name" :value="$r->name" required />
                <x-field label="Description" name="description" :value="$r->description" />
            </div>
            <div class="px-5 pb-5 flex justify-end gap-2">
                <button type="button" data-edit-cancel class="btn cursor-pointer">Cancel</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    @endunless
@endforeach
@endcanDo

@if ($selectedRole)
    @php $granted = $selectedRole->permissions->pluck('id')->all(); @endphp
    <div class="card">
        <div class="card-head">
            <div>
                <div class="card-title">{{ $selectedRole->name }} permissions</div>
                @if ($selectedRole->is_system)<div class="text-[12.5px] text-muted">Default role · cannot be renamed or deleted, but its permissions can be changed</div>@endif
            </div>
        </div>
        <form method="POST" action="{{ route('settings.roles.permissions', $selectedRole->id) }}">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead><tr><th>Module</th>@foreach ($actions as $a)<th class="text-center capitalize">{{ $a }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach ($permissionsByModule as $module => $perms)
                            <tr>
                                <td class="font-medium capitalize">{{ $module }}</td>
                                @foreach ($actions as $a)
                                    @php $perm = $perms->firstWhere('action', $a); @endphp
                                    <td class="text-center">
                                        @if ($perm)
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" class="accent-brand-600 w-4 h-4" @checked(in_array($perm->id, $granted)) @disabled(! viewer_can('roles', 'update'))>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @canDo('roles', 'update')
                <div class="px-5 py-3 flex justify-end"><button class="btn btn-primary btn-sm">Save</button></div>
            @endcanDo
        </form>
    </div>
@endif
@endsection
