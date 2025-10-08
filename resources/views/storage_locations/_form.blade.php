<div class="space-y-6">
    <div>
        <label class="block text-sm font-medium text-gray-700">Código</label>
        <input type="text" name="code" value="{{ old('code', $storage_location->code ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @error('code') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Nombre</label>
        <input type="text" name="name" value="{{ old('name', $storage_location->name ?? '') }}"
               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Descripción</label>
        <textarea name="description"
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $storage_location->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Tipo</label>
        <select name="type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            @foreach(['warehouse' => 'Almacén', 'reception' => 'Recepción', 'quarantine' => 'Cuarentena', 'shipping' => 'Envío'] as $key => $label)
                <option value="{{ $key }}" @selected(old('type', $storage_location->type ?? '') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="flex items-center">
        <input id="is_active" name="is_active" type="checkbox" value="1"
               class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
               {{ old('is_active', $storage_location->is_active ?? true) ? 'checked' : '' }}>
        <label for="is_active" class="ml-2 text-sm text-gray-700">Activo</label>
    </div>

    <div class="flex justify-end space-x-3">
        <a href="{{ route('storage_locations.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancelar</a>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ $button }}</button>
    </div>
</div>
