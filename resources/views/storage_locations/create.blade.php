<x-app-layout>
<x-slot name="header">
<h2 class="text-2xl font-bold text-gray-900 leading-tight">
{{ __('Crear Ubicación Física') }}
</h2>
<p class="mt-1 text-sm text-gray-600">
{{ __('Define los componentes de la nueva ubicación.') }}
</p>
</x-slot>

<div class="py-8">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-200 p-8">
        <!-- Formulario de creación: Llama al parcial _form con el método POST -->
        <form action="{{ route('storage_locations.store') }}" method="POST">
            @csrf
            @include('storage_locations._form', ['button' => 'Guardar Ubicación'])
        </form>
    </div>
</div>


</x-app-layout>