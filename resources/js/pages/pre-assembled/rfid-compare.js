/**
 * ============================================================================
 * ENTRY POINT: Pre-Armado RFID Comparativa
 * ============================================================================
 * 
 * @ubicación resources/js/pages/pre-assembled/rfid-compare.js
 * @author Erick - DRG Services & Solutions
 * 
 * FIX: Los tags llegan al RFIDManager (aparecen en consola) pero
 * el callback onTagDetected no los pasa al CompareManager.
 * 
 * SOLUCIÓN: Interceptar DIRECTAMENTE en window.handleTagDataGlobal
 * para capturar los tags además del flujo normal del módulo RFID.
 */

// ─── IMPORTS ───
import { RFIDManager } from '../../modules/rfid/RFIDManager.js';
import { RFIDCallbacks } from '../../modules/rfid/RFIDCallbacks.js';
import { CompareManager } from '../../modules/compare/CompareManager.js';
import { CompareUI } from '../../modules/compare/CompareUI.js';

// ─── INSTANCIAS ───
let rfidManager = null;
let rfidCallbacks = null;
let compareManager = null;

// ─── REFERENCIAS DOM ───
const DOM = {};

// ==============================================
// INICIALIZACIÓN
// ==============================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ [PRE-ASSEMBLED] rfid-compare.js cargado');

    // 1. Leer configuración del DOM
    const configEl = document.getElementById('rfid-compare-config');
    if (!configEl) {
        console.error('❌ [PRE-ASSEMBLED] No se encontró #rfid-compare-config');
        return;
    }

    const config = {
        packageId: configEl.dataset.packageId,
        csrfToken: configEl.dataset.csrf,
        routes: {
            compare: configEl.dataset.routeCompare,
            add: configEl.dataset.routeAdd,
            search: configEl.dataset.routeSearch,
        },
    };

    // 2. Cachear referencias DOM
    cacheDomRefs();

    // 3. Inicializar CompareManager
    initCompareManager(config);

    // 4. Inicializar RFID
    initRfid();

    // 5. ★ CRÍTICO: Interceptar tags directamente desde Enterprise Browser
    installTagInterceptor();

    // 6. Vincular eventos de botones
    bindEvents();

    // 7. Log inicial
    logToConsole('Sistema de comparativa RFID iniciado.', 'info');
    logToConsole('Presione "Conectar Lector" para comenzar.', 'info');
});

// ==============================================
// CACHE DE REFERENCIAS DOM
// ==============================================
function cacheDomRefs() {
    DOM.connectBtn = document.getElementById('rfid-connect-btn');
    DOM.disconnectBtn = document.getElementById('rfid-disconnect-btn');
    DOM.startBtn = document.getElementById('rfid-start-btn');
    DOM.stopBtn = document.getElementById('rfid-stop-btn');
    DOM.clearBtn = document.getElementById('rfid-clear-btn');

    DOM.statusDiv = document.getElementById('rfid-status');
    DOM.feedbackDiv = document.getElementById('rfid-feedback');
    DOM.consoleDiv = document.getElementById('rfid-console');
    DOM.tagsCountDiv = document.getElementById('rfid-tags-count');

    DOM.compareBtn = document.getElementById('rfid-compare-btn');
    DOM.addAllBtn = document.getElementById('rfid-add-all-btn');

    DOM.comparisonTableBody = document.getElementById('comparison-table-body');
    DOM.extrasTableBody = document.getElementById('extras-table-body');
    DOM.unknownList = document.getElementById('unknown-epcs-list');

    DOM.comparisonSection = document.getElementById('comparison-section');
    DOM.extrasSection = document.getElementById('extras-section');
    DOM.unknownSection = document.getElementById('unknown-section');
    DOM.scannedTagsList = document.getElementById('scanned-tags-list');

    DOM.stats = {
        complete: document.getElementById('stat-complete'),
        partial: document.getElementById('stat-partial'),
        missing: document.getElementById('stat-missing'),
        extra: document.getElementById('stat-extra'),
        percentage: document.getElementById('stat-percentage'),
        progressBar: document.getElementById('stat-progress-bar'),
    };

    DOM.autoAddToggle = document.getElementById('auto-add-toggle');
}

