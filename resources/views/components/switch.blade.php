@props(['on' => false])
<button type="button" role="switch" data-switch aria-checked="{{ $on ? 'true' : 'false' }}" class="group relative inline-flex h-5 w-9 shrink-0 items-center rounded-full cursor-pointer transition-colors bg-slate-300 aria-checked:bg-brand-600">
    <span class="inline-block h-4 w-4 rounded-full bg-white shadow transition-transform translate-x-0.5 group-aria-checked:translate-x-[18px]"></span>
</button>
