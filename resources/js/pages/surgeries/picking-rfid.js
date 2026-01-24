import { RFIDManager } from '../../modules/rfid/RFIDManager.js';
import { RFIDCallbacks } from '../../modules/rfid/RFIDCallbacks.js';
import { PickingManager } from '../../modules/picking/PickingManager.js';
import { PickingUI } from '../../modules/picking/PickingUI.js';

/**
 * Inicialización del sistema de picking con RFID
 * Script específico para la página de surtido de productos
 * @author Erick - DRG Services & Solutions
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Inicializando sistema de picking RFID...');
    
    // ==========================================
    // OBTENER DATOS DE LA PÁGINA
    // ==========================================
    
    const surgeryId = document.querySelector('[data-surgery-id]')?.dataset.surgeryId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    if (!surgeryId || !csrfToken) {
        console.error('❌ Faltan datos necesarios para inicializar el sistema');
        return;
    }
    
    // ==========================================
    // INICIALIZAR MANAGERS
    // ==========================================
    
    const rfidManager = new RFIDManager();
    const rfidCallbacks = new RFIDCallbacks();
    const pickingManager = new PickingManager(surgeryId, csrfToken);
    
    // Vincular callbacks
    rfidCallbacks.setManager(rfidManager);
    
    // ==========================================
    // CONFIGURAR EVENTOS RFID
    // ==========================================
    
    rfidManager.setCallbacks({
        /**
         * Callback cuando se detecta un tag RFID
         */
        onTagDetected: async (epc, tagData) => {
            rfidManager.log(`🔍 Buscando información del tag...`, 'info');
            
            try {
                const data = await pickingManager.searchEPC(epc);
                
                if (data.success) {
                    rfidManager.log(`✓ Unidad encontrada: ${data.data.product_name}`, 'success');
                    pickingManager.pendingRfidEPC = PickingUI.showRfidConfirmModal(data.data);
                } else {
                    rfidManager.log(`⚠️ ${data.message}`, 'warning');
                    PickingUI.showError(data.message, 'rfidResult');
                }
            } catch (error) {
                rfidManager.log(`❌ Error al buscar tag: ${error}`, 'error');
                console.error('Error completo:', error);
            }
        },
        
        /**
         * Callback cuando se conecta el lector
         */
        onConnected: (readerID) => {
            console.log(`✓ RFID conectado: ${readerID}`);
        },
        
        /**
         * Callback cuando se desconecta el lector
         */
        onDisconnected: () => {
            console.log('✗ RFID desconectado');
        },
        
        /**
         * Callback de errores del lector
         */
        onError: (error) => {
            console.error('❌ Error RFID:', error);
        }
    });
    
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
    // MODO MANUAL: BARCODE
    // ==========================================
    
    const barcodeForm = document.getElementById('barcodeForm');
    if (barcodeForm) {
        barcodeForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const input = document.getElementById('barcode_scan');
            const button = document.getElementById('barcodeButton');
            const barcode = input.value.trim();
            
            if (!barcode) return;
            
            // Deshabilitar inputs
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
                    
                    // Si el item se completó, removerlo de la tabla
                    if (data.data.quantity_missing <= 0) {
                        setTimeout(() => PickingUI.removeItemFromTable(data.data.item_id), 1000);
                    }
                    
                    // Si toda la preparación se completó
                    if (data.data.preparation_complete) {
                        PickingUI.showCompletionAlert();
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
    // CONFIRMACIÓN RFID
    // ==========================================
    
    const confirmRfidBtn = document.getElementById('confirmRfidBtn');
    if (confirmRfidBtn) {
        confirmRfidBtn.addEventListener('click', async () => {
            const epc = pickingManager.pendingRfidEPC;
            if (!epc) return;
            
            confirmRfidBtn.disabled = true;
            confirmRfidBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Procesando...';
            
            try {
                const data = await pickingManager.confirmRFID(epc);
                
                if (data.success) {
                    PickingUI.closeRfidModal();
                    PickingUI.showSuccess(data.message, 'rfidResult');
                    rfidManager.log(`✓ Unidad confirmada: ${data.data.product_name}`, 'success');
                    
                    PickingUI.updateItemInTable(data.data);
                    await PickingUI.updateProgress(pickingManager);
                    
                    // Si el item se completó
                    if (data.data.quantity_missing <= 0) {
                        setTimeout(() => PickingUI.removeItemFromTable(data.data.item_id), 1000);
                    }
                    
                    // Si toda la preparación se completó
                    if (data.data.preparation_complete) {
                        PickingUI.showCompletionAlert();
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al confirmar la unidad');
            } finally {
                confirmRfidBtn.disabled = false;
                confirmRfidBtn.innerHTML = '<i class="fas fa-check mr-1"></i> Confirmar y Agregar';
            }
        });
    }
    
    // ==========================================
    // EXPONER FUNCIONES GLOBALES PARA BLADE
    // ==========================================
    
    window.connectRFIDReader = () => rfidManager.connect();
    window.disconnectRFIDReader = () => rfidManager.disconnect();
    window.startRFIDReading = () => rfidManager.start();
    window.stopRFIDReading = () => rfidManager.stop();
    window.clearRFIDConsole = () => rfidManager.clearConsole();
    window.switchMode = (mode) => pickingManager.switchMode(mode, rfidManager);
    window.closeRfidModal = () => {
        PickingUI.closeRfidModal();
        pickingManager.pendingRfidEPC = null;
    };
    
    // ==========================================
    // AUTO-UPDATE PROGRESO
    // ==========================================
    
    setInterval(() => {
        PickingUI.updateProgress(pickingManager);
    }, 30000); // Cada 30 segundos
    
    console.log('✅ Sistema de picking RFID inicializado correctamente');
});