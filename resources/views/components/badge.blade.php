@props(['status'])
@php
$map = [
    'active' => 'ok', 'paid' => 'ok', 'trial' => 'info', 'due' => 'warn', 'maintenance' => 'warn',
    'suspended' => 'bad', 'expired' => 'bad', 'inactive' => 'muted',
    'partial' => 'warn', 'unpaid' => 'bad', 'overdue' => 'bad', 'success' => 'ok',
];
@endphp
<span class="badge badge-{{ $map[strtolower($status)] ?? 'muted' }}">{{ ucfirst($status) }}</span>
