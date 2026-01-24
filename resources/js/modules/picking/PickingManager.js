/**
 * Gestor de operaciones de picking
 * Maneja comunicación con backend y lógica de negocio
 * @author Erick - DRG Services & Solutions
 */
export class PickingManager {
    constructor(surgeryId, csrfToken) {
        this.surgeryId = surgeryId;
        this.csrfToken = csrfToken;
        this.currentMode = 'manual';
        this.pendingRfidEPC = null;

                console.log('✅ [PICKING] PickingManager.js inicializado', {
            surgeryId: this.surgeryId,
            mode: this.currentMode
        });

    }
    
    // ==========================================
    // API - BACKEND
    // ==========================================
    
    /**
     * Buscar información de un tag RFID por EPC
     * @param {string} epc - Código EPC del tag
     * @returns {Promise<Object>}
     */
    async searchEPC(epc) {
        const url = `/surgeries/${this.surgeryId}/preparations/search-epc?epc=${encodeURIComponent(epc)}`;
        const response = await fetch(url, {
            headers: { 'X-CSRF-TOKEN': this.csrfToken }
        });
        return await response.json();
    }
    
    /**
     * Confirmar y agregar unidad RFID al picking
     * @param {string} epc - Código EPC del tag
     * @returns {Promise<Object>}
     */
    async confirmRFID(epc) {
        const url = `/surgeries/${this.surgeryId}/preparations/confirm-rfid`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ epc })
        });
        return await response.json();
    }
    
    /**
     * Escanear código de barras (modo manual)
     * @param {string} barcode - Código de barras
     * @returns {Promise<Object>}
     */
    async scanBarcode(barcode) {
        const url = `/surgeries/${this.surgeryId}/preparations/scan-barcode`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ barcode })
        });
        return await response.json();
    }
    
    /**
     * Obtener estado actual del progreso
     * @returns {Promise<Object>}
     */
    async getStatus() {
        const url = `/surgeries/${this.surgeryId}/preparations/status`;
        const response = await fetch(url);
        return await response.json();
    }
    
    /**
     * Cancelar preparación
     * @param {string} reason - Motivo de cancelación
     * @returns {Promise<Object>}
     */
    async cancelPreparation(reason) {
        const url = `/surgeries/${this.surgeryId}/preparations/cancel`;
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify({ reason })
        });
        return await response.json();
    }
    
    // ==========================================
    // LÓGICA DE NEGOCIO
    // ==========================================
    
    /**
     * Cambiar entre modo manual y RFID
     * @param {string} mode - 'manual' o 'rfid'
     * @param {RFIDManager} rfidManager - Instancia del manager RFID
     */
    switchMode(mode, rfidManager = null) {
        this.currentMode = mode;
        
        const manualBtn = document.getElementById('manualModeBtn');
        const rfidBtn = document.getElementById('rfidModeBtn');
        const manualSection = document.getElementById('manualModeSection');
        const rfidSection = document.getElementById('rfidModeSection');
        
        if (mode === 'manual') {
            manualBtn.classList.add('active');
            rfidBtn.classList.remove('active');
            manualSection.classList.remove('hidden');
            rfidSection.classList.add('hidden');
            
            // Detener lectura RFID si está activa
            if (rfidManager?.isReading()) {
                rfidManager.stop();
            }
            
            // Focus en el input de barcode
            document.getElementById('barcode_scan')?.focus();
            console.log('📦 Modo Manual activado');
        } else {
            rfidBtn.classList.add('active');
            manualBtn.classList.remove('active');
            rfidSection.classList.remove('hidden');
            manualSection.classList.add('hidden');
            console.log('📡 Modo RFID activado');
        }
    }
}