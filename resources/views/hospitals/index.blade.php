<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-hospital mr-2 text-indigo-600"></i>
                    {{ __('Hospitales') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Gestión de hospitales y configuración fiscal</p>
            </div>
            <a href="{{ route('hospitals.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Hospital
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-8xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operación y Facturación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($hospitals as $hospital)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <i class="fas fa-hospital text-indigo-600"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $hospital->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $hospital->rfc ?? 'Sin RFC' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="flex flex-col space-y-1">
                                            @forelse($hospital->configs as $config)
                                                <div class="flex items-center text-xs">
                                                    <span class="px-2 py-0.5 rounded-full font-bold mr-2 {{ $config->modality->name == 'Seguro' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                                        {{ $config->modality->name }}
                                                    </span>
                                                    <span class="text-gray-600 italic">
                                                        <i class="fas fa-file-invoice-dollar mr-1"></i>
                                                        {{ $config->legalEntity->name }}
                                                    </span>
                                                </div>
                                            @empty
                                                <span class="text-xs text-red-500 font-medium italic">Sin configuración fiscal</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $hospital->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $hospital->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-3">
                                            <a href="{{ route('hospitals.edit', $hospital) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-cog mr-1"></i> Configurar
                                            </a>
                                            <a href="{{ route('hospitals.show', $hospital) }}" class="text-gray-600 hover:text-gray-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>