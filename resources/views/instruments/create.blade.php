{{-- resources/views/instruments/create.blade.php --}}
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
                    <i class="fas fa-plus-circle mr-2 text-indigo-600"></i> Nuevo Instrumental
                </h2>
                <p class="text-sm text-gray-600 mt-1">Registrar un nuevo instrumental o equipo</p>
            </div>
            <a href="{{ route('instruments.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-md transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('instruments.store') }}">
                    @csrf
                    <div class="p-6 space-y-6">

                        <!-- Identificación -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-fingerprint mr-2 text-indigo-600"></i> Identificación
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Número de Serie <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number') }}" required
                                           placeholder="Ej: PK-001, SEP-042..."
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono @error('serial_number') border-red-500 @enderror">
                                    @error('serial_number')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Código Interno</label>
                                    <input type="text" name="code" id="code" value="{{ old('code') }}"
                                           placeholder="Opcional"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre / Descripción <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                           placeholder="Ej: Pinza Kelly recta 14cm"
                                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Clasificación -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-layer-group mr-2 text-indigo-600"></i> Clasificación
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        Categoría <span class="text-red-500">*</span>
                                    </label>
                                    <select name="category_id" id="category_id" required
                                            class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('category_id') border-red-500 @enderror">
                                        <option value="">Seleccionar categoría...</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                            </div>
                        </div>

                        <!-- Relaciones opcionales -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                <i class="fas fa-link mr-2 text-indigo-600"></i> Relaciones (Opcional)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Producto del Catálogo</label>
                                    <select name="product_id" id="product_id" placeholder="Buscar producto..."></select>
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i> Liga este instrumento a un producto existente
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Depende de</label>
                                    <select name="depends_on_id" id="depends_on_id" placeholder="Buscar instrumento..."></select>
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i> Ej: una hoja de bisturí depende de su mango
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas</label>
                            <textarea name="notes" id="notes" rows="3" placeholder="Observaciones adicionales..."
                                      class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3">
                        <a href="{{ route('instruments.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-times mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-save mr-1"></i> Registrar Instrumento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Tom Select: Producto del catálogo
        new TomSelect('#product_id', {
            valueField: 'id', labelField: 'text', searchField: 'text',
            placeholder: 'Buscar producto...', openOnFocus: false,
            plugins: ['clear_button'],
            shouldLoad: function(query) { return query.length > 0; },
            load: function(query, callback) {
                fetch(`/api/products/select2?search=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(data => callback(data.results)).catch(() => callback());
            },
            render: {
                option: (data, escape) => `<div class="py-2 px-3">${escape(data.text)}</div>`,
                item: (data, escape) => `<div>${escape(data.text)}</div>`,
                no_results: () => '<div style="padding:10px;text-align:center;color:#6b7280;">No encontrado</div>',
            },
        });

        // Tom Select: Dependencia de otro instrumento
        new TomSelect('#depends_on_id', {
            valueField: 'id', labelField: 'text', searchField: 'text',
            placeholder: 'Buscar instrumento...', openOnFocus: false,
            plugins: ['clear_button'],
            shouldLoad: function(query) { return query.length > 0; },
            load: function(query, callback) {
                fetch(`/api/instruments/search-available?search=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(data => callback(data.results)).catch(() => callback());
            },
            render: {
                option: (data, escape) => `<div class="py-2 px-3">${escape(data.text)}</div>`,
                item: (data, escape) => `<div>${escape(data.text)}</div>`,
                no_results: () => '<div style="padding:10px;text-align:center;color:#6b7280;">No encontrado</div>',
            },
        });
    </script>
    @endpush
</x-app-layout>