// ==============================================
// INICIALIZAR COMPARE MANAGER
// ==============================================
function initCompareManager(config) {
    compareManager = new CompareManager(config);

    compareManager.setCallbacks({
        onCompareComplete: (data) => {
            renderFullComparison(data);
        },
        onTagProcessed: (epc, result) => {
            if (result.success && result.data?.is_extra) {
                CompareUI.highlightTag(epc, 'extra');
            } else if (result.success) {
                CompareUI.highlightTag(epc, 'success');
            }
        },
        onError: (message, code) => {
            logToConsole(`❌ [${code}] ${message}`, 'error');
        },
        onLog: (message, type) => {
            logToConsole(message, type);
        },
    });
}

// ==============================================
// INICIALIZAR RFID (RFIDManager + Callbacks)
// ==============================================
function initRfid() {
    rfidCallbacks = new RFIDCallbacks();

    rfidManager = new RFIDManager({
        cooldownTime: 2000,
    });

    rfidCallbacks.setManager(rfidManager);

    rfidManager.init({
        connectBtn: DOM.connectBtn,
        disconnectBtn: DOM.disconnectBtn,
        startBtn: DOM.startBtn,
        stopBtn: DOM.stopBtn,
        statusDiv: DOM.statusDiv,
        feedbackDiv: DOM.feedbackDiv,
        consoleDiv: DOM.consoleDiv,
        tagsCountDiv: DOM.tagsCountDiv,
    });

    // Intento normal de callbacks (puede no disparar onTagDetected)
    rfidManager.setCallbacks({
        onTagDetected: handleTagDetected,
        onConnected: handleRfidConnected,
        onDisconnected: handleRfidDisconnected,
        onError: handleRfidError,
    });

    console.log('✅ [PRE-ASSEMBLED] RFID Manager inicializado');
}

