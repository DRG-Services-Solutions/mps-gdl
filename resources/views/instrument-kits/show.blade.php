{{-- resources/views/instrument-kits/show.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        .ts-wrapper { width: 100% !important; }
        .ts-wrapper .ts-control { border: 1px solid #d1d5db !important; border-radius: 0.5rem !important; padding: 0.5rem 0.75rem !important; background-image: none !important; min-height: 42px !important; display: flex !important; align-items: center !important; }
        .ts-wrapper .ts-control input[type="text"], .ts-wrapper .ts-control > input { border: none !important; padding: 0 !important; margin: 0 !important; background: transparent !important; box-shadow: none !important; outline: none !important; min-height: auto !important; width: auto !important; flex: 1 1 auto !important; }
        .ts-wrapper.focus .ts-control { border-color: #6366f1 !important; box-shadow: 0 0 0 1px #6366f1 !important; }
        .ts-wrapper .ts-dropdown { border: 1px solid #d1d5db !important; border-radius: 0.5rem !important; margin-top: 4px !important; z-index: 9999 !important; }
        .ts-wrapper .ts-dropdown .option { padding: 8px 12px !important; }
        .ts-wrapper .ts-dropdown .active { background-color: #eef2ff !important; color: #4f46e5 !important; }
    </style>
    @endpush

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-box-open mr-2 text-indigo-600"></i>
                    {{ $instrumentKit->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $instrumentKit->code }}</span>
                    <span class="mx-2">|</span>
                    S/N: {{ $instrumentKit->serial_number }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('instrument-kits.edit', $instrumentKit) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
                <a href="{{ route('instrument-kits.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Banner de estado -->
            @php
                $bannerColor = match($instrumentKit->status) {
                    'available'  => 'from-green-500 to-emerald-600',
                    'incomplete' => 'from-yellow-500 to-amber-600',
                    'in_surgery' => 'from-purple-500 to-indigo-600',
                    default      => 'from-gray-400 to-gray-500',
                };
                $statusLabel = match($instrumentKit->status) {
                    'available'  => 'Disponible — Completo',
                    'incomplete' => 'Incompleto — Faltan piezas',
                    'in_surgery' => 'En Cirugía',
                    'maintenance' => 'En Mantenimiento',
                    default      => $instrumentKit->status,
                };
            @endphp
            <div class="bg-gradient-to-r {{ $bannerColor }} rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 rounded-lg p-4">
                            <i class="fas fa-box-open text-4xl"></i>
                        </div>
                        <div>
                            <p class="text-sm uppercase tracking-wider opacity-80">Estado</p>
                            <h3 class="text-2xl font-bold">{{ $statusLabel }}</h3>
                            @if($instrumentKit->template)
                                <p class="text-sm opacity-80">Plantilla: {{ $instrumentKit->template->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-5xl font-black">{{ $stats['total'] }}/{{ $stats['expected'] }}</p>
                        <p class="text-sm opacity-80">piezas</p>
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Piezas Actuales</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Esperadas</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['expected'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 {{ $stats['missing'] > 0 ? 'border-red-500' : 'border-green-500' }}">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Faltantes</p>
                    <p class="text-3xl font-black {{ $stats['missing'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $stats['missing'] }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Completitud</p>
                    <p class="text-3xl font-black text-gray-900">{{ $stats['completeness'] }}%</p>
                </div>
            </div>

            <!-- Asignar instrumento -->
            @if($instrumentKit->status !== 'in_surgery')
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i> Asignar Instrumento
                </h3>
                <form action="{{ route('instrument-kits.assign', $instrumentKit) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-9">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Instrumento Disponible</label>
                            <select name="instrument_id" id="instrument_search" placeholder="Buscar por serial o nombre..." required></select>
                        </div>
                        <div class="md:col-span-3">
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Asignar al Kit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            <!-- Tabla de instrumentos en el kit -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-list mr-2 text-indigo-600"></i>
                        Contenido del Kit ({{ $stats['total'] }} piezas)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instrumento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Condición</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Dependencias</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($instrumentKit->instruments->sortBy('name') as $instrument)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-9 w-9 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-tools text-sm"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-bold text-gray-900">{{ $instrument->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $instrument->serial_number }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $instrument->category->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $instrument->condition_color['classes'] }}">
                                        {{ $instrument->condition_color['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($instrument->dependsOn)
                                        <span class="text-xs text-blue-600">
                                            <i class="fas fa-arrow-up mr-1"></i> {{ $instrument->dependsOn->serial_number }} -- 
                                        </span>
                                        <div class="text-xs text-gray-500 italic">
                                            {{ $instrument->dependsOn->name }}
                                        </div>
                                    @endif
                                    @if($instrument->dependents->count() > 0)
                                        <span class="text-xs text-purple-600">
                                            <i class="fas fa-arrow-down mr-1"></i> {{ $instrument->dependents->count() }} dependientes
                                        </span>
                                    @endif
                                    @if(!$instrument->dependsOn && $instrument->dependents->count() === 0)
                                        <span class="text-xs text-gray-400 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end items-center space-x-2">
                                        <a href="{{ route('instruments.show', $instrument) }}" class="text-gray-400 hover:text-indigo-600" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($instrumentKit->status !== 'in_surgery')
                                        <form action="{{ route('instrument-kits.remove', [$instrumentKit, $instrument]) }}" method="POST" class="inline"
                                              onsubmit="return confirm('¿Remover {{ $instrument->serial_number }} del kit?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600" title="Remover del kit">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center text-gray-400">
                                        <i class="fas fa-inbox text-5xl mb-4"></i>
                                        <p class="text-lg font-medium text-gray-900 mb-2">Kit vacío</p>
                                        <p class="text-sm text-gray-600">Usa el buscador de arriba para asignar instrumentos a este kit</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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

    @push('scripts')
    <script>
        new TomSelect('#instrument_search', {
            valueField: 'id', labelField: 'text', searchField: 'text',
            placeholder: 'Buscar instrumento por serial o nombre...', openOnFocus: false,
            shouldLoad: function(query) { return query.length > 0; },
            load: function(query, callback) {
                fetch(`/api/instruments/search-available?search=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(data => callback(data.results)).catch(() => callback());
            },
            render: {
                option: (data, escape) => `<div class="py-2 px-3">${escape(data.text)}</div>`,
                item: (data, escape) => `<div>${escape(data.text)}</div>`,
                no_results: () => '<div style="padding:10px;text-align:center;color:#6b7280;">No se encontraron instrumentos disponibles</div>',
            },
        });
    </script>
    @endpush
</x-app-layout>