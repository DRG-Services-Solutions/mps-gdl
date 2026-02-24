<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 leading-tight">
                    <i class="fas fa-file-invoice mr-2 text-indigo-600"></i>Remisiones
                </h2>
                <p class="mt-1 text-sm text-gray-600">Gestión de remisiones quirúrgicas</p>
            </div>
            <a href="{{ route('shipping-notes.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-2"></i>Nueva Remisión
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Alertas --}}
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Filtros --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <form method="GET" action="{{ route('shipping-notes.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Buscar por número o hospital..."
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <select name="hospital_id" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Todos los hospitales</option>
                            @foreach ($hospitals as $hospital)
                                <option value="{{ $hospital->id }}" {{ request('hospital_id') == $hospital->id ? 'selected' : '' }}>
                                    {{ $hospital->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">Todos los estados</option>
                            @foreach ($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full rounded-lg border-gray-300 text-sm" placeholder="Desde">
                    </div>
                    <div class="flex gap-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="flex-1 rounded-lg border-gray-300 text-sm" placeholder="Hasta">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
                            <i class="fas fa-search"></i>
                        </button>
                        @if (request()->hasAny(['search', 'hospital_id', 'status', 'date_from', 'date_to']))
                            <a href="{{ route('shipping-notes.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Remisión</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Hospital</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Doctor</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Fecha Cirugía</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Razón Social</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($shippingNotes as $note)
                                @php
                                    $colors = \App\Models\ShippingNote::getStatusColors();
                                    $labels = \App\Models\ShippingNote::getStatusLabels();
                                    $color = $colors[$note->status] ?? 'gray';
                                    $badgeClasses = [
                                        'gray' => 'bg-gray-100 text-gray-700',
                                        'blue' => 'bg-blue-100 text-blue-700',
                                        'yellow' => 'bg-yellow-100 text-yellow-700',
                                        'orange' => 'bg-orange-100 text-orange-700',
                                        'purple' => 'bg-purple-100 text-purple-700',
                                        'green' => 'bg-green-100 text-green-700',
                                        'red' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('shipping-notes.show', $note) }}"
                                            class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">
                                            {{ $note->shipping_number }}
                                        </a>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ $note->surgery_type }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $note->hospital->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $note->doctor->full_name ?? 'Sin asignar' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 text-center">
                                        {{ $note->surgery_date?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $badgeClasses[$color] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $labels[$note->status] ?? $note->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ $note->billingLegalEntity->name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('shipping-notes.show', $note) }}"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                            <i class="fas fa-eye mr-1"></i>Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-3 block"></i>
                                        <p class="text-lg font-medium">No hay remisiones</p>
                                        <p class="text-sm mt-1">Crea una nueva desde una cirugía programada</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($shippingNotes->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $shippingNotes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>