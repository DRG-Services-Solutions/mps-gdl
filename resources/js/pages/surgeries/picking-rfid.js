import { RFIDManager } from '../../modules/rfid/RFIDManager.js';
import { RFIDCallbacks } from '../../modules/rfid/RFIDCallbacks.js';
import { PickingManager } from '../../modules/picking/PickingManager.js';
import { PickingUI } from '../../modules/picking/PickingUI.js';
import { ToastNotification } from '../../modules/picking/ToastNotification.js'; // 🆕 NUEVO

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Inicializando sistema de picking RFID...');
    
    const surgeryId = document.querySelector('[data-surgery-id]')?.dataset.surgeryId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!surgeryId || !csrfToken) {
        console.error('❌ Faltan datos necesarios');
        return;
    }
    
    // ==========================================
    // INICIALIZAR MANAGERS
    // ==========================================
    
    const rfidManager = new RFIDManager();
    const rfidCallbacks = new RFIDCallbacks();
    const pickingManager = new PickingManager(surgeryId, csrfToken);
    
    rfidCallbacks.setManager(rfidManager);
    
    // 🆕 CONTADORES RFID
    let correctCount = 0;
    let incorrectCount = 0;
    let incorrectTags = [];
    
    // ==========================================
    // 🆕 CONFIGURAR EVENTOS RFID - FLUJO AUTOMÁTICO
    // ==========================================
    
    rfidManager.setCallbacks({
        /**
         * 🆕 Callback cuando se detecta un tag RFID - PROCESO AUTOMÁTICO
         */
        onTagDetected: async (epc, tagData) => {
            rfidManager.log(`🔍 Tag detectado: ${epc}`, 'info');
            
            try {
                // 1. Buscar información del tag
                const searchData = await pickingManager.searchEPC(epc);
                
                if (searchData.success) {
                    // ✅ TAG CORRECTO - Confirmar automáticamente
                    rfidManager.log(`✅ Tag correcto: ${searchData.data.product_name}`, 'success');
                    
                    // Confirmar inmediatamente sin modal
                    const confirmData = await pickingManager.confirmRFID(epc);
                    
                    if (confirmData.success) {
                        correctCount++;
                        updateRFIDStats();
                        
                        // Toast de éxito
                        ToastNotification.success(`✓ ${confirmData.data.product_name} agregado`);
                        
                        // Actualizar tabla con animación
                        PickingUI.updateItemInTable(confirmData.data);
                        flashRowGreen(confirmData.data.item_id);
                        
                        // Actualizar progreso
                        await PickingUI.updateProgress(pickingManager);
                        
                        // Si el item se completó
                        if (confirmData.data.quantity_missing <= 0) {
                            setTimeout(() => PickingUI.removeItemFromTable(confirmData.data.item_id), 1500);
                        }
                        
                        // Si toda la preparación se completó
                        if (confirmData.data.preparation_complete) {
                            PickingUI.showCompletionAlert();
                        }
                        if (confirmData.data?.conditional_actions?.length > 0) {
                            confirmData.data.conditional_actions.forEach(action => {
                                ToastNotification.warning(action.message);
                            });
                            setTimeout(() => window.location.reload(), 1800);
                        }
                    }
                } else {
                    // ❌ TAG INCORRECTO - Registrar y alertar
                    incorrectCount++;
                    updateRFIDStats();
                    
                    const incorrectTag = {
                        epc: epc,
                        message: searchData.message,
                        timestamp: new Date().toLocaleTimeString()
                    };
                    
                    incorrectTags.push(incorrectTag);
                    addIncorrectTagToPanel(incorrectTag);
                    
                    rfidManager.log(`❌ Tag incorrecto: ${epc}`, 'error');
                    
                    // Toast de error
                    ToastNotification.error(searchData.message);
                    
                    // Beep de error (si es posible con RFD90)
                    playErrorSound();
                }
                
            } catch (error) {
                rfidManager.log(`❌ Error al procesar tag: ${error}`, 'error');
                ToastNotification.error('Error al procesar tag RFID');
                console.error('Error completo:', error);
            }
        },
        
        onConnected: (readerID) => {
            console.log(`✓ RFID conectado: ${readerID}`);
            ToastNotification.info(`Lector ${readerID} conectado`);
        },
        
        onDisconnected: () => {
            console.log('✗ RFID desconectado');
            ToastNotification.warning('Lector RFID desconectado');
        },
        
        onError: (error) => {
            console.error('Error RFID:', error);
            ToastNotification.error('Error en lector RFID');
        }
    });
    
    // ==========================================
    // 🆕 FUNCIONES DE UI PARA RFID
    // ==========================================
    
    /**
     * Actualizar estadísticas de escaneo
     */
    function updateRFIDStats() {
        document.getElementById('rfid-correct-count').textContent = correctCount;
        document.getElementById('rfid-incorrect-count').textContent = incorrectCount;
        document.getElementById('rfid-total-scanned').textContent = correctCount + incorrectCount;
        
        // Mostrar panel de stats
        document.getElementById('rfid-stats-panel').classList.remove('hidden');
        
        // Mostrar panel de incorrectos si hay errores
        if (incorrectCount > 0) {
            document.getElementById('incorrect-tags-panel').classList.remove('hidden');
            document.getElementById('incorrect-tags-count').textContent = incorrectCount;
        }
    }
    
    /**
     * Agregar tag incorrecto al panel
     */
    function addIncorrectTagToPanel(tag) {
        const list = document.getElementById('incorrect-tags-list');
        const entry = document.createElement('div');
        entry.className = 'bg-white p-3 rounded border border-red-300 flex items-start space-x-3';
        entry.innerHTML = `
            <i class="fas fa-exclamation-triangle text-red-600 mt-1"></i>
            <div class="flex-1">
                <p class="font-mono text-sm text-gray-900">${tag.epc}</p>
                <p class="text-xs text-red-600 mt-1">${tag.message}</p>
                <p class="text-xs text-gray-500 mt-1">${tag.timestamp}</p>
            </div>
        `;
        list.insertBefore(entry, list.firstChild);
    }
    
    /**
     * Limpiar lista de tags incorrectos
     */
    window.clearIncorrectTags = function() {
        incorrectTags = [];
        incorrectCount = 0;
        document.getElementById('incorrect-tags-list').innerHTML = '';
        document.getElementById('incorrect-tags-panel').classList.add('hidden');
        updateRFIDStats();
    };
    
    /**
     * Animar fila de la tabla en verde
     */
    function flashRowGreen(itemId) {
        const row = document.getElementById(`item-row-${itemId}`);
        if (!row) return;
        
        row.classList.add('bg-green-100');
        setTimeout(() => {
            row.classList.remove('bg-green-100');
        }, 1000);
    }
    
    /**
     * Reproducir sonido de error (simulado con vibración en TC50)
     */
    function playErrorSound() {
        // En TC50, podemos usar vibración como feedback
        if ('vibrate' in navigator) {
            navigator.vibrate([100, 50, 100]); // Patrón de vibración
        }
    }
    
    // ==========================================
    // INICIALIZAR DOM RFID
    // ==========================================
    
    rfidManager.init({
        connectBtn: document.getElementById('rfid-connect-btn'),
        disconnectBtn: document.getElementById('rfid-disconnect-btn'),
        startBtn: document.getElementById('rfid-start-btn'),
        stopBtn: document.getElementById('rfid-stop-btn'),
        statusDiv: document.getElementById('rfid-status'),
        feedbackDiv: document.getElementById('rfid-feedback'),
        consoleDiv: document.getElementById('rfid-console'),
        tagsCountDiv: document.getElementById('rfid-tags-count')
    });
    
    // ==========================================
    // MODO MANUAL: BARCODE (mantener igual)
    // ==========================================
    
    const barcodeForm = document.getElementById('barcodeForm');
    if (barcodeForm) {
        barcodeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const input = document.getElementById('barcode_scan');
            const button = document.getElementById('barcodeButton');
            const barcode = input.value.trim();
            
            if (!barcode) return;
            
            input.disabled = true;
            button.disabled = true;
            PickingUI.showLoading();
            
            try {
                const data = await pickingManager.scanBarcode(barcode);
                PickingUI.hideLoading();
                
                if (data.success) {
                    PickingUI.showSuccess(data.message, 'barcodeResult');
                    PickingUI.updateItemInTable(data.data);
                    await PickingUI.updateProgress(pickingManager);
                    input.value = '';
                    
                    if (data.data.quantity_missing <= 0) {
                        setTimeout(() => PickingUI.removeItemFromTable(data.data.item_id), 1000);
                    }
                    
                    if (data.data.preparation_complete) {
                        PickingUI.showCompletionAlert();
                    }
                    if (data.data?.conditional_actions?.length > 0) {
                        data.data.conditional_actions.forEach(action => {
                            ToastNotification.warning(action.message);
                        });
                        setTimeout(() => window.location.reload(), 1800);
                    }

                } else {
                    PickingUI.showError(data.message, 'barcodeResult');
                }
            } catch (error) {
                PickingUI.hideLoading();
                console.error('Error:', error);
                PickingUI.showError('Error de conexión. Intenta de nuevo.', 'barcodeResult');
            } finally {
                input.disabled = false;
                button.disabled = false;
                input.focus();
            }
        });
    }
    
    // ==========================================
    // EXPONER FUNCIONES GLOBALES PARA BLADE
    // ==========================================
    
    window.connectRFIDReader = () => rfidManager.connect();
    window.disconnectRFIDReader = () => rfidManager.disconnect();
    window.startRFIDReading = () => {
        // Resetear contadores al iniciar
        correctCount = 0;
        incorrectCount = 0;
        incorrectTags = [];
        updateRFIDStats();
        
        rfidManager.start();
    };
    window.stopRFIDReading = () => rfidManager.stop();
    window.clearRFIDConsole = () => rfidManager.clearConsole();
    window.switchMode = (mode) => {
        pickingManager.switchMode(mode, rfidManager);
        
        // Mostrar/ocultar panel de stats según modo
        if (mode === 'rfid') {
            document.getElementById('rfid-stats-panel')?.classList.remove('hidden');
        } else {
            document.getElementById('rfid-stats-panel')?.classList.add('hidden');
            document.getElementById('incorrect-tags-panel')?.classList.add('hidden');
        }
    };
    
    // ==========================================
    // AUTO-UPDATE PROGRESO
    // ==========================================
    
    setInterval(() => PickingUI.updateProgress(pickingManager), 30000);
    
    console.log('✅ Sistema de picking RFID inicializado correctamente');
});