<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = Auth::user()->tenant_id;

        // Card list: cheap (no permissions loaded here — only the selected
        // role's matrix below needs that).
        $roles = Role::query()
            ->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))
            ->withCount(['staff as staff_count' => fn ($q) => $q->whereHas(
                'user',
                fn ($u) => $u->where('tenant_id', $tenantId)
            )])
            ->orderByRaw("is_system desc, field(name, 'Admin', 'Librarian', 'Accountant')")
            ->orderBy('name')
            ->get();

        $selected = $request->integer('role');
        $selectedRole = $selected
            ? $roles->firstWhere('id', $selected)
            : null;
        $selectedRole ??= $roles->firstWhere('name', 'Admin') ?? $roles->first();
        $selectedRole?->load('permissions');

        $actions = ['view', 'add', 'update', 'delete'];
        $permissionsByModule = Permission::whereIn('action', $actions)->get()->groupBy('module');

        return view('settings.roles', compact('roles', 'selectedRole', 'permissionsByModule', 'actions'));
    }

    /**
     * A role usable by this tenant: either one of the global defaults
     * (tenant_id is null) or a custom role this tenant created.
     */
    private function visibleRole(int $id): Role
    {
        $tenantId = Auth::user()->tenant_id;

        return Role::where('id', $id)
            ->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))
            ->firstOrFail();
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Role::create([
            'tenant_id' => Auth::user()->tenant_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('settings.roles')->with('success', 'Role created.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = $this->visibleRole($id);

        abort_if($role->is_system, 403, 'This is a default role and cannot be renamed.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('settings.roles')->with('success', 'Role updated.');
    }

    public function updatePermissions(Request $request, int $id): RedirectResponse
    {
        $role = $this->visibleRole($id);

        // The role itself (its name, its existence) is locked for the defaults,
        // but what it grants is always editable — that's the whole point of a
        // permission matrix.
        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync(
            collect($data['permissions'] ?? [])->mapWithKeys(fn ($id) => [$id => ['created_at' => now()]])
        );

        return redirect()->route('settings.roles')->with('success', 'Permissions saved.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = $this->visibleRole($id);

        abort_if($role->is_system, 403, 'This is a default role and cannot be deleted.');

        $role->delete();

        return redirect()->route('settings.roles')->with('success', 'Role deleted.');
    }
}
