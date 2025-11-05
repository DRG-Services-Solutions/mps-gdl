<div class="space-y-6">

<p class="text-sm text-gray-500 mb-6">
    La ubicación es una combinación única de Codigo, Organizador, Nivel y Sección.
</p>

<!-- Campo: Codigo -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="code" :value="__('Codigo (Ej: A-001)')" />
        <x-text-input id="code" name="code" type="text" class="mt-1 block w-full" :value="old('code', $storageLocation->code ?? '')" required autofocus maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('code')" />
        <p class="mt-1 text-xs text-gray-500">Codigo</p>
    </div>

    <!-- Campo: Organizador -->
    <div>
        <x-input-label for="name" :value="__('Nombre (Ej: Almacen Principal)')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $storageLocation->name ?? '')" required maxlength="100" />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
        <p class="mt-1 text-xs text-gray-500">Nombre de la Ubicacion</p>
    </div>
</div>




<!-- Campo: Descripción (Opcional) -->
<div>
    <x-input-label for="description" :value="__('Descripción')" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('description', $storageLocation->description ?? '') }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
    <p class="mt-1 text-xs text-gray-500">Información extra sobre la ubicación</p>
</div>

<!-- Botón de Envío -->
<div class="flex justify-end pt-4">
    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-md">
        {{ __($button) }}
    </button>
</div>


</div>