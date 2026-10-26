<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reenvío de documentos rechazados
    |--------------------------------------------------------------------------
    |
    | Política de Coordinación Académica para documentos en estado
    | "Rechazado". Si es false, el estudiante no puede enviar una nueva
    | versión de un documento rechazado; si es true, se trata igual que
    | un documento "A corregir".
    |
    */

    'permitir_reenvio_rechazado' => env('DOCUMENTOS_PERMITIR_REENVIO_RECHAZADO', false),

    /*
    |--------------------------------------------------------------------------
    | Archivos permitidos
    |--------------------------------------------------------------------------
    */

    'extensiones' => ['pdf', 'doc', 'docx'],

    // Tamaño máximo en kilobytes.
    'tamano_maximo' => 5120,

];
