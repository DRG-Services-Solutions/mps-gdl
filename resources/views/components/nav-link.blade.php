@props(['active'])

@php
$baseClasses = 'bg-blue-200  flex items-center w-full p-5 space-x-3 hover:bg-cyan-500 rounded-lg text-black transition-colors duration-200';

$stateClasses = ($active ?? false)
    ? 'bg-cyan-600 text-white' 
    : 'hover:bg-cyan-800 hover:text-white';

$classes = $baseClasses . ' ' . $stateClasses;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if (isset($icon))
        <div class="flex-shrink-0">
            {{ $icon }}
        </div>
    @endif

    <span class="ml-4 flex-1 whitespace-nowrap 
                transition-opacity duration-300"
        :class="{ 'lg:opacity-100': desktopSidebarOpen, 'lg:opacity-0': !desktopSidebarOpen }">
        {{ $slot }}
    </span>
</a>