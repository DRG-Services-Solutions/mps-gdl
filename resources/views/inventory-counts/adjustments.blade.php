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
                        Ajustes de Inventario
                    </h2>
                    <p class="text-sm text-gray-500">{{ $inventoryCount->count_number }}</p>
                </div>
            </div>
            @if($pendingCount > 0)
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                    {{ $pendingCount }} Pendientes
                </span>
            @else
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800">
                    Todos Procesados
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showRejectModal: false, rejectId: null, rejectNumber: '' }">
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

            {{-- Resumen de Ajustes --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-400">
                    <p class="text-sm text-gray-500">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $adjustments->where('status', 'pending')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-400">
                    <p class="text-sm text-gray-500">Aprobados</p>
                    <p class="text-2xl font-bold text-green-600">{{ $adjustments->where('status', 'approved')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-red-400">
                    <p class="text-sm text-gray-500">Rechazados</p>
                    <p class="text-2xl font-bold text-red-600">{{ $adjustments->where('status', 'rejected')->count() }}</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-gray-400">
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-bold text-gray-700">{{ $adjustments->count() }}</p>
                </div>
            </div>

            {{-- Lista de Ajustes --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 bg-gradient-to-r from-indigo-50 to-purple-50 border-b">
                    <h3 class="text-lg font-bold text-gray-900">Ajustes Generados</h3>
                    <p class="text-sm text-gray-600">Revisa y aprueba cada ajuste para aplicarlo al inventario</p>
                </div>

                @if($adjustments->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Motivo</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($adjustments as $adjustment)
                                    <tr class="hover:bg-gray-50
                                        {{ $adjustment->status === 'pending' ? 'bg-yellow-50' : '' }}
                                        {{ $adjustment->status === 'approved' ? 'bg-green-50' : '' }}
                                        {{ $adjustment->status === 'rejected' ? 'bg-red-50' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-gray-900">{{ $adjustment->adjustment_number }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $adjustment->product->code }}</div>
                                            <div class="text-xs text-gray-500">{{ Str::limit($adjustment->product->name, 30) }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $adjustment->type_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $adjustment->type_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $adjustment->type_color === 'orange' ? 'bg-orange-100 text-orange-800' : '' }}
                                                {{ $adjustment->type_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $adjustment->type_color === 'purple' ? 'bg-purple-100 text-purple-800' : '' }}">
                                                {{ $adjustment->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-lg font-bold {{ $adjustment->is_positive ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $adjustment->formatted_quantity }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-600">{{ Str::limit($adjustment->reason, 40) }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                                {{ $adjustment->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                {{ $adjustment->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $adjustment->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                {{ $adjustment->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}">
                                                {{ $adjustment->status_label }}
                                            </span>
                                            @if($adjustment->approved_at)
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{ $adjustment->approved_at->format('d/m H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($adjustment->status === 'pending')
                                                <div class="flex items-center justify-center space-x-2">
                                                    <form action="{{ route('inventory-adjustments.approve', $adjustment) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition-colors"
                                                                onclick="return confirm('¿Aprobar y aplicar este ajuste?')">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Aprobar
                                                        </button>
                                                    </form>
                                                    <button @click="rejectId = {{ $adjustment->id }}; rejectNumber = '{{ $adjustment->adjustment_number }}'; showRejectModal = true"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                        Rechazar
                                                    </button>
                                                </div>
                                            @elseif($adjustment->status === 'approved')
                                                <span class="text-xs text-gray-500">
                                                    Por: {{ $adjustment->approvedBy->name ?? 'N/A' }}
                                                </span>
                                            @elseif($adjustment->status === 'rejected')
                                                <span class="text-xs text-red-600" title="{{ $adjustment->rejection_reason }}">
                                                    {{ Str::limit($adjustment->rejection_reason, 20) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay ajustes</h3>
                        <p class="mt-1 text-sm text-gray-500">No se han generado ajustes para este inventario.</p>
                        <div class="mt-4">
                            <a href="{{ route('inventory-counts.review', $inventoryCount) }}" 
                               class="text-indigo-600 hover:text-indigo-800 font-medium">
                                Ir a Revisar Discrepancias →
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Acciones Finales --}}
            @if($adjustments->count() > 0)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div>
                            @if($pendingCount > 0)
                                <h4 class="font-semibold text-gray-900">Ajustes Pendientes</h4>
                                <p class="text-sm text-gray-500">
                                    Aún tienes {{ $pendingCount }} ajuste(s) por procesar.
                                </p>
                            @else
                                <h4 class="font-semibold text-green-700">¡Todos los ajustes procesados!</h4>
                                <p class="text-sm text-gray-500">
                                    Puedes aprobar el inventario.
                                </p>
                            @endif
                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('inventory-counts.review', $inventoryCount) }}" 
                               class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors">
                                Volver a Revisión
                            </a>
                            @if($pendingCount === 0)
                                <form action="{{ route('inventory-counts.approve', $inventoryCount) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                                            onclick="return confirm('¿Aprobar el inventario completo?')">
                                        Aprobar Inventario
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal Rechazar --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showRejectModal" @click="showRejectModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div x-show="showRejectModal" class="relative bg-white rounded-lg max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Rechazar Ajuste</h3>
                    <p class="text-sm text-gray-500 mb-4">Ajuste: <strong x-text="rejectNumber"></strong></p>
                    
                    <form :action="`{{ url('inventory-adjustments') }}/${rejectId}/reject`" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo del rechazo <span class="text-red-500">*</span>
                            </label>
                            <textarea name="rejection_reason" rows="3" required
                                      class="block w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"
                                      placeholder="Explica por qué rechazas este ajuste..."></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showRejectModal = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                Confirmar Rechazo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
