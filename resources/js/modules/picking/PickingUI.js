/**
 * Utilidades de UI para el sistema de picking
 * Maneja actualizaciones visuales y feedback al usuario
 * @author Erick - DRG Services & Solutions
 */
export class PickingUI {
    // ==========================================
    // INDICADORES DE CARGA
    // ==========================================
    
    static showLoading() {
        document.getElementById('loadingIndicator')?.classList.remove('hidden');
    }
    
    static hideLoading() {
        document.getElementById('loadingIndicator')?.classList.add('hidden');
    }
    
    // ==========================================
    // MENSAJES DE FEEDBACK
    // ==========================================
    
    /**
     * Mostrar mensaje de éxito
     * @param {string} message - Mensaje a mostrar
     * @param {string} targetId - ID del elemento contenedor
     */
    static showSuccess(message, targetId) {
        const div = document.getElementById(targetId);
        if (!div) return;
        
        div.className = 'mt-4 p-4 bg-green-50 border border-green-200 rounded-lg';
        div.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                <p class="font-semibold text-green-900">${message}</p>
            </div>
        `;
        div.classList.remove('hidden');
        setTimeout(() => div.classList.add('hidden'), 3000);
    }
    
    /**
     * Mostrar mensaje de error
     * @param {string} message - Mensaje a mostrar
     * @param {string} targetId - ID del elemento contenedor
     */
    static showError(message, targetId) {
        const div = document.getElementById(targetId);
        if (!div) return;
        
        div.className = 'mt-4 p-4 bg-red-50 border border-red-300 rounded-lg';
        div.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-3"></i>
                <p class="font-semibold text-red-900">${message}</p>
            </div>
        `;
        div.classList.remove('hidden');
        setTimeout(() => div.classList.add('hidden'), 5000);
    }
    
    // ==========================================
    // MODALES
    // ==========================================
    
    /**
     * Mostrar modal de confirmación RFID con info del producto
     * @param {Object} unitData - Datos de la unidad detectada
     * @returns {string} - EPC del tag
     */
    static showRfidConfirmModal(unitData) {
        const modal = document.getElementById('rfidConfirmModal');
        const content = document.getElementById('rfidModalContent');
        if (!modal || !content) return null;
        
        let html = `
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Producto</p>
                        <p class="font-bold text-gray-900">${unitData.product_name}</p>
                        <p class="text-xs text-gray-600 font-mono">${unitData.product_code}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">EPC</p>
                        <p class="font-mono text-xs text-gray-700">${unitData.epc}</p>
                    </div>
        `;
        
        // Serial (opcional)
        if (unitData.serial_number) {
            html += `
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Serial</p>
                        <p class="font-semibold">${unitData.serial_number}</p>
                    </div>
            `;
        }
        
        // Lote (opcional)
        if (unitData.batch_number) {
            html += `
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Lote</p>
                        <p class="font-semibold">${unitData.batch_number}</p>
                    </div>
            `;
        }
        
        // Fecha de caducidad (opcional)
        if (unitData.expiration_date) {
            const daysText = unitData.days_until_expiration ? `(${unitData.days_until_expiration} días)` : '';
            const expiryClass = unitData.is_expiring_soon ? 'text-red-600' : 'text-gray-900';
            
            html += `
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Caducidad</p>
                        <p class="font-semibold ${expiryClass}">
                            ${unitData.expiration_date}
                            ${unitData.is_expiring_soon ? '<i class="fas fa-exclamation-triangle ml-1"></i>' : ''}
                        </p>
                        <p class="text-xs text-gray-600">${daysText}</p>
                    </div>
            `;
        }
        
        // Ubicación (opcional)
        if (unitData.location_code) {
            html += `
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Ubicación</p>
                        <p class="font-semibold text-indigo-600">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            ${unitData.location_code}
                        </p>
                    </div>
            `;
        }
        
        html += `</div></div>`;
        
        // Alerta si está próximo a caducar
        if (unitData.is_expiring_soon) {
            html += `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                    <p class="text-sm text-yellow-800">
                        Esta unidad está próxima a caducar (${unitData.days_until_expiration} días)
                    </p>
                </div>
            `;
        }
        
        content.innerHTML = html;
        modal.classList.remove('hidden');
        
        return unitData.epc;
    }
    
