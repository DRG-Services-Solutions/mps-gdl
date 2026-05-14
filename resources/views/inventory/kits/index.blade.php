<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8 text-center">
                <div class="mb-6">
                    <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-indigo-100">
                        <i class="fas fa-barcode text-3xl text-indigo-600"></i>
                    </div>
                    <h2 class="mt-4 text-2xl font-bold text-gray-900">Estación de Armado de Kits</h2>
                    <p class="text-gray-500">Escanee el código de la charola para comenzar el checklist</p>
                </div>

                <form action="{{ route('kits.startAssembly') }}" method="POST" class="max-w-md mx-auto">
                    @csrf
                    <div class="relative">
                        <input type="text" 
                               name="code" 
                               id="charola_code"
                               autofocus 
                               autocomplete="off"
                               class="block w-full pl-4 pr-12 py-4 border-2 border-indigo-500 rounded-xl focus:ring-indigo-500 focus:border-indigo-600 text-xl font-mono text-center"
                               placeholder="Ej: CH-100234">
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                            <kbd class="hidden sm:inline-block px-2 py-1 text-xs font-semibold text-gray-500 bg-gray-100 border border-gray-300 rounded-lg">
                                Enter
                            </kbd>
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-4 w-full bg-indigo-600 text-white py-3 rounded-lg font-bold hover:bg-indigo-700 transition">
                        Comenzar Verificación
                    </button>
                </form>
            </div>

            <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Armados Recientes</h3>
                </div>
                <ul class="divide-y divide-gray-200">
                    @forelse($recentAssemblies as $assembly)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-indigo-600 truncate">
                                        {{ $assembly->stockUnit->product->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 italic">
                                        Serie: {{ $assembly->stockUnit->serial_number }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $assembly->status == 'completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $assembly->status }}
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">{{ $assembly->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-center text-gray-500 text-sm">No hay actividad reciente.</li>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('click', () => {
            document.getElementById('charola_code').focus();
        });
    </script>
</x-app-layout>