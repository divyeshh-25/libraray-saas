{{-- Simple SVG column chart. $data: [label => value] --}}
@props(['data', 'prefix' => '', 'height' => 200])
@php
    $max = max($data) ?: 1; $n = count($data); $w = 600; $h = $height; $pad = 24;
    $step = ($w - 8) / $n; $bw = $step * 0.55;
@endphp
<svg viewBox="0 0 {{ $w }} {{ $h + $pad }}" class="w-full h-auto" role="img">
    @foreach ([0.25, 0.5, 0.75, 1] as $g)
        <line x1="0" x2="{{ $w }}" y1="{{ $h - $h * $g * 0.92 }}" y2="{{ $h - $h * $g * 0.92 }}" stroke="#eef0f3" />
    @endforeach
    @foreach ($data as $label => $v)
        @php $bh = ($v / $max) * $h * 0.92; $x = 4 + $loop->index * $step + ($step - $bw) / 2; $last = $loop->last; @endphp
        <rect class="bar" style="animation-delay: {{ $loop->index * 45 }}ms" x="{{ $x }}" y="{{ $h - $bh }}" width="{{ $bw }}" height="{{ $bh }}" rx="2" fill="{{ $last ? '#3453c4' : '#c3d0f5' }}"><title>{{ $label }}: {{ $prefix }}{{ number_format($v) }}</title></rect>
        <text x="{{ $x + $bw / 2 }}" y="{{ $h + 16 }}" text-anchor="middle" font-size="11" fill="#667085">{{ $label }}</text>
    @endforeach
</svg>
