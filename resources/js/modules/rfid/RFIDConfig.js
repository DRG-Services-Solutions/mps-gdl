/**
 * Configuración del lector RFID RFD90
 * @author Erick - DRG Services & Solutions
 */
export const RFID_CONFIG = {
    // Transportes disponibles (orden de intento)
    TRANSPORTS: ['bluetooth', 'serial'],
    
    // Control de duplicados
    COOLDOWN_TIME: 2000, // 2 segundos entre lecturas del mismo tag
    
    // Configuración de triggers (gatillos físicos)
    TRIGGERS: {
        START: 'triggerPress',      // Presionar gatillo inicia lectura
        STOP: 'triggerRelease'       // Soltar gatillo detiene lectura
    },
    
    // Feedback
    BEEP_ON_READ: true,              // Beep al leer tag
    REPORT_UNIQUE_TAGS: true,        // Solo reportar tags únicos
    
    // Colores de consola de debugging
    CONSOLE_COLORS: {
        error: '#ff7b7b',
        success: '#7bff7b',
        warning: '#ffff7b',
        info: '#7bc0ff'
    },
    
    // Códigos de error documentados del API RFID
    ERROR_CODES: {
        2000: 'Parámetro inválido o faltante',
        2001: 'Plugin ocupado',
        2002: 'Fallo al crear thread',
        2003: 'No conectado',
        2004: 'InvalidUsageException',
        2005: 'OperationFailureException',
        1000: 'Status event (trigger, disconnection, etc.)'
    }
};

console.log('✅ [RFID] RFIDConfig.js cargado correctamente');
