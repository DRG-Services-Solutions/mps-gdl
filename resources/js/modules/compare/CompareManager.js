/**
 * CompareManager - Lógica de comparativa Pre-Armado vs Checklist
 * 
 * Maneja las llamadas API al backend y el estado de la comparación.
 * Reutilizable en cualquier contexto de comparativa RFID.
 * 
 * @ubicación resources/js/modules/compare/CompareManager.js
 * @author Erick - DRG Services & Solutions
 */

export class CompareManager {
    /**
     * @param {Object} config
     * @param {string} config.packageId - ID del paquete pre-armado
     * @param {string} config.csrfToken - Token CSRF de Laravel
     * @param {Object} config.routes - Rutas del backend
     * @param {string} config.routes.compare - URL para comparativa
     * @param {string} config.routes.add - URL para agregar producto
     * @param {string} config.routes.search - URL para buscar EPC
     */
    constructor(config) {
        this.config = config;

        // Estado
        this.scannedEpcs = new Set();
        this.comparisonData = null;
        this.autoAdd = false;

        // Callbacks
        this.callbacks = {
            onTagProcessed: null,    // (epc, result) => {}
            onCompareComplete: null, // (data) => {}
            onError: null,           // (message, code) => {}
            onLog: null,             // (message, type) => {}
        };
    }

    /**
     * Configurar callbacks de eventos
     */
    setCallbacks(callbacks) {
        this.callbacks = { ...this.callbacks, ...callbacks };
        return this;
    }

    // ==========================================
    // MANEJO DE TAGS ESCANEADOS
    // ==========================================

    /**
     * Registrar un nuevo EPC escaneado
     * @param {string} epc
     * @returns {boolean} true si es nuevo, false si ya existía
     */
    addEpc(epc) {
        const normalized = epc.toUpperCase().trim();

        if (this.scannedEpcs.has(normalized)) {
            this.log(`↩️ Tag ya escaneado: ${normalized}`, 'warning');
            return false;
        }

        this.scannedEpcs.add(normalized);
        this.log(`✅ Nuevo tag registrado: ${normalized}`, 'success');
        return true;
    }

    /**
     * Remover un EPC de la lista
     */
    removeEpc(epc) {
        this.scannedEpcs.delete(epc.toUpperCase().trim());
    }

    /**
     * Limpiar todos los EPCs
     */
    clearEpcs() {
        this.scannedEpcs.clear();
        this.comparisonData = null;
    }

    /**
     * Obtener array de EPCs escaneados
     */
    getEpcs() {
        return Array.from(this.scannedEpcs);
    }

    /**
     * Cantidad de EPCs escaneados
     */
    get count() {
        return this.scannedEpcs.size;
    }

    // ==========================================
    // API: COMPARATIVA
    // ==========================================

