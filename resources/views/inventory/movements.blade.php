<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    <i class="fas fa-history mr-2 text-indigo-600"></i>
                    Historial de Movimientos
                </h2>
                <p class="text-sm text-gray-600 mt-1">Auditoría detallada de entradas y salidas (Kardex)</p>
            </div>
            
            <div class="flex gap-2">
                <a href="{{ route('inventory.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                    <i class="fas fa-boxes mr-2"></i> Ver Existencias
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    
                    <form method="GET" action="{{ route('inventory.movements') }}" class="mb-6 pb-6 border-b border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Nombre o Código..."
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Almacén</label>
                                <select name="sub_warehouse_id" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                    <option value="">Todos</option>
                                    @foreach($subWarehouses as $sw)
                                        <option value="{{ $sw->id }}" {{ request('sub_warehouse_id') == $sw->id ? 'selected' : '' }}>
                                            {{ $sw->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Movimiento</label>
                                <select name="type" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm text-sm">
                                    <option value="">Todos</option>
                                    <option value="entry" {{ request('type') == 'entry' ? 'selected' : '' }}>Entrada (In)</option>
                                    <option value="exit" {{ request('type') == 'exit' ? 'selected' : '' }}>Salida (Out)</option>
                                </select>
                            </div>

                            <div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                                    <i class="fas fa-filter mr-2"></i> Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Referencia / Usuario</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($movements as $mov)
                                    <tr class="hover:bg-gray-50 transition">
                                        
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div class="font-bold">{{ $mov->created_at->format('d/m/Y') }}</div>
                                            <div class="text-xs text-gray-400">{{ $mov->created_at->format('H:i') }}</div>
                                        </td>

                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $mov->product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $mov->product->code }}</div>
                                            @if($mov->productUnit)
                                                <div class="mt-1">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                        <i class="fas fa-barcode mr-1"></i>
                                                        {{ $mov->productUnit->serial_number ?? $mov->productUnit->epc ?? 'Codigo: '
                                                        .$mov->productUnit->product->code }}
                                                    </span>
                                                </div>
                                            @elseif($mov->batch_number)
                                                 <div class="mt-1 text-xs text-gray-500">
                                                    Lote: <span class="font-mono">{{ $mov->batch_number }}</span>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            @if($mov->type === 'entry')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fas fa-arrow-down mr-1"></i> Entrada
                                                </span>
                                            @else
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-arrow-up mr-1"></i> Salida
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm font-bold {{ $mov->type === 'entry' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $mov->type === 'entry' ? '+' : '-' }}{{ number_format($mov->quantity) }}
                                            </span>
                                            <div class="text-xs text-gray-400">Saldo: {{ number_format($mov->new_balance) }}</div>
                                        </td>

                                        

                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <div class="font-medium text-gray-700">{{ $mov->reference_number ?? '-' }}</div>
                                            <div class="text-xs">{{ $mov->reason }}</div>
                                            <div class="mt-1 flex items-center text-xs text-gray-400">
                                                <i class="fas fa-user-circle mr-1"></i> {{ $mov->user->name ?? 'Sistema' }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                            <i class="fas fa-search text-4xl mb-3 text-gray-300 block"></i>
                                            No hay movimientos registrados con estos filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $movements->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>