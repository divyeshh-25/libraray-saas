@props(['label' => 'Password', 'name' => 'password', 'required' => true])
<div>
    <label class="label" for="{{ $name }}">{{ $label }}@if ($required)<span class="text-red-600"> *</span>@endif</label>
    <div class="relative">
        <input id="{{ $name }}" name="{{ $name }}" type="password" class="input !pr-14" autocomplete="off" @if ($required) required @endif>
        <button type="button" data-pw-toggle class="absolute right-0 top-0 h-9 px-3 text-[12.5px] text-muted hover:text-ink cursor-pointer">Show</button>
    </div>
    @error($name)<p class="hint text-red-600">{{ $message }}</p>@enderror
</div>
