<?php

return [
    // Prefijo de tu empresa (7 dígitos hex)
    'company_prefix' => env('RFID_COMPANY_PREFIX', 1234567),

    // Configuración de impresora
    'printer' => [
        'ip' => env('PRINTER_IP', '192.168.0.199'),
        'port' => env('PRINTER_PORT', 9100),
        'name' => env('PRINTER_NAME', 'zebra_rfid_01'),
    ],

    // Configuración de etiquetas
    'label' => [
        'width_mm' => 74,   // milímetros
        'height_mm' => 18,  // milímetros
        'width_cm' => 7.4,  // centímetros
        'height_cm' => 1.8, // centímetros
        'dpi' => 203,       // dots per inch
    ],

    // Configuración de reintentos
    'retry' => [
        'max_attempts' => 3,
        'delay_seconds' => 10,
    ],

    // IP whitelisting para Print Agent
    'allowed_ips' => [
        '192.168.0.0/24', // Tu red local
        '127.0.0.1', // Localhost para pruebas
    ],
];