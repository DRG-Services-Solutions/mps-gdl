/**
 * CompareUI - Renderizado de la interfaz de comparativa
 * 
 * Todos los métodos son static para evitar instanciación.
 * Se encarga de renderizar tablas, stats y listas en el DOM.
 * 
 * @ubicación resources/js/modules/compare/CompareUI.js
 * @author Erick - DRG Services & Solutions
 */

export class CompareUI {

    // ==========================================
    // STATS (cards de estadísticas)
    // ==========================================

    /**
     * Renderizar las cards de estadísticas
     * @param {Object} stats - Objeto stats del backend
     * @param {Object} domRefs - Referencias DOM { complete, partial, missing, extra, percentage, progressBar }
     */
    static renderStats(stats, domRefs) {
        if (domRefs.complete) domRefs.complete.textContent = stats.complete;
        if (domRefs.partial) domRefs.partial.textContent = stats.partial;
        if (domRefs.missing) domRefs.missing.textContent = stats.missing;
        if (domRefs.extra) domRefs.extra.textContent = stats.extra;
        if (domRefs.percentage) domRefs.percentage.textContent = stats.completeness_percentage + '%';

        if (domRefs.progressBar) {
            const pct = stats.completeness_percentage;
            domRefs.progressBar.style.width = pct + '%';

            // Color según porcentaje
            let colorClass = 'bg-red-500';
            if (pct >= 80) colorClass = 'bg-green-500';
            else if (pct >= 50) colorClass = 'bg-yellow-500';

            domRefs.progressBar.className = `h-full rounded-full transition-all duration-500 ${colorClass}`;
        }
    }

    // ==========================================
    // TABLA: Items del Checklist
    // ==========================================

    /**
     * Renderizar tabla de comparativa con el checklist
     * @param {Array} comparison - Array de items comparados
     * @param {HTMLElement} tbody - Elemento tbody de la tabla
     */
    static renderChecklistTable(comparison, tbody) {
        if (!tbody) return;

        tbody.innerHTML = comparison.map(item => {
            const badge = CompareUI.getStatusBadge(item.status);
            const epcsHtml = CompareUI.formatEpcList(item.scanned_epcs);
            const rowBg = item.status === 'missing' ? 'bg-red-50' : '';

            const missingCell = item.missing_qty > 0
                ? `<span class="text-red-600 font-bold">-${item.missing_qty}</span>`
                : '<span class="text-green-600"><i class="fas fa-check"></i></span>';

            return `
                <tr class="hover:bg-gray-50 ${rowBg}">
                    <td class="px-4 py-3">
                        <div class="font-medium text-sm text-gray-900">${CompareUI.escape(item.product_name)}</div>
                        <div class="text-xs text-gray-500">${CompareUI.escape(item.product_code)}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-semibold">${item.required_qty}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-blue-600 font-medium">${item.in_package_qty}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-indigo-600 font-medium">${item.scanned_qty}</span>
                    </td>
                    <td class="px-4 py-3 text-center">${missingCell}</td>
                    <td class="px-4 py-3 text-center">${badge}</td>
                    <td class="px-4 py-3 text-xs">${epcsHtml}</td>
                </tr>
            `;
        }).join('');
    }

    // ==========================================
    // TABLA: Items Extra (fuera del checklist)
    // ==========================================

    /**
     * Renderizar tabla de productos extra
     * @param {Array} extraItems
     * @param {HTMLElement} tbody
     * @param {HTMLElement} section - Contenedor a mostrar/ocultar
     */
    static renderExtrasTable(extraItems, tbody, section) {
        if (!tbody || !section) return;

        if (extraItems.length === 0) {
            section.classList.add('hidden');
            return;
        }

        section.classList.remove('hidden');

        tbody.innerHTML = extraItems.map(item => {
            const safeName = CompareUI.escape(item.product_name);
            const epc = item.scanned_epcs?.[0] || '';

            const actionBtn = item.already_in_package
                ? '<span class="text-green-600 text-xs"><i class="fas fa-check mr-1"></i>Ya en paquete</span>'
                : `<button onclick="window.CompareActions.addExtra('${epc}', '${safeName}')"
                          class="px-3 py-1 text-xs bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg transition-colors shadow-sm">
                      <i class="fas fa-plus mr-1"></i>Agregar
                  </button>`;

            return `
                <tr class="hover:bg-yellow-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                            <div>
                                <div class="font-medium text-sm text-gray-900">${safeName}</div>
                                <div class="text-xs text-gray-500">${CompareUI.escape(item.product_code)}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-semibold text-yellow-700">${item.scanned_qty}</span>
                    </td>
                    <td class="px-4 py-3 text-center">${actionBtn}</td>
                </tr>
            `;
        }).join('');
    }

