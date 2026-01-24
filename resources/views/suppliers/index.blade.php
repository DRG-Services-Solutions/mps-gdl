<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Demo RFD90 RFID</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .content {
            padding: 30px;
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            margin: 5px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1em;
            transition: border 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-weight: 500;
        }

        .status-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .log {
            background: #1e1e1e;
            color: #00ff00;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }

        .log-entry {
            margin: 5px 0;
            padding: 5px;
            border-bottom: 1px solid #333;
        }

        .tag-list {
            display: grid;
            gap: 10px;
            margin-top: 15px;
        }

        .tag-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 2px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .tag-id {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #667eea;
        }

        .tag-info {
            font-size: 0.9em;
            color: #666;
        }

        .input-group {
            margin: 15px 0;
        }

        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .scanning {
            animation: pulse 1.5s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📡 RFD90 RFID Demo</h1>
            <p>Aplicación de demostración para lector RFID móvil Zebra RFD90</p>
        </div>

        <div class="content">
            <!-- Sección: Log de Eventos - MOVIDO ARRIBA -->
            <div class="section">
                <h2>📝 Log de Eventos</h2>
                <div class="log" id="eventLog">
                    <div class="log-entry">Sistema iniciado. Esperando conexión...</div>
                </div>
            </div>

            <!-- Sección: Conexión -->
            <div class="section">
                <h2>🔌 Conexión al Lector</h2>
                <div id="connectionStatus" class="status status-info">
                    Estado: Desconectado
                </div>
                <div class="input-group">
                    <label>Tipo de Transporte:</label>
                    <select id="transportType">
                        <option value="Bluetooth">Bluetooth</option>
                        <option value="serial">Serial</option>
                    </select>
                </div>
                <div class="button-group">
                    <button class="btn btn-success" onclick="enumerateReaders()">Enumerar Lectores</button>
                    <button class="btn btn-success" onclick="connectReader()" id="btnConnect">Conectar</button>
                    <button class="btn btn-danger" onclick="disconnectReader()" id="btnDisconnect" disabled>Desconectar</button>
                </div>
            </div>

            <!-- Sección: Inventario -->
            <div class="section">
                <h2>📋 Inventario de Tags</h2>
                <div class="input-group">
                    <label>Reportar tags únicos:</label>
                    <select id="reportUniqueTags">
                        <option value="true">Sí</option>
                        <option value="false">No</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Beep al leer:</label>
                    <select id="beepOnRead">
                        <option value="true">Sí</option>
                        <option value="false">No</option>
                    </select>
                </div>
                <div class="button-group">
                    <button class="btn" onclick="startInventory()" id="btnStartInventory" disabled>Iniciar Inventario</button>
                    <button class="btn btn-danger" onclick="stopInventory()" id="btnStopInventory" disabled>Detener</button>
                    <button class="btn btn-warning" onclick="clearTags()">Limpiar Historial</button>
                </div>
                <div id="inventoryStats" class="status status-info">
                    Tags únicos: 0 | Total lecturas: 0
                </div>
                <div class="tag-list" id="tagList"></div>
            </div>

            <!-- Sección: Localización de Tag -->
            <div class="section">
                <h2>🎯 Localizar Tag por Proximidad</h2>
                <p style="color: #666; margin-bottom: 15px;">
                    Encuentra un tag específico usando señales de proximidad. La frecuencia del beep aumenta cuando te acercas al tag (efecto Geiger).
                </p>
                
                <!-- Nota de Debugging -->
                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <strong>💡 Modo Debug:</strong> Si el indicador visual no funciona, revisa el <strong>Log de Eventos</strong> arriba. 
                    Los mensajes "DEBUG - Campos del tag" mostrarán todos los campos disponibles. 
                    Si no ves "relativeDistance", tu lector puede no soportar esta función o usar otro nombre.
                </div>
                
                <div class="input-group">
                    <label>Tag ID a localizar (hex):</label>
                    <input type="text" id="locateTagId" placeholder="Ej: E2003411B802011721500194">
                </div>
                <div class="input-group">
                    <label>Antena a usar (no puede ser "Todas"):</label>
                    <select id="locateAntenna">
                        <option value="1">Antena 1</option>
                        <option value="2">Antena 2</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Beep según proximidad:</label>
                    <select id="locateBeep">
                        <option value="true" selected>Sí (Efecto Geiger)</option>
                        <option value="false">No</option>
                    </select>
                </div>
                <div class="button-group">
                    <button class="btn" onclick="startLocate()" id="btnStartLocate" disabled>Iniciar Localización</button>
                    <button class="btn btn-danger" onclick="stopLocate()" id="btnStopLocate" disabled>Detener</button>
                </div>
                
                <!-- Indicador de Proximidad -->
                <div id="proximityIndicator" style="display: none; margin-top: 20px;">
                    <h3 style="margin-bottom: 10px;">Proximidad del Tag</h3>
                    <div style="background: #e0e0e0; height: 40px; border-radius: 20px; overflow: hidden; position: relative;">
                        <div id="proximityBar" style="height: 100%; width: 0%; background: linear-gradient(90deg, #eb3349 0%, #f5576c 50%, #38ef7d 100%); transition: width 0.3s ease;"></div>
                        <div id="proximityText" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-weight: bold; color: #333;">0%</div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 5px; font-size: 0.9em; color: #666;">
                        <span>Lejos</span>
                        <span id="proximityStatus">Buscando...</span>
                        <span>Cerca</span>
                    </div>
                </div>
            </div>

            <!-- Sección: Lectura de Tag -->
            <div class="section">
                <h2>📖 Leer Tag Específico</h2>
                <div class="input-group">
                    <label>Tag ID (hex):</label>
                    <input type="text" id="readTagId" placeholder="Ej: E2003411B802011721500194">
                </div>
                <div class="input-group">
                    <label>Banco de Memoria:</label>
                    <select id="readMemBank">
                        <option value="Reserved">Reserved</option>
                        <option value="EPC" selected>EPC</option>
                        <option value="TID">TID</option>
                        <option value="User">User</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Offset (words):</label>
                    <input type="number" id="readOffset" value="0" min="0">
                </div>
                <div class="input-group">
                    <label>Tamaño a leer (bytes, 0=todo):</label>
                    <input type="number" id="readSize" value="0" min="0">
                </div>
                <button class="btn" onclick="readTag()" id="btnReadTag" disabled>Leer Tag</button>
                <div id="readResult" class="log"></div>
            </div>

            <!-- Sección: Escritura en Tag -->
            <div class="section">
                <h2>✍️ Escribir en Tag</h2>
                <div class="input-group">
                    <label>Tag ID (hex):</label>
                    <input type="text" id="writeTagId" placeholder="Ej: E2003411B802011721500194">
                </div>
                <div class="input-group">
                    <label>Banco de Memoria:</label>
                    <select id="writeMemBank">
                        <option value="Reserved">Reserved</option>
                        <option value="EPC" selected>EPC</option>
                        <option value="TID">TID</option>
                        <option value="User">User</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Offset (words):</label>
                    <input type="number" id="writeOffset" value="0" min="0">
                </div>
                <div class="input-group">
                    <label>Datos a escribir (hex):</label>
                    <input type="text" id="writeData" placeholder="Ej: 1234567890ABCDEF">
                </div>
                <div class="input-group">
                    <label>Password de acceso (hex):</label>
                    <input type="text" id="writePassword" value="00000000">
                </div>
                <button class="btn btn-warning" onclick="writeTag()" id="btnWriteTag" disabled>Escribir en Tag</button>
                <div id="writeResult" class="log"></div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        var isConnected = false;
        var isScanning = false;
        var isLocating = false;
        var tagsFound = new Map();
        var tagHistory = []; // Historial completo de lecturas
        var totalReads = 0;

        // ====== CALLBACKS GLOBALES DE ZEBRA ======
        // Estos callbacks DEBEN ser funciones globales y registrarse como strings
        
        // Callback para enumeración de lectores
        function enumRFIDCallback(rfidArray) {
            logEvent('Respuesta de enumeración recibida', 'info');
            
            try {
                logEvent('Lectores encontrados: ' + rfidArray.length, 'success');
                
                for (var i = 0; i < rfidArray.length; i++) {
                    logEvent('ID: ' + rfidArray[i][0] + ' | Nombre: ' + rfidArray[i][1] + ' | Dir: ' + rfidArray[i][2], 'info');
                }
            } catch (error) {
                logEvent('Error al procesar enumeración: ' + error.message, 'error');
            }
        }
        
        // Callback para tags encontrados
        function tagEventCallback(tagData) {
            try {
                if (tagData.TagData && Array.isArray(tagData.TagData)) {
                    for (var i = 0; i < tagData.TagData.length; i++) {
                        var tag = tagData.TagData[i];
                        var tagId = tag.tagID;
                        totalReads++;
                        
                        // DEBUG: Mostrar todos los campos del tag en modo localización
                        if (isLocating) {
                            logEvent('DEBUG - Campos del tag: ' + JSON.stringify(tag), 'info');
                        }
                        
                        // Si estamos en modo localización
                        if (isLocating) {
                            // Intentar obtener proximidad del tag
                            var proximity = tag.relativeDistance || tag.RelativeDistance || 
                                          tag.proximity || tag.distance || tag.percentProximity;
                            
                            if (proximity !== undefined && proximity !== null) {
                                // Usar valor directo de proximidad
                                updateProximity(proximity, tagId);
                                logEvent('Tag encontrado con proximidad: ' + proximity + '%', 'success');
                            } else {
                                // Usar RSSI como alternativa
                                if (tag.RSSI) {
                                    var calculatedProximity = calculateProximityFromRSSI(tag.RSSI);
                                    if (calculatedProximity !== null) {
                                        updateProximity(calculatedProximity, tagId);
                                        logEvent('Proximidad calculada desde RSSI (' + tag.RSSI + '): ' + calculatedProximity + '%', 'warning');
                                    }
                                } else {
                                    logEvent('ADVERTENCIA: No hay relativeDistance ni RSSI disponible', 'warning');
                                }
                            }
                        }
                        
                        // Actualizar o agregar tag al mapa
                        var existingTag = tagsFound.get(tagId);
                        if (!existingTag && !isLocating) {
                            logEvent('Nuevo tag: ' + tagId, 'success');
                        }
                        
                        var tagInfo = {
                            id: tagId,
                            rssi: tag.RSSI || 'N/A',
                            antenna: tag.antennaID || 'N/A',
                            count: existingTag ? existingTag.count + 1 : 1,
                            pc: tag.PC || 'N/A',
                            timestamp: new Date().toLocaleTimeString(),
                            relativeDistance: tag.relativeDistance || tag.RelativeDistance
                        };
                        
                        tagsFound.set(tagId, tagInfo);
                        
                        // Agregar al historial
                        tagHistory.push({
                            id: tagInfo.id,
                            rssi: tagInfo.rssi,
                            antenna: tagInfo.antenna,
                            count: tagInfo.count,
                            timestamp: tagInfo.timestamp,
                            fullTimestamp: new Date()
                        });
                        
                        updateTagList();
                        updateStats();
                    }
                }
            } catch (error) {
                logEvent('Error al procesar tags: ' + error.message, 'error');
            }
        }
        
        // Callback para eventos de estado/error
        function statusEventCallback(eventInfo) {
            try {
                var msg = 'Método: ' + eventInfo.method;
                
                if (eventInfo.errorCode) {
                    msg += ' | Error: ' + eventInfo.errorCode;
                    if (eventInfo.vendorMessage) {
                        msg += ' | ' + eventInfo.vendorMessage;
                    }
                    logEvent(msg, 'error');
                } else {
                    logEvent(msg, 'info');
                }
            } catch (error) {
                logEvent('Error en statusEvent: ' + error.message, 'error');
            }
        }

        // Funciones de utilidad para logging
        function logEvent(message, type = 'info') {
            const log = document.getElementById('eventLog');
            const timestamp = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            
            const color = {
                'info': '#00ff00',
                'success': '#00ff00',
                'error': '#ff0000',
                'warning': '#ffff00'
            }[type] || '#00ff00';
            
            entry.style.color = color;
            entry.textContent = `[${timestamp}] ${message}`;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
        }

        function updateConnectionStatus(connected) {
            isConnected = connected;
            const status = document.getElementById('connectionStatus');
            const btnConnect = document.getElementById('btnConnect');
            const btnDisconnect = document.getElementById('btnDisconnect');
            const btnStartInventory = document.getElementById('btnStartInventory');
            const btnReadTag = document.getElementById('btnReadTag');
            const btnWriteTag = document.getElementById('btnWriteTag');
            const btnStartLocate = document.getElementById('btnStartLocate');

            if (connected) {
                status.textContent = 'Estado: Conectado ✓';
                status.className = 'status status-success';
                btnConnect.disabled = true;
                btnDisconnect.disabled = false;
                btnStartInventory.disabled = false;
                btnReadTag.disabled = false;
                btnWriteTag.disabled = false;
                btnStartLocate.disabled = false;
            } else {
                status.textContent = 'Estado: Desconectado';
                status.className = 'status status-info';
                btnConnect.disabled = false;
                btnDisconnect.disabled = true;
                btnStartInventory.disabled = true;
                btnReadTag.disabled = true;
                btnWriteTag.disabled = true;
                btnStartLocate.disabled = true;
            }
        }

        // ====== FUNCIONES DE CONEXIÓN ======
        
        function enumerateReaders() {
            logEvent('Enumerando lectores RFID...', 'info');
            
            try {
                // Configurar el tipo de transporte
                var transport = document.getElementById('transportType').value;
                rfid.transport = transport;
                
                // IMPORTANTE: Registrar callback como STRING
                rfid.enumRFIDEvent = "enumRFIDCallback(%s)";
                
                // Enumerar lectores
                rfid.enumerate();
                
                logEvent('Comando enumerate enviado', 'success');
            } catch (error) {
                logEvent('Error al enumerar: ' + error.message, 'error');
            }
        }

        function handleEnumRFIDEvent(jsonObject) {
            // Esta función ya no se usa, se usa enumRFIDCallback
        }

        function connectReader() {
            logEvent('Intentando conectar al lector...', 'info');
            
            try {
                // Configurar eventos ANTES de conectar como STRINGS
                rfid.statusEvent = "statusEventCallback(%json)";
                
                // Conectar al lector
                rfid.connect();
                
                // Configurar tagEvent DESPUÉS de conectar
                rfid.tagEvent = "tagEventCallback(%json)";
                
                // Simular conexión exitosa (en dispositivo real esperar callback)
                setTimeout(function() {
                    updateConnectionStatus(true);
                    logEvent('Conectado exitosamente al lector RFID', 'success');
                }, 500);
                
            } catch (error) {
                logEvent('Error al conectar: ' + error.message, 'error');
            }
        }

        function disconnectReader() {
            logEvent('Desconectando del lector...', 'info');
            
            try {
                rfid.disconnect();
                updateConnectionStatus(false);
                logEvent('Desconectado correctamente', 'success');
            } catch (error) {
                logEvent('Error al desconectar: ' + error.message, 'error');
            }
        }

        // ====== FUNCIONES DE INVENTARIO ======
        
        function startInventory() {
            if (!isConnected) {
                logEvent('Debe conectar al lector primero', 'error');
                return;
            }

            logEvent('Iniciando inventario...', 'info');
            
            try {
                // Configurar parámetros de inventario
                rfid.reportUniqueTags = document.getElementById('reportUniqueTags').value === 'true' ? 1 : 0;
                rfid.beepOnRead = document.getElementById('beepOnRead').value === 'true' ? 1 : 0;
                rfid.reportTrigger = 1; // Reportar cada tag inmediatamente
                
                // Habilitar campos necesarios en el reporte
                rfid.enableTagAntennaID = 1;
                rfid.enableTagRSSI = 1;
                rfid.enableTagSeenCount = 1;
                rfid.enableTagPC = 1;
                
                // IMPORTANTE: Registrar callback como STRING antes de iniciar
                rfid.tagEvent = "tagEventCallback(%json)";
                
                // Iniciar inventario
                rfid.performInventory();
                
                isScanning = true;
                document.getElementById('btnStartInventory').disabled = true;
                document.getElementById('btnStopInventory').disabled = false;
                document.getElementById('btnStartInventory').classList.add('scanning');
                
                logEvent('Inventario iniciado. Esperando tags...', 'success');
                
            } catch (error) {
                logEvent('Error al iniciar inventario: ' + error.message, 'error');
            }
        }

        function stopInventory() {
            logEvent('Deteniendo inventario...', 'info');
            
            try {
                rfid.stop();
                
                isScanning = false;
                document.getElementById('btnStartInventory').disabled = false;
                document.getElementById('btnStopInventory').disabled = true;
                document.getElementById('btnStartInventory').classList.remove('scanning');
                
                logEvent('Inventario detenido. Tags únicos: ' + tagsFound.size + ' | Total lecturas: ' + totalReads, 'success');
                
            } catch (error) {
                logEvent('Error al detener inventario: ' + error.message, 'error');
            }
        }

        function updateTagList() {
            var tagListDiv = document.getElementById('tagList');
            // NO borramos el contenido, solo actualizamos
            
            // Limpiar y recrear para mantener orden actualizado
            tagListDiv.innerHTML = '';
            
            // Ordenar por cantidad de veces visto
            var sortedTags = Array.from(tagsFound.entries()).sort(function(a, b) {
                return b[1].count - a[1].count;
            });
            
            for (var i = 0; i < sortedTags.length; i++) {
                var tagId = sortedTags[i][0];
                var tag = sortedTags[i][1];
                
                var tagItem = document.createElement('div');
                tagItem.className = 'tag-item';
                
                var proximityInfo = tag.relativeDistance !== undefined 
                    ? '| Proximidad: ' + tag.relativeDistance + '%' 
                    : '';
                
                tagItem.innerHTML = 
                    '<div>' +
                        '<div class="tag-id">' + tagId + '</div>' +
                        '<div class="tag-info">' +
                            'RSSI: ' + tag.rssi + ' | Antena: ' + tag.antenna + ' | ' +
                            'Visto: ' + tag.count + ' veces | Última: ' + tag.timestamp + ' ' + proximityInfo +
                        '</div>' +
                    '</div>';
                
                tagListDiv.appendChild(tagItem);
            }
        }

        function updateStats() {
            var statsDiv = document.getElementById('inventoryStats');
            statsDiv.textContent = 'Tags únicos: ' + tagsFound.size + ' | Total lecturas: ' + totalReads;
        }

        function clearTags() {
            tagsFound.clear();
            tagHistory = [];
            totalReads = 0;
            updateTagList();
            updateStats();
            logEvent('Historial de tags limpiado', 'info');
        }

        // ====== FUNCIONES DE LECTURA ======
        
        function readTag() {
            if (!isConnected) {
                logEvent('Debe conectar al lector primero', 'error');
                return;
            }

            const tagId = document.getElementById('readTagId').value.trim();
            const memBank = document.getElementById('readMemBank').value;
            const offset = document.getElementById('readOffset').value;
            const size = document.getElementById('readSize').value;

            if (!tagId) {
                logEvent('Debe especificar un Tag ID', 'error');
                return;
            }

            logEvent(`Leyendo tag ${tagId} desde ${memBank}...`, 'info');
            
            try {
                // Configurar parámetros de lectura
                rfid.tagID = tagId;
                rfid.tagMemBank = memBank;
                rfid.tagOffset = parseInt(offset);
                rfid.tagReadSize = parseInt(size);
                
                // Ejecutar lectura
                rfid.tagRead();
                
                logEvent('Comando de lectura enviado', 'success');
                
                // Simular respuesta (en dispositivo real usar tagEvent callback)
                setTimeout(() => {
                    const resultDiv = document.getElementById('readResult');
                    resultDiv.innerHTML = `
                        <div class="log-entry" style="color: #00ff00;">
                            Tag ID: ${tagId}<br>
                            Banco: ${memBank}<br>
                            Offset: ${offset}<br>
                            Datos: [Esperar callback tagEvent para datos reales]
                        </div>
                    `;
                }, 500);
                
            } catch (error) {
                logEvent('Error al leer tag: ' + error.message, 'error');
            }
        }

        // ====== FUNCIONES DE ESCRITURA ======
        
        function writeTag() {
            if (!isConnected) {
                logEvent('Debe conectar al lector primero', 'error');
                return;
            }

            const tagId = document.getElementById('writeTagId').value.trim();
            const memBank = document.getElementById('writeMemBank').value;
            const offset = document.getElementById('writeOffset').value;
            const data = document.getElementById('writeData').value.trim();
            const password = document.getElementById('writePassword').value.trim();

            if (!tagId) {
                logEvent('Debe especificar un Tag ID', 'error');
                return;
            }

            if (!data) {
                logEvent('Debe especificar datos a escribir', 'error');
                return;
            }

            logEvent(`Escribiendo en tag ${tagId}...`, 'warning');
            
            try {
                // Configurar parámetros de escritura
                rfid.tagID = tagId;
                rfid.tagMemBank = memBank;
                rfid.tagOffset = parseInt(offset);
                rfid.tagWriteData = data;
                rfid.tagPassword = password;
                
                // Ejecutar escritura
                rfid.tagWrite();
                
                logEvent('Comando de escritura enviado', 'success');
                
                // Simular respuesta
                setTimeout(() => {
                    const resultDiv = document.getElementById('writeResult');
                    resultDiv.innerHTML = `
                        <div class="log-entry" style="color: #00ff00;">
                            Escritura completada:<br>
                            Tag ID: ${tagId}<br>
                            Banco: ${memBank}<br>
                            Offset: ${offset}<br>
                            Datos escritos: ${data}
                        </div>
                    `;
                    logEvent('Escritura completada exitosamente', 'success');
                }, 500);
                
            } catch (error) {
                logEvent('Error al escribir tag: ' + error.message, 'error');
            }
        }

        // ====== FUNCIONES DE LOCALIZACIÓN ======
        
        function startLocate() {
            if (!isConnected) {
                logEvent('Debe conectar al lector primero', 'error');
                return;
            }

            var tagId = document.getElementById('locateTagId').value.trim();
            var antenna = document.getElementById('locateAntenna').value;
            var beep = document.getElementById('locateBeep').value;

            if (!tagId) {
                logEvent('Debe especificar un Tag ID a localizar', 'error');
                return;
            }

            logEvent('=== INICIANDO LOCALIZACIÓN ===', 'info');
            logEvent('Tag a buscar: ' + tagId, 'info');
            
            try {
                // Configurar parámetros de localización
                rfid.tagID = tagId;
                rfid.antennaSelected = parseInt(antenna); // Debe ser específico, no 0
                rfid.beepOnRead = beep === 'true' ? 1 : 0;
                
                // Configurar triggers para localización
                rfid.startTriggerType = 'immediate';
                rfid.stopTriggerType = 'none'; // O 'triggerRelease'
                
                // Habilitar TODOS los campos necesarios
                rfid.enableTagRSSI = 1;
                rfid.enableTagAntennaID = 1;
                rfid.enableTagSeenCount = 1;
                rfid.enableTagPC = 1;
                
                // Reportar cada tag inmediatamente
                rfid.reportTrigger = 1;
                rfid.reportUniqueTags = 0;  // Reportar todos para ver cambios de proximidad
                
                // IMPORTANTE: Registrar callback como STRING
                rfid.tagEvent = "tagEventCallback(%json)";
                
                logEvent('Parámetros configurados:', 'info');
                logEvent('  - TagID: ' + tagId, 'info');
                logEvent('  - Antena: ' + antenna, 'info');
                logEvent('  - Beep: ' + (beep === 'true' ? 'Sí' : 'No'), 'info');
                
                // Iniciar localización
                rfid.locateTag();
                
                isLocating = true;
                document.getElementById('btnStartLocate').disabled = true;
                document.getElementById('btnStopLocate').disabled = false;
                document.getElementById('proximityIndicator').style.display = 'block';
                
                logEvent('LOCALIZACIÓN INICIADA - Mueva el lector hacia el tag', 'success');
                logEvent('El beep aumentará en frecuencia cuando se acerque', 'info');
                
            } catch (error) {
                logEvent('Error al iniciar localización: ' + error.message, 'error');
            }
        }

        function stopLocate() {
            logEvent('Deteniendo localización...', 'info');
            
            try {
                rfid.stop();
                
                isLocating = false;
                document.getElementById('btnStartLocate').disabled = false;
                document.getElementById('btnStopLocate').disabled = true;
                
                logEvent('Localización detenida', 'success');
                
                // Ocultar indicador después de 2 segundos
                setTimeout(() => {
                    document.getElementById('proximityIndicator').style.display = 'none';
                }, 2000);
                
            } catch (error) {
                logEvent('Error al detener localización: ' + error.message, 'error');
            }
        }

        function updateProximity(distance, tagId) {
            var bar = document.getElementById('proximityBar');
            var text = document.getElementById('proximityText');
            var status = document.getElementById('proximityStatus');
            
            // Validar y normalizar el valor
            var normalizedDistance = parseFloat(distance);
            if (isNaN(normalizedDistance)) {
                logEvent('ADVERTENCIA: Valor de proximidad inválido: ' + distance, 'warning');
                return;
            }
            
            // Asegurar que esté en rango 0-100
            normalizedDistance = Math.max(0, Math.min(100, normalizedDistance));
            
            // Actualizar barra de progreso
            bar.style.width = normalizedDistance + '%';
            text.textContent = Math.round(normalizedDistance) + '%';
            
            // Actualizar estado textual
            if (normalizedDistance >= 80) {
                status.textContent = 'MUY CERCA! 🎯';
                status.style.color = '#38ef7d';
                status.style.fontWeight = 'bold';
            } else if (normalizedDistance >= 60) {
                status.textContent = 'Cerca 📍';
                status.style.color = '#f5576c';
                status.style.fontWeight = 'bold';
            } else if (normalizedDistance >= 40) {
                status.textContent = 'Medio 📡';
                status.style.color = '#f093fb';
                status.style.fontWeight = 'normal';
            } else if (normalizedDistance >= 20) {
                status.textContent = 'Lejos 📶';
                status.style.color = '#667eea';
                status.style.fontWeight = 'normal';
            } else {
                status.textContent = 'Muy lejos 🔍';
                status.style.color = '#999';
                status.style.fontWeight = 'normal';
            }
            
            logEvent('Proximidad actualizada: ' + Math.round(normalizedDistance) + '%', 'info');
        }

        // Calcular proximidad basada en RSSI (alternativa si no hay relativeDistance)
        function calculateProximityFromRSSI(rssi) {
            // RSSI típicamente va de -90 (lejos) a -30 (cerca)
            // Convertir a escala 0-100
            var rssiNum = parseFloat(rssi);
            if (isNaN(rssiNum)) return null;
            
            // Mapear RSSI a proximidad (ajustar estos valores según tu lector)
            var minRSSI = -90;  // Muy lejos
            var maxRSSI = -30;  // Muy cerca
            
            // Invertir y normalizar
            var proximity = ((rssiNum - minRSSI) / (maxRSSI - minRSSI)) * 100;
            proximity = Math.max(0, Math.min(100, proximity));
            
            return Math.round(proximity);
        }

        // ====== MANEJADOR DE EVENTOS DE ESTADO ======
        
        // ====== INICIALIZACIÓN ======
        
        window.onload = function() {
            logEvent('Aplicación RFD90 RFID cargada', 'success');
            logEvent('Configure el tipo de transporte y enumere los lectores', 'info');
            logEvent('NUEVO: Historial de tags se mantiene para análisis', 'info');
            logEvent('NUEVO: Función de localización por proximidad disponible', 'info');
            
            // Verificar si el objeto rfid está disponible
            if (typeof rfid === 'undefined') {
                logEvent('ADVERTENCIA: Objeto rfid no disponible. Esta aplicación requiere Zebra RhoElements/Enterprise Browser', 'warning');
                logEvent('En un entorno de desarrollo, las llamadas a rfid.* generarán errores', 'warning');
            }
        };
    </script>
</body>
</html>