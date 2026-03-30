{{-- resources/views/instruments/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-tools mr-2 text-indigo-600"></i>
                    {{ $instrument->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $instrument->serial_number }}</span>
                    @if($instrument->code)
                        <span class="mx-2">|</span>
                        <span class="text-gray-500">{{ $instrument->code }}</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('instruments.edit', $instrument) }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
                <a href="{{ route('instruments.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Banner de estado -->
            @php
                $bannerColor = match($instrument->status) {
                    'available'   => 'from-green-500 to-emerald-600',
                    'in_kit'      => 'from-blue-500 to-indigo-600',
                    'in_surgery'  => 'from-purple-500 to-indigo-600',
                    'maintenance' => 'from-yellow-500 to-amber-600',
                    'retired'     => 'from-gray-400 to-gray-500',
                    'lost'        => 'from-red-500 to-red-600',
                    default       => 'from-gray-400 to-gray-500',
                };
            @endphp
            <div class="bg-gradient-to-r {{ $bannerColor }} rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <i class="fas fa-tools text-4xl"></i>
                        </div>
                        <div>
                            <p class="text-sm uppercase tracking-wider opacity-80">Estado</p>
                            <h3 class="text-2xl font-bold">{{ $instrument->status_color['label'] }}</h3>
                            <p class="text-sm opacity-80">{{ $instrument->category->name }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-white bg-opacity-20">
                            {{ $instrument->condition_color['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Datos Generales -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                        <i class="fas fa-info-circle mr-2 text-indigo-600"></i> Datos Generales
                    </h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Número de Serie</dt>
                            <dd class="text-sm font-mono font-bold text-gray-900">{{ $instrument->serial_number }}</dd>
                        </div>
                        @if($instrument->code)
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Código Interno</dt>
                            <dd class="text-sm font-mono text-gray-900">{{ $instrument->code }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Categoría</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $instrument->category->name }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Condición</dt>
                            <dd>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $instrument->condition_color['classes'] }}">
                                    {{ $instrument->condition_color['label'] }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Registrado</dt>
                            <dd class="text-sm text-gray-900">{{ $instrument->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Asignación -->
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                        <i class="fas fa-link mr-2 text-indigo-600"></i> Asignación
                    </h3>
                    <dl class="space-y-4">
                        <!-- Kit -->
                        <div>
                            <dt class="text-sm text-gray-500 mb-1">Kit Asignado</dt>
                            <dd>
                                @if($instrument->kit)
                                    <a href="{{ route('instrument-kits.show', $instrument->kit) }}"
                                       class="inline-flex items-center px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-box-open mr-2"></i>
                                        {{ $instrument->kit->name }}
                                        <span class="ml-2 font-mono text-xs text-blue-500">{{ $instrument->kit->code }}</span>
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400 italic">Sin asignar a un kit</span>
                                @endif
                            </dd>
                        </div>

                        <!-- Producto del catálogo -->
                        <div>
                            <dt class="text-sm text-gray-500 mb-1">Producto del Catálogo</dt>
                            <dd>
                                @if($instrument->product)
                                    <span class="text-sm text-gray-900">
                                        <span class="font-mono font-bold">{{ $instrument->product->code }}</span>
                                        — {{ $instrument->product->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400 italic">No ligado a un producto</span>
                                @endif
                            </dd>
                        </div>

                        <!-- Dependencia -->
                        <div>
                            <dt class="text-sm text-gray-500 mb-1">Depende de</dt>
                            <dd>
                                @if($instrument->dependsOn)
                                    <a href="{{ route('instruments.show', $instrument->dependsOn) }}"
                                       class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-arrow-up mr-1"></i>
                                        {{ $instrument->dependsOn->serial_number }} — {{ $instrument->dependsOn->name }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400 italic">Sin dependencias</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Dependientes (instrumentos que dependen de este) -->
            @if($instrument->dependents->count() > 0)
            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-purple-50">
                    <h3 class="text-lg font-semibold text-purple-900 flex items-center">
                        <i class="fas fa-arrow-down mr-2 text-purple-600"></i>
                        Instrumentos que Dependen de Este ({{ $instrument->dependents->count() }})
                    </h3>
                    <p class="text-sm text-purple-700 mt-1">Estos instrumentos requieren de este para funcionar</p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($instrument->dependents as $dependent)
                    <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 bg-purple-50 rounded-lg flex items-center justify-center border border-purple-100">
                                <i class="fas fa-tools text-purple-500 text-xs"></i>
                            </div>
                            <div class="ml-3">
                                <span class="text-sm font-semibold text-gray-900">{{ $dependent->name }}</span>
                                <span class="text-xs text-gray-500 font-mono ml-2">{{ $dependent->serial_number }}</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $dependent->status_color['classes'] }}">
                                {{ $dependent->status_color['label'] }}
                            </span>
                            <a href="{{ route('instruments.show', $dependent) }}" class="text-gray-400 hover:text-indigo-600">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Notas -->
            @if($instrument->notes)
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    <i class="fas fa-sticky-note mr-2 text-indigo-600"></i> Notas
                </h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $instrument->notes }}</p>
            </div>
            @endif

            <!-- Acciones de Estado -->
            @if(!in_array($instrument->status, ['retired']))
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    <i class="fas fa-exchange-alt mr-2 text-indigo-600"></i> Cambiar Estado
                </h3>
                <div class="flex flex-wrap gap-3">
                    @if($instrument->status === 'maintenance')
                        <form action="{{ route('instruments.update-status', $instrument) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Marcar como disponible?')">
                            @csrf
                            <input type="hidden" name="status" value="available">
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                <i class="fas fa-check-circle mr-2"></i> Regresó de Mantenimiento
                            </button>
                        </form>
                    @endif

                    @if(in_array($instrument->status, ['available', 'in_kit']))
                        <form action="{{ route('instruments.update-status', $instrument) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Enviar a mantenimiento?')">
                            @csrf
                            <input type="hidden" name="status" value="maintenance">
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-yellow-700 bg-yellow-100 border border-yellow-300 rounded-lg hover:bg-yellow-200 transition-colors">
                                <i class="fas fa-wrench mr-2"></i> Enviar a Mantenimiento
                            </button>
                        </form>

                        <form action="{{ route('instruments.update-status', $instrument) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Marcar como extraviado? Esta acción es importante para el control de inventario.')">
                            @csrf
                            <input type="hidden" name="status" value="lost">
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-300 rounded-lg hover:bg-red-100 transition-colors">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Marcar como Extraviado
                            </button>
                        </form>
                    @endif

                    @if($instrument->status !== 'in_surgery')
                        <form action="{{ route('instruments.update-status', $instrument) }}" method="POST" class="inline"
                              onsubmit="return confirm('¿Retirar instrumento? Se removerá de su kit si está asignado.')">
                            @csrf
                            <input type="hidden" name="status" value="retired">
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors">
                                <i class="fas fa-ban mr-2"></i> Retirar
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @else
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-ban text-gray-400 text-xl mr-3"></i>
                    <p class="text-sm text-gray-600">Este instrumento ha sido retirado del servicio.</p>
                </div>
            </div>
            @endif

            <!-- Eliminar -->
            @if(!$instrument->kit_id && $instrument->status !== 'in_surgery')
            <div class="flex justify-end">
                <form action="{{ route('instruments.destroy', $instrument) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este instrumento permanentemente?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="fas fa-trash mr-1"></i> Eliminar Instrumento
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>

    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
         x-transition class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
    </div>
    @endif
</x-app-layout>