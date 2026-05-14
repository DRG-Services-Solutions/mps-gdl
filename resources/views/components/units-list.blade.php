@props(['action', 'method' => 'POST', 'confirmText'])
<form action="{{ $action }}" method="POST" onsubmit="return confirm('{{ $confirmText }}')">
    @csrf
    @method($method)
    <button type="submit" {{ $attributes->merge(['class' => 'px-4 py-2 text-sm font-medium border rounded-lg transition-colors']) }}>
        {{ $slot }}
    </button>
</form>