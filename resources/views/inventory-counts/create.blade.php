<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory-counts.index') }}" 
               class="text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nueva Toma de Inventario') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <p class="text-red-700 font-medium">{{ session('error') }}</p>
                        <button @click="show = false" class="text-red-400 hover:text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <h3 class="text-lg font-bold text-gray-900">Configurar Toma de Inventario</h3>
                    <p class="mt-1 text-sm text-gray-600">Define el alcance y método del conteo físico</p>
                </div>

                <form action="{{ route('inventory-counts.store') }}" method="POST" class="p-6 space-y-6" x-data="createInventoryForm()">
                    @csrf

                    {{-- Tipo de Inventario --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Tipo de Inventario <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="type" value="full" class="peer sr-only" {{ old('type') == 'full' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600 peer-checked:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span class="text-sm font-medium">Completo</span>
                                    <span class="block text-xs text-gray-500">Todo el inventario</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="type" value="partial" class="peer sr-only" {{ old('type', 'partial') == 'partial' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Parcial</span>
                                    <span class="block text-xs text-gray-500">Por ubicación</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="type" value="cyclic" class="peer sr-only" {{ old('type') == 'cyclic' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span class="text-sm font-medium">Cíclico</span>
                                    <span class="block text-xs text-gray-500">Rotativo ABC</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="type" value="spot_check" class="peer sr-only" {{ old('type') == 'spot_check' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Verificación</span>
                                    <span class="block text-xs text-gray-500">Auditoría</span>
                                </div>
                            </label>
                        </div>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Método de Conteo --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">
                            Método de Conteo <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="method" value="rfid_bulk" class="peer sr-only" {{ old('method') == 'rfid_bulk' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                                    </svg>
                                    <span class="text-sm font-medium">RFID Masivo</span>
                                    <span class="block text-xs text-gray-500">Portal/Fijo</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="method" value="rfid_handheld" class="peer sr-only" {{ old('method') == 'rfid_handheld' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium">RFID Portátil</span>
                                    <span class="block text-xs text-gray-500">Pistola/Handheld</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="method" value="barcode_scan" class="peer sr-only" {{ old('method', 'barcode_scan') == 'barcode_scan' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Código Barras</span>
                                    <span class="block text-xs text-gray-500">Escaneo</span>
                                </div>
                            </label>

                            <label class="relative cursor-pointer">
                                <input type="radio" name="method" value="manual" class="peer sr-only" {{ old('method') == 'manual' ? 'checked' : '' }}>
                                <div class="p-4 border-2 rounded-lg text-center peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-gray-400 transition-all">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Manual</span>
                                    <span class="block text-xs text-gray-500">Conteo visual</span>
                                </div>
                            </label>
                        </div>
                        @error('method')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alcance --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="legal_entity_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Razón Social <span class="text-red-500">*</span>
                            </label>
                            <select name="legal_entity_id" id="legal_entity_id" 
                                    x-model="legalEntityId"
                                    @change="loadSubWarehouses()"
                                    class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">Seleccionar...</option>
                                @foreach($legalEntities as $entity)
                                    <option value="{{ $entity->id }}" {{ old('legal_entity_id') == $entity->id ? 'selected' : '' }}>
                                        {{ $entity->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('legal_entity_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sub_warehouse_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Sub-Almacén <span class="text-gray-400">(Opcional)</span>
                            </label>
                            <select name="sub_warehouse_id" id="sub_warehouse_id"
                                    x-model="subWarehouseId"
                                    @change="loadLocations()"
                                    class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todos los sub-almacenes</option>
                                @foreach($subWarehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('sub_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="storage_location_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                Ubicación Específica <span class="text-gray-400">(Opcional)</span>
                            </label>
                            <select name="storage_location_id" id="storage_location_id" 
                                    class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Todas las ubicaciones</option>
                                @foreach($storageLocations as $location)
                                    <option value="{{ $location->id }}" {{ old('storage_location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-semibold text-gray-700 mb-2">
                                Fecha Programada <span class="text-gray-400">(Opcional)</span>
                            </label>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" 
                                   value="{{ old('scheduled_at') }}"
                                   class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                            Notas <span class="text-gray-400">(Opcional)</span>
                        </label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Instrucciones especiales, observaciones...">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Botones --}}
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('inventory-counts.index') }}" 
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            Crear Toma de Inventario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function createInventoryForm() {
        return {
            legalEntityId: '{{ old('legal_entity_id') }}',
            subWarehouseId: '{{ old('sub_warehouse_id') }}',
            
            loadSubWarehouses() {
                // Si tienes API para cargar sub-almacenes dinámicamente
            },
            
            loadLocations() {
                // Si tienes API para cargar ubicaciones dinámicamente
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
