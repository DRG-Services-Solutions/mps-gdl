<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    <i class="fas fa-edit mr-2 text-indigo-600"></i>Editar Remisión
                </h2>
                <p class="mt-1 text-sm text-gray-600">{{ $shippingNote->shipping_number }}</p>
            </div>
            <a href="{{ route('shipping-notes.show', $shippingNote) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-300 transition">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">

                {{-- Datos no editables (referencia) --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Datos de la Cirugía (no editables)</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Hospital:</span>
                            <span class="ml-1 text-gray-900 font-medium">{{ $shippingNote->hospital->name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Doctor:</span>
                            <span class="ml-1 text-gray-900">{{ $shippingNote->doctor->full_name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Checklist:</span>
                            <span class="ml-1 text-gray-900">{{ $shippingNote->surgicalChecklist->surgery_type ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Fecha de cirugía:</span>
                            <span class="ml-1 text-gray-900">{{ $shippingNote->surgery_date?->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Formulario editable --}}
                <form method="POST" action="{{ route('shipping-notes.update', $shippingNote) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Razón Social (Facturación) <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_legal_entity_id" required
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($legalEntities as $entity)
                                    <option value="{{ $entity->id }}"
                                        {{ (old('billing_legal_entity_id', $shippingNote->billing_legal_entity_id) == $entity->id) ? 'selected' : '' }}>
                                        {{ $entity->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('billing_legal_entity_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas</label>
                            <textarea name="notes" rows="4"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Observaciones adicionales...">{{ old('notes', $shippingNote->notes) }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('shipping-notes.show', $shippingNote) }}"
                            class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            <i class="fas fa-save mr-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>

                {{-- Zona de peligro --}}
                <div class="mt-8 pt-6 border-t border-red-200">
                    <h4 class="text-sm font-semibold text-red-700 mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Zona de peligro
                    </h4>
                    <div class="flex items-center justify-between bg-red-50 rounded-lg p-4">
                        <div>
                            <p class="text-sm text-red-800 font-medium">Eliminar esta remisión</p>
                            <p class="text-xs text-red-600 mt-0.5">Se liberarán los paquetes asignados. Esta acción no se puede deshacer.</p>
                        </div>
                        <form method="POST" action="{{ route('shipping-notes.destroy', $shippingNote) }}"
                            onsubmit="return confirm('¿Eliminar esta remisión permanentemente?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition">
                                <i class="fas fa-trash mr-1"></i>Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>