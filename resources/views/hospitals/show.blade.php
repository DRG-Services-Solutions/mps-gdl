<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                    Detalle del Hospital
                </h2>
                <p class="text-sm text-gray-600 mt-1">Visualización completa del perfil y configuraciones</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('hospitals.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <a href="{{ route('hospitals.edit', $hospital) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-id-card mr-2 text-indigo-600"></i> Información General
                            </h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 italic">Nombre Comercial</dt>
                                    <dd class="text-lg font-semibold text-gray-900">{{ $hospital->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 italic">RFC</dt>
                                    <dd class="text-lg font-mono text-gray-900">{{ $hospital->rfc }}</dd>
                                </div>
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 italic">Estado del Hospital</dt>
                                    <dd class="mt-1">
                                        @if($hospital->is_active)
                                            <span class="px-3 py-1 text-xs font-bold bg-green-100 text-green-800 rounded-full italic">
                                                <i class="fas fa-check-circle mr-1"></i> OPERATIVO / ACTIVO
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-bold bg-red-100 text-red-800 rounded-full italic">
                                                <i class="fas fa-ban mr-1"></i> INACTIVO / FUERA DE SERVICIO
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-file-invoice-dollar mr-2 text-indigo-600"></i> Esquema de Facturación Asignado
                            </h3>
                            
                            <div class="space-y-4">
                                @forelse($hospital->configs as $config)
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border border-gray-100">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-white shadow-sm">
                                                <i class="fas {{ $config->modality->name == 'Seguro' ? 'fa-shield-alt' : 'fa-user' }}"></i>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-bold text-gray-900 uppercase">Modalidad {{ $config->modality->name }}</p>
                                                <p class="text-xs text-gray-500">Facturado por nuestra entidad legal:</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-indigo-700">{{ $config->legalEntity->name }}</p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $config->legalEntity->rfc }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-6 bg-red-50 rounded-xl border border-red-100">
                                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl mb-2"></i>
                                        <p class="text-red-600 font-medium">Este hospital no tiene configurada ninguna modalidad de cobro.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar de Contacto y Notas -->
                <!--
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 uppercase tracking-wider border-b pb-2">
                                <i class="fas fa-address-book mr-2 text-indigo-600"></i> Contacto
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 block">Teléfono</label>
                                    <p class="text-sm font-semibold text-gray-800">{{ $hospital->phone ?? 'No registrado' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 block">Email</label>
                                    <p class="text-sm font-semibold text-gray-800">{{ $hospital->email ?? 'No registrado' }}</p>
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-gray-500 block">Dirección</label>
                                    <p class="text-sm text-gray-700 leading-relaxed italic">{{ $hospital->address ?? 'Sin dirección registrada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                -->

                    @if($hospital->notes)
                    <div class="bg-yellow-50 overflow-hidden shadow-sm sm:rounded-lg border border-yellow-100">
                        <div class="p-6">
                            <h3 class="text-sm font-bold text-yellow-800 mb-2 uppercase">
                                <i class="fas fa-sticky-note mr-2"></i> Notas Internas
                            </h3>
                            <p class="text-sm text-yellow-900 italic">
                                "{{ $hospital->notes }}"
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>