@props(['label', 'value' => null, 'mono' => false])
<div class="flex justify-between border-b border-gray-50 pb-2">
    <dt class="text-sm text-gray-500">{{ $label }}</dt>
    <dd class="text-sm {{ $mono ? 'font-mono font-bold' : '' }} text-gray-900">
        {{ $value ?? $slot }}
    </dd>
</div>