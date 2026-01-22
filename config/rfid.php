<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RFID Company Prefix (CRÍTICO)
    |--------------------------------------------------------------------------
    |
    | Este valor DEBE coincidir con lo que usan RfidLabelService y EPCGenerator
    | Clave: 'company_prefix_numeric' (NO 'company_prefix')
    |
    */
    'company_prefix_numeric' => env('RFID_COMPANY_PREFIX', 614141),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Impresora
    |--------------------------------------------------------------------------
    */
    'printer' => [
        'ip' => env('PRINTER_IP', '192.168.0.199'),
        'port' => env('PRINTER_PORT', 9100),
        'name' => env('PRINTER_NAME', 'zebra_rfid_01'),
        'model' => 'ZT411R',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Etiquetas
    |--------------------------------------------------------------------------
    */
    'label' => [
        'width_mm' => 74,
        'height_mm' => 18,
        'width_cm' => 7.4,
        'height_cm' => 1.8,
        'dpi' => 203,
        'width_dots' => 591,  // 74mm / 25.4 * 203 DPI
        'height_dots' => 144, // 18mm / 25.4 * 203 DPI
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Reintentos
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'max_attempts' => 3,
        'delay_seconds' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelisting para Print Agent
    |--------------------------------------------------------------------------
    */
    'allowed_ips' => [
        '192.168.0.0/24',
         '10.20.1.0/24',
        '127.0.0.1',
        '::1',
    ],

    /*
    |--------------------------------------------------------------------------
    | RFID Tag Settings
    |--------------------------------------------------------------------------
    */
    'tag' => [
        'protocol' => 'Gen2',
        'memory_bank' => 'EPC',
        'epc_length_bits' => 96,
        'epc_length_words' => 6,
        'start_word' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validación
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'enforce_length' => true,
        'enforce_header' => true,
        'expected_header' => '30',
        'allow_random_epcs' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => env('RFID_LOGGING_ENABLED', true),
        'log_epc_generation' => true,
        'log_zpl_commands' => true,
        'log_encoding_errors' => true,
    ],
];