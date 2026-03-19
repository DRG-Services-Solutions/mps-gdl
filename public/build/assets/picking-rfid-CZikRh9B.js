import{R as b,a as v}from"./RFIDCallbacks-R4uarqB9.js";class w{constructor(e,t){this.surgeryId=e,this.csrfToken=t,this.currentMode="manual",this.pendingRfidEPC=null,console.log("✅ [PICKING] PickingManager.js inicializado",{surgeryId:this.surgeryId,mode:this.currentMode})}async searchEPC(e){const t=`/surgeries/${this.surgeryId}/preparations/search-epc?epc=${encodeURIComponent(e)}`;return await(await fetch(t,{headers:{"X-CSRF-TOKEN":this.csrfToken}})).json()}async confirmRFID(e){const t=`/surgeries/${this.surgeryId}/preparations/confirm-rfid`;return await(await fetch(t,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":this.csrfToken},body:JSON.stringify({epc:e})})).json()}async scanBarcode(e){const t=`/surgeries/${this.surgeryId}/preparations/scan-barcode`;return await(await fetch(t,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":this.csrfToken},body:JSON.stringify({barcode:e})})).json()}async getStatus(){const e=`/surgeries/${this.surgeryId}/preparations/status`;return await(await fetch(e)).json()}async cancelPreparation(e){const t=`/surgeries/${this.surgeryId}/preparations/cancel`;return await(await fetch(t,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":this.csrfToken},body:JSON.stringify({reason:e})})).json()}switchMode(e,t=null){this.currentMode=e;const s=document.getElementById("manualModeBtn"),n=document.getElementById("rfidModeBtn"),o=document.getElementById("manualModeSection"),a=document.getElementById("rfidModeSection");e==="manual"?(s.classList.add("active"),n.classList.remove("active"),o.classList.remove("hidden"),a.classList.add("hidden"),t?.isReading()&&t.stop(),document.getElementById("barcode_scan")?.focus(),console.log("📦 Modo Manual activado")):(n.classList.add("active"),s.classList.remove("active"),a.classList.remove("hidden"),o.classList.add("hidden"),console.log("📡 Modo RFID activado"))}}class i{static showLoading(){document.getElementById("loadingIndicator")?.classList.remove("hidden")}static hideLoading(){document.getElementById("loadingIndicator")?.classList.add("hidden")}static showSuccess(e,t){const s=document.getElementById(t);s&&(s.className="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg",s.innerHTML=`
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                <p class="font-semibold text-green-900">${e}</p>
            </div>
        `,s.classList.remove("hidden"),setTimeout(()=>s.classList.add("hidden"),3e3))}static showError(e,t){const s=document.getElementById(t);s&&(s.className="mt-4 p-4 bg-red-50 border border-red-300 rounded-lg",s.innerHTML=`
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-600 text-2xl mr-3"></i>
                <p class="font-semibold text-red-900">${e}</p>
            </div>
        `,s.classList.remove("hidden"),setTimeout(()=>s.classList.add("hidden"),5e3))}static showRfidConfirmModal(e){const t=document.getElementById("rfidConfirmModal"),s=document.getElementById("rfidModalContent");if(!t||!s)return null;let n=`
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Producto</p>
                        <p class="font-bold text-gray-900">${e.product_name}</p>
                        <p class="text-xs text-gray-600 font-mono">${e.product_code}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-xs uppercase">EPC</p>
                        <p class="font-mono text-xs text-gray-700">${e.epc}</p>
                    </div>
        `;if(e.serial_number&&(n+=`
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Serial</p>
                        <p class="font-semibold">${e.serial_number}</p>
                    </div>
            `),e.batch_number&&(n+=`
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Lote</p>
                        <p class="font-semibold">${e.batch_number}</p>
                    </div>
            `),e.expiration_date){const o=e.days_until_expiration?`(${e.days_until_expiration} días)`:"",a=e.is_expiring_soon?"text-red-600":"text-gray-900";n+=`
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Caducidad</p>
                        <p class="font-semibold ${a}">
                            ${e.expiration_date}
                            ${e.is_expiring_soon?'<i class="fas fa-exclamation-triangle ml-1"></i>':""}
                        </p>
                        <p class="text-xs text-gray-600">${o}</p>
                    </div>
            `}return e.location_code&&(n+=`
                    <div>
                        <p class="text-gray-500 text-xs uppercase">Ubicación</p>
                        <p class="font-semibold text-indigo-600">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            ${e.location_code}
                        </p>
                    </div>
            `),n+="</div></div>",e.is_expiring_soon&&(n+=`
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 flex items-start">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-2"></i>
                    <p class="text-sm text-yellow-800">
                        Esta unidad está próxima a caducar (${e.days_until_expiration} días)
                    </p>
                </div>
            `),s.innerHTML=n,t.classList.remove("hidden"),e.epc}static closeRfidModal(){document.getElementById("rfidConfirmModal")?.classList.add("hidden")}static updateItemInTable(e){const t=document.getElementById(`picked-${e.item_id}`),s=document.getElementById(`missing-${e.item_id}`),n=document.getElementById(`item-row-${e.item_id}`);if(!(!t||!s||!n)&&(t.textContent=e.quantity_picked,s.textContent=e.quantity_missing,e.quantity_missing<=0)){n.classList.add("bg-green-50","opacity-75"),s.classList.remove("bg-red-100","text-red-700"),s.classList.add("bg-gray-100","text-gray-400");const o=document.createElement("i");o.className="fas fa-check-circle text-green-500 ml-2 animate-bounce",s.appendChild(o)}}static removeItemFromTable(e){const t=document.getElementById(`item-row-${e}`);t&&(t.style.transition="all 0.5s ease-out",t.style.opacity="0",t.style.transform="translateX(100%)",setTimeout(()=>{t.remove();const s=document.querySelectorAll('tbody tr[id^="item-row-"]').length,n=document.getElementById("pending-count");if(n&&(n.textContent=s),s===0){const o=document.querySelector("#pendingItemsTable tbody");o&&(o.innerHTML=`
                        <tr id="empty-state">
                            <td colspan="6" class="px-6 py-10 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                                    <p class="text-gray-700 font-semibold text-lg">¡Excelente trabajo!</p>
                                    <p class="text-gray-500 text-sm mt-1">No hay productos pendientes</p>
                                </div>
                            </td>
                        </tr>
                    `)}},500))}static async updateProgress(e){try{const t=await e.getStatus();if(!t.success)return;const s=t.data,n=document.getElementById("progress-bar"),o=document.getElementById("progress-percentage"),a=document.getElementById("required-quantity"),g=document.getElementById("picked-quantity"),u=document.getElementById("missing-quantity"),y=document.getElementById("mandatory-pending");n&&(n.style.width=s.completion_percentage+"%"),o&&(o.textContent=Math.round(s.completion_percentage)+"%"),a&&(a.textContent=s.total_quantity_required),g&&(g.textContent=s.total_quantity_picked),u&&(u.textContent=s.total_quantity_missing),y&&(y.textContent=s.mandatory_pending)}catch(t){console.error("Error al actualizar progreso:",t)}}static showCompletionAlert(){const e=document.createElement("div");e.className="fixed top-20 left-1/2 transform -translate-x-1/2 bg-green-500 text-white px-8 py-4 rounded-lg shadow-2xl z-50 animate-bounce",e.innerHTML=`
            <div class="flex items-center">
                <i class="fas fa-check-circle text-3xl mr-4"></i>
                <div>
                    <p class="font-bold text-xl">¡Preparación Completa!</p>
                    <p class="text-sm">Todos los productos han sido surtidos</p>
                </div>
            </div>
        `,document.body.appendChild(e),setTimeout(()=>e.remove(),5e3)}}console.log("✅ [PICKING] PickingUI.js cargado correctamente");class p{static success(e,t=3e3){this.show(e,"success",t)}static error(e,t=5e3){this.show(e,"error",t)}static warning(e,t=4e3){this.show(e,"warning",t)}static info(e,t=3e3){this.show(e,"info",t)}static show(e,t="info",s=3e3){const n=document.getElementById("toast-container");if(!n)return;const o=document.createElement("div");o.className=`toast-${t} transform transition-all duration-300 ease-in-out opacity-0 translate-x-full`;const a={success:"fa-check-circle",error:"fa-exclamation-circle",warning:"fa-exclamation-triangle",info:"fa-info-circle"},g={success:"bg-green-500 border-green-600",error:"bg-red-500 border-red-600",warning:"bg-yellow-500 border-yellow-600",info:"bg-blue-500 border-blue-600"};o.innerHTML=`
            <div class="${g[t]} text-white px-6 py-4 rounded-lg shadow-2xl border-l-4 flex items-center space-x-3">
                <i class="fas ${a[t]} text-2xl"></i>
                <p class="font-semibold flex-1">${e}</p>
                <button onclick="this.closest('.toast-${t}').remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `,n.appendChild(o),setTimeout(()=>{o.classList.remove("opacity-0","translate-x-full")},10),setTimeout(()=>{o.classList.add("opacity-0","translate-x-full"),setTimeout(()=>o.remove(),300)},s)}}document.addEventListener("DOMContentLoaded",()=>{console.log("🚀 Inicializando sistema de picking RFID...");const f=document.querySelector("[data-surgery-id]")?.dataset.surgeryId,e=document.querySelector('meta[name="csrf-token"]')?.content;if(!f||!e){console.error("❌ Faltan datos necesarios");return}const t=new b,s=new v,n=new w(f,e);s.setManager(t);let o=0,a=0,g=[];t.setCallbacks({onTagDetected:async(r,d)=>{t.log(`🔍 Tag detectado: ${r}`,"info");try{const c=await n.searchEPC(r);if(c.success){t.log(`✅ Tag correcto: ${c.data.product_name}`,"success");const l=await n.confirmRFID(r);l.success&&(o++,u(),p.success(`✓ ${l.data.product_name} agregado`),i.updateItemInTable(l.data),h(l.data.item_id),await i.updateProgress(n),l.data.quantity_missing<=0&&setTimeout(()=>i.removeItemFromTable(l.data.item_id),1500),l.data.preparation_complete&&i.showCompletionAlert())}else{a++,u();const l={epc:r,message:c.message,timestamp:new Date().toLocaleTimeString()};g.push(l),y(l),t.log(`❌ Tag incorrecto: ${r}`,"error"),p.error(c.message),I()}}catch(c){t.log(`❌ Error al procesar tag: ${c}`,"error"),p.error("Error al procesar tag RFID"),console.error("Error completo:",c)}},onConnected:r=>{console.log(`✓ RFID conectado: ${r}`),p.info(`Lector ${r} conectado`)},onDisconnected:()=>{console.log("✗ RFID desconectado"),p.warning("Lector RFID desconectado")},onError:r=>{console.error("Error RFID:",r),p.error("Error en lector RFID")}});function u(){document.getElementById("rfid-correct-count").textContent=o,document.getElementById("rfid-incorrect-count").textContent=a,document.getElementById("rfid-total-scanned").textContent=o+a,document.getElementById("rfid-stats-panel").classList.remove("hidden"),a>0&&(document.getElementById("incorrect-tags-panel").classList.remove("hidden"),document.getElementById("incorrect-tags-count").textContent=a)}function y(r){const d=document.getElementById("incorrect-tags-list"),c=document.createElement("div");c.className="bg-white p-3 rounded border border-red-300 flex items-start space-x-3",c.innerHTML=`
            <i class="fas fa-exclamation-triangle text-red-600 mt-1"></i>
            <div class="flex-1">
                <p class="font-mono text-sm text-gray-900">${r.epc}</p>
                <p class="text-xs text-red-600 mt-1">${r.message}</p>
                <p class="text-xs text-gray-500 mt-1">${r.timestamp}</p>
            </div>
        `,d.insertBefore(c,d.firstChild)}window.clearIncorrectTags=function(){g=[],a=0,document.getElementById("incorrect-tags-list").innerHTML="",document.getElementById("incorrect-tags-panel").classList.add("hidden"),u()};function h(r){const d=document.getElementById(`item-row-${r}`);d&&(d.classList.add("bg-green-100"),setTimeout(()=>{d.classList.remove("bg-green-100")},1e3))}function I(){"vibrate"in navigator&&navigator.vibrate([100,50,100])}t.init({connectBtn:document.getElementById("rfid-connect-btn"),disconnectBtn:document.getElementById("rfid-disconnect-btn"),startBtn:document.getElementById("rfid-start-btn"),stopBtn:document.getElementById("rfid-stop-btn"),statusDiv:document.getElementById("rfid-status"),feedbackDiv:document.getElementById("rfid-feedback"),consoleDiv:document.getElementById("rfid-console"),tagsCountDiv:document.getElementById("rfid-tags-count")});const x=document.getElementById("barcodeForm");x&&x.addEventListener("submit",async r=>{r.preventDefault();const d=document.getElementById("barcode_scan"),c=document.getElementById("barcodeButton"),l=d.value.trim();if(l){d.disabled=!0,c.disabled=!0,i.showLoading();try{const m=await n.scanBarcode(l);i.hideLoading(),m.success?(i.showSuccess(m.message,"barcodeResult"),i.updateItemInTable(m.data),await i.updateProgress(n),d.value="",m.data.quantity_missing<=0&&setTimeout(()=>i.removeItemFromTable(m.data.item_id),1e3),m.data.preparation_complete&&i.showCompletionAlert()):i.showError(m.message,"barcodeResult")}catch(m){i.hideLoading(),console.error("Error:",m),i.showError("Error de conexión. Intenta de nuevo.","barcodeResult")}finally{d.disabled=!1,c.disabled=!1,d.focus()}}}),window.connectRFIDReader=()=>t.connect(),window.disconnectRFIDReader=()=>t.disconnect(),window.startRFIDReading=()=>{o=0,a=0,g=[],u(),t.start()},window.stopRFIDReading=()=>t.stop(),window.clearRFIDConsole=()=>t.clearConsole(),window.switchMode=r=>{n.switchMode(r,t),r==="rfid"?document.getElementById("rfid-stats-panel")?.classList.remove("hidden"):(document.getElementById("rfid-stats-panel")?.classList.add("hidden"),document.getElementById("incorrect-tags-panel")?.classList.add("hidden"))},setInterval(()=>i.updateProgress(n),3e4),console.log("✅ Sistema de picking RFID inicializado correctamente")});
