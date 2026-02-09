<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventory-counts.index') }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $inventoryCount->count_number }}
                    </h2>
                    <p class="text-sm text-gray-500">{{ $inventoryCount->type_label }} - {{ $inventoryCount->method_label }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold
                {{ $inventoryCount->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}
                {{ $inventoryCount->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                {{ $inventoryCount->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}
                {{ $inventoryCount->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                {{ $inventoryCount->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}">
                {{ $inventoryCount->status_label }}
            </span>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ showCancelModal: false }">
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Columna Principal --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Información General --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                            <h3 class="text-lg font-bold text-gray-900">Información General</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm text-gray-500">Razón Social</p>
                                    <p class="font-semibold text-gray-900">{{ $inventoryCount->legalEntity->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Ubicación</p>
                                    <p class="font-semibold text-gray-900">{{ $inventoryCount->location_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Tipo</p>
                                    <p class="font-semibold text-gray-900">{{ $inventoryCount->type_label }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Método</p>
                                    <p class="font-semibold text-gray-900">{{ $inventoryCount->method_label }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Creado por</p>
                                    <p class="font-semibold text-gray-900">{{ $inventoryCount->createdBy->name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Fecha Creación</p>
                                    <p class="font-semibold text-gray-900">{{ $inventoryCount->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @if($inventoryCount->started_at)
                                    <div>
                                        <p class="text-sm text-gray-500">Fecha Inicio</p>
                                        <p class="font-semibold text-gray-900">{{ $inventoryCount->started_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                                @if($inventoryCount->completed_at)
                                    <div>
                                        <p class="text-sm text-gray-500">Fecha Finalización</p>
                                        <p class="font-semibold text-gray-900">{{ $inventoryCount->completed_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                                @if($inventoryCount->approved_at)
                                    <div>
                                        <p class="text-sm text-gray-500">Aprobado por</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ $inventoryCount->approvedBy->name }}<br>
                                            <span class="text-xs text-gray-500">{{ $inventoryCount->approved_at->format('d/m/Y H:i') }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if($inventoryCount->notes)
                                <div class="mt-6 pt-6 border-t">
                                    <p class="text-sm text-gray-500 mb-1">Notas</p>
                                    <p class="text-gray-700">{{ $inventoryCount->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Estadísticas de Conteo --}}
                    @if($inventoryCount->status !== 'draft')
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b">
                                <h3 class="text-lg font-bold text-gray-900">Resultado del Conteo</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                                        <p class="text-3xl font-bold text-gray-700">{{ $itemStats['total'] }}</p>
                                        <p class="text-sm text-gray-500">Total Productos</p>
                                    </div>
                                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                                        <p class="text-3xl font-bold text-blue-600">{{ $itemStats['pending'] }}</p>
                                        <p class="text-sm text-blue-600">Pendientes</p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-4 text-center">
                                        <p class="text-3xl font-bold text-green-600">{{ $itemStats['matched'] }}</p>
                                        <p class="text-sm text-green-600">Coinciden</p>
                                    </div>
                                    <div class="bg-red-50 rounded-lg p-4 text-center">
                                        <p class="text-3xl font-bold text-red-600">{{ $itemStats['discrepancies'] }}</p>
                                        <p class="text-sm text-red-600">Discrepancias</p>
                                    </div>
                                </div>

                                @if($inventoryCount->accuracy_percentage !== null)
                                    <div class="mt-6">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">Precisión del Inventario</span>
                                            <span class="text-lg font-bold 
                                                {{ $inventoryCount->accuracy_percentage >= 95 ? 'text-green-600' : '' }}
                                                {{ $inventoryCount->accuracy_percentage >= 80 && $inventoryCount->accuracy_percentage < 95 ? 'text-yellow-600' : '' }}
                                                {{ $inventoryCount->accuracy_percentage < 80 ? 'text-red-600' : '' }}">
                                                {{ number_format($inventoryCount->accuracy_percentage, 1) }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="h-4 rounded-full transition-all duration-500
                                                {{ $inventoryCount->accuracy_percentage >= 95 ? 'bg-green-500' : '' }}
                                                {{ $inventoryCount->accuracy_percentage >= 80 && $inventoryCount->accuracy_percentage < 95 ? 'bg-yellow-500' : '' }}
                                                {{ $inventoryCount->accuracy_percentage < 80 ? 'bg-red-500' : '' }}"
                                                 style="width: {{ $inventoryCount->accuracy_percentage }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Lista de Items (resumen) --}}
                    @if($inventoryCount->items->count() > 0)
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b flex items-center justify-between">
                                <h3 class="text-lg font-bold text-gray-900">Productos ({{ $inventoryCount->items->count() }})</h3>
                            </div>
                            <div class="overflow-x-auto max-h-96">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Producto</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Sistema</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Contado</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Diferencia</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($inventoryCount->items->take(20) as $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->product_code }}</div>
                                                    <div class="text-xs text-gray-500">{{ Str::limit($item->product_name, 40) }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-center text-sm">{{ $item->expected_quantity }}</td>
                                                <td class="px-4 py-3 text-center text-sm">{{ $item->counted_quantity }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="text-sm font-semibold
                                                        {{ $item->difference > 0 ? 'text-blue-600' : '' }}
                                                        {{ $item->difference < 0 ? 'text-red-600' : '' }}
                                                        {{ $item->difference == 0 ? 'text-green-600' : '' }}">
                                                        {{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold
                                                        {{ $item->status_color === 'gray' ? 'bg-gray-100 text-gray-800' : '' }}
                                                        {{ $item->status_color === 'green' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $item->status_color === 'red' ? 'bg-red-100 text-red-800' : '' }}
                                                        {{ $item->status_color === 'blue' ? 'bg-blue-100 text-blue-800' : '' }}
                                                        {{ $item->status_color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                                        {{ $item->status_label }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($inventoryCount->items->count() > 20)
                                    <div class="p-4 bg-gray-50 text-center">
                                        <p class="text-sm text-gray-500">Mostrando 20 de {{ $inventoryCount->items->count() }} productos</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Columna Lateral --}}
                <div class="space-y-6">
                    {{-- Acciones --}}
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-b">
                            <h4 class="text-lg font-bold text-gray-900">Acciones</h4>
                        </div>
                        <div class="p-6 space-y-3">
                            @if(in_array($inventoryCount->status, ['draft', 'in_progress']))
                                <a href="{{ route('inventory-counts.count', $inventoryCount) }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    {{ $inventoryCount->status === 'draft' ? 'Iniciar Conteo' : 'Continuar Conteo' }}
                                </a>
                            @endif

                            @if($inventoryCount->status === 'pending_review')
                                <a href="{{ route('inventory-counts.review', $inventoryCount) }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Revisar Discrepancias
                                </a>

                                <a href="{{ route('inventory-counts.adjustments', $inventoryCount) }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                    </svg>
                                    Ver Ajustes
                                </a>
                            @endif

                            @if($inventoryCount->status === 'approved')
                                <a href="{{ route('inventory-counts.report', $inventoryCount) }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Ver Reporte
                                </a>
                            @endif

                            @if($inventoryCount->canBeEdited())
                                <button @click="showCancelModal = true" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar Inventario
                                </button>
                            @endif

                            <a href="{{ route('inventory-counts.index') }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Volver al Listado
                            </a>
                        </div>
                    </div>

                    {{-- Timeline (si aplica) --}}
                    @if($inventoryCount->started_at || $inventoryCount->completed_at || $inventoryCount->approved_at)
                        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                            <div class="p-6 border-b">
                                <h4 class="text-lg font-bold text-gray-900">Historial</h4>
                            </div>
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Creado</p>
                                            <p class="text-xs text-gray-500">{{ $inventoryCount->created_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>

                                    @if($inventoryCount->started_at)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Iniciado</p>
                                                <p class="text-xs text-gray-500">{{ $inventoryCount->started_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($inventoryCount->completed_at)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Conteo Finalizado</p>
                                                <p class="text-xs text-gray-500">{{ $inventoryCount->completed_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                        </div>
                                    @endif

                                    @if($inventoryCount->approved_at)
                                        <div class="flex items-start space-x-3">
                                            <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Aprobado</p>
                                                <p class="text-xs text-gray-500">{{ $inventoryCount->approved_at->format('d/m/Y H:i') }}</p>
                                                <p class="text-xs text-gray-500">por {{ $inventoryCount->approvedBy->name }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal Cancelar --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div x-show="showCancelModal" @click="showCancelModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                <div x-show="showCancelModal" class="relative bg-white rounded-lg max-w-md w-full p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Cancelar Toma de Inventario</h3>
                    <form action="{{ route('inventory-counts.cancel', $inventoryCount) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Motivo de Cancelación <span class="text-red-500">*</span>
                            </label>
                            <textarea name="cancellation_reason" rows="3" required
                                      class="block w-full rounded-lg border-gray-300 focus:ring-red-500 focus:border-red-500"></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showCancelModal = false"
                                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
                                Cerrar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                                Confirmar Cancelación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
