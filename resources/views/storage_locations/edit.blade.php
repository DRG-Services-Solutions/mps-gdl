<x-app-layout>
<x-slot name="header">
<h2 class="text-2xl font-bold text-gray-900 leading-tight">
{{ __('Editar Ubicación') }}
</h2>
{{-- Muestra la ubicación actual que se está editando usando el accesorio 'full_location' --}}
<p class="mt-1 text-sm text-gray-600">
{{ __('Modificando la ubicación:') }} <strong class="text-indigo-600">{{ $storageLocation->full_location ?? $storage_location->area . '-' . $storage_location->organizer . '-' . $storage_location->shelf_level . '-' . $storage_location->shelf_section }}</strong>
</p>
</x-slot>

<div class="py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
        {{-- Formulario de actualización: usa el método POST, se simula PUT con @method('PUT') --}}
        <form action="{{ route('storage_locations.update', $storageLocation) }}" method="POST">
            @csrf
            @method('PUT')
            
            {{-- 
                Incluye el parcial. Este parcial (si fue actualizado en pasos anteriores)
                debe poder autocompletar los campos usando la variable $storage_location, 
                la cual está disponible en esta vista.
            --}}
            @include('storage_locations._form', ['button' => 'Actualizar'])
        </form>
    </div>
</div>


</x-app-layout>