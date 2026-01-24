import { RFID_CONFIG } from './RFIDConfig.js';

/**
 * Gestor principal del lector RFID RFD90
 * Maneja conexión, lectura y eventos del lector
 * @author Erick - DRG Services & Solutions
 */
export class RFIDManager {
    constructor(options = {}) {
        this.config = { ...RFID_CONFIG, ...options };
        
        // Estado interno del lector
        this.state = {
            readerID: null,
            isConnected: false,
            isReading: false,
            scannedTags: new Set(),
            lastScannedTags: new Map(),
            currentTransportIndex: 0
        };
        
        // Referencias DOM
        this.dom = {};
        
        // Callbacks del usuario
        this.callbacks = {};
        console.log('✅ [RFID] RFIDManager.js inicializado', {
            transports: this.config.TRANSPORTS,
            cooldown: this.config.COOLDOWN_TIME
        });

    }
    
    // ==========================================
    // INICIALIZACIÓN
    // ==========================================
    
    /**
     * Inicializar con referencias DOM
     * @param {Object} domRefs - Objetos DOM necesarios
     */
    init(domRefs) {
        this.dom = { ...this.dom, ...domRefs };
        this.log('Sistema RFID iniciado. Presione "Conectar Lector" para comenzar.', 'info');
        return this;
    }
    
    /**
     * Configurar callbacks de eventos
     * @param {Object} callbacks - onTagDetected, onConnected, onDisconnected, onError
     */
    setCallbacks(callbacks) {
        this.callbacks = { ...this.callbacks, ...callbacks };
        return this;
    }
    
    // ==========================================
    // CONEXIÓN
    // ==========================================
    
    /**
     * Iniciar proceso de conexión con el lector
     */
    connect() {
        this.log('🔄 Iniciando conexión con lector RFID...', 'info');
        this.updateStatus(false);
        this.dom.connectBtn.disabled = true;
        this.state.currentTransportIndex = 0;
        this.tryNextTransport();
    }
    
    /**
     * Desconectar del lector actual
     */
    disconnect() {
        if (!this.state.isConnected) return;
        
        try {
            if (this.state.isReading) {
                this.stop();
            }
            rfid.disconnect();
            this.log('🔌 Desconexión solicitada.', 'info');
            this.updateStatus(false);
        } catch (e) {
            this.log('❌ Error al desconectar: ' + e.message, 'error');
            this.callbacks.onError?.(e);
        }
    }
    
    /**
     * Intentar conexión por el siguiente transporte
     */
    tryNextTransport() {
        if (this.state.currentTransportIndex >= this.config.TRANSPORTS.length) {
            this.log('❌ No se detectaron lectores en ningún transporte.', 'error');
            this.updateStatus(false);
            this.dom.connectBtn.disabled = false;
            return;
        }
        
        const transport = this.config.TRANSPORTS[this.state.currentTransportIndex];
        this.log(`🔍 Buscando lectores por ${transport.toUpperCase()}...`, 'info');
        
        try {
            rfid.transport = transport;
            rfid.enumRFIDEvent = 'RFIDCallbacks.handleEnumeration(%s)';
            rfid.enumerate();
        } catch (e) {
            this.log(`❌ Error al enumerar: ${e.message}`, 'error');
            this.state.currentTransportIndex++;
            this.tryNextTransport();
        }
    }
    
    // ==========================================
    // OPERACIONES DE LECTURA
    // ==========================================
    
    /**
     * Iniciar lectura continua de tags
     */
    start() {
        this.state.scannedTags.clear();
        this.state.isReading = true;
        this.dom.startBtn.disabled = true;
        this.dom.stopBtn.disabled = false;
        
        this.log('📡 Iniciando lectura continua...', 'success');
        this.dom.feedbackDiv.textContent = 'Leyendo tags... Acerque los productos al lector.';
        
        try {
            rfid.performInventory();
        } catch (e) {
            this.log('❌ Error al iniciar: ' + e.message, 'error');
            this.state.isReading = false;
            this.dom.startBtn.disabled = false;
            this.dom.stopBtn.disabled = true;
        }
    }
    
    /**
     * Detener lectura de tags
     */
    stop() {
        try {
            rfid.stop();
            this.state.isReading = false;
            this.dom.startBtn.disabled = false;
            this.dom.stopBtn.disabled = true;
            
            this.log('⏸️ Lectura detenida.', 'warning');
            this.dom.feedbackDiv.textContent = `Detenido. ${this.state.scannedTags.size} tags detectados.`;
        } catch (e) {
            this.log('❌ Error al detener: ' + e.message, 'error');
        }
    }
    
    // ==========================================
    // CALLBACKS DE ENTERPRISE BROWSER
    // ==========================================
    
    /**
     * Callback cuando se enumeran lectores disponibles
     * @param {Array} rfidArray - Array de lectores encontrados
     */
    handleEnumeration(rfidArray) {
        this.log(`📡 Lectores encontrados: ${rfidArray?.length || 0}`, 'info');
        
        if (!rfidArray || rfidArray.length === 0) {
            if (this.state.currentTransportIndex < this.config.TRANSPORTS.length - 1) {
                this.state.currentTransportIndex++;
                this.tryNextTransport();
            } else {
                this.log('⚠️ No se encontraron lectores.', 'error');
                this.updateStatus(false);
                this.dom.connectBtn.disabled = false;
            }
            return;
        }
        
        // Tomar el primer lector encontrado
        this.state.readerID = rfidArray[0][0];
        this.log(`🔌 Lector encontrado: ${this.state.readerID}`, 'success');
        
        try {
            // Configurar el lector
            rfid.readerID = this.state.readerID;
            rfid.startTriggerType = this.config.TRIGGERS.START;
            rfid.stopTriggerType = this.config.TRIGGERS.STOP;
            rfid.beepOnRead = this.config.BEEP_ON_READ;
            rfid.reportUniqueTags = this.config.REPORT_UNIQUE_TAGS;
            rfid.tagEvent = 'RFIDCallbacks.handleTagData(%json)';
            rfid.statusEvent = 'RFIDCallbacks.handleStatus(%json)';
            
            // Conectar
            rfid.connect();
        } catch (e) {
            this.log(`❌ Error al configurar: ${e.message}`, 'error');
            this.updateStatus(false);
            this.dom.connectBtn.disabled = false;
        }
    }
    
