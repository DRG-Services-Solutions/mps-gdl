<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('purchase-orders.show', $receipt->purchaseOrder) }}" 
                   class="text-gray-600 hover:text-gray-900 transition-colors duration-150">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Trabajos de Impresión RFID
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Recepción: <strong>{{ $receipt->receipt_number }}</strong>
                    </p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Total -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Total</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-full">
                            <i class="fas fa-tags text-gray-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pendientes -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Imprimiendo -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Imprimiendo</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['printing'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <i class="fas fa-print text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Completados -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Completados</p>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Fallidos -->
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Fallidos</p>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['failed'] }}</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-full">
                            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            @if($stats['failed'] > 0 || $stats['pending'] > 0)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-tools text-gray-500"></i>
                            <span class="text-sm font-medium text-gray-700">Acciones rápidas</span>
                        </div>
                        <div class="flex space-x-2">
                            @if($stats['failed'] > 0)
                                <form action="{{ route('receipts.print-jobs.retry', $receipt) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-redo mr-1"></i>
                                        Reintentar Fallidos
                                    </button>
                                </form>
                            @endif

                            @if($stats['pending'] > 0)
                                <form action="{{ route('receipts.print-jobs.cancel', $receipt) }}" method="POST" class="inline"
                                      onsubmit="return confirm('¿Estás seguro de cancelar todos los trabajos pendientes?')">
                                    @csrf
                                    <button type="submit" 
                                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                                        <i class="fas fa-times mr-1"></i>
                                        Cancelar Pendientes
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tabla de trabajos -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <h3 class="text-xl font-bold text-white">Detalle de Trabajos de Impresión</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    # Trabajo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Producto
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Código EPC
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Reintentos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                    Fecha
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($printJobs as $job)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $job->job_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div class="font-medium">{{ $job->productUnit->product->code }}</div>
                                        <div class="text-xs text-gray-500">{{ $job->productUnit->product->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-mono text-gray-600">
                                        {{ $job->epc_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @switch($job->status)
                                            @case('pending')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Pendiente
                                                </span>
                                                @break
                                            @case('printing')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                    <i class="fas fa-print mr-1"></i>
                                                    Imprimiendo
                                                </span>
                                                @break
                                            @case('completed')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    Completado
                                                </span>
                                                @break
                                            @case('failed')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    Fallido
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                                    <i class="fas fa-ban mr-1"></i>
                                                    Cancelado
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($job->retry_count > 0)
                                            <span class="text-red-600 font-semibold">{{ $job->retry_count }}</span>
                                        @else
                                            <span class="text-gray-400">0</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div>{{ $job->created_at->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $job->created_at->format('H:i:s') }}</div>
                                    </td>
                                </tr>
                                @if($job->error_message && $job->status === 'failed')
                                    <tr class="bg-red-50">
                                        <td colspan="6" class="px-6 py-2 text-sm text-red-700">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Error:</strong> {{ $job->error_message }}
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                                        <p class="text-gray-500">No hay trabajos de impresión registrados</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($printJobs->hasPages())
                    <div class="bg-gray-50 px-6 py-4">
                        {{ $printJobs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>