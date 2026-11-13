@props(['data', 'height' => 180])
@php
    $vals = array_values($data); $labels = array_keys($data);
    $min = min($vals) * 0.9; $max = max($vals) * 1.05; $w = 600; $h = $height; $n = count($vals);
    $pts = [];
    foreach ($vals as $i => $v) { $pts[] = [round($i * ($w / ($n - 1)), 1), round($h - (($v - $min) / ($max - $min)) * $h, 1)]; }
    $line = implode(' ', array_map(fn ($p) => $p[0].','.$p[1], $pts));
@endphp
<svg viewBox="0 -6 {{ $w }} {{ $h + 34 }}" class="w-full h-auto overflow-visible" role="img">
    @foreach ([0, 0.33, 0.66, 1] as $g)
        <line x1="0" x2="{{ $w }}" y1="{{ $h * $g }}" y2="{{ $h * $g }}" stroke="#eef0f3" />
    @endforeach
    <polygon class="area-fade" points="0,{{ $h }} {{ $line }} {{ $w }},{{ $h }}" fill="#3453c4" opacity=".07" />
    <polyline class="line-draw" pathLength="1" points="{{ $line }}" fill="none" stroke="#3453c4" stroke-width="2" stroke-linejoin="round" />
    @foreach ($pts as $i => $p)
        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="{{ $loop->last ? 4 : 2.5 }}" fill="#fff" stroke="#3453c4" stroke-width="2"><title>{{ $labels[$i] }}: {{ number_format($vals[$i]) }}</title></circle>
        @if ($i % 2 == 0 || $loop->last)<text x="{{ $p[0] }}" y="{{ $h + 28 }}" text-anchor="{{ $i == 0 ? 'start' : ($loop->last ? 'end' : 'middle') }}" font-size="17" fill="#667085">{{ $labels[$i] }}</text>@endif
    @endforeach
</svg>
