{{-- resources/views/invoices/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-plus-circle mr-2 text-yellow-600"></i>
                    {{ __('Generar Remisión') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $surgery->code }} - {{ $surgery->patient_name }}</p>
            </div>
            <a href="{{ route('surgeries.show', $surgery) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('invoices.store') }}" method="POST">
                @csrf
                <input type="hidden" name="surgery_id" value="{{ $surgery->id }}">

                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="p-6 space-y-6">
                        <!-- Información de la Cirugía -->
                        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                <i class="fas fa-procedures mr-2 text-indigo-600"></i>
                                Información de la Cirugía
                            </h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Código</dt>
                                    <dd class="text-sm text-gray-900 font-semibold">{{ $surgery->code }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Paciente</dt>
                                    <dd class="text-sm text-gray-900 font-semibold">{{ $surgery->patient_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Hospital</dt>
                                    <dd class="text-sm text-gray-900 font-semibold">{{ $surgery->hospital->business_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Doctor</dt>
                                    <dd class="text-sm text-gray-900 font-semibold">{{ $surgery->doctor->name }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Productos de la Preparación -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-box mr-2 text-yellow-600"></i>
                                Productos a Incluir en la Remisión
                            </h3>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            $subtotal = 0;
                                        @endphp
                                        @foreach($surgery->preparation->items as $item)
                                        @php
                                            $itemSubtotal = $item->quantity_required * $item->product->price;
                                            $subtotal += $itemSubtotal;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-semibold text-gray-900">{{ $item->product->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $item->product->code }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                                    {{ $item->quantity_required }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-900">
                                                ${{ number_format($item->product->price, 2) }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                                ${{ number_format($itemSubtotal, 2) }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Subtotal:</td>
                                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">${{ number_format($subtotal, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-right text-sm font-semibold text-gray-900">IVA (16%):</td>
                                            <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">${{ number_format($subtotal * 0.16, 2) }}</td>
                                        </tr>
                                        <tr class="bg-yellow-50">
                                            <td colspan="3" class="px-6 py-4 text-right text-lg font-bold text-gray-900">Total:</td>
                                            <td class="px-6 py-4 text-right text-xl font-bold text-yellow-600">${{ number_format($subtotal * 1.16, 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Notas Adicionales -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notas Adicionales
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3"
                                      placeholder="Notas u observaciones para la remisión..."
                                      class="w-full rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('surgeries.show', $surgery) }}" 
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 transition-colors">
                            <i class="fas fa-save mr-1"></i>
                            Generar Remisión
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>