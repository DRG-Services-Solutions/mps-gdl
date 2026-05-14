<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Armado de Kit: {{ $unit->product->name }}</h2>
        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
            ID: {{ $unit->serial_number }}
        </span>
    </div>

    <form action="{{ route('kits.finalize', $assembly) }}" method="POST">
        @csrf
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left">Instrumento (SKU)</th>
                    <th class="px-4 py-2 text-center">Req.</th>
                    <th class="px-4 py-2 text-center">Confirmado</th>
                    <th class="px-4 py-2 text-left">Estado</th>
                </tr>
            </thead>
            <tbody x-data="{ items: @js($bom) }">
                @foreach($bom as $index => $item)
                <tr class="border-b">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $item->component->name }}</div>
                        <div class="text-xs text-gray-500">{{ $item->component->sku }}</div>
                    </td>
                    <td class="px-4 py-3 text-center font-bold">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-center">
                        <input type="number" 
                               name="items[{{ $item->component_id }}][qty]" 
                               value="{{ $item->quantity }}"
                               class="w-20 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                    </td>
                    <td class="px-4 py-3">
                        @if($item->is_mandatory)
                            <span class="text-red-600 text-xs font-bold uppercase italic">Obligatorio</span>
                        @else
                            <span class="text-gray-400 text-xs">Opcional</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-8 flex justify-end gap-4">
            <a href="#" class="px-4 py-2 text-gray-600">Cancelar</a>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700">
                Finalizar y Marcar como Listo
            </button>
        </div>
    </form>
</div>