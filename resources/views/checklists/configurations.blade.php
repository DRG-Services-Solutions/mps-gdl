<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 rounded-xl p-3">
                    <i class="fas fa-server text-2xl text-indigo-600"></i>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        Configuraciones de Torre / Equipos
                    </h2>
                    <div class="flex items-center space-x-3 mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md bg-gray-100 text-xs font-mono font-semibold text-gray-600">
                            {{ $checklist->code }}
                        </span>
                        <span class="text-sm text-gray-500">{{ $checklist->surgery_type }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('checklists.show', $checklist) }}" 
                   class="inline-flex items-center px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 shadow-sm transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2 text-gray-400"></i>
                    Volver al Check List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mensajes de feedback --}}
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                        <p class="text-sm font-medium text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-3"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p class="text-sm text-red-700">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Formulario para crear nueva configuración --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center">
                        <i class="fas fa-plus-circle mr-2 text-indigo-500"></i>
                        Crear Nueva Configuración de Torre
                    </h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('checklists.configurations.store', $checklist) }}" method="POST" class="flex flex-col sm:flex-row items-end gap-4">
                        @csrf
                        <div class="flex-1 w-full">
                            <label for="config_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre de la Configuración
                            </label>
                            <input type="text" 
                                   id="config_name"
                                   name="name" 
                                   required
                                   class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Ej: Torre Principal, Torre Artroscopia Básica..."
                                   value="{{ old('name') }}">
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm text-gray-600">Predeterminada</span>
                            </label>
                        </div>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 whitespace-nowrap">
                            <i class="fas fa-plus mr-2"></i>
                            Crear Configuración
                        </button>
                    </form>
                </div>
            </div>

            {{-- Listado de configuraciones existentes --}}
            @forelse($configurations as $config)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100" x-data="{ open: true, editing: false }">
                    {{-- Header de la configuración --}}
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between cursor-pointer hover:bg-gray-50 transition-colors"
                         @click="open = !open">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                                <i class="fas fa-server text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    {{ $config->name }}
                                    @if($config->is_default)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 border border-yellow-200">
                                            <i class="fas fa-star mr-1"></i>PREDETERMINADA
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $config->requirements->count() }} equipo(s) requerido(s)
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            {{-- Botón editar --}}
                            <button @click.stop="editing = !editing" 
                                    class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                    title="Editar nombre">
                                <i class="fas fa-pen text-sm"></i>
                            </button>
                            {{-- Botón eliminar --}}
                            <form action="{{ route('checklists.configurations.destroy', $config) }}" method="POST" 
                                  onsubmit="return confirm('¿Eliminar esta configuración y todos sus equipos asociados?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" @click.stop
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Eliminar configuración">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                            {{-- Chevron --}}
                            <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200" 
                               :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    {{-- Formulario de edición inline --}}
                    <div x-show="editing" x-cloak class="px-6 py-3 bg-indigo-50 border-b border-indigo-100">
                        <form action="{{ route('checklists.configurations.update', $config) }}" method="POST" class="flex items-end gap-3">
                            @csrf
                            @method('PUT')
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nuevo nombre</label>
                                <input type="text" name="name" value="{{ $config->name }}" required
                                       class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            </div>
                            <label class="inline-flex items-center gap-2 cursor-pointer pb-2">
                                <input type="checkbox" name="is_default" value="1" {{ $config->is_default ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-xs text-gray-600">Predeterminada</span>
                            </label>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-save mr-1.5"></i> Guardar
                            </button>
                            <button type="button" @click="editing = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                                Cancelar
                            </button>
                        </form>
                    </div>

                    {{-- Cuerpo expandible --}}
                    <div x-show="open" x-cloak>
                        {{-- Tabla de requerimientos existentes --}}
                        @if($config->requirements->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50/80">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Equipo / Item</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Disponible</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Notas</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @foreach($config->requirements as $req)
                                            <tr class="hover:bg-gray-50/50 transition-colors">
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-9 w-9 bg-gradient-to-br from-purple-50 to-indigo-100 rounded-lg flex items-center justify-center">
                                                            @switch($req->item->type ?? '')
                                                                @case('tower')
                                                                    <i class="fas fa-server text-indigo-500 text-sm"></i>
                                                                    @break
                                                                @case('console')
                                                                    <i class="fas fa-desktop text-indigo-500 text-sm"></i>
                                                                    @break
                                                                @case('equipment')
                                                                    <i class="fas fa-cogs text-indigo-500 text-sm"></i>
                                                                    @break
                                                                @case('tray')
                                                                    <i class="fas fa-th text-indigo-500 text-sm"></i>
                                                                    @break
                                                                @default
                                                                    <i class="fas fa-microchip text-indigo-500 text-sm"></i>
                                                            @endswitch
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-semibold text-gray-900">{{ $req->item->name ?? 'Item eliminado' }}</p>
                                                            <p class="text-xs text-gray-400 font-mono">{{ $req->item->code ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                                        {{ $req->item->typeLabel ?? $req->item->type ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    @if($req->item)
                                                        @php $stockCount = $req->item->availableStockCount; @endphp
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $stockCount > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                                            {{ $stockCount }} disponible(s)
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-gray-400">—</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="text-xs text-gray-500">{{ $req->notes ?: '—' }}</span>
                                                </td>
                                                <td class="px-6 py-4 text-right">
                                                    <form action="{{ route('checklists.configurations.requirements.destroy', $req) }}" method="POST"
                                                          onsubmit="return confirm('¿Quitar este equipo de la configuración?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 text-xs font-medium rounded-lg transition-colors border border-red-200">
                                                            <i class="fas fa-times mr-1"></i> Quitar
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-6 py-8 text-center bg-gray-50/30">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                                <p class="text-sm text-gray-500">No hay equipos en esta configuración.</p>
                                <p class="text-xs text-gray-400 mt-1">Usa el formulario de abajo para agregar equipos.</p>
                            </div>
                        @endif

                        {{-- Formulario para agregar equipos --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-indigo-50/30 border-t border-gray-100">
                            <h4 class="text-xs font-semibold text-gray-600 uppercase tracking-wider mb-3">
                                <i class="fas fa-plus-circle text-indigo-400 mr-1"></i>
                                Agregar Equipo a esta Configuración
                            </h4>
                            <form action="{{ route('checklists.configurations.requirements.store', $config) }}" method="POST" 
                                  x-data="itemSearch_{{ $config->id }}()"
                                  @submit.prevent="submitForm($el)">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                    <div class="md:col-span-6 relative">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Buscar Equipo / Item</label>
                                        <input type="text"
                                               x-model="query"
                                               @input.debounce.300ms="search()"
                                               @focus="showDropdown = results.length > 0"
                                               @click.away="showDropdown = false"
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                               placeholder="Escribe código o nombre del equipo...">
                                        <input type="hidden" name="item_id" x-model="selectedId">

                                        {{-- Dropdown de resultados --}}
                                        <div x-show="showDropdown && results.length > 0" x-cloak
                                             class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                            <template x-for="item in results" :key="item.id">
                                                <button type="button"
                                                        @click="selectItem(item)"
                                                        class="w-full px-4 py-2.5 text-left hover:bg-indigo-50 transition-colors flex items-center justify-between border-b border-gray-50 last:border-0">
                                                    <div>
                                                        <span class="text-sm font-medium text-gray-900" x-text="item.name"></span>
                                                        <span class="text-xs text-gray-400 font-mono ml-2" x-text="item.code"></span>
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600" x-text="item.type_label"></span>
                                                </button>
                                            </template>
                                        </div>

                                        {{-- Indicador de selección --}}
                                        <div x-show="selectedId" x-cloak class="mt-1.5 flex items-center gap-2">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-100 text-indigo-700">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <span x-text="selectedName"></span>
                                            </span>
                                            <button type="button" @click="clearSelection()" class="text-xs text-red-500 hover:text-red-700">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="md:col-span-4">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Notas (opcional)</label>
                                        <input type="text" name="notes" 
                                               class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                               placeholder="Ej: Modelo específico...">
                                    </div>
                                    <div class="md:col-span-2">
                                        <button type="submit" 
                                                :disabled="!selectedId"
                                                class="w-full inline-flex items-center justify-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed whitespace-nowrap">
                                            <i class="fas fa-link mr-2"></i>
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Estado vacío --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-16 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-50 to-purple-100 rounded-2xl mb-5">
                            <i class="fas fa-server text-3xl text-indigo-400"></i>
                        </div>
                        <p class="text-base font-semibold text-gray-900 mb-1">No hay configuraciones de torre</p>
                        <p class="text-sm text-gray-400 max-w-md mx-auto">
                            Crea una configuración de torre para definir qué equipos (consolas, charolas, instrumentales) se necesitan en esta cirugía.
                            Estas configuraciones estarán disponibles al momento de surtir una cirugía en la Zona 2.
                        </p>
                    </div>
                </div>
            @endforelse

        </div>
    </div>

    @push('scripts')
    <script>
        @foreach($configurations as $config)
        function itemSearch_{{ $config->id }}() {
            return {
                query: '',
                results: [],
                selectedId: null,
                selectedName: '',
                showDropdown: false,

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.showDropdown = false;
                        return;
                    }
                    
                    try {
                        const response = await fetch(`{{ route('api.items.search') }}?q=${encodeURIComponent(this.query)}&all=1`);
                        this.results = await response.json();
                        this.showDropdown = this.results.length > 0;
                    } catch (e) {
                        console.error('Error buscando items:', e);
                    }
                },

                selectItem(item) {
                    this.selectedId = item.id;
                    this.selectedName = `${item.name} (${item.code})`;
                    this.query = '';
                    this.showDropdown = false;
                    this.results = [];
                },

                clearSelection() {
                    this.selectedId = null;
                    this.selectedName = '';
                    this.query = '';
                },

                submitForm(el) {
                    if (!this.selectedId) return;
                    el.submit();
                }
            };
        }
        @endforeach
    </script>
    @endpush
</x-app-layout>
