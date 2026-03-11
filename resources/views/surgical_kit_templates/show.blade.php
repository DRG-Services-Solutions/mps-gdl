<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('surgical_kit_templates.index') }}" class="text-gray-500 hover:text-indigo-600 mr-4 transition-colors">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-2xl text-gray-800 leading-tight flex items-center">
                        <i class="fas fa-medkit mr-2 text-indigo-600"></i>
                        {{ $surgicalKitTemplate->name }}
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">Detalles de la lista</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('surgical_kit_templates.edit', $surgicalKitTemplate) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 text-gray-700 font-medium transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Lista
                </a>
                {{-- Nota: Este botón de agregar artículo lo conectaremos en el siguiente paso --}}
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Agregar Artículo
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                    <p class="font-medium"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white rounded-lg shadow-sm p-6 h-fit">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4">
                        <i class="fas fa-info-circle text-gray-400 mr-2"></i>Información General
                    </h3>
                    
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Código de Referencia</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded inline-block">
                                {{ $surgicalKitTemplate->code ?? 'Sin código' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">CheckList Asociado</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded inline-block">
                                {{ $surgicalKitTemplate->surgery_type }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Estado</dt>
                            <dd class="mt-1">
                                @if($surgicalKitTemplate->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactivo
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                                {{ $surgicalKitTemplate->description ?? 'No hay descripción registrada para este kit.' }}
                            </dd>
                        </div>

                        <div class="pt-4 mt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Total de Artículos</dt>
                            <dd class="mt-1 text-2xl font-semibold text-indigo-600">
                                {{ $surgicalKitTemplate->items->count() }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="lg:col-span-2 space-y-6">
    
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-100">
        <div class="p-4 bg-gray-50 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-plus-circle text-indigo-500 mr-1"></i> Agregar Instrumental
            </h3>
        </div>
        <div class="p-4">
            {{-- Apuntaremos esta ruta al controlador del "Hijo" en el siguiente paso --}}
            <form action="{{ route('surgical_kit_template_items.store') }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                @csrf
                
                {{-- Campo oculto para mandar el ID de la receta padre --}}
                <input type="hidden" name="surgical_kit_template_id" value="{{ $surgicalKitTemplate->id }}">

                <div class="flex-1 w-full">
                    <label for="product_id" class="block text-xs font-medium text-gray-700 mb-1">Instrumento / Artículo</label>
                    <select name="product_id" id="product_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">-- Selecciona un artículo --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full sm:w-32">
                    <label for="quantity" class="block text-xs font-medium text-gray-700 mb-1">Cantidad</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" required
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">
                        Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-list-ul text-gray-400 mr-2"></i>Contenido Actual
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Artículo / Instrumental</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($surgicalKitTemplate->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $item->product->name ?? 'Producto Eliminado del Catálogo' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                {{-- Estos formularios los conectaremos después para actualizar y eliminar --}}
                                <button class="text-blue-600 hover:text-blue-900" title="Editar cantidad">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900" title="Quitar del kit">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-box-open text-4xl mb-3"></i>
                                    <p class="text-base font-medium text-gray-900 mb-1">Receta Vacía</p>
                                    <p class="text-sm text-gray-500">Usa el formulario de arriba para agregar instrumental a este kit.</p>
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
        </div>
    </div>
</x-app-layout>