    /**
     * Callback de cambios de estado del lector
     * @param {Object} eventInfo - Información del evento
     */
    handleStatus(eventInfo) {
        const msg = eventInfo?.status?.toLowerCase() || eventInfo?.vendorMessage?.toLowerCase() || '';
        
        if (msg.includes('connect')) {
            this.updateStatus(true, `Lector ${this.state.readerID} conectado.`);
            this.callbacks.onConnected?.(this.state.readerID);
        } else if (msg.includes('disconnect')) {
            this.updateStatus(false, 'Lector desconectado.');
            this.callbacks.onDisconnected?.();
        } else if (msg.includes('error')) {
            this.log(`❌ Error: ${msg}`, 'error');
            this.callbacks.onError?.(msg);
        }
    }
    
    /**
     * Callback cuando se leen tags RFID
     * @param {Object} tagArray - Array de tags leídos
     */
    handleTagData(tagArray) {
        if (!this.state.isReading || !tagArray?.TagData) return;
        
        tagArray.TagData.forEach(tag => {
            const epc = tag.tagID;
            
            // Verificar cooldown para evitar duplicados
            if (epc && !this.isInCooldown(epc)) {
                this.state.scannedTags.add(epc);
                this.addToCooldown(epc);
                
                this.log(`✓ Tag: ${epc}`, 'success');
                
                // Actualizar contador UI
                if (this.dom.tagsCountDiv) {
                    this.dom.tagsCountDiv.querySelector('p.text-2xl').textContent = this.state.scannedTags.size;
                }
                
                // Notificar al callback externo
                this.callbacks.onTagDetected?.(epc, tag);
            }
        });
    }
    
    // ==========================================
    // COOLDOWN (Anti-duplicados)
    // ==========================================
    
    /**
     * Verificar si un EPC está en cooldown
     * @param {string} epc - EPC del tag
     * @returns {boolean}
     */
    isInCooldown(epc) {
        if (!this.state.lastScannedTags.has(epc)) return false;
        const lastTime = this.state.lastScannedTags.get(epc);
        return (Date.now() - lastTime) < this.config.COOLDOWN_TIME;
    }
    
    /**
     * Agregar EPC al cooldown temporal
     * @param {string} epc - EPC del tag
     */
    addToCooldown(epc) {
        this.state.lastScannedTags.set(epc, Date.now());
        setTimeout(() => {
            this.state.lastScannedTags.delete(epc);
        }, this.config.COOLDOWN_TIME);
    }
    
    // ==========================================
    // UI Y LOGGING
    // ==========================================
    
    /**
     * Actualizar estado visual del lector
     * @param {boolean} isConnected - Estado de conexión
     * @param {string} message - Mensaje opcional
     */
    updateStatus(isConnected, message = '') {
        this.state.isConnected = isConnected;
        
        if (this.dom.statusDiv) {
            this.dom.statusDiv.innerHTML = isConnected
                ? `<span class="text-green-600 font-semibold">Estado: ✓ Conectado (${this.state.readerID})</span>`
                : `<span class="text-gray-600">Estado: Desconectado</span>`;
        }
        
        // Habilitar/deshabilitar botones según estado
        this.dom.connectBtn.disabled = isConnected;
        this.dom.disconnectBtn.disabled = !isConnected;
        this.dom.startBtn.disabled = !isConnected || this.state.isReading;
        this.dom.stopBtn.disabled = !isConnected || !this.state.isReading;
        
        if (!isConnected && this.state.isReading) {
            this.state.isReading = false;
        }
        
        if (message) this.log(message, isConnected ? 'success' : 'info');
    }
    
    /**
     * Agregar mensaje a la consola de debugging
     * @param {string} message - Mensaje a mostrar
     * @param {string} type - Tipo: info, success, warning, error
     */
    log(message, type = 'info') {
        if (!this.dom.consoleDiv) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const logEntry = document.createElement('div');
        logEntry.textContent = `[${timestamp}] ${message}`;
        logEntry.style.color = this.config.CONSOLE_COLORS[type] || '#f0f0f0';
        
        this.dom.consoleDiv.appendChild(logEntry);
        this.dom.consoleDiv.scrollTop = this.dom.consoleDiv.scrollHeight;
    }
    
    /**
     * Limpiar consola de debugging
     */
    clearConsole() {
        if (this.dom.consoleDiv) {
            this.dom.consoleDiv.innerHTML = '';
            this.log('Consola limpiada.', 'info');
        }
    }
    
    // ==========================================
    // GETTERS PÚBLICOS
    // ==========================================
    
    isConnected() { return this.state.isConnected; }
    isReading() { return this.state.isReading; }
    getScannedTags() { return Array.from(this.state.scannedTags); }
    getReaderID() { return this.state.readerID; }
    
}