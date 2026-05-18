                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-green-600"></i>
                        Contenido del Paquete (<span id="total-items-count">{{ $preAssembled->contents->count() }}</span> items)
                    </h3>
                </div>
                
                @if($preAssembled->contents->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">EPC / Código</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Caducidad</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Agregado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($preAssembled->contents->groupBy('product_id') as $productId => $items)
                            @php
                                $firstItem = $items->first();
                                $product = $firstItem->product;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-box text-indigo-600"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-tag mr-1"></i>
                                                {{ $product->code }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @foreach($items as $item)
                                            @if($item->productUnit && $item->productUnit->epc)
                                                <div class="flex items-center space-x-2">
                                                    <i class="fas fa-qrcode text-xs text-gray-400"></i>
                                                    <span class="text-xs font-mono text-gray-600">
                                                        {{ Str::limit($item->productUnit->epc, 20, '...')  }}
                                                    </span>
                                                </div>
                                            @endif
                                            @endforeach
                                                <div class="text-xs text-gray-400">
                                                    <i class="fas fa-qrcode text-xs text-gray-400"></i>
                                                    {{ $item->product->code }}
                                                </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                        {{ $items->sum('quantity') }} 
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $hasExpired = $items->contains(fn($item) => $item->productUnit && $item->isExpired());
                                        $nearExpiry = $items->contains(fn($item) => $item->productUnit && $item->isExpiringSoon(30));
                                    @endphp
                                    @if($hasExpired)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Vencido
                                        </span>
                                    @elseif($nearExpiry)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-clock mr-1"></i>
                                            Próximo
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                            OK
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-gray-500">
                                    {{ $firstItem->added_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <form action="{{ route('pre-assembled.remove-product', $preAssembled) }}" 
                                        method="POST" 
                                        class="inline remove-product-form"
                                        onsubmit="return confirm('¿Remover {{ $items->sum('quantity') }} unidad(es) de {{ $product->name }} del paquete?')">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $productId }}">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 hover:bg-red-50 p-2 rounded transition-colors"
                                                title="Remover">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-box-open text-6xl mb-4 text-gray-300"></i>
                        <p class="text-base font-medium text-gray-900 mb-2">El paquete está vacío</p>
                        <p class="text-sm text-gray-600">Comienza escaneando productos arriba</p>
                    </div>
                </div>
                @endif
