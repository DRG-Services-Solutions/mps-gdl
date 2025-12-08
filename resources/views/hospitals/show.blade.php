<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                    {{ $hospital->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $hospital->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas {{ $hospital->is_active ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
                        {{ $hospital->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <form action="{{ route('hospitals.toggle-status', $hospital) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 {{ $hospital->is_active ? 'bg-gray-600' : 'bg-green-600' }} border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:{{ $hospital->is_active ? 'bg-gray-700' : 'bg-green-700' }} transition">
                        <i class="fas {{ $hospital->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                        {{ $hospital->is_active ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
                <a href="{{ route('hospitals.edit', $hospital) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                <a href="{{ route('hospitals.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            
            <!-- Hospital Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 border-b pb-3">
                        <i class="fas fa-info-circle mr-2 text-indigo-600"></i>Información del Hospital
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información General -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nombre</p>
                                <p class="text-base text-gray-900">{{ $hospital->name }}</p>
                            </div>
                            @if($hospital->code)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Código</p>
                                    <p class="text-base text-gray-900">{{ $hospital->code }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Contacto -->
                        <div class="space-y-4">
                            @if($hospital->contact_person)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        <i class="fas fa-user mr-1"></i>Persona de Contacto
                                    </p>
                                    <p class="text-base text-gray-900">{{ $hospital->contact_person }}</p>
                                </div>
                            @endif
                            @if($hospital->phone)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        <i class="fas fa-phone mr-1"></i>Teléfono
                                    </p>
                                    <p class="text-base text-gray-900">{{ $hospital->phone }}</p>
                                </div>
                            @endif
                            @if($hospital->email)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        <i class="fas fa-envelope mr-1"></i>Email
                                    </p>
                                    <p class="text-base text-gray-900">
                                        <a href="mailto:{{ $hospital->email }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $hospital->email }}
                                        </a>
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Dirección -->
                        @if($hospital->address || $hospital->city || $hospital->state)
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Dirección
                                </p>
                                <div class="text-base text-gray-900">
                                    @if($hospital->address)
                                        <p>{{ $hospital->address }}</p>
                                    @endif
                                    @if($hospital->city || $hospital->state || $hospital->zip_code)
                                        <p>
                                            {{ $hospital->city }}{{ $hospital->city && $hospital->state ? ', ' : '' }}{{ $hospital->state }}{{ ($hospital->city || $hospital->state) && $hospital->zip_code ? '. ' : '' }}{{ $hospital->zip_code ? 'CP ' . $hospital->zip_code : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Notas -->
                        @if($hospital->notes)
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notas
                                </p>
                                <p class="text-base text-gray-900">{{ $hospital->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Quotations -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-file-invoice mr-2"></i>Cotizaciones Recientes
                    </h3>
                    <a href="{{ route('quotations.create') }}?hospital_id={{ $hospital->id }}" 
                       class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>Nueva Cotización
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cirugía</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($hospital->quotations as $quotation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $quotation->doctor->full_name ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $quotation->surgery_type ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'draft' => 'bg-gray-100 text-gray-800',
                                                'sent' => 'bg-blue-100 text-blue-800',
                                                'in_surgery' => 'bg-yellow-100 text-yellow-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'invoiced' => 'bg-indigo-100 text-indigo-800',
                                            ];
                                            $statusLabels = [
                                                'draft' => 'Borrador',
                                                'sent' => 'Enviada',
                                                'in_surgery' => 'En Cirugía',
                                                'completed' => 'Completada',
                                                'invoiced' => 'Facturada',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$quotation->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusLabels[$quotation->status] ?? $quotation->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $quotation->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                                        <p>No hay cotizaciones registradas</p>
                                        <a href="{{ route('quotations.create') }}?hospital_id={{ $hospital->id }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-900 font-medium">
                                            <i class="fas fa-plus mr-1"></i>Crear primera cotización
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            
            <!-- Delete Button -->
            @if($hospital->quotations()->count() === 0 && $hospital->sales()->count() === 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-red-600 mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Zona de Peligro
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Esta acción es permanente y no se puede deshacer. El hospital no tiene cotizaciones ni ventas asociadas.
                        </p>
                        <form action="{{ route('hospitals.destroy', $hospital) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este hospital? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring focus:ring-red-300 transition">
                                <i class="fas fa-trash mr-2"></i>
                                Eliminar Hospital
                            </button>
                        </form>
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>