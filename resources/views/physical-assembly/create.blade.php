<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                <i class="fas fa-boxes-packing text-indigo-600 mr-2"></i> Ensamblaje Físico: {{ $product->name }}
            </h2>
            <span class="bg-indigo-100 text-indigo-800 text-sm font-bold px-3 py-1 rounded-full">
                {{ $product->code }}
            </span>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8" 
         x-data="assemblyScanner(@js($recipe), '{{ route('physical-assembly.store', $product) }}')">
         
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- PANEL IZQUIERDO: EL ESCÁNER --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Configuración Inicial --}}
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 border-b pb-2">1. Ubicación Final</h3>
                    <label class="block text-sm font-medium text-gray-700 mb-1">¿Dónde se guardará esta caja armada?</label>
                    <select x-model="locationId" class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 shadow-sm">
                        <option value="">Seleccione una ubicación...</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- El Lector --}}
                <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1" :class="scanStatusColor"></div>
                    
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4">2. Escáner de Piezas</h3>
                    
                    <form @submit.prevent="handleScan" class="relative">
                        <input type="text" 
                            x-model="scanInput"
                            x-ref="scannerInput"
                            :disabled="!locationId"
                            placeholder="Escanea Serial, EPC o Lote..."
                            class="block w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-indigo-500 shadow-sm text-lg py-3 pl-10 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
                            autocomplete="off">
                        
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-barcode text-gray-400"></i>
                        </div>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <i class="fas fa-circle-notch fa-spin text-indigo-500" x-show="isScanning" x-cloak></i>
                        </div>
                    </form>

                    {{-- Mensajes de estado del escáner --}}
                    <div class="mt-4 p-3 rounded-lg text-sm font-medium flex items-start" x-show="scanMessage" x-cloak :class="scanMessageBg">
                        <i class="mt-0.5 mr-2" :class="scanMessageIcon"></i>
                        <span x-text="scanMessage"></span>
                    </div>

                    <div class="mt-6 text-xs text-gray-500">
                        <i class="fas fa-info-circle text-indigo-400 mr-1"></i> Asegúrate de seleccionar la ubicación antes de comenzar a escanear. El cursor regresará automáticamente aquí.
                    </div>
                </div>

            </div>

            {{-- PANEL DERECHO: LA RECETA Y PROGRESO --}}
            <div class="lg:col-span-2 bg-white shadow-sm rounded-xl border border-gray-200 flex flex-col min-h-[600px]">
                <div class="p-6 border-b border-gray-200 bg-gray-50 rounded-t-xl flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Progreso del Armado</h3>
                    <div class="text-sm font-medium text-gray-600">
                        <span x-text="totalScanned"></span> piezas escaneadas
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-white space-y-4">
                    <template x-for="item in recipe" :key="item.product_id">
                        <div class="p-4 border rounded-lg transition-colors flex items-center justify-between"
                             :class="{
                                 'border-green-500 bg-green-50': item.scanned_qty >= item.required_qty,
                                 'border-yellow-300 bg-yellow-50': item.scanned_qty > 0 && item.scanned_qty < item.required_qty,
                                 'border-gray-200 bg-white': item.scanned_qty === 0
                             }">
                            
                            <div class="flex-1">
                                <div class="flex items-center">
                                    <h4 class="font-bold text-gray-900 text-sm" x-text="item.code"></h4>
                                    <span x-show="item.is_mandatory" class="ml-2 text-[10px] uppercase font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded">Obligatorio</span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1" x-text="item.name"></p>
                            </div>

                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <div class="text-2xl font-black" :class="item.scanned_qty >= item.required_qty ? 'text-green-600' : 'text-gray-700'">
                                        <span x-text="item.scanned_qty"></span> <span class="text-sm text-gray-400 font-normal">/ <span x-text="item.required_qty"></span></span>
                                    </div>
                                </div>
                                <div class="w-8 flex justify-center">
                                    <i class="fas fa-check-circle text-2xl text-green-500" x-show="item.scanned_qty >= item.required_qty"></i>
                                    <i class="fas fa-circle text-2xl text-gray-200" x-show="item.scanned_qty < item.required_qty"></i>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer Guardar --}}
                <div class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <span x-show="!canSubmit()" class="text-red-500 font-medium"><i class="fas fa-exclamation-triangle"></i> Faltan piezas obligatorias</span>
                        <span x-show="canSubmit()" class="text-green-600 font-medium"><i class="fas fa-check-double"></i> Listo para ensamblar</span>
                    </div>
                    
                    <button type="button" 
                            @click="submitAssembly" 
                            :disabled="!canSubmit() || isSubmitting"
                            class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white font-bold py-3 px-8 rounded-lg shadow-md transition-all flex items-center">
                        <i class="fas fa-box-archive mr-2" x-show="!isSubmitting"></i>
                        <i class="fas fa-circle-notch fa-spin mr-2" x-show="isSubmitting" x-cloak></i>
                        <span x-text="isSubmitting ? 'Ensamblando...' : 'Sellar y Generar Caja Física'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('assemblyScanner', (initialRecipe, submitUrl) => ({
                recipe: initialRecipe,
                locationId: '',
                scanInput: '',
                isScanning: false,
                isSubmitting: false,
                scannedUnitIds: [], // Aquí guardamos los IDs físicos reales
                
                scanStatus: 'idle', // idle, success, error
                scanMessage: '',

                get scanStatusColor() {
                    if (this.scanStatus === 'success') return 'bg-green-500';
                    if (this.scanStatus === 'error') return 'bg-red-500';
                    return 'bg-transparent';
                },

                get scanMessageBg() {
                    if (this.scanStatus === 'success') return 'bg-green-50 text-green-800 border border-green-200';
                    if (this.scanStatus === 'error') return 'bg-red-50 text-red-800 border border-red-200';
                    return '';
                },

                get scanMessageIcon() {
                    if (this.scanStatus === 'success') return 'fas fa-check-circle text-green-500';
                    if (this.scanStatus === 'error') return 'fas fa-times-circle text-red-500';
                    return '';
                },

                get totalScanned() {
                    return this.scannedUnitIds.length;
                },

                async handleScan() {
                    const query = this.scanInput.trim();
                    if (!query) return;

                    this.isScanning = true;
                    this.scanStatus = 'idle';
                    
                    try {
                        // Buscamos la unidad física en la base de datos
                        const response = await fetch(`{{ route('api.product-units.search') }}?q=${encodeURIComponent(query)}`);
                        const units = await response.json();

                        if (units.length === 0) {
                            this.showError(`No se encontró ninguna pieza disponible con el código: ${query}`);
                            return;
                        }

                        // Si hay varios resultados (ej. mismo lote), tomamos el primero disponible
                        const unit = units[0];

                        this.validateAndAddUnit(unit);

                    } catch (error) {
                        this.showError('Error de conexión al buscar la pieza.');
                    } finally {
                        this.isScanning = false;
                        this.scanInput = ''; // Limpiamos el input
                        this.$refs.scannerInput.focus(); // Regresamos el foco al input
                    }
                },

                validateAndAddUnit(unit) {
                    // 1. Verificar si ya escaneamos esta unidad física exacta
                    if (this.scannedUnitIds.includes(unit.id)) {
                        this.showError(`La pieza ${unit.serial_number || unit.batch_number} ya fue escaneada en esta caja.`);
                        return;
                    }

                    // 2. Buscar si el producto pertenece a la receta
                    const recipeItem = this.recipe.find(r => r.product_id === unit.product_id);
                    
                    if (!recipeItem) {
                        this.showError(`La pieza ${unit.product.name} NO PERTENECE a este Set.`);
                        return;
                    }

                    // 3. Verificar si ya tenemos suficientes piezas de este tipo
                    if (recipeItem.scanned_qty >= recipeItem.required_qty) {
                        this.showError(`Ya completaste la cantidad necesaria de ${unit.product.name}.`);
                        return;
                    }

                    // Todo correcto: Agregamos
                    this.scannedUnitIds.push(unit.id);
                    recipeItem.scanned_qty++;
                    
                    this.showSuccess(`¡Agregado: ${unit.product.name} (${unit.serial_number || unit.batch_number || 'Sin SN'})!`);
                },

                showError(msg) {
                    this.scanStatus = 'error';
                    this.scanMessage = msg;
                    // Opcional: Podrías hacer sonar un beep de error aquí
                },

                showSuccess(msg) {
                    this.scanStatus = 'success';
                    this.scanMessage = msg;
                },

                canSubmit() {
                    if (!this.locationId) return false;
                    
                    // Verificamos que todos los items obligatorios estén completos
                    for (const item of this.recipe) {
                        if (item.is_mandatory && item.scanned_qty < item.required_qty) {
                            return false;
                        }
                    }
                    return true;
                },

                async submitAssembly() {
                    if (!this.canSubmit()) return;
                    
                    this.isSubmitting = true;
                    
                    try {
                        const response = await fetch(submitUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                location_id: this.locationId,
                                validated_unit_ids: this.scannedUnitIds 
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            alert(data.message);
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'Ocurrió un error al ensamblar.');
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error de conexión al intentar ensamblar la caja.');
                        this.isSubmitting = false;
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>