@extends('layouts.app')
@section('title', 'General Settings')
@section('content')
<x-page-header title="General Settings" subtitle="Platform-wide defaults" />

<div class="grid lg:grid-cols-[200px_1fr] gap-6 items-start" data-tabs-root>
    <nav class="flex lg:flex-col gap-1 overflow-x-auto" data-tabs>
        @foreach (['organization' => 'Organization', 'regional' => 'Regional', 'billing' => 'Billing & invoices', 'notifications' => 'Notifications'] as $k => $l)
            <button type="button" data-tab="{{ $k }}" class="text-left px-3 py-2 rounded-md text-[13.5px] whitespace-nowrap hover:bg-white {{ $loop->first ? 'bg-white font-medium border border-line' : 'text-muted border border-transparent' }}"
                onclick="document.querySelectorAll('[data-tabs] [data-tab]').forEach(b=>{const on=b===this;b.classList.toggle('bg-white',on);b.classList.toggle('font-medium',on);b.classList.toggle('border-line',on);b.classList.toggle('text-muted',!on);b.classList.toggle('border-transparent',!on)})">{{ $l }}</button>
        @endforeach
    </nav>

    <form onsubmit="return false" class="space-y-4 max-w-3xl">
        <div data-panel="organization" class="card">
            <div class="card-head"><div class="card-title">Organization</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Product name" name="app_name" value="LMS" />
                <x-field label="Support email" name="support_email" value="support@DivyeshCodes.test" />
                <x-field label="Support phone" name="support_phone" value="1800 120 4455" />
                <x-field label="Website" name="website" value="https://DivyeshCodes.test" />
                <x-field label="Registered address" name="address" type="textarea" span="sm:col-span-2" value="402, Corporate House, SG Highway, Ahmedabad 380054" />
                <div class="sm:col-span-2">
                    <label class="label">Logo</label>
                    <div class="flex items-center gap-4">
                        <span class="w-12 h-12 rounded-md bg-brand-500 text-white grid place-items-center text-[18px] font-semibold">W</span>
                        <button type="button" class="btn btn-sm" data-toast="Upload is disabled in this preview">Replace logo</button>
                        <span class="hint !mt-0">PNG or SVG, at least 256 px.</span>
                    </div>
                </div>
            </div>
        </div>

        <div data-panel="regional" class="card hidden">
            <div class="card-head"><div class="card-title">Regional</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Timezone" name="tz" :options="['Asia/Kolkata (UTC+05:30)', 'UTC']" />
                <x-field label="Currency" name="currency" :options="['INR (₹)', 'USD ($)']" />
                <x-field label="Date format" name="date_format" :options="['13 Nov 2026', '13/11/2026', '2026-11-13']" />
                <x-field label="Week starts on" name="week" :options="['Monday', 'Sunday']" />
            </div>
        </div>

        <div data-panel="billing" class="card hidden">
            <div class="card-head"><div class="card-title">Billing & invoices</div></div>
            <div class="card-body grid sm:grid-cols-2 gap-4">
                <x-field label="Invoice prefix" name="prefix" value="INV" />
                <x-field label="GST number" name="gst" value="24AABCW1234F1Z5" />
                <x-field label="Payment due after (days)" name="due_days" type="number" value="7" />
                <x-field label="Trial length (days)" name="trial" type="number" value="14" />
                <x-field label="Invoice footer note" name="footer" type="textarea" span="sm:col-span-2" value="Thank you for choosing LMS. Payments are due within 7 days." />
            </div>
        </div>

        <div data-panel="notifications" class="card hidden">
            <div class="card-head"><div class="card-title">Notifications</div></div>
            <ul class="divide-y divide-line">
                @foreach ([['New tenant registered', 'Email the support team when a library signs up', true], ['Subscription expiring', 'Remind tenants 7 days before expiry', true], ['Failed payments', 'Alert super admins when a charge fails', true], ['Weekly summary', 'Send a Monday report to super admins', false]] as [$t, $d, $on])
                    <li class="px-5 py-3.5 flex items-center justify-between gap-4"><div><div class="font-medium">{{ $t }}</div><div class="text-[12.5px] text-muted">{{ $d }}</div></div><x-switch :on="$on" /></li>
                @endforeach
            </ul>
        </div>

        <x-form-actions :cancel="route('dashboard')" save="Save settings" />
    </form>
</div>
@endsection
