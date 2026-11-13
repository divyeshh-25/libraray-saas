@props(['label', 'name', 'type' => 'text', 'value' => '', 'hint' => null, 'options' => null, 'placeholder' => '', 'span' => '', 'required' => false])
@php($value = old($name, $value))
<div class="{{ $span }}">
    <label class="label" for="{{ $name }}">{{ $label }}@if ($required)<span class="text-red-600"> *</span>@endif</label>
    @if ($options !== null)
        <select id="{{ $name }}" name="{{ $name }}" class="input" @if ($required) required @endif>
            @foreach ($options as $o)
                <option @selected($o == $value)>{{ $o }}</option>
            @endforeach
        </select>
    @elseif ($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="input" placeholder="{{ $placeholder }}" @if ($required) required @endif>{{ $value }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" class="input" placeholder="{{ $placeholder }}" @if ($required) required @endif>
    @endif
    @error($name)<p class="hint text-red-600">{{ $message }}</p>@enderror
    @if ($hint)<p class="hint">{{ $hint }}</p>@endif
</div>