    // ==========================================
    // LISTA: EPCs Desconocidos
    // ==========================================

    /**
     * Renderizar lista de EPCs no registrados
     */
    static renderUnknownEpcs(unknownEpcs, listEl, section) {
        if (!listEl || !section) return;

        if (unknownEpcs.length === 0) {
            section.classList.add('hidden');
            return;
        }

        section.classList.remove('hidden');

        listEl.innerHTML = unknownEpcs.map(epc => `
            <div class="flex items-center justify-between py-1 px-2 bg-red-50 rounded mb-1">
                <code class="text-xs text-red-700">${CompareUI.escape(epc)}</code>
                <span class="text-xs text-red-500">
                    <i class="fas fa-question-circle mr-1"></i>No registrado
                </span>
            </div>
        `).join('');
    }

    // ==========================================
    // LISTA: Tags Escaneados
    // ==========================================

    /**
     * Renderizar lista de tags escaneados
     */
    static renderScannedTags(epcs, container) {
        if (!container) return;

        if (epcs.length === 0) {
            container.innerHTML = `
                <div class="text-center text-gray-400 py-4">
                    <i class="fas fa-satellite-dish text-3xl mb-2"></i>
                    <p class="text-sm">Esperando tags RFID...</p>
                </div>`;
            return;
        }

        container.innerHTML = epcs.map((epc, i) => `
            <div class="flex items-center justify-between py-1.5 px-3 bg-white rounded border border-gray-200 mb-1"
                 id="tag-${epc}">
                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-gray-400">#${i + 1}</span>
                    <code class="text-xs font-mono text-gray-700">${CompareUI.escape(epc)}</code>
                </div>
                <button onclick="window.CompareActions.removeTag('${epc}')"
                        class="text-red-400 hover:text-red-600 text-xs">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }

    /**
     * Resaltar un tag con un color específico
     */
    static highlightTag(epc, type) {
        const el = document.getElementById(`tag-${epc}`);
        if (!el) return;

        const colors = {
            extra: 'border-yellow-400 bg-yellow-50',
            error: 'border-red-400 bg-red-50',
            success: 'border-green-400 bg-green-50',
        };

        el.className = el.className
            .replace('border-gray-200', '')
            .replace('bg-white', '') +
            ' ' + (colors[type] || '');
    }

    // ==========================================
    // CONSOLA DE EVENTOS
    // ==========================================

    /**
     * Agregar entrada a la consola RFID
     */
    static appendToConsole(message, type, consoleEl) {
        if (!consoleEl) return;

        const now = new Date();
        const time = now.toLocaleTimeString('es-MX', { hour12: false });

        const entry = document.createElement('div');
        entry.textContent = `[${time}] ${message}`;
        entry.style.fontSize = '12px';
        entry.style.fontFamily = 'monospace';

        const colors = {
            error: '#ff7b7b',
            success: '#7bff7b',
            warning: '#ffff7b',
            info: '#7bc0ff',
        };

        entry.style.color = colors[type] || '#f0f0f0';

        consoleEl.appendChild(entry);
        consoleEl.scrollTop = consoleEl.scrollHeight;
    }

    // ==========================================
    // UTILIDADES
    // ==========================================

    static getStatusBadge(status) {
        const badges = {
            complete: `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <i class="fas fa-check-circle mr-1"></i>Completo</span>`,
            partial: `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                <i class="fas fa-exclamation-circle mr-1"></i>Parcial</span>`,
            missing: `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                <i class="fas fa-times-circle mr-1"></i>Faltante</span>`,
        };
        return badges[status] || '';
    }

    static formatEpcList(epcs) {
        if (!epcs || epcs.length === 0) {
            return '<span class="text-gray-400 text-xs">Sin escanear</span>';
        }

        return epcs.map(e =>
            `<code class="text-xs bg-gray-100 px-1 rounded">${CompareUI.escape(e.substring(0, 16))}...</code>`
        ).join(' ');
    }

    /**
     * Escape HTML para prevenir XSS
     */
    static escape(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
}