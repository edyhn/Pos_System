@props(['active' => false, 'href' => '#', 'icon' => ''])

<a href="{{ $href }}"
   {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150 ' . ($active ? 'bg-blue-600/20 text-blue-400 shadow-sm' : 'text-white/70 hover:bg-white/5 hover:text-white')]) }}
   @if($active) data-active="true" @endif>
    <span class="shrink-0 {{ $active ? 'text-blue-400' : 'text-white/50' }}">{!! $icon !!}</span>
    <span>{{ $slot }}</span>
    @if($active)
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-blue-400"></span>
    @endif
</a>
