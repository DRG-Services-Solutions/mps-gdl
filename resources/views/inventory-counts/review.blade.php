<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventory-counts.show', $inventoryCount) }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Revisión de Discrepancias
                    </h2>
                    <p class="text-sm text-gray-500">{{ $inventoryCount->count_number }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                Pendiente Revisión
            </span>
        </div>
    </x-slot>

    <div class="py-12" x-data="reviewApp()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                        <button @click="show = false" class="text-green-400 hover:text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Resumen --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-400">
                    <p class="text-sm text-gray-500">Coinciden</p>
                    <p class="text-2xl font-bold text-green-600">{{ $matched->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-400">
                    <p class="text-sm text-gray-500">Faltantes</p>
                    <p class="text-2xl font-bold text-red-600">{{ $discrepancies->whereIn('status', ['shortage', 'not_found'])->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-400">
                    <p class="text-sm text-gray-500">Sobrantes</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $discrepancies->where('status', 'surplus')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
                    <p class="text-sm text-gray-500">No Esperados</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $discrepancies->where('status', 'unexpected')->count() }}</p>
                </div>
            </div>

            {{-- Discrepancias --}}
            @if($discrepancies->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 bg-gradient-to-r from-red-50 to-orange-50 border-b">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Discrepancias Detectadas</h3>
                                <p class="text-sm text-gray-600">{{ $discrepancies->count() }} productos con diferencias</p>
                            </div>
                            <form action="{{ route('inventory-counts.generate-adjustments', $inventoryCount) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    Generar Ajustes
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Sistema</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Contado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Diferencia</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Justificación</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($discrepancies as $item)
                                    <tr class="hover:bg-gray-50
                                        {{ in_array($item->status, ['shortage', 'not_found']) ? 'bg-red-50' : '' }}
                                        {{ $item->status === 'surplus' ? 'bg-blue-50' : '' }}
                                        {{ $item->status === 'unexpected' ? 'bg-yellow-50' : '' }}">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $item->product_code }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->product_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm font-medium">{{ $item->expected_quantity }}</td>
                                        <td class="px-6 py-4 text-center text-sm font-medium">{{ $item->counted_quantity }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-lg font-bold
                                                {{ $item->difference > 0 ? 'text-blue-600' : 'text-red-600' }}">
                                                {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $item->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $item->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $item->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                {{ $item->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($item->discrepancy_justified)
                                                <div class="text-sm text-gray-700">
                                                    <span class="text-green-600">✓</span> {{ Str::limit($item->discrepancy_reason, 30) }}
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400">Sin justificar</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if(!$item->discrepancy_justified)
                                                <button @click="openJustifyModal({{ $item->id }}, '{{ $item->product_code }}')"
                                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                                    Justificar
                                                </button>
                                            @else
                                                <span class="text-green-600 text-sm">Justificado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm p-8 text-center mb-6">
                    <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">¡Sin Discrepancias!</h3>
                    <p class="mt-1 text-sm text-gray-500">Todos los productos coinciden con el sistema.</p>
                </div>
            @endif

            {{-- Productos que Coinciden --}}
            @if($matched->count() > 0)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="p-6 bg-gradient-to-r from-green-50 to-emerald-50 border-b">
                        <h3 class="text-lg font-bold text-gray-900">Productos que Coinciden</h3>
                        <p class="text-sm text-gray-600">{{ $matched->count() }} productos sin diferencias</p>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($matched->take(12) as $item)
                                <div class="flex items-center p-3 bg-green-50 rounded-lg">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product_code }}</p>
                                        <p class="text-xs text-gray-500">Cant: {{ $item->counted_quantity }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($matched->count() > 12)
                            <p class="text-center text-sm text-gray-500 mt-4">
                                Y {{ $matched->count() - 12 }} productos más...
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Acciones Finales --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="font-semibold text-gray-900">¿Listo para aprobar?</h4>
                        <p class="text-sm text-gray-500">
                            @if($discrepancies->count() > 0)
                                Primero genera los ajustes para las {{ $discrepancies->count() }} discrepancias.
                            @else
                                No hay discrepancias. Puedes aprobar directamente.
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('inventory-counts.show', $inventoryCount) }}" 
                           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                            Volver
                        </a>
                        @if($discrepancies->count() > 0)
                            <a href="{{ route('inventory-counts.adjustments', $inventoryCount) }}" 
                               class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors">
                                Ver Ajustes Pendientes
                            </a>
                        @else
                            <form action="{{ route('inventory-counts.approve', $inventoryCount) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                                    Aprobar Inventario
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Justificar --}}
        <div x-show="showJustifyModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showJustifyModal" @click="showJustifyModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div x-show="showJustifyModal" class="relative bg-white rounded-lg max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Justificar Discrepancia</h3>
                    <p class="text-sm text-gray-500 mb-4">Producto: <strong x-text="justifyProductCode"></strong></p>
                    
                    <form @submit.prevent="submitJustification()">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de la discrepancia <span class="text-red-500">*</span>
                            </label>
                            <textarea x-model="justifyReason" rows="3" required
                                      class="block w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                      placeholder="Explica el motivo de la diferencia..."></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showJustifyModal = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg"
                                    :disabled="isSubmitting">
                                <span x-show="!isSubmitting">Guardar</span>
                                <span x-show="isSubmitting">Guardando...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function reviewApp() {
        return {
            showJustifyModal: false,
            justifyItemId: null,
            justifyProductCode: '',
            justifyReason: '',
            isSubmitting: false,

            openJustifyModal(itemId, productCode) {
                this.justifyItemId = itemId;
                this.justifyProductCode = productCode;
                this.justifyReason = '';
                this.showJustifyModal = true;
            },

            async submitJustification() {
                if (!this.justifyReason.trim()) return;
                
                this.isSubmitting = true;

                try {
                    const response = await fetch(`{{ url('inventory-counts') }}/{{ $inventoryCount->id }}/items/${this.justifyItemId}/justify`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            reason: this.justifyReason
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        this.showJustifyModal = false;
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
    </script>
    @endpush
</x-app-layout>
