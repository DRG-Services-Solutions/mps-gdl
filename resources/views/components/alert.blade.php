{{-- resources/views/components/alert.blade.php --}}
@props(['type' => 'info'])

@php
    $styles = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    ];

    $icons = [
        'success' => 'fa-circle-check',
        'error' => 'fa-circle-xmark',
        'warning' => 'fa-triangle-exclamation',
        'info' => 'fa-circle-info',
    ];
@endphp

<div x-data="{ show: true }" 
     x-show="show"
     x-transition
     {{ $attributes->merge(['class' => 'rounded-lg border p-4 mb-4 ' . $styles[$type]]) }}>
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="fas {{ $icons[$type] }} text-lg"></i>
        </div>
        <div class="ml-3 flex-1">
            {{ $slot }}
        </div>
        <div class="ml-auto pl-3">
            <button @click="show = false" class="inline-flex text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>