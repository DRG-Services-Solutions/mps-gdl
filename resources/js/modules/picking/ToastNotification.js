/**
 * Sistema de notificaciones Toast
 * @author Erick - DRG Services & Solutions
 */
export class ToastNotification {
    /**
     * Mostrar toast de éxito
     */
    static success(message, duration = 3000) {
        this.show(message, 'success', duration);
    }
    
    /**
     * Mostrar toast de error
     */
    static error(message, duration = 5000) {
        this.show(message, 'error', duration);
    }
    
    /**
     * Mostrar toast de advertencia
     */
    static warning(message, duration = 4000) {
        this.show(message, 'warning', duration);
    }
    
    /**
     * Mostrar toast de información
     */
    static info(message, duration = 3000) {
        this.show(message, 'info', duration);
    }
    
    /**
     * Crear y mostrar toast
     */
    static show(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast-${type} transform transition-all duration-300 ease-in-out opacity-0 translate-x-full`;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        const colors = {
            success: 'bg-green-500 border-green-600',
            error: 'bg-red-500 border-red-600',
            warning: 'bg-yellow-500 border-yellow-600',
            info: 'bg-blue-500 border-blue-600'
        };
        
        toast.innerHTML = `
            <div class="${colors[type]} text-white px-6 py-4 rounded-lg shadow-2xl border-l-4 flex items-center space-x-3">
                <i class="fas ${icons[type]} text-2xl"></i>
                <p class="font-semibold flex-1">${message}</p>
                <button onclick="this.closest('.toast-${type}').remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.appendChild(toast);
        
        // Animar entrada
        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-x-full');
        }, 10);
        
        // Auto-remover
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }
}