    /**
     * Ejecutar comparativa de EPCs escaneados vs checklist
     * @returns {Promise<Object|null>} Datos de comparación o null si error
     */
    async compare() {
        if (this.scannedEpcs.size === 0) {
            this.log('⚠️ No hay tags escaneados para comparar.', 'warning');
            return null;
        }

        this.log(`📊 Comparando ${this.scannedEpcs.size} EPCs con checklist...`, 'info');

        try {
            const response = await fetch(this.config.routes.compare, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({
                    epcs: this.getEpcs(),
                }),
            });

            const result = await response.json();

            if (result.success) {
                this.comparisonData = result.data;
                this.log(
                    `📊 Completado: ${result.data.stats.completeness_percentage}% ` +
                    `(${result.data.stats.complete}/${result.data.stats.total_checklist_items} completos)`,
                    'success'
                );
                this.callbacks.onCompareComplete?.(result.data);
                return result.data;
            } else {
                this.log(`❌ Error en comparativa: ${result.message}`, 'error');
                this.callbacks.onError?.(result.message, 'COMPARE_FAILED');
                return null;
            }
        } catch (error) {
            this.log(`❌ Error de red: ${error.message}`, 'error');
            this.callbacks.onError?.(error.message, 'NETWORK_ERROR');
            return null;
        }
    }

    // ==========================================
    // API: AGREGAR PRODUCTO
    // ==========================================

    /**
     * Agregar un producto por EPC al paquete
     * @param {string} epc
     * @param {boolean} force - Si true, agrega aunque no esté en checklist
     * @returns {Promise<Object>} Resultado de la operación
     */
    async addProduct(epc, force = false) {
        try {
            const response = await fetch(this.config.routes.add, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ epc, force }),
            });

            const result = await response.json();

            if (result.success) {
                this.log(`✅ ${result.data.product_name} agregado al paquete.`, 'success');

                if (result.data.is_extra) {
                    this.log('⚠️ NOTA: Este producto NO está en el checklist.', 'warning');
                }
            } else if (result.requires_confirmation) {
                this.log(`⚠️ ${result.data.product_name}: NO está en checklist.`, 'warning');
            } else {
                const msg = result.message || 'Error desconocido';
                this.log(`⚠️ ${msg}`, 'warning');
            }

            this.callbacks.onTagProcessed?.(epc, result);
            return result;
        } catch (error) {
            this.log(`❌ Error al agregar ${epc}: ${error.message}`, 'error');
            this.callbacks.onError?.(error.message, 'ADD_FAILED');
            return { success: false, message: error.message, code: 'NETWORK_ERROR' };
        }
    }

    /**
     * Agregar todos los EPCs escaneados al paquete
     * @returns {Promise<Object>} Resumen { success, errors, extras, skipped }
     */
    async addAll() {
        const summary = { success: 0, errors: 0, extras: 0, skipped: 0 };

        for (const epc of this.scannedEpcs) {
            const result = await this.addProduct(epc, true);

            if (result.success) {
                summary.success++;
                if (result.data?.is_extra) summary.extras++;
            } else if (result.code === 'ALREADY_IN_PACKAGE') {
                summary.skipped++;
            } else {
                summary.errors++;
            }
        }

        this.log(
            `📦 Resultado: ${summary.success} agregados, ` +
            `${summary.extras} extras, ${summary.errors} errores, ` +
            `${summary.skipped} ya existían.`,
            'success'
        );

        return summary;
    }

    // ==========================================
    // API: BUSCAR EPC
    // ==========================================

    /**
     * Buscar información de un EPC individual
     * @param {string} epc
     * @returns {Promise<Object|null>}
     */
    async searchEpc(epc) {
        try {
            const url = `${this.config.routes.search}?epc=${encodeURIComponent(epc)}`;
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (result.success) {
                this.log(`🔍 ${result.data.product_name} (${result.data.status_label})`, 'success');
                return result.data;
            } else {
                this.log(`⚠️ ${result.message}`, 'warning');
                return null;
            }
        } catch (error) {
            this.log(`❌ Error buscando EPC: ${error.message}`, 'error');
            return null;
        }
    }

    // ==========================================
    // AUTO-ADD (cuando se escanea un tag)
    // ==========================================

    /**
     * Procesar un tag en modo auto-agregar
     * Si el producto no está en checklist, NO lo agrega (requiere confirmación)
     */
    async processAutoAdd(epc) {
        if (!this.autoAdd) return;

        this.log(`🔄 Auto-procesando: ${epc}...`, 'info');
        const result = await this.addProduct(epc, false);

        // Si requiere confirmación (no en checklist), no se agrega automáticamente
        if (result.requires_confirmation) {
            this.log(`⏸️ ${result.data.product_name} requiere confirmación manual.`, 'warning');
        }

        return result;
    }

    // ==========================================
    // HELPERS PRIVADOS
    // ==========================================

    _headers() {
        return {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': this.config.csrfToken,
            'Accept': 'application/json',
        };
    }

    log(message, type = 'info') {
        this.callbacks.onLog?.(message, type);
    }
}