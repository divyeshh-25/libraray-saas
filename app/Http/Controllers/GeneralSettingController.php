<?php

namespace App\Http\Controllers;

use App\Models\TenantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GeneralSettingController extends Controller
{
    /**
     * Sensible defaults shown the first time a tenant visits this page,
     * before they've saved anything of their own.
     */
    private function defaults(): array
    {
        return [
            'organization' => [
                'app_name' => 'LMS',
                'support_email' => '',
                'support_phone' => '',
                'website' => '',
                'address' => '',
            ],
            'regional' => [
                'tz' => 'Asia/Kolkata (UTC+05:30)',
                'currency' => 'INR (₹)',
                'date_format' => '13 Nov 2026',
                'week' => 'Monday',
            ],
            'billing' => [
                'prefix' => 'INV',
                'gst' => '',
                'due_days' => 7,
                'trial' => 14,
                'footer' => '',
            ],
            'notifications' => [
                'new_tenant' => true,
                'expiring' => true,
                'failed_payments' => true,
                'weekly_summary' => false,
            ],
        ];
    }

    public function index(): View
    {
        $tenantId = Auth::user()->tenant_id;

        $saved = TenantSetting::where('tenant_id', $tenantId)
            ->whereIn('setting_key', array_keys($this->defaults()))
            ->get()
            ->pluck('setting_value', 'setting_key');

        $settings = collect($this->defaults())->map(
            fn ($groupDefaults, $group) => array_merge($groupDefaults, $saved->get($group, []))
        );

        return view('settings.general', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $tenantId = Auth::user()->tenant_id;

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:150'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'tz' => ['required', 'string'],
            'currency' => ['required', 'string'],
            'date_format' => ['required', 'string'],
            'week' => ['required', 'string'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'gst' => ['nullable', 'string', 'max:30'],
            'due_days' => ['required', 'integer', 'min:0'],
            'trial' => ['required', 'integer', 'min:0'],
            'footer' => ['nullable', 'string'],
        ]);

        $groups = [
            'organization' => ['app_name', 'support_email', 'support_phone', 'website', 'address'],
            'regional' => ['tz', 'currency', 'date_format', 'week'],
            'billing' => ['prefix', 'gst', 'due_days', 'trial', 'footer'],
            'notifications' => [],
        ];

        foreach ($groups as $group => $keys) {
            if ($group === 'notifications') {
                continue;
            }

            TenantSetting::updateOrCreate(
                ['tenant_id' => $tenantId, 'setting_key' => $group],
                ['setting_value' => array_intersect_key($data, array_flip($keys))]
            );
        }

        TenantSetting::updateOrCreate(
            ['tenant_id' => $tenantId, 'setting_key' => 'notifications'],
            ['setting_value' => [
                'new_tenant' => $request->boolean('new_tenant'),
                'expiring' => $request->boolean('expiring'),
                'failed_payments' => $request->boolean('failed_payments'),
                'weekly_summary' => $request->boolean('weekly_summary'),
            ]]
        );

        return redirect()->route('settings.general')->with('success', 'Settings saved.');
    }
}
