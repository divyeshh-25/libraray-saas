@props(['name'])
<span class="w-8 h-8 shrink-0 rounded-full bg-brand-50 text-brand-700 grid place-items-center text-[12px] font-medium">{{ collect(explode(' ', $name))->map(fn ($p) => $p[0])->take(2)->implode('') }}</span>