// ==============================================
// ★ FIX PRINCIPAL: INTERCEPTOR DIRECTO DE TAGS
// ==============================================
// El RFIDManager logea los tags en la consola (los vemos ahí)
// pero el callback onTagDetected nunca dispara hacia afuera.
//
// Este interceptor ENVUELVE el callback global de Enterprise
// Browser para capturar los tags directamente, sin depender
// del flujo interno del RFIDManager.
//
// CompareManager.addEpc() filtra duplicados internamente,
// así que si el callback original SÍ funciona en algún
// momento, no habrá problema de tags duplicados.
// ==============================================
function installTagInterceptor() {
    console.log('🔧 [INTERCEPTOR] Instalando interceptor de tags...');

    // Cooldown propio
    const tagCooldown = new Map();
    const COOLDOWN_MS = 2000;

    function isInCooldown(epc) {
        if (!tagCooldown.has(epc)) return false;
        return (Date.now() - tagCooldown.get(epc)) < COOLDOWN_MS;
    }

    function addToCooldown(epc) {
        tagCooldown.set(epc, Date.now());
        setTimeout(() => tagCooldown.delete(epc), COOLDOWN_MS);
    }

    /**
     * Extraer EPCs de cualquier formato de Enterprise Browser
     */
    function extractEpcs(eventData) {
        let tags = [];

        if (Array.isArray(eventData)) {
            tags = eventData;
        } else if (eventData && eventData.TagData) {
            tags = Array.isArray(eventData.TagData) ? eventData.TagData : [eventData.TagData];
        } else if (eventData && eventData.tags) {
            tags = Array.isArray(eventData.tags) ? eventData.tags : [eventData.tags];
        } else if (eventData && (eventData.tagID || eventData.TagID || eventData.epc)) {
            tags = [eventData];
        }

        return tags.map(tag => {
            if (typeof tag === 'string') return tag.toUpperCase().trim();
            return (tag.tagID || tag.TagID || tag.epc || tag.EPC || '').toString().toUpperCase().trim();
        }).filter(epc => epc.length >= 8);
    }

    // ─── WRAP handleTagDataGlobal ───
    // Esperamos a que el RFIDManager lo defina, luego lo envolvemos
    function wrapHandler() {
        const originalHandler = window.handleTagDataGlobal;

        window.handleTagDataGlobal = function(eventData) {
            // 1. Dejar que RFIDManager procese normalmente (consola, etc.)
            if (typeof originalHandler === 'function') {
                try {
                    originalHandler(eventData);
                } catch (e) {
                    console.warn('⚠️ Error en handler original:', e);
                }
            }

            // 2. ★ Extraer EPCs e inyectar al CompareManager
            const epcs = extractEpcs(eventData);
            epcs.forEach(epc => {
                if (isInCooldown(epc)) return;
                addToCooldown(epc);
                handleTagDetected({ epc: epc });
            });
        };
    }

    // Si handleTagDataGlobal ya existe, envolver ahora
    if (typeof window.handleTagDataGlobal === 'function') {
        wrapHandler();
        console.log('✅ [INTERCEPTOR] Envuelto handleTagDataGlobal existente');
    } else {
        // Si no existe aún, vigilar hasta que aparezca
        // (puede definirse cuando RFIDManager.start() configura los callbacks)
        let attempts = 0;
        const watcher = setInterval(() => {
            attempts++;
            if (typeof window.handleTagDataGlobal === 'function') {
                clearInterval(watcher);
                wrapHandler();
                console.log(`✅ [INTERCEPTOR] Envuelto handleTagDataGlobal (intento ${attempts})`);
            }
            if (attempts > 100) { // ~10 segundos
                clearInterval(watcher);
                console.warn('⚠️ [INTERCEPTOR] handleTagDataGlobal nunca se definió, creando uno nuevo');
                // Crear handler desde cero
                window.handleTagDataGlobal = function(eventData) {
                    const epcs = extractEpcs(eventData);
                    epcs.forEach(epc => {
                        if (isInCooldown(epc)) return;
                        addToCooldown(epc);
                        handleTagDetected({ epc: epc });
                    });
                };
            }
        }, 100);
    }

    // ─── TAMBIÉN interceptar RFIDCallbacks.handleTagData ───
    if (window.RFIDCallbacks) {
        const origHandle = window.RFIDCallbacks.handleTagData;
        window.RFIDCallbacks.handleTagData = function(tagArray) {
            if (typeof origHandle === 'function') {
                try { origHandle(tagArray); } catch (e) { /* ignore */ }
            }
            const epcs = extractEpcs(tagArray);
            epcs.forEach(epc => {
                if (isInCooldown(epc)) return;
                addToCooldown(epc);
                handleTagDetected({ epc: epc });
            });
        };
        console.log('✅ [INTERCEPTOR] También envuelto RFIDCallbacks.handleTagData');
    }
}