    /**
     * Cerrar modal de confirmación RFID
     */
    static closeRfidModal() {
        document.getElementById('rfidConfirmModal')?.classList.add('hidden');
    }
    
    // ==========================================
    // TABLA DE PRODUCTOS
    // ==========================================
    
    /**
     * Actualizar cantidades de un item en la tabla
     * @param {Object} itemData - Datos del item actualizado
     */
    static updateItemInTable(itemData) {
        const pickedSpan = document.getElementById(`picked-${itemData.item_id}`);
        const missingSpan = document.getElementById(`missing-${itemData.item_id}`);
        const row = document.getElementById(`item-row-${itemData.item_id}`);
        
        if (!pickedSpan || !missingSpan || !row) return;
        
        pickedSpan.textContent = itemData.quantity_picked;
        missingSpan.textContent = itemData.quantity_missing;
        
        // Si ya está completo, marcar visualmente
        if (itemData.quantity_missing <= 0) {
            row.classList.add('bg-green-50', 'opacity-75');
            missingSpan.classList.remove('bg-red-100', 'text-red-700');
            missingSpan.classList.add('bg-gray-100', 'text-gray-400');
            
            const checkIcon = document.createElement('i');
            checkIcon.className = 'fas fa-check-circle text-green-500 ml-2 animate-bounce';
            missingSpan.appendChild(checkIcon);
        }
    }
    
    /**
     * Remover item de la tabla con animación
     * @param {number} itemId - ID del item a remover
     */
    static removeItemFromTable(itemId) {
        const row = document.getElementById(`item-row-${itemId}`);
        if (!row) return;
        
        // Animación de salida
        row.style.transition = 'all 0.5s ease-out';
        row.style.opacity = '0';
        row.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            row.remove();
            
            // Actualizar contador
            const pendingCount = document.querySelectorAll('tbody tr[id^="item-row-"]').length;
            const countSpan = document.getElementById('pending-count');
            if (countSpan) countSpan.textContent = pendingCount;
            
            // Si no quedan items, mostrar estado vacío
            if (pendingCount === 0) {
                const tbody = document.querySelector('#pendingItemsTable tbody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr id="empty-state">
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                                    <p class="text-gray-700 font-semibold text-lg">¡Excelente trabajo!</p>
                                    <p class="text-gray-500 text-sm mt-1">No hay productos pendientes</p>
                                </div>
                            </td>
                        </tr>
                    `;
                }
            }
        }, 500);
    }
    
    // ==========================================
    // PROGRESO
    // ==========================================
    
    /**
     * Actualizar barra de progreso y contadores
     * @param {PickingManager} pickingManager - Instancia del manager
     */
    static async updateProgress(pickingManager) {
        try {
            const result = await pickingManager.getStatus();
            if (!result.success) return;
            
            const summary = result.data;
            
            // Actualizar elementos en el DOM
            const progressBar = document.getElementById('progress-bar');
            const progressPercentage = document.getElementById('progress-percentage');
            const requiredQty = document.getElementById('required-quantity');
            const pickedQty = document.getElementById('picked-quantity');
            const missingQty = document.getElementById('missing-quantity');
            const mandatoryPending = document.getElementById('mandatory-pending');
            
            if (progressBar) progressBar.style.width = summary.completion_percentage + '%';
            if (progressPercentage) progressPercentage.textContent = Math.round(summary.completion_percentage) + '%';
            if (requiredQty) requiredQty.textContent = summary.total_quantity_required;
            if (pickedQty) pickedQty.textContent = summary.total_quantity_picked;
            if (missingQty) missingQty.textContent = summary.total_quantity_missing;
            if (mandatoryPending) mandatoryPending.textContent = summary.mandatory_pending;
        } catch (error) {
            console.error('Error al actualizar progreso:', error);
        }
    }
    
    /**
     * Mostrar alerta de preparación completa
     */
    static showCompletionAlert() {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-8 py-4 rounded-lg shadow-2xl z-50 animate-bounce';
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-check-circle text-3xl mr-4"></i>
                <div>
                    <p class="font-bold text-xl">¡Preparación Completa!</p>
                    <p class="text-sm">Todos los productos han sido surtidos</p>
                </div>
            </div>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => alertDiv.remove(), 5000);
    }
}
console.log('✅ [PICKING] PickingUI.js cargado correctamente');
