<div class="space-y-6">

<p class="text-sm text-gray-500 mb-6">
    La ubicación es una combinación única de Área, Organizador, Nivel y Sección.
</p>

<!-- Campo: Área -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <x-input-label for="area" :value="__('Área (Ej: A, R01)')" />
        <x-text-input id="area" name="area" type="text" class="mt-1 block w-full" :value="old('area', $storageLocation->area ?? '')" required autofocus maxlength="50" />
        <x-input-error class="mt-2" :messages="$errors->get('area')" />
        <p class="mt-1 text-xs text-gray-500">Máximo 50 caracteres. Este es el primer componente de la ubicación.</p>
    </div>

    <!-- Campo: Organizador -->
    <div>
        <x-input-label for="organizer" :value="__('Organizador (Ej: 1, F02)')" />
        <x-text-input id="organizer" name="organizer" type="text" class="mt-1 block w-full" :value="old('organizer', $storageLocation->organizer ?? '')" required maxlength="10" />
        <x-input-error class="mt-2" :messages="$errors->get('organizer')" />
        <p class="mt-1 text-xs text-gray-500">Máximo 10 caracteres.</p>
    </div>
</div>

<!-- Campos: Nivel y Sección (Números enteros) -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <!-- Campo: Nivel de Estantería -->
    <div>
        <x-input-label for="shelf_level" :value="__('Nivel de Estantería ')" />
        <x-text-input id="shelf_level" name="shelf_level" type="number" class="mt-1 block w-full" :value="old('shelf_level', $storageLocation->shelf_level ?? 1)" required min="1" />
        <x-input-error class="mt-2" :messages="$errors->get('shelf_level')" />
    </div>

    <!-- Campo: Sección de Estantería -->
    <div>
        <x-input-label for="shelf_section" :value="__('Sección de Estantería ')" />
        <x-text-input id="shelf_section" name="shelf_section" type="number" class="mt-1 block w-full" :value="old('shelf_section', $storageLocation->shelf_section ?? 1)" required min="1" />
        <x-input-error class="mt-2" :messages="$errors->get('shelf_section')" />
    </div>
</div>

<div class="py-4 border-t border-gray-200"></div>

<!-- Campo: Descripción (Opcional) -->
<div>
    <x-input-label for="description" :value="__('Descripción o Notas Adicionales')" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">{{ old('description', $storageLocation->description ?? '') }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
    <p class="mt-1 text-xs text-gray-500">Información extra sobre la ubicación (ej: contiene productos frágiles).</p>
</div>

<!-- Botón de Envío -->
<div class="flex justify-end pt-4">
    <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-50 transition ease-in-out duration-150 shadow-md">
        {{ __($button) }}
    </button>
</div>


</div>