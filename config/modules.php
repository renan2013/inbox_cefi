<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inbox BPM - Configuración Modular del Sistema (Feature Flags)
    |--------------------------------------------------------------------------
    |
    | Permite activar o desactivar módulos del sistema para instalaciones
    | On-Premise o paquetes comerciales según las características contratadas.
    |
    | Puedes sobreescribir cada módulo mediante variables en tu archivo .env
    | o modificando directamente el valor booleano en este archivo.
    |
    */

    'modules' => [
        // --- NÚCLEO Y PRODUCTIVIDAD ---
        'dashboard' => true,
        'tareas_gantt' => false,

        // --- GESTIÓN ACADÉMICA Y DOCENTE (CONTRATADO POR CEFI) ---
        'gestion_cursos' => true,
        'registro_academico' => true,
        'expedientes_360' => true,
        'moodle_sync' => true,
        'estudiante_tcu' => false,

        // --- FACTURACIÓN Y FINANZAS (CONTRATADO POR CEFI) ---
        'boletas_matricula' => true,
        'finanzas' => true,

        // --- NO CONTRATADO POR CEFI (OCULTOS / DESACTIVADOS) ---
        'inventario' => false,
        'planilla' => false,
        'soporte' => false,
        'whatsapp_n8n' => false,
        'inbox_ai' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metadatos descriptivos de los módulos
    |--------------------------------------------------------------------------
    */
    'info' => [
        'dashboard' => [
            'nombre'      => 'Dashboard & Métricas',
            'categoria'   => 'Núcleo',
            'descripcion' => 'Panel de control con indicadores de productividad y tareas.'
        ],
        'tareas_gantt' => [
            'nombre'      => 'Tareas & Gantt',
            'categoria'   => 'Núcleo',
            'descripcion' => 'Planificación colaborativa, asignaciones y cronograma Gantt.'
        ],
        'gestion_cursos' => [
            'nombre'      => 'Gestión de Cursos & Sílabos',
            'categoria'   => 'Académico',
            'descripcion' => 'Aulas activas, diseño de sílabos con IA, rúbricas y actas de notas.'
        ],
        'registro_academico' => [
            'nombre'      => 'Registro Académico & Malla Curricular',
            'categoria'   => 'Académico',
            'descripcion' => 'Planes de estudio, catálogo de carreras, asignaturas y repositorios.'
        ],
        'expedientes_360' => [
            'nombre'      => 'Expediente Digital Estudiantil 360°',
            'categoria'   => 'Académico',
            'descripcion' => 'Historial integral, récord académico, bóveda documental y firmas.'
        ],
        'moodle_sync' => [
            'nombre'      => 'Integración Moodle Bridge',
            'categoria'   => 'Integraciones',
            'descripcion' => 'Sincronización automática de calificaciones y matriculados.'
        ],
        'estudiante_tcu' => [
            'nombre'      => 'Módulo Estudiante & TCU',
            'categoria'   => 'Académico',
            'descripcion' => 'Seguimiento de bitácoras de TCU y trámites estudiantiles.'
        ],
        'boletas_matricula' => [
            'nombre'      => 'Boletas de Matrícula',
            'categoria'   => 'Finanzas',
            'descripcion' => 'Emisión formal de boletas de pago, contratos y firma digital.'
        ],
        'finanzas' => [
            'nombre'      => 'Finanzas, Morosidad & Cobros',
            'categoria'   => 'Finanzas',
            'descripcion' => 'Estados de cuenta, cálculo de mora diaria y gestión de cobro.'
        ],
        'inventario' => [
            'nombre'      => 'Logística & Control de Inventario',
            'categoria'   => 'Operaciones',
            'descripcion' => 'Catálogo de bodegas, existencias, alertas de stock mínimo y despachos.'
        ],
        'planilla' => [
            'nombre'      => 'Planilla & Asistencia QR',
            'categoria'   => 'Operaciones',
            'descripcion' => 'Marcación por credencial QR, cómputo de horas y liquidaciones.'
        ],
        'soporte' => [
            'nombre'      => 'Mesa de Ayuda & Tickets',
            'categoria'   => 'Soporte',
            'descripcion' => 'Recepción, seguimiento y resolución de averías o requerimientos.'
        ],
        'whatsapp_n8n' => [
            'nombre'      => 'Suite de WhatsApp & n8n',
            'categoria'   => 'Automatizaciones',
            'descripcion' => 'Avisos de vencimientos preventivos y cobros automáticos desatendidos.'
        ],
        'inbox_ai' => [
            'nombre'      => 'Cerebro Inbox AI 2.0',
            'categoria'   => 'Inteligencia Artificial',
            'descripcion' => 'Asistente generativo contextual basado en modelos LLM.'
        ],
    ],
];
