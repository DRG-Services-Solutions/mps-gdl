<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-edit mr-2 text-indigo-600"></i>
                    {{ __('Editar Cotización') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $quotation->quotation_number }}</p>
            </div>
            <a href="{{ route('quotations.show', $quotation) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Warning -->
            @if($quotation->status !== 'draft')
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Atención:</strong> Solo se pueden editar cotizaciones en estado "Borrador".
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('quotations.update', $quotation) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Hospital -->
                    <div>
                        <label for="hospital_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-hospital mr-1 text-indigo-600"></i>
                            Hospital <span class="text-red-500">*</span>
                        </label>
                        <select id="hospital_id" 
                                name="hospital_id" 
                                required
                                {{ $quotation->status !== 'draft' ? 'disabled' : '' }}
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('hospital_id') border-red-500 @enderror {{ $quotation->status !== 'draft' ? 'bg-gray-100' : '' }}">
                            <option value="">Seleccionar hospital...</option>
                            @foreach($hospitals as $hospital)
                                <option value="{{ $hospital->id }}" {{ old('hospital_id', $quotation->hospital_id) == $hospital->id ? 'selected' : '' }}>
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
                                {{ $quotation->status !== 'draft' ? 'disabled' : '' }}
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('doctor_id') border-red-500 @enderror {{ $quotation->status !== 'draft' ? 'bg-gray-100' : '' }}">
                            <option value="">Seleccionar doctor (opcional)...</option>
                            @foreach($doctors as $doctor)
                                <option value="{{ $doctor->id }}" {{ old('doctor_id', $quotation->doctor_id) == $doctor->id ? 'selected' : '' }}>
                                    {{ $doctor->full_name }} {{ $doctor->specialty ? '- ' . $doctor->specialty : '' }}
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
                               value="{{ old('surgery_type', $quotation->surgery_type) }}"
                               {{ $quotation->status !== 'draft' ? 'disabled' : '' }}
                               placeholder="Ej: Artroscopia de rodilla, Reconstrucción de LCA..."
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('surgery_type') border-red-500 @enderror {{ $quotation->status !== 'draft' ? 'bg-gray-100' : '' }}">
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
                               value="{{ old('surgery_date', $quotation->surgery_date?->format('Y-m-d')) }}"
                               {{ $quotation->status !== 'draft' ? 'disabled' : '' }}
                               class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('surgery_date') border-red-500 @enderror {{ $quotation->status !== 'draft' ? 'bg-gray-100' : '' }}">
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
                                {{ $quotation->status !== 'draft' ? 'disabled' : '' }}
                                class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('billing_legal_entity_id') border-red-500 @enderror {{ $quotation->status !== 'draft' ? 'bg-gray-100' : '' }}">
                            <option value="">Seleccionar razón social...</option>
                            @foreach($legalEntities as $entity)
                                <option value="{{ $entity->id }}" {{ old('billing_legal_entity_id', $quotation->billing_legal_entity_id) == $entity->id ? 'selected' : '' }}>
                                    {{ $entity->business_name }} ({{ $entity->rfc }})
                                </option>
                            @endforeach
                        </select>
                        @error('billing_legal_entity_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                                  {{ $quotation->status !== 'draft' ? 'disabled' : '' }}
                                  placeholder="Información adicional sobre la cirugía..."
                                  class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm @error('notes') border-red-500 @enderror {{ $quotation->status !== 'draft' ? 'bg-gray-100' : '' }}">{{ old('notes', $quotation->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('quotations.show', $quotation) }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                        @if($quotation->status === 'draft')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 transition">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Cambios
                            </button>
                        @endif
                    </div>

                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>