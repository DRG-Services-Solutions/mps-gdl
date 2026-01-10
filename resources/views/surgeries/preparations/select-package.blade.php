{{-- resources/views/surgeries/preparations/select-package.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-purple-600"></i>
                   Seleccionar Paquete Pre-Armado
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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Información de la Cirugía -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-indigo-100">Check List</p>
                        <p class="text-lg font-semibold">{{ $surgery->checklist->surgery_type }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-100">Hospital</p>
                        <p class="text-lg font-semibold">{{ $surgery->hospital->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-100">Fecha de Cirugía</p>
                        <p class="text-lg font-semibold">{{ $surgery->surgery_datetime->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-indigo-100">Modalidad</p>
                        <p class="text-lg font-semibold">{{ $surgery->modality->name }}</p>
                        TEST: {{ $surgery->preAssembledPackage }}
                    </div>
                </div>
            </div>

            <!-- Opciones -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Opción 1: Usar Paquete Existente -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border-2 border-green-200 hover:border-green-400 transition-colors">
                    <div class="px-6 py-4 bg-gradient-to-r from-green-50 to-green-100 border-b border-green-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-box-open mr-2 text-green-600"></i>
                            Usar Paquete Pre-Armado Existente
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Selecciona un paquete disponible y completa los faltantes</p>
                    </div>
                    <div class="p-6">
                        <div class="text-center mb-4">
                            <div class="text-4xl font-bold text-green-600">{{ $availablePackages->count() }}</div>
                            <div class="text-sm text-gray-600">Paquetes disponibles</div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-check-circle text-green-600 mr-1"></i>
                            Aprovecha productos ya preparados
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-clock text-green-600 mr-1"></i>
                            Ahorra tiempo en preparación
                        </p>
                    </div>
                </div>

                <!-- Opción 2: Crear Desde Cero -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden border-2 border-blue-200 hover:border-blue-400 transition-colors">
                    <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-layer-group mr-2 text-blue-600"></i>
                            Preparar Desde Cero
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Arma un nuevo paquete completo</p>
                    </div>
                    <div class="p-6">
                        <div class="text-center mb-4">
                            <div class="text-4xl font-bold text-blue-600">100%</div>
                            <div class="text-sm text-gray-600">Control total</div>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-cog text-blue-600 mr-1"></i>
                            Control total del proceso
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-box text-blue-600 mr-1"></i>
                            Selecciona cada producto
                        </p>
                        <form action="{{ route('surgeries.preparations.assignPackage', $surgery) }}" method="POST">
                            @csrf
                            <input type="hidden" name="package_id" value="">
                            <button type="submit" 
                                    class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-play mr-1"></i>
                                Preparar Desde Cero
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Paquetes Disponibles -->
            @if($availablePackages->count() > 0)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-green-600"></i>
                        Paquetes Pre-Armados Disponibles
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paquete</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Contenido</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Completitud</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Usos</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($availablePackages as $package)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box-open text-green-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $package->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $package->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $package->contents->count() }} items
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $completeness = $package->getCompletenessPercentage();
                                        $color = $completeness >= 80 ? 'green' : ($completeness >= 50 ? 'yellow' : 'red');
                                    @endphp
                                    <div class="flex items-center justify-center">
                                        <div class="w-full max-w-xs">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-semibold text-{{ $color }}-600">{{ number_format($completeness, 1) }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-{{ $color }}-500 h-2 rounded-full transition-all duration-300" 
                                                     style="width: {{ $completeness }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm font-semibold text-gray-900">{{ $package->times_used }}</div>
                                    @if($package->last_used_at)
                                        <div class="text-xs text-gray-500">{{ $package->last_used_at->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($package->hasExpiredProducts())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Productos vencidos
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            OK
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('surgeries.preparations.assignPackage', $surgery) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                            <i class="fas fa-check mr-1"></i>
                                            Seleccionar
                                        </button>   
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mr-3"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-900 mb-2">No hay paquetes disponibles</h4>
                        <p class="text-sm text-yellow-800 mb-4">
                            No hay paquetes pre-armados disponibles para este tipo de cirugía. 
                            Deberás preparar desde cero o crear un nuevo paquete.
                        </p>
                        <form action="{{ route('surgeries.preparations.assignPackage', $surgery) }}" method="POST">
                            @csrf
                            <input type="hidden" name="package_id" value="">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-play mr-1"></i>
                                Preparar Desde Cero
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>