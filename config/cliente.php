<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identidad del Cliente / Institución (Marca Blanca Inbox)
    |--------------------------------------------------------------------------
    |
    | Aquí se definen los datos institucionales del cliente activo para Inbox.
    | Permite que el sistema se adapte dinámicamente a la identidad institucional de CEFI
    | sin tocar el código fuente ni alterar la estética visual del sistema.
    |
    */

    'id' => env('CLIENT_ID', 'cefi'),
    'nombre' => env('CLIENT_NAME', 'CEFI'),
    'nombre_legal' => env('CLIENT_LEGAL_NAME', 'Centro de Estudios Financieros e Internacionales'),
    'siglas' => env('CLIENT_SHORT_NAME', 'CEFI'),
    'slogan' => env('CLIENT_SLOGAN', 'Excelencia y Liderazgo Académico'),

    /*
    |--------------------------------------------------------------------------
    | Canales de Contacto Oficiales
    |--------------------------------------------------------------------------
    */
    'telefono' => env('CLIENT_PHONE', '50687777849'),
    'telefono_display' => env('CLIENT_PHONE_DISPLAY', '+506 8777-7849'),
    'email_soporte' => env('CLIENT_EMAIL_SUPPORT', 'soporte@cefi.cr'),
    'email_finanzas' => env('CLIENT_EMAIL_FINANZAS', 'finanzas@cefi.cr'),
    'email_contacto' => env('CLIENT_EMAIL_CONTACT', 'info@cefi.cr'),
    'direccion' => env('CLIENT_ADDRESS', 'San José, Costa Rica'),

    /*
    |--------------------------------------------------------------------------
    | Enlaces de Plataformas Institucionales
    |--------------------------------------------------------------------------
    */
    'sitio_web' => env('CLIENT_WEBSITE_URL', 'https://cefi.cr'),
    'campus_virtual' => env('CLIENT_CAMPUS_URL', 'https://virtual.cefi.cr'),
    'moodle_key' => env('CLIENT_MOODLE_KEY', 'cefi2026'),

    /*
    |--------------------------------------------------------------------------
    | Recursos Gráficos e Identidad Visual del Cliente
    |--------------------------------------------------------------------------
    */
    'logo_url' => env('CLIENT_LOGO_URL', '/imgs/logo.png'),
    'logo_banner_whatsapp' => env('CLIENT_WA_BANNER_URL', '/imgs/fondo_defecto_notificacion.png'),
    'favicon' => env('CLIENT_FAVICON', '/favicon.ico'),

    /*
    |--------------------------------------------------------------------------
    | n8n & Orquestación de Automatizaciones por Cliente
    |--------------------------------------------------------------------------
    |
    | Webhooks específicos o router n8n para este cliente.
    |
    */
    'n8n' => [
        'webhook_base_url' => env('N8N_WEBHOOK_BASE_URL', 'https://n8n.renangalvan.net'),
        'webhook_boleta' => env('N8N_WEBHOOK_BOLETA_URL', 'https://n8n.renangalvan.net/webhook/cefi-boleta'),
        'webhook_morosidad' => env('N8N_WEBHOOK_MOROSIDAD_URL', 'https://n8n.renangalvan.net/webhook/cefi-morosidad'),
        'webhook_recordatorio' => env('N8N_WEBHOOK_RECORDATORIO_URL', 'https://n8n.renangalvan.net/webhook/cefi-recordatorio'),
        'webhook_campana' => env('N8N_WEBHOOK_CAMPANA_URL', 'https://n8n.renangalvan.net/webhook/cefi-campana'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integración WhatsApp / Evolution API
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'instance_name' => env('WHATSAPP_INSTANCE_NAME', 'cefi_whatsapp'),
        'api_url' => env('WHATSAPP_API_URL', 'http://93.127.215.91:8080'),
        'api_key' => env('WHATSAPP_API_KEY', 'RenanEvolution2026_KeySecret!'),
    ],
];
