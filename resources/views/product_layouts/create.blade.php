<x-app-layout>
<x-slot name="header">
<h2 class="text-2xl font-bold text-gray-900 leading-tight">
{{ __('Crear Nuevo Layout de Producto') }}
</h2>
</x-slot>

<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <form action="{{ route('product_layouts.store') }}" method="POST" class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
            @csrf
            
            <h3 class="text-xl font-semibold text-gray-800 mb-6 border-b pb-3">{{ __('Detalles de la Ubicación Exacta') }}</h3>

            {{-- Contenedor de Alertas de Validación --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <p class="font-bold mb-1">Se encontraron los siguientes errores:</p>
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="space-y-6">
                {{-- Campo de Bodega Principal (Storage Location) --}}
                <div>
                    <label for="storage_location_id" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Bodega Principal (Storage Location)') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="storage_location_id" name="storage_location_id" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('storage_location_id') border-red-500 @enderror">
                        <option value="">{{ __('Seleccione una bodega') }}</option>
                        {{-- $storageLocations viene del método create() del controlador --}}
                        @foreach($storageLocations as $location)
                            <option value="{{ $location->id }}" {{ old('storage_location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->name }} ({{ $location->code }})
                            </option>
                        @endforeach
                    </select>
                    @error('storage_location_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Campo de Producto (Asumiendo que tienes una lista de $products) --}}
                {{-- Si no tienes la lista $products en el controlador, remueve este bloque o añádela --}}
                <div>
                    <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('Producto') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="product_id" name="product_id" required
                            placeholder="ID o Código de Producto (temporal)"
                            value="{{ old('product_id') }}"
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('product_id') border-red-500 @enderror">
                    @error('product_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    {{-- Idealmente, esto sería un SELECT o un buscador de productos real --}}
                </div>


                {{-- Estante, Nivel y Posición en GRID para mejor visualización --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    {{-- Estante (shelf) --}}
                    <div>
                        <label for="shelf" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Estante (Ej: 1, 2, 3)') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="shelf" name="shelf" required
                               value="{{ old('shelf') }}" min="1" step="1"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('shelf') border-red-500 @enderror">
                        @error('shelf')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nivel (level) --}}
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Nivel (Ej: A, B, C)') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="level" name="level" required
                               value="{{ old('level') }}" maxlength="2"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase @error('level') border-red-500 @enderror">
                        @error('level')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Posición (position) --}}
                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('Posición Decimal (Ej: 3.2, 1.0)') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="position" name="position" required
                               value="{{ old('position') }}" step="0.01"
                               class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('position') border-red-500 @enderror">
                        @error('position')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
                
            </div>
            
            <div class="mt-8 pt-6 border-t flex justify-end space-x-3">
                <a href="{{ route('product_layouts.index') }}" 
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 shadow-sm transition-colors">
                    {{ __('Cancelar') }}
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-all duration-200">
                    <i class="fas fa-save mr-2"></i>
                    {{ __('Guardar Layout') }}
                </button>
            </div>
        </form>
    </div>
</div>


</x-app-layout>