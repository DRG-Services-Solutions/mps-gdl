/**
 * Callbacks globales para Enterprise Browser
 * EB requiere callbacks en el scope de window
 * @author Erick - DRG Services & Solutions
 */
export class RFIDCallbacks {
    constructor() {
        this.manager = null;
        this.setupGlobalCallbacks();
    }
    
    /**
     * Configura los callbacks globales que Enterprise Browser llamará
     */
    setupGlobalCallbacks() {
        window.RFIDCallbacks = {
            handleEnumeration: (rfidArray) => {
                if (this.manager) {
                    this.manager.handleEnumeration(rfidArray);
                }
            },
            
            handleStatus: (eventInfo) => {
                if (this.manager) {
                    this.manager.handleStatus(eventInfo);
                }
            },
            
            handleTagData: (tagArray) => {
                if (this.manager) {
                    this.manager.handleTagData(tagArray);
                }
            }
        };
    }
    
    
    /**
     * Vincula el manager RFID a los callbacks
     */
    setManager(manager) {
        this.manager = manager;
                console.log('✅ [RFID] RFIDCallbacks.js inicializado');

    }
}