<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-purple-600"></i>
                    Seleccionar Paquete Pre-Armado
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $surgery->code }} - {{ $surgery->patient_name }}
                </p>
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
                        <p class="text-lg font-semibold">{{ $surgery->hospitalModalityConfig->modality->name ?? 'Sin modalidad' }}</p>
                    </div>
                </div>
            </div>

            <!-- Flujo del Proceso -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-route mr-2 text-indigo-600"></i>
                    ¿Qué sucede después de seleccionar?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-bold text-sm">1</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Seleccionar Paquete</p>
                            <p class="text-xs text-gray-500">Elige el paquete disponible</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-sm">2</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Comparar Contenido</p>
                            <p class="text-xs text-gray-500">Ver qué tiene y qué falta</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-green-600 font-bold text-sm">3</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Surtir Faltantes</p>
                            <p class="text-xs text-gray-500">Escanear productos con RFID</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <span class="text-purple-600 font-bold text-sm">4</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">Verificar</p>
                            <p class="text-xs text-gray-500">Completar preparación</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paquetes Disponibles -->
            @if($availablePackages->count() > 0)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-boxes mr-2 text-green-600"></i>
                                Paquetes Pre-Armados Disponibles
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Selecciona el paquete con mayor completitud para ahorrar tiempo
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-3xl font-bold text-green-600">{{ $availablePackages->count() }}</span>
                            <p class="text-xs text-gray-600">disponibles</p>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Paquete
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unidades Físicas
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Completitud
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ubicación
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acción
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($availablePackages as $package)
                            @php
                                $completeness = $package->completeness ?? 0;
                                $hasExpired = $package->has_expired ?? false;

                                // FIX: Clases completas para que Tailwind las detecte en producción
                                $colorClasses = match(true) {
                                    $completeness >= 80 => ['text' => 'text-green-700', 'bg' => 'bg-green-500'],
                                    $completeness >= 50 => ['text' => 'text-yellow-700', 'bg' => 'bg-yellow-500'],
                                    default             => ['text' => 'text-red-700', 'bg' => 'bg-red-500'],
                                };
                            @endphp
                            <tr class="hover:bg-green-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box-open text-green-600 text-xl"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $package->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $package->code }}</div>
                                            @if($package->last_used_at)
                                                <div class="text-xs text-gray-400 mt-0.5">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Usado {{ $package->last_used_at->diffForHumans() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                            {{ $package->contents->count() }} unidades
                                        </span>
                                        <span class="text-xs text-gray-500 mt-1">
                                            {{ $package->contents->groupBy('product_id')->count() }} productos diferentes
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center">
                                        <div class="w-full max-w-xs">
                                            <div class="flex items-center justify-between mb-1">
                                                <span class="text-sm font-bold {{ $colorClasses['text'] }}">
                                                    {{ number_format($completeness, 1) }}%
                                                </span>
                                                @if($completeness >= 80)
                                                    <i class="fas fa-star text-yellow-500" title="Muy completo"></i>
                                                @elseif($completeness >= 50)
                                                    <i class="fas fa-check-circle text-green-500" title="Bueno"></i>
                                                @else
                                                    <i class="fas fa-exclamation-circle text-red-500" title="Bajo"></i>
                                                @endif
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="{{ $colorClasses['bg'] }} h-2.5 rounded-full transition-all duration-300 shadow-sm" 
                                                     style="width: {{ $completeness }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($package->storageLocation)
                                        <div class="flex flex-col items-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $package->storageLocation->code }}
                                            </span>
                                            @if($package->storageLocation->name)
                                                <span class="text-xs text-gray-500 mt-1">
                                                    {{ $package->storageLocation->name }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Sin ubicación</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($hasExpired)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Productos vencidos
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Vigente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('surgeries.preparations.assignPackage', $surgery) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md hover:shadow-lg transition-all duration-200"
                                                @if($hasExpired)
                                                    onclick="return confirm('Este paquete contiene productos vencidos. ¿Deseas continuar de todos modos?')"
                                                @endif>
                                            <i class="fas fa-check-circle mr-2"></i>
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

            <!-- Información Adicional -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-900 mb-2">
                            Después de seleccionar el paquete
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-blue-600 mr-2 mt-1"></i>
                                <span>Verás una <strong>comparación detallada</strong> entre lo que necesitas y lo que tiene el paquete</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-blue-600 mr-2 mt-1"></i>
                                <span>Podrás identificar <strong>qué productos faltan</strong> por surtir</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-arrow-right text-blue-600 mr-2 mt-1"></i>
                                <span>Escanearás los faltantes con <strong>RFID</strong> para completar la preparación</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            @else
            <!-- No hay paquetes disponibles -->
            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-3xl mr-4"></i>
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-yellow-900 mb-2">
                            No hay paquetes pre-armados disponibles
                        </h4>
                        <p class="text-sm text-yellow-800 mb-4">
                            No existen paquetes disponibles para el checklist: <strong>{{ $surgery->checklist->surgery_type }}</strong>.
                            Deberás preparar todos los productos desde cero escaneando cada uno con RFID.
                        </p>
                        
                        <div class="bg-white rounded-lg p-4 mb-4 border border-yellow-200">
                            <h5 class="text-sm font-semibold text-gray-900 mb-2">¿Qué sucederá?</h5>
                            <ol class="text-sm text-gray-700 space-y-2">
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-2">1</span>
                                    <span>Se creará una preparación vacía basada en el checklist</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-2">2</span>
                                    <span>Deberás escanear <strong>todos</strong> los productos requeridos</span>
                                </li>
                                <li class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold mr-2">3</span>
                                    <span>Una vez completo, podrás verificar y marcar como listo</span>
                                </li>
                            </ol>
                        </div>

                        <form action="{{ route('surgeries.preparations.assignPackage', $surgery) }}" method="POST" onsubmit="return confirm('¿Confirmas que deseas preparar desde cero? Todos los productos deberán ser escaneados.');">
                            @csrf
                            <input type="hidden" name="package_id" value="">
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md hover:shadow-lg transition-all duration-200">
                                <i class="fas fa-play-circle mr-2"></i>
                                Preparar Desde Cero
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Toasts con auto-dismiss usando Alpine.js --}}
    @if(session('success'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 3000)"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" 
         x-show="show" 
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-2"
         class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
    @endif
</x-app-layout>