// ==============================================
// VINCULAR EVENTOS
// ==============================================
function bindEvents() {
    DOM.connectBtn?.addEventListener('click', () => rfidManager.connect());
    DOM.disconnectBtn?.addEventListener('click', () => rfidManager.disconnect());
    DOM.startBtn?.addEventListener('click', () => rfidManager.start());
    DOM.stopBtn?.addEventListener('click', () => rfidManager.stop());

    DOM.clearBtn?.addEventListener('click', () => {
        if (compareManager.count === 0) return;
        if (!confirm('¿Limpiar todos los tags escaneados?')) return;

        compareManager.clearEpcs();
        CompareUI.renderScannedTags([], DOM.scannedTagsList);
        updateTagCounter();
        DOM.comparisonSection?.classList.add('hidden');
        logToConsole('🗑️ Tags limpiados.', 'info');
    });

    DOM.compareBtn?.addEventListener('click', async () => {
        DOM.compareBtn.disabled = true;
        DOM.compareBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Comparando...';
        await compareManager.compare();
        DOM.compareBtn.disabled = false;
        DOM.compareBtn.innerHTML = '<i class="fas fa-balance-scale mr-2"></i>Comparar con Checklist';
    });

    DOM.addAllBtn?.addEventListener('click', async () => {
        if (compareManager.count === 0) {
            logToConsole('⚠️ No hay tags para agregar.', 'warning');
            return;
        }
        if (!confirm(`¿Agregar ${compareManager.count} productos escaneados al paquete?`)) return;

        DOM.addAllBtn.disabled = true;
        DOM.addAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';

        const summary = await compareManager.addAll();

        DOM.addAllBtn.disabled = false;
        DOM.addAllBtn.innerHTML = '<i class="fas fa-plus-circle mr-2"></i>Agregar Todos al Paquete';

        if (summary.success > 0) {
            setTimeout(() => window.location.reload(), 1500);
        }
    });

    DOM.autoAddToggle?.addEventListener('change', (e) => {
        compareManager.autoAdd = e.target.checked;
        logToConsole(
            compareManager.autoAdd
                ? '🔄 Auto-agregar ACTIVADO.'
                : '⏸️ Auto-agregar DESACTIVADO.',
            'info'
        );
    });

    // Funciones globales para onclick en HTML dinámico de CompareUI
    window.CompareActions = {
        removeTag: (epc) => {
            compareManager.removeEpc(epc);
            CompareUI.renderScannedTags(compareManager.getEpcs(), DOM.scannedTagsList);
            updateTagCounter();
            logToConsole(`🗑️ Tag removido: ${epc}`, 'info');
        },
        addExtra: async (epc, productName) => {
            if (!confirm(`⚠️ "${productName}" NO está en el checklist.\n\n¿Agregar de todos modos?`)) return;
            const result = await compareManager.addProduct(epc, true);
            if (result.success) {
                await compareManager.compare();
            }
        },
    };

    // Funciones globales para botones Blade
    window.connectRFIDReader = () => rfidManager.connect();
    window.disconnectRFIDReader = () => rfidManager.disconnect();
    window.startRFIDReading = () => rfidManager.start();
    window.stopRFIDReading = () => rfidManager.stop();
}

// ==============================================
// HANDLER PRINCIPAL DE TAGS
// ==============================================
async function handleTagDetected(data) {
    const epc = (data?.epc || data || '').toString().toUpperCase().trim();
    if (!epc || epc.length < 8) return;

    console.log('🏷️ [COMPARE] handleTagDetected:', epc);

    const isNew = compareManager.addEpc(epc);
    if (!isNew) return;

    CompareUI.renderScannedTags(compareManager.getEpcs(), DOM.scannedTagsList);
    updateTagCounter();
    logToConsole(`📦 Tag registrado: ${epc}`, 'success');

    if (compareManager.autoAdd) {
        await compareManager.processAutoAdd(epc);
    }
}

function handleRfidConnected(data) {
    const readerId = data?.readerID || 'RFD90';
    logToConsole(`✅ Lector conectado: ${readerId}`, 'success');
    DOM.statusDiv.innerHTML = `
        <span class="text-green-600 font-semibold text-sm">
            <i class="fas fa-circle text-xs mr-1"></i>Conectado (${readerId})
        </span>`;
}

function handleRfidDisconnected() {
    logToConsole('🔌 Lector desconectado.', 'warning');
    DOM.statusDiv.innerHTML = `
        <span class="text-gray-500 text-sm">
            <i class="fas fa-circle text-xs mr-1"></i>Desconectado
        </span>`;
}

function handleRfidError(error) {
    logToConsole(`❌ Error RFID: ${error?.message || error}`, 'error');
}

// ==============================================
// RENDERIZADO
// ==============================================
function renderFullComparison(data) {
    DOM.comparisonSection?.classList.remove('hidden');
    CompareUI.renderStats(data.stats, DOM.stats);
    CompareUI.renderChecklistTable(data.comparison, DOM.comparisonTableBody);
    CompareUI.renderExtrasTable(data.extra_items, DOM.extrasTableBody, DOM.extrasSection);
    CompareUI.renderUnknownEpcs(data.unknown_epcs, DOM.unknownList, DOM.unknownSection);
}

// ==============================================
// HELPERS
// ==============================================
function updateTagCounter() {
    if (DOM.tagsCountDiv) {
        DOM.tagsCountDiv.textContent = compareManager.count;
    }
}

function logToConsole(message, type = 'info') {
    CompareUI.appendToConsole(message, type, DOM.consoleDiv);
}