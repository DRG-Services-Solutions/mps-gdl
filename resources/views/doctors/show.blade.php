<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-user-md mr-2 text-indigo-600"></i>
                    @if($doctor->middle_name)
                        Dr. {{ $doctor->first_name }} {{ $doctor->middle_name }} {{ $doctor->last_name }}
                    @else
                        Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                    @endif
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                   
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $doctor->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        <i class="fas {{ $doctor->is_active ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
                        {{ $doctor->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <form action="{{ route('doctors.toggle-status', $doctor) }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 {{ $doctor->is_active ? 'bg-gray-600' : 'bg-green-600' }} border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:{{ $doctor->is_active ? 'bg-gray-700' : 'bg-green-700' }} transition">
                        <i class="fas {{ $doctor->is_active ? 'fa-ban' : 'fa-check-circle' }} mr-2"></i>
                        {{ $doctor->is_active ? 'Desactivar' : 'Activar' }}
                    </button>
                </form>
                <a href="{{ route('doctors.edit', $doctor) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-2"></i>Editar
                </a>
                <a href="{{ route('doctors.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 transition">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-file-invoice text-indigo-600 text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Total Cotizaciones</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_quotations'] }}</p>
                        </div>
                    </div>
                </div>
               
            </div>

            <!-- Doctor Info -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 border-b pb-3">
                        <i class="fas fa-info-circle mr-2 text-indigo-600"></i>Información del Doctor
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información Personal -->
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nombre Completo</p>
                                @if($doctor->middle_name)
                                    <p class="text-base text-gray-900">{{ $doctor->first_name }} {{ $doctor->middle_name }} {{ $doctor->last_name }}</p>
                                @else
                                    <p class="text-base text-gray-900">{{ $doctor->first_name }} {{ $doctor->last_name }}</p>
                                @endif
                            </div>
                       
                        </div>

                        <!-- Hospital y Contacto -->
                        <div class="space-y-4">
                           
                            @if($doctor->phone)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        <i class="fas fa-phone mr-1"></i>Teléfono
                                    </p>
                                    <p class="text-base text-gray-900">{{ $doctor->phone }}</p>
                                </div>
                            @endif
                            @if($doctor->email)
                                <div>
                                    <p class="text-sm font-medium text-gray-500">
                                        <i class="fas fa-envelope mr-1"></i>Email
                                    </p>
                                    <p class="text-base text-gray-900">
                                        <a href="mailto:{{ $doctor->email }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $doctor->email }}
                                        </a>
                                    </p>
                                </div>
                            @endif
                        </div>

                        <!-- Información Profesional -->
                        @if($doctor->license_number || $doctor->specialty_license)
                            <div class="md:col-span-2 pt-4 border-t">
                                <p class="text-sm font-medium text-gray-500 mb-3">
                                    <i class="fas fa-id-card mr-1"></i>Información Profesional
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @if($doctor->license_number)
                                        <div>
                                            <p class="text-xs text-gray-500">Cédula Profesional</p>
                                            <p class="text-sm text-gray-900 font-medium">{{ $doctor->license_number }}</p>
                                        </div>
                                    @endif
                                    @if($doctor->specialty_license)
                                        <div>
                                            <p class="text-xs text-gray-500">Cédula de Especialidad</p>
                                            <p class="text-sm text-gray-900 font-medium">{{ $doctor->specialty_license }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Notas -->
                        @if($doctor->notes)
                            <div class="md:col-span-2 pt-4 border-t">
                                <p class="text-sm font-medium text-gray-500 mb-2">
                                    <i class="fas fa-sticky-note mr-1"></i>Notas
                                </p>
                                <p class="text-base text-gray-900">{{ $doctor->notes }}</p>
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
                    <a href="{{ route('quotations.create') }}?doctor_id={{ $doctor->id }}" 
                       class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>Nueva Cotización
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cirugía</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($doctor->quotations as $quotation)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="{{ route('quotations.show', $quotation) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $quotation->hospital->name }}
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
                                        <a href="{{ route('quotations.create') }}?doctor_id={{ $doctor->id }}" class="mt-2 inline-block text-indigo-600 hover:text-indigo-900 font-medium">
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
            @if($doctor->quotations()->count() === 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-red-600 mb-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Zona de Peligro
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Esta acción es permanente y no se puede deshacer. El doctor no tiene cotizaciones asociadas.
                        </p>
                        <form action="{{ route('doctors.destroy', $doctor) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este doctor? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring focus:ring-red-300 transition">
                                <i class="fas fa-trash mr-2"></i>
                                Eliminar Doctor
                            </button>
                        </form>
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-app-layout>