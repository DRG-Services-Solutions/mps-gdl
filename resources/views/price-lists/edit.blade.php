{{-- resources/views/price-lists/edit.blade.php --}}
<x-app-layout>
    @push('styles')
    <style>
        .ts-wrapper { width: 100% !important; }
        .ts-wrapper .ts-control {
            border: 1px solid #d1d5db !important; border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important; background-image: none !important;
            min-height: 42px !important; display: flex !important; align-items: center !important;
        }
        .ts-wrapper .ts-control input[type="text"], .ts-wrapper .ts-control > input {
            border: none !important; padding: 0 !important; margin: 0 !important;
            background: transparent !important; box-shadow: none !important; outline: none !important;
            min-height: auto !important; width: auto !important; flex: 1 1 auto !important;
        }
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
                    <i class="fas fa-edit mr-2 text-indigo-600"></i>
                    Editar Lista de Precios
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-indigo-600">{{ $priceList->code }}</span>
                </p>
            </div>
            <a href="{{ route('price-lists.show', $priceList) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('price-lists.update', $priceList) }}">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-tags mr-2 text-indigo-600"></i> Datos Generales
                            </h3>
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $priceList->name) }}" required
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="hospital_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Hospital / Cliente <span class="text-red-500">*</span>
                                    </label>
                                    <select name="hospital_id" id="hospital_id" placeholder="Buscar hospital..." required>
                                        <option value="{{ $priceList->hospital_id }}" selected>{{ $priceList->hospital->name }}</option>
                                    </select>
                                    @error('hospital_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                                    <textarea name="notes" id="notes" rows="3"
                                              class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes', $priceList->notes) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <form action="{{ route('price-lists.destroy', $priceList) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta lista de precios? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                <i class="fas fa-trash mr-1"></i> Eliminar Lista
                            </button>
                        </form>

                        <div class="flex items-center space-x-3">
                            <a href="{{ route('price-lists.show', $priceList) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-save mr-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        new TomSelect('#hospital_id', {
            valueField: 'id', labelField: 'text', searchField: 'text',
            placeholder: 'Buscar hospital...', openOnFocus: false,
            shouldLoad: function(query) { return query.length > 0; },
            load: function(query, callback) {
                fetch(`/api/hospitals/select2?search=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(data => callback(data.results)).catch(() => callback());
            },
            render: {
                option: (data, escape) => `<div class="py-2 px-3">${escape(data.text)}</div>`,
                item: (data, escape) => `<div>${escape(data.text)}</div>`,
                no_results: () => '<div style="padding:10px;text-align:center;color:#6b7280;">No se encontraron hospitales</div>',
            },
        });
    </script>
    @endpush
</x-app-layout>
