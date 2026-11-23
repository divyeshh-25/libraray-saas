<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $principal = $this->principal();

        return view('profile', [
            'principal' => $principal,
            'isAdmin' => $principal instanceof Admin,
            'roleLabel' => $this->roleLabel($principal),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $principal = $this->principal();

        $rules = ['name' => ['required', 'string', 'max:150']];

        if ($principal instanceof User) {
            $rules['phone'] = ['nullable', 'string', 'max:20'];
        }

        $principal->update($request->validate($rules));

        return redirect()->route('profile')->with('success', 'Profile updated.');
    }

    public function editPassword(): View
    {
        return view('profile-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $principal = $this->principal();

        if (! Hash::check($data['current_password'], $principal->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $principal->update(['password' => Hash::make($data['password'])]);

        return redirect()->route('profile')->with('success', 'Password changed.');
    }

    private function principal(): User|Admin
    {
        return Auth::guard('admin')->user() ?? Auth::guard('web')->user();
    }

    private function roleLabel(User|Admin $principal): string
    {
        if ($principal instanceof Admin) {
            return 'SaaS Admin';
        }

        if ($principal->user_type === 'owner') {
            return 'Owner';
        }

        $roleNames = $principal->staff?->roles->pluck('name')->all() ?? [];

        return $roleNames === [] ? 'Staff' : implode(', ', $roleNames);
    }
}
