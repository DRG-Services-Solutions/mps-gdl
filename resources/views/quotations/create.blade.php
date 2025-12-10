<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                    {{ __('Nueva Cotización') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Crear una nueva cotización para cirugía</p>
            </div>
            <a href="{{ route('quotations.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('quotations.store') }}" class="p-6 space-y-6">
                    @csrf

                    <!-- Hospital -->
                    <div>
                        <label for="hospital_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-hospital mr-1 text-indigo-600"></i>
                            Hospital <span class="text-red-500">*</span>
                        </label>
                        <select id="hospital_id" 
                                name="hospital_id" 
                                required
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('hospital_id') border-red-500 @enderror">
                            <option value="">Seleccionar hospital...</option>
                            @foreach($hospitals as $hospital)
                                <option value="{{ $hospital->id }}" {{ old('hospital_id') == $hospital->id ? 'selected' : '' }}>
                                    {{ $hospital->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('hospital_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Doctor -->
                    <div>
                        <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-user-md mr-1 text-indigo-600"></i>
                            Doctor / Cirujano
                        </label>
                        <select id="doctor_id" 
                                name="doctor_id" 
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('doctor_id') border-red-500 @enderror">
                            <option value="">Seleccionar doctor (opcional)...</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->first_name }} {{ $doctor->last_name ? ' ' . $doctor->last_name : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Cirugía -->
                    <div>
                        <label for="surgery_type" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-stethoscope mr-1 text-indigo-600"></i>
                            Tipo de Cirugía
                        </label>
                        <input type="text" 
                               id="surgery_type" 
                               name="surgery_type" 
                               value="{{ old('surgery_type') }}"
                               placeholder="Ej: Artroscopia de rodilla, Reconstrucción de LCA..."
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('surgery_type') border-red-500 @enderror">
                        @error('surgery_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha de Cirugía -->
                    <div>
                        <label for="surgery_date" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-calendar mr-1 text-indigo-600"></i>
                            Fecha de Cirugía
                        </label>
                        <input type="date" 
                               id="surgery_date" 
                               name="surgery_date" 
                               value="{{ old('surgery_date') }}"
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('surgery_date') border-red-500 @enderror">
                        @error('surgery_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Razón Social (Facturación) -->
                    <div>
                        <label for="billing_legal_entity_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-briefcase mr-1 text-indigo-600"></i>
                            Razón Social (Facturación) <span class="text-red-500">*</span>
                        </label>
                        <select id="billing_legal_entity_id" 
                                name="billing_legal_entity_id" 
                                required
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('billing_legal_entity_id') border-red-500 @enderror">
                            <option value="">Seleccionar razón social...</option>
                            @foreach($legalEntities as $entity)
                                <option value="{{ $entity->id }}" {{ old('billing_legal_entity_id') == $entity->id ? 'selected' : '' }}>
                                    {{ $entity->business_name }} ({{ $entity->rfc }})
                                </option>
                            @endforeach
                        </select>
                        @error('billing_legal_entity_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Selecciona desde qué razón social se facturará esta cotización
                        </p>
                    </div>

                    <!-- Notas -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-sticky-note mr-1 text-indigo-600"></i>
                            Notas / Observaciones
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="4"
                                  placeholder="Información adicional sobre la cirugía..."
                                  class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Después de crear la cotización, podrás agregar los productos que se enviarán a cirugía.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('quotations.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                            <i class="fas fa-save mr-2"></i>
                            Crear Cotización
                        </button>
                    </div>

                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>