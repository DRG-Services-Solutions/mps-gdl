<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">
            {{ __('Editar Ubicación') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <form action="{{ route('storage_locations.update', $storage_location) }}" method="POST">
                @csrf
                @method('PUT')
                @include('storage_locations._form', ['button' => 'Actualizar'])
            </form>
        </div>
    </div>
</x-app-layout>
